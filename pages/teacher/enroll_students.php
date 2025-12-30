<?php
// pages/teacher/enroll_students.php
require_once '../../includes/auth.php';
require_role('TEACHER');

$section_id = (int)($_GET['section_id'] ?? 0);
if ($section_id <= 0) {
    die("Invalid section.");
}

// Verify ownership
$stmt = $pdo->prepare("
    SELECT sec.name AS section_name, sy.year_label, sy.school_year_id
    FROM sections sec
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    WHERE sec.section_id = ?
      AND (sec.created_by_teacher_id = ? OR sec.adviser_teacher_id = ?)
");
$stmt->execute([$section_id, $current_user['teacher_id'], $current_user['teacher_id']]);
$section = $stmt->fetch();

if (!$section) {
    die("Access denied: You do not manage this section.");
}

// Get all section_subjects and their semester
$stmt = $pdo->prepare("
    SELECT ss.section_subject_id, sem.semester_id
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN semesters sem ON sec.school_year_id = sem.school_year_id
    WHERE ss.section_id = ?
");
$stmt->execute([$section_id]);
$section_subjects = $stmt->fetchAll();

if (empty($section_subjects)) {
    die("No subjects assigned to this section yet. Please assign subjects first.");
}

// Currently enrolled students
$stmt = $pdo->prepare("
    SELECT DISTINCT s.student_id, s.lrn, s.first_name, s.last_name
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    WHERE e.section_subject_id IN (
        SELECT section_subject_id FROM section_subjects WHERE section_id = ?
    )
    ORDER BY s.last_name, s.first_name
");
$stmt->execute([$section_id]);
$enrolled_students = $stmt->fetchAll();

// Search students (not yet enrolled)
$search = trim($_GET['search'] ?? '');
$available_students = [];

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT s.student_id, s.lrn, s.first_name, s.last_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE u.school_id = ? 
          AND (s.lrn LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)
          AND s.student_id NOT IN (
            SELECT e.student_id 
            FROM enrollments e 
            JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id 
            WHERE ss.section_id = ?
          )
        ORDER BY s.last_name, s.first_name
        LIMIT 100
    ");
    $like = "%$search%";
    $stmt->execute([$_SESSION['school_id'], $like, $like, $like, $section_id]);
    $available_students = $stmt->fetchAll();
}

$success = $error = '';

// Handle single enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student_id'])) {
    $student_id = (int)$_POST['enroll_student_id'];
    $student_ids = [$student_id]; // Treat as array for unified processing
} 
// Handle bulk enrollment
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_enroll']) && !empty($_POST['selected_students'])) {
    $student_ids = array_map('intval', $_POST['selected_students']);
} 
else {
    $student_ids = [];
}

if (!empty($student_ids)) {
    try {
        $pdo->beginTransaction();

        $enrolled_count = 0;
        foreach ($student_ids as $student_id) {
            if ($student_id <= 0) continue;

            foreach ($section_subjects as $ss) {
                $check = $pdo->prepare("
                    SELECT enrollment_id 
                    FROM enrollments 
                    WHERE student_id = ? 
                      AND section_subject_id = ? 
                      AND semester_id = ?
                ");
                $check->execute([$student_id, $ss['section_subject_id'], $ss['semester_id']]);
                if ($check->rowCount() === 0) {
                    $insert = $pdo->prepare("
                        INSERT INTO enrollments 
                        (student_id, section_subject_id, semester_id, attempt_no)
                        VALUES (?, ?, ?, 1)
                    ");
                    $insert->execute([$student_id, $ss['section_subject_id'], $ss['semester_id']]);
                    $enrolled_count++;
                }
            }
        }

        $pdo->commit();
        $count_msg = count($student_ids) == 1 ? "1 student" : count($student_ids) . " students";
        $success = "$count_msg enrolled successfully!";
        header("Location: enroll_students.php?section_id=$section_id&success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to enroll students.";
    }
}

// Handle student removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_student_id'])) {
    $remove_student_id = (int)$_POST['remove_student_id'];

    if ($remove_student_id <= 0) {
        $error = "Invalid student.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                DELETE FROM enrollments 
                WHERE student_id = ? 
                  AND section_subject_id IN (
                    SELECT section_subject_id 
                    FROM section_subjects 
                    WHERE section_id = ?
                  )
            ");
            $stmt->execute([$remove_student_id, $section_id]);

            $pdo->commit();
            $success = "Student removed from section successfully!";
            header("Location: enroll_students.php?section_id=$section_id&success=1");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to remove student.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Students - <?= htmlspecialchars($section['section_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #1e6d4a; }
        .back { margin: 20px 0; }
        .success { color: green; background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { color: red; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background: #f0f0f0; }
        .search-box { margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        input[type="text"] { padding: 10px; width: 300px; font-size: 16px; }
        .student-result { padding: 12px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
        .student-result:hover { background: #f5f5f5; }
        .btn { background: #1e6d4a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #165c3a; }
        .btn-small { padding: 6px 12px; font-size: 14px; }
        .bulk-actions { margin: 15px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Enroll Students: <?= htmlspecialchars($section['section_name']) ?></h2>
    <p><strong>School Year:</strong> <?= htmlspecialchars($section['year_label']) ?></p>

    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a> |
        <a href="manage_subjects.php?section_id=<?= $section_id ?>">← Manage Subjects</a>
    </div>

    <?php if ($_GET['success'] ?? false): ?>
        <div class="success">
            <?= $success ?: "Operation completed successfully!" ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>🔍 Search & Enroll Students</h3>
    <form method="get" class="search-box">
        <input type="hidden" name="section_id" value="<?= $section_id ?>">
        <input type="text" name="search" placeholder="Search by LRN, First Name, or Last Name" 
               value="<?= htmlspecialchars($search) ?>" autofocus>
        <button type="submit" class="btn">Search</button>
    </form>

    <?php if ($search !== ''): ?>
        <?php if (empty($available_students)): ?>
            <p>No available students found.</p>
        <?php else: ?>
            <form method="post">
                <h4>Available Students</h4>
                <div class="bulk-actions">
                    <button type="submit" name="bulk_enroll" class="btn" onclick="return confirm('Enroll all selected students?')">
                        Enroll Selected Students
                    </button>
                </div>
                <?php foreach ($available_students as $student): ?>
                    <div class="student-result">
                        <label style="cursor:pointer; flex:1;">
                            <input type="checkbox" name="selected_students[]" value="<?= $student['student_id'] ?>" style="margin-right:10px;">
                            <strong><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></strong>
                            (LRN: <?= htmlspecialchars($student['lrn']) ?>)
                        </label>
                        <button type="submit" name="enroll_student_id" value="<?= $student['student_id'] ?>" class="btn btn-small">
                            Enroll Individually
                        </button>
                    </div>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <h3>📋 Currently Enrolled Students</h3>
    <?php if (empty($enrolled_students)): ?>
        <p>No students enrolled yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>LRN</th>
                    <th>Student Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrolled_students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['lrn']) ?></td>
                    <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></td>
                    <td>
                        <button onclick="confirmRemove(<?= $student['student_id'] ?>, '<?= htmlspecialchars(addslashes($student['last_name'] . ', ' . $student['first_name']), ENT_QUOTES) ?>')"
                                class="btn" style="background:#d32f2f; padding:6px 12px; font-size:13px;">
                            Remove from Section
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Remove Confirmation Modal -->
    <div id="removeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; max-width:500px; margin:100px auto; padding:30px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
            <h3 style="color:#d32f2f;">Confirm Removal</h3>
            <p>Are you sure you want to remove <strong id="removeStudentName"></strong> from this section?</p>
            <p><small>This will remove the student from <strong>all subjects</strong> in this section.</small></p>

            <form method="post">
                <input type="hidden" name="remove_student_id" id="removeStudentId">
                <button type="submit" style="background:#d32f2f; color:white; padding:10px 20px; border:none; border-radius:5px; margin-right:10px;">
                    Yes, Remove Student
                </button>
                <button type="button" onclick="document.getElementById('removeModal').style.display='none';"
                        style="background:#666; color:white; padding:10px 20px; border:none; border-radius:5px;">
                    Cancel
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmRemove(student_id, student_name) {
    document.getElementById('removeStudentName').textContent = student_name;
    document.getElementById('removeStudentId').value = student_id;
    document.getElementById('removeModal').style.display = 'block';
}
</script>
</body>
</html>