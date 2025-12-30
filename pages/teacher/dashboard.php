<?php
// pages/teacher/dashboard.php
require_once '../../includes/auth.php';
require_role('TEACHER');

$page_title = "Teacher Dashboard";

// Fetch teacher's assigned section_subjects for the active school year
$school_year_id = 1; // From seed data: 2024-2025 is active (we'll make dynamic later)

$stmt = $pdo->prepare("
    SELECT 
        ss.section_subject_id,
        sec.name AS section_name,
        subj.subject_code,
        subj.subject_name,
        sem.name AS semester_name
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    JOIN semesters sem ON sec.school_year_id = sem.school_year_id
    WHERE ss.teacher_id = ? 
      AND sec.school_year_id = ?
    ORDER BY sem.sort_order, sec.name, subj.subject_name
");
// Get teacher_id from teachers table
$stmt_teacher = $pdo->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
$stmt_teacher->execute([$_SESSION['user_id']]);
$teacher = $stmt_teacher->fetch();

if (!$teacher) {
    $assigned_classes = [];
} else {
    $stmt->execute([$teacher['teacher_id'], $school_year_id]);
    $assigned_classes = $stmt->fetchAll();
}

// Also check if teacher is adviser of any section
$stmt = $pdo->prepare("
    SELECT sec.name AS section_name
    FROM sections sec
    WHERE sec.adviser_teacher_id = ?
      AND sec.school_year_id = ?
");
$stmt->execute([$current_user['user_id'], $school_year_id]);
$advised_sections = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - SMARTGRADE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 1000px; margin: auto; }
        h1 { color: #1e6d4a; }
        .card { background: white; padding: 25px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
        .btn { display: inline-block; padding: 8px 16px; background: #1e6d4a; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; }
        .btn:hover { background: #165c3a; }
        .nav { margin: 20px 0; }
        .nav a { margin-right: 15px; }
        td .btn + .btn {
            margin-left: 8px;
            }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($current_user['first_name'] ?? 'Teacher') ?></h1>
    
    <div class="nav">
        <a href="../../index.php">← Home</a> |
        <a href="../adviser/document_center.php">Document Center (Generate SF9)</a> |
        <a href="../../pages/logout.php">Logout</a>

    </div>

    <?php if (!empty($advised_sections)): ?>
    <div class="card">
        <h2>📌 You are Adviser of:</h2>
        <ul>
            <?php foreach ($advised_sections as $sec): ?>
                <li><strong><?= htmlspecialchars($sec['section_name']) ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
<div class="card">
    <h2>➕ Class Management</h2>
    <p><a href="create_section.php" class="btn">Create New Section</a></p>
    <p><small>You will become the adviser by default.</small></p>
</div>
                <div class="card">
    <h2>📋 My Sections (As Adviser/Creator)</h2>
    
    <?php
    // Get sections where teacher is creator OR adviser
    $stmt = $pdo->prepare("
        SELECT 
            sec.section_id,
            sec.name AS section_name,
            sy.year_label
        FROM sections sec
        JOIN school_years sy ON sec.school_year_id = sy.school_year_id
        WHERE (sec.created_by_teacher_id = ? OR sec.adviser_teacher_id = ?)
          AND sy.is_active = TRUE
        ORDER BY sec.name
    ");
    $stmt->execute([$current_user['teacher_id'], $current_user['teacher_id']]);
    $my_sections = $stmt->fetchAll();
    ?>

    <?php if (empty($my_sections)): ?>
        <p>You are not advising or have not created any sections yet.</p>
        <p><a href="create_section.php" class="btn">Create Your First Section</a></p>
    <?php else: ?>
        <table>
         <thead>
             <tr>
                 <th>Section</th>
                 <th>School Year</th>
                 <th>Subjects</th>
                <th>Students</th>
            </tr>
        </thead>
            <tbody>
                <?php foreach ($my_sections as $sec): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sec['section_name']) ?></strong></td>
                    <td><?= htmlspecialchars($sec['year_label']) ?></td>
                    <td>
                    <a href="../teacher/manage_subjects.php?section_id=<?= $sec['section_id'] ?>" class="btn">
                        Manage Subjects
                    </a>
                </td>
                <td>
                   <a href="../teacher/enroll_students.php?section_id=<?= $sec['section_id'] ?>" class="btn">
                        Enroll Students
                    </a>
                </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<div class="card">
    <h2>📚 Your Assigned Classes (2024-2025)</h2>
    
    <?php if (empty($assigned_classes)): ?>
        <p>No classes assigned yet.</p>
    <?php else: ?>
        <?php
        // Group by section and semester
        $grouped = [];
        foreach ($assigned_classes as $class) {
            $key = $class['section_name'] . ' | ' . $class['semester_name'];
            $grouped[$key]['section'] = $class['section_name'];
            $grouped[$key]['semester'] = $class['semester_name'];
            $grouped[$key]['subjects'][] = [
                'code' => $class['subject_code'],
                'name' => $class['subject_name'],
                'ss_id' => $class['section_subject_id']
            ];
        }
        ?>

        <?php foreach ($grouped as $group): ?>
            <div style="margin-bottom: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin:0 0 10px 0; color:#1e6d4a;">
                    <?= htmlspecialchars($group['section']) ?> — <?= htmlspecialchars($group['semester']) ?>
                </h3>
                <ul style="margin:0; padding-left:20px;">
                    <?php foreach ($group['subjects'] as $subj): ?>
                        <li style="margin:8px 0;">
                            <strong><?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?></strong>
                            <a href="../teacher/encode_grades.php?ss=<?= $subj['ss_id'] ?>" class="btn">
                                Encode Grades
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</body>
</html>