<?php
// index.php
require_once 'config/database.php';
session_start();

// Simple role-based greeting (will expand later)
$welcome = "Welcome to SMARTGRADE";
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'Guest';
    $welcome .= " - Logged in as " . ucfirst(strtolower($role));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMARTGRADE - Academic Records System</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: auto; }
        .nav { margin: 20px 0; }
        .nav a { margin-right: 20px; text-decoration: none; color: #0066cc; }
    </style>
</head>
<body>

<div class="container">
    <h1><?= htmlspecialchars($welcome) ?></h1>
    <p>DepEd-compliant Grading & Document Management System</p>

<div class="nav">
<?php if (isset($_SESSION['user_id'])): ?>
    <?php if ($_SESSION['role'] === 'ADMIN'): ?>
        <a href="pages/admin/dashboard.php">Admin Dashboard</a>
    <?php elseif ($_SESSION['role'] === 'TEACHER'): ?>
        <a href="pages/teacher/dashboard.php">Teacher Dashboard</a>
    <?php elseif ($_SESSION['role'] === 'STUDENT'): ?>
        <a href="pages/student/dashboard.php">My Grades & Documents</a>
    <?php endif; ?>
    <a href="pages/logout.php">Logout</a>
<?php else: ?>
    <a href="pages/login.php">Login</a>
    <a href="pages/register.php">Register (Students Only)</a>
<?php endif; ?>
</div>

    <hr>
    <p><small>Current date: December 28, 2025</small></p>
</div>

</body>
</html>