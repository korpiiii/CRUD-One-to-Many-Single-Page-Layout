<?php require __DIR__ . '/includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .registration-container {
            max-width: 600px;
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2 class="text-center mb-4">Library Registration</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']) ?></div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Book of Choice</label>
                <input type="text" name="book_choice" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="register" class="btn btn-primary btn-lg">Register</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
