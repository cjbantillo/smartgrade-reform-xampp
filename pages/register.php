<?php
// pages/register.php
require_once '../config/database.php';
session_start();

// If already logged in, redirect to dashboard/index
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $lrn         = trim($_POST['lrn'] ?? '');
    $birthdate   = $_POST['birthdate'] ?? '';

    // Validation
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($lrn) || empty($birthdate)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (!preg_match('/^\d{12}$/', $lrn)) {
        $errors[] = "LRN must be exactly 12 digits.";
    }

    // Check if email or LRN already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already registered.";
    }

    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "LRN already exists.";
    }

    // If no errors, proceed to register
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into users (role = STUDENT, default school_id = 1 for now)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (school_id, email, password_hash, role, is_active) VALUES (1, ?, ?, 'STUDENT', TRUE)");
            $stmt->execute([$email, $password_hash]);
            $user_id = $pdo->lastInsertId();

            // Insert into students
            $stmt = $pdo->prepare("INSERT INTO students (user_id, lrn, first_name, last_name, birthdate, address, status) VALUES (?, ?, ?, ?, ?, '', 'active')");
            $stmt->execute([$user_id, $lrn, $first_name, $last_name, $birthdate]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - SMARTGRADE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #1e6d4a; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
        }
        button { background: #1e6d4a; color: white; padding: 12px; width: 100%; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #165c3a; }
        .error { color: red; margin: 10px 0; }
        .success { color: green; text-align: center; font-weight: bold; }
        .login-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Registration</h2>

    <?php if ($success): ?>
        <p class="success">Registration successful! You can now <a href="login.php">log in</a>.</p>
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

            <label>LRN (12 digits)</label>
            <input type="text" name="lrn" maxlength="12" pattern="\d{12}" required value="<?= htmlspecialchars($_POST['lrn'] ?? '') ?>">

            <label>Birthdate</label>
            <input type="date" name="birthdate" required value="<?= htmlspecialchars($_POST['birthdate'] ?? '') ?>">

            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Register as Student</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>