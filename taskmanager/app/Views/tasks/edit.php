<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <style>
       body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #74ebd5, #ACB6E5);
    margin: 0;
    padding: 40px 20px;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    box-sizing: border-box;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}

form {
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
}

label {
    font-size: 16px;
    margin-bottom: 8px;
    display: block;
    color: #333;
}

input[type="text"],
input[type="date"],
select,
textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    box-sizing: border-box;
    transition: border 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
select:focus,
textarea:focus {
    border-color: #74b9ff;
    outline: none;
}

textarea {
    resize: vertical;
}

input[type="submit"] {
    background-color: #0984e3;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

input[type="submit"]:hover {
    background-color: #74b9ff;
}

a {
    text-decoration: none;
    color: #0984e3;
    font-weight: bold;
    display: block;
    text-align: center;
    margin-top: 20px;
}

a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>

    <h2>Edit Task</h2>

    <form method="post" action="<?= base_url('task/update/' . $task['id']) ?>">
        <?= csrf_field(); ?>
        
        <label for="title">Title:</label>
        <input type="text" name="title" value="<?= old('title', $task['title']); ?>" required>

        <label for="description">Description:</label>
        <textarea name="description"><?= old('description', $task['description']); ?></textarea>

        <label for="priority">Priority:</label>
        <select name="priority" required>
            <option value="low" <?= $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
            <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
            <option value="high" <?= $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
        </select>

        <label for="deadline">Deadline:</label>
        <input type="date" name="deadline" value="<?= old('deadline', $task['deadline']); ?>" required>

        <label for="status">Status:</label>
        <select name="status">
            <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
        </select>

        <label for="assigned_to">Assigned To:</label>
        <input type="text" name="assigned_to" value="<?= old('assigned_to', $task['assigned_to']); ?>">

        <input type="submit" value="Save Changes">
    </form>

    <a href="<?= base_url('tasks') ?>">← Back</a>


</body>
</html>
