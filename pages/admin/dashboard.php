<?php
// pages/admin/dashboard.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$page_title = "Admin Dashboard";

// Quick stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE status = 'active'");
$stmt->execute();
$active_students = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.user_id WHERE u.is_active = TRUE");
$stmt->execute();
$active_teachers = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM sections sec JOIN school_years sy ON sec.school_year_id = sy.school_year_id WHERE sy.is_active = TRUE");
$stmt->execute();
$active_sections = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT school_name, principal_name FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - SMARTGRADE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .container { max-width: 1100px; margin: auto; }
        h1 { color: #1e6d4a; text-align: center; }
        .nav { text-align: center; margin: 30px 0; }
        .nav a { margin: 0 15px; color: #1e6d4a; font-weight: bold; }
        .stats { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin: 40px 0; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; min-width: 200px; }
        .stat-card h3 { margin: 0; font-size: 36px; color: #1e6d4a; }
        .stat-card p { margin: 10px 0 0; color: #555; font-size: 18px; }
        .menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin: 40px 0; }
        .menu-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .menu-card h2 { color: #1e6d4a; }
        .menu-card a { display: block; margin: 15px 0; padding: 12px; background: #1e6d4a; color: white; text-decoration: none; border-radius: 8px; }
        .menu-card a:hover { background: #165c3a; }
        .school-info { background: #e8f5e8; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>Admin Dashboard</h1>
    <p><strong>School:</strong> <?= htmlspecialchars($school['school_name']) ?> | 
       <strong>Principal:</strong> <?= htmlspecialchars($school['principal_name']) ?></p>

    <div class="nav">
        <a href="../../index.php">← Home</a> |
        <a href="../adviser/document_center.php">Adviser Document Center</a> |
        <a href="school_settings.php">School Settings & Logo</a> |
        <a href="../../pages/logout.php">Logout</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h3><?= $active_students ?></h3>
            <p>Active Students</p>
        </div>
        <div class="stat-card">
            <h3><?= $active_teachers ?></h3>
            <p>Active Teachers</p>
        </div>
        <div class="stat-card">
            <h3><?= $active_sections ?></h3>
            <p>Active Sections</p>
        </div>
    </div>

    <div class="menu">
        <div class="menu-card">
            <h2>👥 User Management</h2>
            <a href="manage_users.php">Manage Users (Add/Edit/Deactivate)</a>
        </div>

        <div class="menu-card">
            <h2>📚 Academic Management</h2>
            <a href="manage_sections.php">View & Manage Sections</a>
            <a href="manage_subjects.php">Manage Subjects</a>
        </div>

        <div class="menu-card">
            <h2>🔐 Security & Logs</h2>
            <a href="audit_logs.php">View Audit Logs</a>
        </div>

        <div class="menu-card">
            <h2>🏫 School Settings</h2>
            <a href="school_settings.php">Edit School Info & Logo</a>
        </div>
    </div>

    <div class="school-info">
        <p><strong>SMARTGRADE</strong> — DepEd-Compliant Academic Records System</p>
        <p>Multi-school ready • Immutable documents • Full audit trail</p>
    </div>
</div>
</body>
</html>