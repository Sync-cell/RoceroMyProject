<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    // Show login form
    public function login()
    {
        return view('auth/login');
    }

    // Show registration form
    public function register()
    {
        return view('auth/register');
    }

    // Store new user
    public function store()
    {
        helper('activity');

        $model = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $role     = $this->request->getPost('role');

        // Check if username exists
        if ($model->where('username', $username)->first()) {
            return redirect()->back()->with('error', 'Username already exists.');
        }

        // Save new user
        $model->save([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role'     => $role
        ]);

        activity_log('Register', 'Registered new user: ' . $username);

        return redirect()->to('/admin/login')->with('success', 'Registered! Please log in.');
    }

    // Authenticate login
    public function authenticate()
    {
        helper('activity');

        $model = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Find user by username
        $user = $model->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {
            // Set session with role info
            session()->set([
                'user_logged_in' => true,
                'username'       => $user['username'],
                'user_id'        => $user['id'],
                'role'           => $user['role']
            ]);

            activity_log('Login', 'User logged in: ' . $user['username']);

            // Redirect based on role
            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }

        // Invalid credentials
        return redirect()->back()->with('error', 'Invalid credentials');
    }

    // Logout user
    public function logout()
    {
        helper('activity');

        $username = session()->get('username') ?? 'unknown';
        activity_log('Logout', 'User logged out: ' . $username);

        // Destroy the session data to log out
        session()->destroy();

        // Redirect to login page after logout
        return redirect()->to('/admin/login');
    }
}