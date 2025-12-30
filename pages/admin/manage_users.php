<?php
// pages/admin/manage_users.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$success = $error = '';

// Handle add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $role        = $_POST['role'];
    $email       = trim($_POST['email']);
    $password    = $_POST['password'];
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $employee_no = trim($_POST['employee_no'] ?? '');

    if (!in_array($role, ['TEACHER', 'ADMIN'])) {
        $error = "Invalid role selected.";
    } elseif (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already in use.";
        } else {
            try {
                $pdo->beginTransaction();

                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users
                $stmt = $pdo->prepare("
                    INSERT INTO users (school_id, email, password_hash, role, is_active)
                    VALUES (?, ?, ?, ?, TRUE)
                ");
                $stmt->execute([$_SESSION['school_id'], $email, $password_hash, $role]);
                $user_id = $pdo->lastInsertId();

                // If TEACHER, insert into teachers table
                if ($role === 'TEACHER') {
                    $stmt = $pdo->prepare("
                        INSERT INTO teachers (user_id, first_name, last_name, employee_no, is_active)
                        VALUES (?, ?, ?, ?, TRUE)
                    ");
                    $stmt->execute([$user_id, $first_name, $last_name, $employee_no]);
                }

                $pdo->commit();
                $success = ucfirst(strtolower($role)) . " added successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to add user.";
            }
        }
    }
}

// Handle toggle active status
if (isset($_GET['toggle']) && isset($_GET['user_id'])) {
    $toggle_user_id = (int)$_GET['user_id'];
    $action = $_GET['toggle'] === 'deactivate' ? 0 : 1;

    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE user_id = ? AND role != 'ADMIN'");
        $stmt->execute([$action, $toggle_user_id]);
        $success = "User status updated.";
    } catch (Exception $e) {
        $error = "Cannot modify admin accounts or failed operation.";
    }
    header("Location: manage_users.php?success=1");
    exit;
}

// Search and list users
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role_filter'] ?? 'all';

$where = "u.school_id = ? AND u.role != 'STUDENT'"; // Admins usually manage teachers/admins
$params = [$_SESSION['school_id']];

if ($role_filter !== 'all') {
    $where .= " AND u.role = ?";
    $params[] = $role_filter;
}

if ($search !== '') {
    $where .= " AND (u.email LIKE ? OR t.first_name LIKE ? OR t.last_name LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.email,
        u.role,
        u.is_active,
        t.first_name,
        t.last_name,
        t.employee_no
    FROM users u
    LEFT JOIN teachers t ON u.user_id = t.user_id
    WHERE $where
    ORDER BY u.role, t.last_name, t.first_name
");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e6d4a; text-align: center; }
        .back { margin: 20px 0; text-align: center; }
        .back a { color: #1e6d4a; font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .search-form { margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap; }
        input, select, button { padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #1e6d4a; color: white; cursor: pointer; }
        button:hover { background: #165c3a; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .action-btn { padding: 6px 12px; font-size: 14px; border-radius: 4px; text-decoration: none; }
        .btn-deactivate { background: #dc3545; color: white; }
        .btn-activate { background: #28a745; color: white; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 30px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Users</h1>

    <div class="back">
        <a href="dashboard.php">← Back to Admin Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add New User Form -->
    <div class="form-section">
        <h2>Add New Teacher or Admin</h2>
        <form method="post">
            <table style="width:100%;">
                <tr>
                    <td><label>Role:</label></td>
                    <td>
                        <select name="role" required>
                            <option value="TEACHER">Teacher</option>
                            <option value="ADMIN">Admin</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Email:</label></td>
                    <td><input type="email" name="email" required style="width:100%;"></td>
                </tr>
                <tr>
                    <td><label>Password:</label></td>
                    <td><input type="password" name="password" required minlength="6" style="width:100%;"></td>
                </tr>
                <tr>
                    <td><label>First Name:</label></td>
                    <td><input type="text" name="first_name" required style="width:100%;"></td>
                </tr>
                <tr>
                    <td><label>Last Name:</label></td>
                    <td><input type="text" name="last_name" required style="width:100%;"></td>
                </tr>
                <tr>
                    <td><label>Employee No (Teacher only):</label></td>
                    <td><input type="text" name="employee_no" style="width:100%;"></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;">
                        <button type="submit" name="add_user">Add User</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Search & Filter -->
    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
        <select name="role_filter">
            <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>All Roles</option>
            <option value="ADMIN" <?= $role_filter === 'ADMIN' ? 'selected' : '' ?>>Admins</option>
            <option value="TEACHER" <?= $role_filter === 'TEACHER' ? 'selected' : '' ?>>Teachers</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <!-- Users List -->
    <h2>Current Users (Teachers & Admins)</h2>
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Employee No</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars(($user['last_name'] ?? '') . ', ' . ($user['first_name'] ?? 'N/A')) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['employee_no'] ?? '-') ?></td>
                    <td class="<?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </td>
                    <td>
                        <?php if ($user['role'] !== 'ADMIN'): // Cannot deactivate other admins ?>
                            <?php if ($user['is_active']): ?>
                                <a href="?toggle=deactivate&user_id=<?= $user['user_id'] ?>" 
                                   class="action-btn btn-deactivate"
                                   onclick="return confirm('Deactivate this user?')">Deactivate</a>
                            <?php else: ?>
                                <a href="?toggle=activate&user_id=<?= $user['user_id'] ?>" 
                                   class="action-btn btn-activate"
                                   onclick="return confirm('Reactivate this user?')">Activate</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>