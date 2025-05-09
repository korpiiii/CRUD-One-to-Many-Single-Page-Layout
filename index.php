<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        created_by INT NOT NULL,
        last_updated_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (last_updated_by) REFERENCES users(id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS subtasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        description TEXT NOT NULL,
        created_by INT NOT NULL,
        last_updated_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (last_updated_by) REFERENCES users(id)
    )");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Current user
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

// Handle all CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $title = trim($_POST['task_title']);
        $stmt = $pdo->prepare("INSERT INTO tasks (title, created_by, last_updated_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $current_user_id, $current_user_id]);
    }
    elseif (isset($_POST['update_task'])) {
        $title = trim($_POST['task_title']);
        $task_id = $_POST['task_id'];
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, last_updated_by = ? WHERE id = ?");
        $stmt->execute([$title, $current_user_id, $task_id]);
    }
    elseif (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $pdo->prepare("DELETE FROM tasks WHERE id = ?")->execute([$task_id]);
    }
    elseif (isset($_POST['add_subtask'])) {
        $task_id = $_POST['task_id'];
        $description = trim($_POST['subtask_description']);
        $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, description, created_by, last_updated_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$task_id, $description, $current_user_id, $current_user_id]);
    }
    elseif (isset($_POST['update_subtask'])) {
        $subtask_id = $_POST['subtask_id'];
        $description = trim($_POST['subtask_description']);
        $stmt = $pdo->prepare("UPDATE subtasks SET description = ?, last_updated_by = ? WHERE id = ?");
        $stmt->execute([$description, $current_user_id, $subtask_id]);
    }
    elseif (isset($_POST['delete_subtask'])) {
        $subtask_id = $_POST['subtask_id'];
        $pdo->prepare("DELETE FROM subtasks WHERE id = ?")->execute([$subtask_id]);
    }

    // Redirect to prevent form resubmission
    header("Location: index.php");
    exit();
}

// Get all tasks with their subtasks
$tasks = $pdo->query("
    SELECT t.*, u1.username as created_by_username, u2.username as updated_by_username
    FROM tasks t
    LEFT JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.last_updated_by = u2.id
    ORDER BY t.id DESC
")->fetchAll();

foreach ($tasks as &$task) {
    $task['subtasks'] = $pdo->query("
        SELECT s.*, u1.username as created_by_username, u2.username as updated_by_username
        FROM subtasks s
        LEFT JOIN users u1 ON s.created_by = u1.id
        LEFT JOIN users u2 ON s.last_updated_by = u2.id
        WHERE s.task_id = {$task['id']}
        ORDER BY s.id DESC
    ")->fetchAll();
}
unset($task);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .task, .subtask { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .task { background-color: #f9f9f9; }
        .subtask { background-color: #f0f0f0; margin-left: 30px; }
        .meta { font-size: 0.8em; color: #666; margin-top: 5px; }
        form { margin-top: 10px; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        button { padding: 5px 10px; margin-right: 5px; cursor: pointer; }
        .add-btn { background-color: #4CAF50; color: white; border: none; }
        .edit-btn { background-color: #2196F3; color: white; border: none; }
        .delete-btn { background-color: #f44336; color: white; border: none; }
        .logout-btn { background-color: #555; color: white; border: none; padding: 8px 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Task Manager</h1>
        <div>
            <span>Welcome, <?= htmlspecialchars($current_username) ?></span>
            <a href="login.php?logout" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Add New Task Form -->
    <div class="task">
        <h2>Add New Task</h2>
        <form method="post">
            <input type="text" name="task_title" placeholder="Task title" required>
            <button type="submit" name="add_task" class="add-btn">Add Task</button>
        </form>
    </div>

    <!-- Tasks List -->
    <?php foreach ($tasks as $task): ?>
        <div class="task">
            <?php if (isset($_GET['edit_task']) && $_GET['edit_task'] == $task['id']): ?>
                <!-- Edit Task Form -->
                <form method="post">
                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                    <input type="text" name="task_title" value="<?= htmlspecialchars($task['title']) ?>" required>
                    <button type="submit" name="update_task" class="edit-btn">Update</button>
                    <a href="index.php">Cancel</a>
                </form>
            <?php else: ?>
                <!-- Task Display -->
                <h2><?= htmlspecialchars($task['title']) ?></h2>
                <div class="meta">
                    Created by <?= htmlspecialchars($task['created_by_username']) ?> |
                    Last updated by <?= htmlspecialchars($task['updated_by_username']) ?>
                </div>
                <div>
                    <a href="index.php?edit_task=<?= $task['id'] ?>" class="edit-btn">Edit</a>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <button type="submit" name="delete_task" class="delete-btn"
                                onclick="return confirm('Delete this task and all its subtasks?')">Delete</button>
                    </form>
                </div>

                <!-- Add Subtask Form -->
                <div style="margin-top: 15px;">
                    <form method="post">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <input type="text" name="subtask_description" placeholder="Add subtask" required>
                        <button type="submit" name="add_subtask" class="add-btn">Add Subtask</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Subtasks List -->
            <?php foreach ($task['subtasks'] as $subtask): ?>
                <div class="subtask">
                    <?php if (isset($_GET['edit_subtask']) && $_GET['edit_subtask'] == $subtask['id']): ?>
                        <!-- Edit Subtask Form -->
                        <form method="post">
                            <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
                            <input type="text" name="subtask_description" value="<?= htmlspecialchars($subtask['description']) ?>" required>
                            <button type="submit" name="update_subtask" class="edit-btn">Update</button>
                            <a href="index.php?task=<?= $task['id'] ?>">Cancel</a>
                        </form>
                    <?php else: ?>
                        <!-- Subtask Display -->
                        <p><?= htmlspecialchars($subtask['description']) ?></p>
                        <div class="meta">
                            Created by <?= htmlspecialchars($subtask['created_by_username']) ?> |
                            Last updated by <?= htmlspecialchars($subtask['updated_by_username']) ?>
                        </div>
                        <div>
                            <a href="index.php?edit_subtask=<?= $subtask['id'] ?>&task=<?= $task['id'] ?>" class="edit-btn">Edit</a>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
                                <button type="submit" name="delete_subtask" class="delete-btn"
                                        onclick="return confirm('Delete this subtask?')">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
