<?php
namespace App\Controllers;

use App\Models\TaskModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // Check if the user is logged in and is a normal user
        if (!session()->get('user_logged_in') || session()->get('role') !== 'user') {
            return redirect()->to('/admin/login');
        }

        $taskModel = new TaskModel();
        $userId = session()->get('user_id');

        // Only get tasks assigned to the current user
        $tasks = $taskModel->where('assigned_to', $userId)->findAll();

        // Attach accepted users for each task if needed
        foreach ($tasks as &$task) {
            $task['accepted_users'] = $taskModel->getAcceptedUsers($task['id']);
            $task['assigned_username'] = session()->get('username'); // assigned user is current user
        }

        $data['tasks'] = $tasks;

        return view('dashboard', $data); // Render the user dashboard view
    }
}
