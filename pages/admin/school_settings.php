<?php
// pages/admin/school_settings.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$success = $error = '';

// Fetch current school info
$stmt = $pdo->prepare("SELECT school_name, principal_name, logo_path FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

if (!$school) {
    die("School not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_name = trim($_POST['school_name']);
    $principal_name = trim($_POST['principal_name']);

    if (empty($school_name) || empty($principal_name)) {
        $error = "School name and principal name are required.";
    } else {
        try {
            $logo_path = $school['logo_path'];

            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
                } elseif ($_FILES['logo']['size'] > 2 * 1024 * 1024) { // 2MB limit
                    $error = "File too large. Max 2MB.";
                } else {
                    $upload_dir = "../../storage/logos/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $logo_path = "storage/logos/school_{$_SESSION['school_id']}_logo." . $ext;
                    move_uploaded_file($_FILES['logo']['tmp_name'], "../../" . $logo_path);
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("UPDATE schools SET school_name = ?, principal_name = ?, logo_path = ? WHERE school_id = ?");
                $stmt->execute([$school_name, $principal_name, $logo_path, $_SESSION['school_id']]);
                $success = "School settings updated successfully!";
                $school['school_name'] = $school_name;
                $school['principal_name'] = $principal_name;
                $school['logo_path'] = $logo_path;
            }
        } catch (Exception $e) {
            $error = "Failed to update settings.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Settings - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e6d4a; text-align: center; }
        .back { text-align: center; margin: 20px 0; }
        .back a { color: #1e6d4a; font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"], input[type="file"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #1e6d4a; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #165c3a; }
        .current-logo { margin: 20px 0; text-align: center; }
        .current-logo img { max-height: 150px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1>School Settings</h1>
    <div class="back">
        <a href="dashboard.php">← Back to Admin Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>School Name</label>
        <input type="text" name="school_name" value="<?= htmlspecialchars($school['school_name']) ?>" required>

        <label>Principal Name</label>
        <input type="text" name="principal_name" value="<?= htmlspecialchars($school['principal_name']) ?>" required>

        <label>School Logo (JPG, PNG, GIF - Max 2MB)</label>
        <div class="current-logo">
            <?php if ($school['logo_path'] && file_exists('../../' . $school['logo_path'])): ?>
                <p>Current Logo:</p>
                <img src="../../<?= htmlspecialchars($school['logo_path']) ?>" alt="Current Logo">
            <?php else: ?>
                <p>No logo uploaded yet.</p>
            <?php endif; ?>
        </div>
        <input type="file" name="logo" accept="image/*">

        <button type="submit">Save Settings</button>
    </form>
</div>
</body>
</html>