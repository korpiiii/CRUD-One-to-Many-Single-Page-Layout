<?php
session_start();

if (isset($_POST['register'])) {
    require __DIR__ . '/db.php';

    // Validate inputs
    $required = ['full_name', 'age', 'book_choice', 'username', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "All fields are required!";
            header("Location: register.php");
            exit;
        }
    }

    $full_name = trim($_POST['full_name']);
    $age = (int)$_POST['age'];
    $book_choice = trim($_POST['book_choice']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users
            (full_name, age, book_choice, username, password)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $age, $book_choice, $username, $password]);

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Username already exists!";
        header("Location: register.php");
        exit;
    }
}

if (isset($_POST['login'])) {
    error_log("Login attempt started"); // Check error logs

    require __DIR__ . '/db.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    error_log("Username: $username"); // Verify input

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        error_log("User found: " . print_r($user, true));
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_full_name'] = $user['full_name'];
            error_log("Login successful, redirecting...");
            header("Location: index.php");
            exit;
        } else {
            error_log("Password verification failed");
            $_SESSION['error'] = "Invalid password!";
        }
    } else {
        error_log("User not found");
        $_SESSION['error'] = "User not found!";
    }
    header("Location: login.php");
    exit;
}
// Existing login handling remains same
?>
