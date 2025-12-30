<?php
// pages/admin/index.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$page_title = "Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - SMARTGRADE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 900px; margin: auto; }
        h1 { color: #1e6d4a; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 12px 20px; background: #1e6d4a; color: white; text-decoration: none; border-radius: 5px; margin: 5px 0; }
        .btn:hover { background: #165c3a; }
        .nav { margin: 20px 0; }
        .nav a { margin-right: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, Admin (<?= htmlspecialchars($current_user['school_name']) ?>)</h1>
    
    <div class="nav">
        <a href="../../index.php">← Back to Home</a> |
        <a href="../../pages/logout.php">Logout</a>
    </div>

    <div class="card">
        <h2>Admin Tools</h2>
        <p><a href="add_teacher.php" class="btn">➕ Add New Teacher</a></p>
        <p><a href="manage_users.php" class="btn">👥 Manage Users (Coming Soon)</a></p>
        <p><a href="school_settings.php" class="btn">🏫 School Settings (Coming Soon)</a></p>
    </div>

    <div class="card">
        <h2>Quick Stats</h2>
        <?php
        $stmt = $pdo->query("SELECT COUNT(*) AS total_students FROM students");
        $students = $stmt->fetch()['total_students'];

        $stmt = $pdo->query("SELECT COUNT(*) AS total_teachers FROM teachers");
        $teachers = $stmt->fetch()['total_teachers'];
        ?>
        <p>Students: <strong><?= $students ?></strong></p>
        <p>Teachers: <strong><?= $teachers ?></strong></p>
    </div>
</div>
</body>
</html>