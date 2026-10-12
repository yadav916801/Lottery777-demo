<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">College Portal</a>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-light btn-sm me-2">My Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="p-5 text-center bg-white rounded-3 shadow-sm border">
        <h1 class="display-5 fw-bold text-dark">Welcome to College Management Portal</h1>
        <p class="lead text-muted mt-3">Smart management platform for Attendance, Faculty records, Student notices, and Examination reports.</p>
        <div class="mt-4">
            <a href="login.php" class="btn btn-primary btn-lg px-4 gap-3">Access Portal</a>
        </div>
    </div>
</div>
</body>
</html>
