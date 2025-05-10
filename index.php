<?php


require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in! Redirect failed.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = $_SESSION['user_id'];

    // Add Student
    if (isset($_POST['add_student'])) {
        $stmt = $pdo->prepare("INSERT INTO students (name, age, created_by) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['age'],
            $current_user
        ]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Add Book
    if (isset($_POST['add_book'])) {
        $stmt = $pdo->prepare("INSERT INTO books (title, student_id, created_by) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['title'],
            $_POST['student_id'],
            $current_user
        ]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Delete Student
    if (isset($_POST['delete_student'])) {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Delete Book
    if (isset($_POST['delete_book'])) {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Handle GET Requests
if (isset($_GET['action'])) {
    // Get Students
    if ($_GET['action'] == 'get_students') {
        $stmt = $pdo->query("SELECT * FROM students");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    // Get Books
    if ($_GET['action'] == 'get_books') {
        $stmt = $pdo->query("SELECT books.*, students.name AS student_name FROM books
                           JOIN students ON books.student_id = students.id");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    // Get Student List for Dropdown
    if ($_GET['action'] == 'get_student_list') {
        $stmt = $pdo->query("SELECT id, name FROM students");
        echo json_encode($stmt->fetchAll());
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Library System</a>
            <div class="d-flex">
                <span class="text-light me-3">
                    Welcome <?= htmlspecialchars($_SESSION['user_full_name'] ?? 'User') ?>
                </span>
                <a href="register.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Students Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Students</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#studentModal">
                    Add Student
                </button>
            </div>
            <div class="card-body">
                <table id="studentsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Books Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Books</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bookModal">
                    Add Book
                </button>
            </div>
            <div class="card-body">
                <table id="booksTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Student</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Modal -->
    <div class="modal fade" id="studentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="studentForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Student Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Age</label>
                            <input type="number" name="age" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Book Modal -->
    <div class="modal fade" id="bookModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bookForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Book Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Student</label>
                            <select name="student_id" class="form-select" required></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
