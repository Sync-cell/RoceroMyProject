<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
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

.container {
    width: 100%;
    max-width: 600px;
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

label {
    font-size: 16px;
    margin-bottom: 8px;
    display: block;
    color: #333;
}

input[type="text"],
input[type="date"],
select {
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
select:focus {
    border-color: #74b9ff;
    outline: none;
}

button {
    padding: 14px;
    background-color: #0984e3;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

button:hover {
    background-color: #74b9ff;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: #0984e3;
    font-weight: bold;
}

.back-link:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>

<div class="container">
    <h2>Add Task</h2>
    <form action="<?= base_url('task/store') ?>" method="post">
        <label for="title">Task Title</label>
        <input type="text" id="title" name="title" placeholder="Task Title" required>

        <label for="priority">Priority</label>
        <select id="priority" name="priority" required>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
        </select>

        <label for="deadline">Deadline</label>
        <input type="date" id="deadline" name="deadline" required>

        <label for="assigned_to">Assigned To</label>
        <input type="text" id="assigned_to" name="assigned_to" placeholder="Assigned To" required>

        <button type="submit">Save Task</button>
    </form>

    <a href="<?= base_url('tasks') ?>" class="back-link">Back to Task List</a>
</div>

</body>
</html>

