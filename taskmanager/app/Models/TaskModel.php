<?php

namespace App\Models;
use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'priority', 'deadline', 'status', 'assigned_to'];

    // Get all users who accepted a specific task
    public function getAcceptedUsers($task_id)
    {
        return $this->db->table('task_acceptances')
            ->select('users.id as user_id, users.username')
            ->join('users', 'users.id = task_acceptances.user_id')
            ->where('task_acceptances.task_id', $task_id)
            ->get()
            ->getResultArray();
    }

    // Accept a task
    public function acceptTask($task_id, $user_id)
    {
        $exists = $this->db->table('task_acceptances')
            ->where(['task_id' => $task_id, 'user_id' => $user_id])
            ->get()
            ->getRowArray();

        if (!$exists) {
            $this->db->table('task_acceptances')->insert([
                'task_id' => $task_id,
                'user_id' => $user_id
            ]);
        }
    }
}
