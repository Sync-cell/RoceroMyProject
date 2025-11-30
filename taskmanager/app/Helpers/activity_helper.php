<?php

use Config\Services;
use Config\Database;

/**
 * Records an activity into activity_logs table and updates ip_monitor.
 * Best-effort; failures are ignored so it will not break app flows.
 *
 * @param string $action
 * @param string $details
 * @return void
 */
if (!function_exists('activity_log')) {
    function activity_log(string $action, string $details = ''): void
    {
        try {
            $db = Database::connect();

            // Obtain IP address
            $ip = null;
            try {
                $request = Services::request();
                if (method_exists($request, 'getIPAddress')) {
                    $ip = $request->getIPAddress();
                }
            } catch (\Throwable $e) {
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            }
            $ip = $ip ? trim((string)$ip) : null;

            // Best-effort MAC lookup
            $mac = get_mac_for_ip($ip);

            $now = date('Y-m-d H:i:s');

            // Insert activity log (best-effort)
            try {
                $db->table('activity_logs')->insert([
                    'user_id'    => session()->get('user_id') ?? null,
                    'username'   => session()->get('username') ?? null,
                    'action'     => $action,
                    'details'    => $details,
                    'ip_address' => $ip,
                    'mac_address'=> $mac,
                    'created_at' => $now,
                ]);
            } catch (\Throwable $e) {
                // ignore activity log failures
            }

            // Upsert ip_monitor: create or update hits/last_seen/username/user_id
            if (!empty($ip)) {
                try {
                    $table = $db->table('ip_monitor');
                    $row = $table->where('ip_address', $ip)->get()->getRowArray();

                    $userId = session()->get('user_id') ?? null;
                    $username = session()->get('username') ?? null;

                    if ($row) {
                        $update = [
                            'hits'      => (int) ($row['hits'] ?? 0) + 1,
                            'last_seen' => $now,
                        ];
                        // preserve existing user info if session not set
                        if ($userId !== null) $update['user_id'] = $userId;
                        if ($username !== null) $update['username'] = $username;
                        $table->where('ip_address', $ip)->update($update);
                    } else {
                        $insert = [
                            'ip_address' => $ip,
                            'user_id'    => $userId,
                            'username'   => $username,
                            'hits'       => 1,
                            'first_seen' => $now,
                            'last_seen'  => $now,
                            'blocked'    => 0,
                        ];
                        $table->insert($insert);
                    }
                } catch (\Throwable $e) {
                    // ignore ip_monitor failures
                }
            }
        } catch (\Throwable $e) {
            // ignore overall failures
        }
    }
}

/**
 * Best-effort MAC lookup for an IP address.
 * Returns MAC as lower-case colon-separated string, or null if not found.
 *
 * Note: MAC address can only be resolved server-side if the client is on the same LAN
 * and the server's ARP/neighbour cache has an entry for that IP. This function tries
 * several methods and returns null otherwise.
 *
 * @param string|null $ip
 * @return string|null
 */
if (!function_exists('get_mac_for_ip')) {
    function get_mac_for_ip(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        $ip = trim((string)$ip);
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
            return null;
        }

        // Normalize IPv6 local addresses (don't attempt)
        if (strpos($ip, ':') !== false) {
            return null;
        }

        // 1) Try /proc/net/arp (Linux)
        if (is_readable('/proc/net/arp')) {
            try {
                $arp = @file_get_contents('/proc/net/arp');
                if ($arp !== false) {
                    $lines = explode("\n", $arp);
                    foreach ($lines as $line) {
                        if (strpos($line, $ip) === 0 || preg_match('/^' . preg_quote($ip, '/') . '\s+/', $line)) {
                            $parts = preg_split('/\s+/', trim($line));
                            // /proc/net/arp columns: IP HW_type Flags HW_address Mask Device
                            if (isset($parts[3]) && preg_match('/([0-9a-f]{2}[:\-]){5}[0-9a-f]{2}/i', $parts[3], $m)) {
                                return normalize_mac($m[0]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Helper to check exec availability
        $execAvailable = (function_exists('exec') && stripos(ini_get('disable_functions') ?: '', 'exec') === false);

        // 2) Try `ip neigh show <ip>` (Linux)
        if ($execAvailable && stripos(PHP_OS, 'WIN') === false) {
            try {
                $cmd = 'ip neigh show ' . escapeshellarg($ip) . ' 2>/dev/null';
                @exec($cmd, $out, $ret);
                if ($ret === 0 && !empty($out) && is_array($out)) {
                    $text = implode("\n", $out);
                    if (preg_match('/([0-9a-f]{2}[:\-]){5}[0-9a-f]{2}/i', $text, $m)) {
                        return normalize_mac($m[0]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 3) Try `arp -n <ip>` (Linux/Mac)
        if ($execAvailable && stripos(PHP_OS, 'WIN') === false) {
            try {
                $cmd = 'arp -n ' . escapeshellarg($ip) . ' 2>/dev/null';
                @exec($cmd, $out, $ret);
                if ($ret === 0 && !empty($out) && is_array($out)) {
                    $text = implode("\n", $out);
                    if (preg_match('/([0-9a-f]{2}[:\-]){5}[0-9a-f]{2}/i', $text, $m)) {
                        return normalize_mac($m[0]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 4) Windows: arp -a
        if ($execAvailable && stripos(PHP_OS, 'WIN') === 0) {
            try {
                @exec('arp -a', $out, $ret);
                if ($ret === 0 && !empty($out) && is_array($out)) {
                    foreach ($out as $line) {
                        if (strpos($line, $ip) !== false && preg_match('/([0-9a-f]{2}[-:]){5}[0-9a-f]{2}/i', $line, $m)) {
                            return normalize_mac($m[0]);
                        }
                    }
                    // fallback: global search
                    $text = implode("\n", $out);
                    if (preg_match('/([0-9a-f]{2}[-:]){5}[0-9a-f]{2}/i', $text, $m)) {
                        return normalize_mac($m[0]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 5) Try arp -a <ip> (generic)
        if ($execAvailable) {
            try {
                $cmd = 'arp -a ' . escapeshellarg($ip) . ' 2>/dev/null';
                @exec($cmd, $out, $ret);
                if ($ret === 0 && !empty($out) && is_array($out)) {
                    $text = implode("\n", $out);
                    if (preg_match('/([0-9a-f]{2}[:\-]){5}[0-9a-f]{2}/i', $text, $m)) {
                        return normalize_mac($m[0]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return null;
    }
}

/**
 * Normalize a MAC address string to lower-case colon-separated format: aa:bb:cc:dd:ee:ff
 *
 * @param string $raw
 * @return string
 */
if (!function_exists('normalize_mac')) {
    function normalize_mac(string $raw): string
    {
        $raw = strtolower($raw);
        $raw = str_replace('-', ':', $raw);
        $raw = preg_replace('/[^0-9a-f:]/', '', $raw);
        $parts = array_values(array_filter(explode(':', $raw), fn($p) => $p !== ''));
        $parts = array_map(fn($p) => str_pad($p, 2, '0', STR_PAD_LEFT), $parts);
        return implode(':', $parts);
    }
}
