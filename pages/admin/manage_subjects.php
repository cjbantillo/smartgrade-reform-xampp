<?php
// pages/admin/manage_subjects.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$success = $error = '';

// Handle add new subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_code = strtoupper(trim($_POST['subject_code']));
    $subject_name = trim($_POST['subject_name']);
    $units        = (float)$_POST['units'];
    $subject_type = $_POST['subject_type'];

    if (empty($subject_code) || empty($subject_name)) {
        $error = "Subject code and name are required.";
    } elseif (!in_array($subject_type, ['core', 'applied', 'specialized'])) {
        $error = "Invalid subject type.";
    } elseif ($units <= 0) {
        $error = "Units must be greater than 0.";
    } else {
        // Check duplicate code
        $stmt = $pdo->prepare("SELECT subject_id FROM subjects WHERE school_id = ? AND subject_code = ?");
        $stmt->execute([$_SESSION['school_id'], $subject_code]);
        if ($stmt->rowCount() > 0) {
            $error = "Subject code already exists.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO subjects 
                    (school_id, subject_code, subject_name, units, subject_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['school_id'], $subject_code, $subject_name, $units, $subject_type]);
                $success = "Subject added successfully!";
            } catch (Exception $e) {
                $error = "Failed to add subject.";
            }
        }
    }
}

// Handle edit subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_subject'])) {
    $subject_id   = (int)$_POST['subject_id'];
    $subject_code = strtoupper(trim($_POST['subject_code']));
    $subject_name = trim($_POST['subject_name']);
    $units        = (float)$_POST['units'];
    $subject_type = $_POST['subject_type'];

    if (empty($subject_code) || empty($subject_name)) {
        $error = "Subject code and name are required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE subjects 
                SET subject_code = ?, subject_name = ?, units = ?, subject_type = ?
                WHERE subject_id = ? AND school_id = ?
            ");
            $stmt->execute([$subject_code, $subject_name, $units, $subject_type, $subject_id, $_SESSION['school_id']]);
            $success = "Subject updated successfully!";
        } catch (Exception $e) {
            $error = "Failed to update subject.";
        }
    }
}

// Fetch all subjects
$stmt = $pdo->prepare("
    SELECT 
        subject_id,
        subject_code,
        subject_name,
        units,
        subject_type
    FROM subjects 
    WHERE school_id = ?
    ORDER BY subject_code
");
$stmt->execute([$_SESSION['school_id']]);
$subjects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e6d4a; text-align: center; }
        .back { margin: 20px 0; text-align: center; }
        .back a { color: #1e6d4a; font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .form-section { background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
        input, select { padding: 10px; width: 100%; box-sizing: border-box; border-radius: 6px; border: 1px solid #ccc; }
        .btn { background: #1e6d4a; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn:hover { background: #165c3a; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Subjects</h1>

    <div class="back">
        <a href="dashboard.php">← Back to Admin Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add New Subject -->
    <div class="form-section">
        <h2>Add New Subject</h2>
        <form method="post">
            <table>
                <tr>
                    <td><label>Subject Code:</label></td>
                    <td><input type="text" name="subject_code" placeholder="e.g., ENG12" required></td>
                </tr>
                <tr>
                    <td><label>Subject Name:</label></td>
                    <td><input type="text" name="subject_name" placeholder="e.g., English for Academic Purposes" required></td>
                </tr>
                <tr>
                    <td><label>Units:</label></td>
                    <td><input type="number" step="0.5" name="units" value="3.00" required></td>
                </tr>
                <tr>
                    <td><label>Type:</label></td>
                    <td>
                        <select name="subject_type" required>
                            <option value="core">Core</option>
                            <option value="applied">Applied</option>
                            <option value="specialized">Specialized</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;">
                        <button type="submit" name="add_subject" class="btn">Add Subject</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Current Subjects -->
    <h2>Current Subjects</h2>
    <?php if (empty($subjects)): ?>
        <p>No subjects defined yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Units</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subj): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($subj['subject_code']) ?></strong></td>
                    <td><?= htmlspecialchars($subj['subject_name']) ?></td>
                    <td><?= number_format($subj['units'], 2) ?></td>
                    <td><?= ucfirst($subj['subject_type']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>