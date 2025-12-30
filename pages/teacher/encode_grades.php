<?php
// pages/teacher/encode_grades.php
// error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
require_once '../../includes/auth.php';
require_role('TEACHER');

$section_subject_id = (int)($_GET['ss'] ?? 0);
if ($section_subject_id <= 0) {
    die("Invalid class.");
}

// Verify teacher is assigned to this section_subject
$stmt = $pdo->prepare("
    SELECT 1 
    FROM section_subjects ss
    WHERE ss.section_subject_id = ? 
      AND ss.teacher_id = ?
");
$stmt->execute([$section_subject_id, $current_user['teacher_id']]);
if ($stmt->rowCount() === 0) {
    die("Access denied: You are not assigned to teach this subject.");
}
// Fetch class details for display
$stmt = $pdo->prepare("
    SELECT 
        sec.name AS section_name,
        subj.subject_name,
        subj.subject_code,
        sem.name AS semester_name
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    JOIN semesters sem ON sec.school_year_id = sem.school_year_id
    WHERE ss.section_subject_id = ?
");
$stmt->execute([$section_subject_id]);
$class_info = $stmt->fetch();

if (!$class_info) {
    die("Class not found.");
}
// Fetch enrolled students with current grades
$stmt = $pdo->prepare("
    SELECT 
        e.enrollment_id,
        s.student_id,
        s.lrn,
        s.first_name,
        s.last_name,
        g.written_work,
        g.performance_task,
        g.quarterly_exam,
        g.final_grade,
        g.is_final
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id
    WHERE e.section_subject_id = ? AND e.semester_id = (
        SELECT semester_id FROM semesters WHERE school_year_id = 1 AND name = ?
    )
    ORDER BY s.last_name, s.first_name
");
$stmt->execute([$section_subject_id, $class_info['semester_name']]);
$students = $stmt->fetchAll();

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        foreach ($students as $student) {
            $enrollment_id = $student['enrollment_id'];
            $ww = $_POST['ww'][$enrollment_id] ?? null;
            $pt = $_POST['pt'][$enrollment_id] ?? null;
            $qe = $_POST['qe'][$enrollment_id] ?? null;
            $final = $_POST['final'][$enrollment_id] ?? null;

            // Simple average calculation (customize later)
            if ($ww !== null && $pt !== null && $qe !== null) {
                $computed_final = round(($ww + $pt + $qe) / 3, 2);
            } else {
                $computed_final = null;
            }

            if ($student['enrollment_id'] && $student['grade_id'] ?? false) {
                // Update existing
                $stmt = $pdo->prepare("
                    UPDATE grades 
                    SET written_work = ?, performance_task = ?, quarterly_exam = ?, final_grade = ?
                    WHERE enrollment_id = ?
                ");
                $stmt->execute([$ww, $pt, $qe, $computed_final, $enrollment_id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("
                    INSERT INTO grades (enrollment_id, written_work, performance_task, quarterly_exam, final_grade, is_final)
                    VALUES (?, ?, ?, ?, ?, FALSE)
                ");
                $stmt->execute([$enrollment_id, $ww, $pt, $qe, $computed_final]);
            }
        }

        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to save grades.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Encode Grades - <?= htmlspecialchars($class_info['subject_name']) ?></title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f9f9f9; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #1e6d4a; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #f0f0f0; }
        input[type="number"] { width: 80px; padding: 8px; }
        .btn { padding: 10px 20px; background: #1e6d4a; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .back { margin: 20px 0; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
<h2>Encode Grades: <?= htmlspecialchars($class_info['subject_code'] . ' - ' . $class_info['subject_name']) ?></h2>
<p><strong>Section:</strong> <?= htmlspecialchars($class_info['section_name']) ?> | 
   <strong>Semester:</strong> <?= htmlspecialchars($class_info['semester_name']) ?></p>

    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <p class="success">Grades saved successfully!</p>
    <?php endif; ?>

    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>LRN</th>
                    <th>Student Name</th>
                    <th>Written Work</th>
                    <th>Performance Task</th>
                    <th>Quarterly Exam</th>
                    <th>Final Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['lrn']) ?></td>
                    <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></td>
                    <td><input type="number" step="0.01" min="0" max="100" name="ww[<?= $student['enrollment_id'] ?>]" value="<?= htmlspecialchars($student['written_work'] ?? '') ?>"></td>
                    <td><input type="number" step="0.01" min="0" max="100" name="pt[<?= $student['enrollment_id'] ?>]" value="<?= htmlspecialchars($student['performance_task'] ?? '') ?>"></td>
                    <td><input type="number" step="0.01" min="0" max="100" name="qe[<?= $student['enrollment_id'] ?>]" value="<?= htmlspecialchars($student['quarterly_exam'] ?? '') ?>"></td>
                    <td><strong><?= htmlspecialchars($student['final_grade'] ?? '-') ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn" style="margin-top:20px; font-size:16px;">Save All Grades</button>
    </form>
</div>
</body>
</html>