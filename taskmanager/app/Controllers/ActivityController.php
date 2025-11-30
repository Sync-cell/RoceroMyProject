<?php

namespace App\Controllers;

use Config\Database;

class ActivityController extends BaseController
{
    public function index()
    {
        if (!session()->get('user_logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/admin/login')->with('error', 'Access denied.');
        }

        $db = Database::connect();

        // Get filters from query string
        $filterRole = trim((string) $this->request->getGet('role'));
        $searchQ = trim((string) $this->request->getGet('q'));

        // Detect users table schema at runtime to avoid unknown column errors
        $usersFields = [];
        try {
            $usersFields = $db->getFieldNames('users');
        } catch (\Throwable $e) {
            $usersFields = [];
        }

        $userPk      = in_array('user_id', $usersFields, true) ? 'user_id' : (in_array('id', $usersFields, true) ? 'id' : null);
        $usernameCol = in_array('username', $usersFields, true) ? 'username' : (in_array('name', $usersFields, true) ? 'name' : null);
        $hasRoleCol  = in_array('role', $usersFields, true);

        // Build select dynamically based on detected columns
        $select = 'activity_logs.*';
        if ($userPk) {
            $select .= ", users.{$userPk} AS users_user_id";
        }
        if ($usernameCol) {
            $select .= ", users.{$usernameCol} AS users_username";
        }
        if ($hasRoleCol) {
            $select .= ", users.role AS user_role";
        }

        $builder = $db->table('activity_logs')->select($select);

        // Only join if we detected a usable PK column on users
        if ($userPk) {
            $builder->join('users', "users.{$userPk} = activity_logs.user_id", 'left');
        }

        // Apply role filter only if users.role column exists
        if ($hasRoleCol && in_array($filterRole, ['admin', 'user'], true)) {
            $builder->where('users.role', $filterRole);
        }

        if ($searchQ !== '') {
            $builder->groupStart()
                    ->like('activity_logs.username', $searchQ);

            if ($usernameCol) {
                $builder->orLike("users.{$usernameCol}", $searchQ);
            }

            $builder->orLike('activity_logs.details', $searchQ)
                    ->groupEnd();
        }

        $builder->orderBy('activity_logs.created_at', 'DESC');

        $data['logs'] = $builder->get()->getResultArray() ?: [];
        $data['filterRole'] = $filterRole;
        $data['searchQ'] = $searchQ;

        return view('admin/activity_logs', $data);
    }
}
