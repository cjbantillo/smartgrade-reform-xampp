<?php
// pages/teacher/create_section.php
require_once '../../includes/auth.php';
require_role('TEACHER');

$errors = [];
$success = false;
$section_name = '';

// Get active school year
$stmt = $pdo->prepare("SELECT school_year_id, year_label FROM school_years WHERE school_id = ? AND is_active = TRUE");
$stmt->execute([$_SESSION['school_id']]);
$active_year = $stmt->fetch();

if (!$active_year) {
    die("No active school year found. Contact administrator.");
}

// Ensure teacher has a teacher_id
if (empty($current_user['teacher_id'])) {
    die("Teacher profile not properly linked. Contact administrator.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = trim($_POST['section_name'] ?? '');

    if (empty($section_name)) {
        $errors[] = "Section name is required.";
    }
 // Check for duplicate (will also be enforced by DB)
$stmt = $pdo->prepare("SELECT section_id FROM sections WHERE school_year_id = ? AND LOWER(name) = LOWER(?)");
$stmt->execute([$active_year['school_year_id'], $section_name]);
if ($stmt->rowCount() > 0) {
    $errors[] = "A section with this name already exists for this school year.";
}

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO sections 
                (school_year_id, name, created_by_teacher_id, adviser_teacher_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $active_year['school_year_id'],
                $section_name,
                $current_user['teacher_id'],
                $current_user['teacher_id']  // Creator becomes adviser by default
            ]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to create section. Error: " . $e->getMessage(); // Remove in production
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Section - Teacher</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #1e6d4a; text-align: center; }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background: #1e6d4a; color: white; padding: 14px; width: 100%; border: none; border-radius: 5px; margin-top: 20px; font-size: 16px; cursor: pointer; }
        button:hover { background: #165c3a; }
        .back { text-align: center; margin-top: 20px; }
        .error { color: red; background: #ffe6e6; padding: 12px; border-radius: 5px; margin: 15px 0; }
        .success { color: green; background: #e6ffe6; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Create New Section</h2>

    <div class="info">
        <strong>School Year:</strong> <?= htmlspecialchars($active_year['year_label']) ?><br>
        <strong>Adviser:</strong> You will automatically become the adviser.
    </div>

    <?php if ($success): ?>
        <div class="success">
            Section "<?= htmlspecialchars($section_name) ?>" created successfully!<br>
            You can now assign subjects and enroll students.
        </div>
        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Section Name</label>
            <input type="text" name="section_name" placeholder="e.g., Grade 11 - Curie" required 
                   value="<?= htmlspecialchars($section_name) ?>">

            <button type="submit">Create Section</button>
        </form>

        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>