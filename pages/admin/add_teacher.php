<?php
// pages/admin/add_teacher.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $employee_no = trim($_POST['employee_no'] ?? '');

    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $errors[] = "Email, password, first name, and last name are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already in use.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user as TEACHER
            $stmt = $pdo->prepare("INSERT INTO users (school_id, email, password_hash, role, is_active) VALUES (?, ?, ?, 'TEACHER', TRUE)");
            $stmt->execute([$_SESSION['school_id'], $email, $password_hash]);
            $user_id = $pdo->lastInsertId();

            // Insert teacher profile
            $stmt = $pdo->prepare("INSERT INTO teachers (user_id, first_name, last_name, employee_no, is_active) VALUES (?, ?, ?, ?, TRUE)");
            $stmt->execute([$user_id, $first_name, $last_name, $employee_no]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to create teacher. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #1e6d4a; text-align: center; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
        }
        button { background: #1e6d4a; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; margin-top: 20px; cursor: pointer; }
        .back { text-align: center; margin-top: 20px; }
        .error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .success { color: green; background: #e6ffe6; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New Teacher</h2>

    <?php if ($success): ?>
        <div class="success">Teacher successfully created!</div>
        <div class="back"><a href="index.php">← Back to Admin Dashboard</a></div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>First Name</label>
            <input type="text" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">

            <label>Last Name</label>
            <input type="text" name="last_name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">

            <label>Employee No (Optional)</label>
            <input type="text" name="employee_no" value="<?= htmlspecialchars($_POST['employee_no'] ?? '') ?>">

            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password (min 6 chars)</label>
            <input type="password" name="password" required minlength="6">

            <button type="submit">Create Teacher Account</button>
        </form>

        <div class="back">
            <a href="index.php">← Back to Admin Dashboard</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>