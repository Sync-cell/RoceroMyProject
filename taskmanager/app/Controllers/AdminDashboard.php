<?php
namespace App\Controllers;

use App\Models\TaskModel;

class AdminDashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('user_logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/admin/login');
        }

        $taskModel = new TaskModel();
        $data['tasks'] = $taskModel->findAll();

        return view('admin/dashboard', $data);
    }
}
