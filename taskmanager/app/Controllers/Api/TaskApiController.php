<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;
use App\Models\TaskModel;
use CodeIgniter\API\ResponseTrait;

class TaskApiController extends BaseController
{
    use ResponseTrait;

    // GET /api/tasks - List all tasks (Admin/User)
    public function index()
    {
        $model = new TaskModel();
        return $this->respond($model->findAll());
    }

    // GET /api/tasks/{id} - Show specific task (Admin/User)
    public function show($id)
    {
        $model = new TaskModel();
        $task = $model->find($id);

        return $task
            ? $this->respond($task)
            : $this->failNotFound("Task not found");
    }

    // POST /api/tasks - Create a new task (Admin/User)
    public function create()
    {
        if (!session()->get('user_logged_in')) {
            return $this->failUnauthorized("You must be logged in to create tasks.");
        }

        $model = new TaskModel();
        $data = $this->request->getJSON(true);

        if (!$model->insert($data)) {
            return $this->failValidationErrors($model->errors());
        }

        return $this->respondCreated($data);
    }

    // PUT /api/tasks/{id} - Update task (Admin/User)
    public function update($id)
    {
        if (!session()->get('user_logged_in')) {
            return $this->failUnauthorized("You must be logged in to update tasks.");
        }

        $model = new TaskModel();
        $data = $this->request->getJSON(true);

        if (!$model->update($id, $data)) {
            return $this->failValidationErrors($model->errors());
        }

        return $this->respond(['message' => 'Task updated']);
    }

    // DELETE /api/tasks/{id} - Delete task (Admin only)
    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->failForbidden("Only Admins can delete tasks.");
        }

        $model = new TaskModel();
        $task = $model->find($id);

        if (!$task) {
            return $this->failNotFound("Task not found");
        }

        $model->delete($id);
        return $this->respondDeleted(['message' => 'Task deleted']);
    }
}
