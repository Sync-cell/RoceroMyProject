<?php


namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\ForceHTTPS;
use App\Filters\MaintenanceFilter;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'         => \CodeIgniter\Filters\CSRF::class,
        'toolbar'      => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'     => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders'=> SecureHeaders::class,
        'cors'         => Cors::class,
        'forcehttps'   => ForceHTTPS::class,
        'pagecache'    => PageCache::class,
        'performance'  => PerformanceMetrics::class,
        'maintenance'  => MaintenanceFilter::class,
        'ipblock'      => \App\Filters\IpBlockFilter::class,
       'startsession'  => \App\Filters\StartSession::class,
       'checkip'      => \App\Filters\CheckBlockedIP::class,
    ];

    public array $globals = [
       'before' => [
        'startsession',  // session always FIRST
        'checkip',      // check blocked IPs
        'ipblock',       // safe before csrf
        'maintenance',   // safe before csrf
        'csrf',          // MUST be last in "before"
    ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}