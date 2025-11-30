<!DOCTYPE html>
<html>
<head>
    <title>Task List</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #74ebd5, #ACB6E5);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 40px;
        box-sizing: border-box;
      }

      .container {
        width: 90%;
        max-width: 1000px;
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      }

      h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
      }

      a {
        color: #0984e3;
        text-decoration: none;
        font-weight: bold;
      }

      a:hover {
        text-decoration: underline;
      }

      /* Add Task Link */
      .container > a:first-of-type {
        display: inline-block;
        margin-bottom: 20px;
        background-color: #0984e3;
        color: white;
        padding: 10px 16px;
        border-radius: 6px;
        text-decoration: none;
      }

      .container > a:first-of-type:hover {
        background-color: #74b9ff;
      }

      /* Logout Button */
      .logout-button {
        float: right;
        background-color: #d63031;
        color: white;
        padding: 10px 16px;
        border-radius: 6px;
        text-decoration: none;
      }

      .logout-button:hover {
        background-color: #e17055;
      }

      /* Upload Section */
      .upload-section {
        margin-bottom: 25px;
        padding: 15px;
        background-color: #f0f6ff;
        border-radius: 8px;
        border: 1px solid #dce3f0;
      }

      .upload-section label {
        font-weight: bold;
        display: block;
        margin-bottom: 10px;
        color: #333;
      }

      .upload-section input[type="file"] {
        margin-bottom: 15px;
      }

      .upload-section button {
        background-color: #00b894;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease;
      }

      .upload-section button:hover {
        background-color: #55efc4;
      }

      /* Table Styling */
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
      }

      th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
      }

      th {
        background-color: #0984e3;
        color: white;
      }

      td {
        background-color: #fafafa;
      }

      /* Responsive */
      @media (max-width: 768px) {
        body {
          padding: 20px;
        }

        .container {
          padding: 20px;
        }

        .logout-button,
        .container > a:first-of-type {
          width: 100%;
          display: block;
          text-align: center;
          margin: 10px 0;
        }

        table, thead, tbody, th, td, tr {
          display: block;
        }

        tr {
          margin-bottom: 15px;
          background-color: #f9f9f9;
          border-radius: 8px;
          padding: 12px;
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        th {
          display: none;
        }

        td {
          padding: 10px;
          border: none;
          position: relative;
          padding-left: 50%;
        }

        td::before {
          position: absolute;
          top: 10px;
          left: 10px;
          width: 45%;
          white-space: nowrap;
          font-weight: bold;
          color: #333;
        }

        td:nth-of-type(1)::before { content: "Title"; }
        td:nth-of-type(2)::before { content: "Priority"; }
        td:nth-of-type(3)::before { content: "Deadline"; }
        td:nth-of-type(4)::before { content: "Status"; }
        td:nth-of-type(5)::before { content: "Assigned To"; }
        td:nth-of-type(6)::before { content: "Actions"; }
      }
    </style>
</head>
<body>

<div class="container">

  <?php if (session()->getFlashdata('error')): ?>
    <div style="color: #d63031; margin-bottom: 15px; text-align: center; font-weight: bold;">
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <h2>Tasks</h2>

  <!-- ✅ Upload Form Section -->
  <div class="upload-section">
    <form action="<?= base_url('upload') ?>" method="post" enctype="multipart/form-data">
      <label>Select a picture to upload (JPG, PNG, PDF):</label>
      <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
      <button type="submit">Upload Picture</button>
    </form>

    <?php if (session()->getFlashdata('success')): ?>
      <p style="color:green; margin-top: 10px;"><?= session()->getFlashdata('success') ?></p>
    <?php elseif (session()->getFlashdata('error')): ?>
      <p style="color:red; margin-top: 10px;"><?= session()->getFlashdata('error') ?></p>
    <?php endif; ?>
  </div>

  <!-- Add Task Link -->
  <a href="<?= base_url('task/create') ?>">+ Add Task</a>

  <!-- Logout Button -->
  <a href="<?= base_url('admin/logout') ?>" class="logout-button">Logout</a>

  <!-- Task Table -->
  <table>
    <tr>
      <th>Title</th>
      <th>Priority</th>
      <th>Deadline</th>
      <th>Status</th>
      <th>Assigned To</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($tasks as $task): ?>
    <tr>
      <td><?= esc($task['title']) ?></td>
      <td><?= esc($task['priority']) ?></td>
      <td><?= esc($task['deadline']) ?></td>
      <td><?= esc($task['status']) ?></td>
      <td><?= esc($task['assigned_to']) ?></td>
      <td>
        <a href="<?= base_url('task/edit/' . $task['id']) ?>">Edit</a> |
        <a href="<?= base_url('task/delete/' . $task['id']) ?>" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

</body>
</html>
