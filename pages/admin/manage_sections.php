<?php
// pages/admin/manage_sections.php
require_once '../../includes/auth.php';
require_role('ADMIN');

$success = $error = '';

// Handle adviser change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_adviser'])) {
    $section_id = (int)$_POST['section_id'];
    $new_adviser_id = (int)$_POST['new_adviser_id'];

    if ($section_id <= 0) {
        $error = "Invalid section.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE sections SET adviser_teacher_id = ? WHERE section_id = ?");
            $stmt->execute([$new_adviser_id ?: null, $section_id]);
            $success = "Adviser updated successfully!";
        } catch (Exception $e) {
            $error = "Failed to update adviser.";
        }
    }
    header("Location: manage_sections.php?success=1");
    exit;
}

// Fetch all teachers for dropdown
$stmt = $pdo->prepare("
    SELECT t.teacher_id, t.first_name, t.last_name
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
    WHERE u.school_id = ? AND u.is_active = TRUE
    ORDER BY t.last_name, t.first_name
");
$stmt->execute([$_SESSION['school_id']]);
$teachers = $stmt->fetchAll();

// Fetch all sections in active school year
$stmt = $pdo->prepare("
    SELECT 
        sec.section_id,
        sec.name AS section_name,
        sec.created_by_teacher_id,
        t1.first_name AS creator_first,
        t1.last_name AS creator_last,
        t2.teacher_id AS adviser_id,
        t2.first_name AS adviser_first,
        t2.last_name AS adviser_last,
        (SELECT COUNT(DISTINCT e.student_id) 
         FROM enrollments e 
         JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id 
         WHERE ss.section_id = sec.section_id) AS student_count,
        (SELECT COUNT(*) 
         FROM section_subjects ss 
         WHERE ss.section_id = sec.section_id) AS subject_count
    FROM sections sec
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    LEFT JOIN teachers t1 ON sec.created_by_teacher_id = t1.teacher_id
    LEFT JOIN teachers t2 ON sec.adviser_teacher_id = t2.teacher_id
    WHERE sy.is_active = TRUE
    ORDER BY sec.name
");
$stmt->execute();
$sections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1e6d4a; text-align: center; }
        .back { margin: 20px 0; text-align: center; }
        .back a { color: #1e6d4a; font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
        .adviser-current { font-weight: bold; color: #1e6d4a; }
        select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .btn { background: #1e6d4a; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; border: none; cursor: pointer; }
        .btn:hover { background: #165c3a; }
        .no-adviser { color: #999; font-style: italic; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Sections</h1>

    <div class="back">
        <a href="dashboard.php">← Back to Admin Dashboard</a>
    </div>

    <?php if ($_GET['success'] ?? false): ?>
        <div class="success">Operation completed successfully!</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h2>Active Sections (2024-2025)</h2>
    <?php if (empty($sections)): ?>
        <p>No sections found for the active school year.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Created By</th>
                    <th>Current Adviser</th>
                    <th>Students</th>
                    <th>Subjects</th>
                    <th>Change Adviser</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sections as $sec): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sec['section_name']) ?></strong></td>
                    <td>
                        <?= $sec['creator_last'] ? htmlspecialchars($sec['creator_last'] . ', ' . $sec['creator_first']) : '<em>Unknown</em>' ?>
                    </td>
                    <td class="adviser-current">
                        <?= $sec['adviser_last'] 
                            ? htmlspecialchars($sec['adviser_last'] . ', ' . $sec['adviser_first'])
                            : '<span class="no-adviser">No adviser assigned</span>' ?>
                    </td>
                    <td><?= (int)$sec['student_count'] ?></td>
                    <td><?= (int)$sec['subject_count'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="section_id" value="<?= $sec['section_id'] ?>">
                            <select name="new_adviser_id">
                                <option value="">-- No Adviser --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['teacher_id'] ?>" 
                                        <?= $sec['adviser_id'] == $t['teacher_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="change_adviser" class="btn" style="font-size:13px; padding:6px 12px;">
                                Update
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>