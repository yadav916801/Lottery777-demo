<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['faculty', 'admin'])) {
    header("Location: ../login.php");
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $student_id = intval($_POST['student_id']);
    $subject = trim($_POST['subject']);
    $theory = intval($_POST['theory']);
    $practical = intval($_POST['practical']);
    $semester = trim($_POST['semester']);

    $stmt = $db->prepare("INSERT INTO marks (student_id, subject, theory_marks, practical_marks, semester) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $subject, $theory, $practical, $semester]);
    $msg = "Marks successfully published in marksheet!";
}

$students = $db->query("SELECT id, name, roll_no, course, semester FROM users WHERE role='student' AND status='approved'")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container pb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-award-fill text-warning me-2"></i>Examination Marks Entry Desk</h5>
        <a href="dashboard.php" class="btn btn-sm btn-outline-primary rounded-pill">Back to Dashboard</a>
    </div>

    <?php if($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-5 mb-3">
            <div class="card card-custom p-4 bg-white">
                <h6 class="fw-bold mb-3">Update Student Semester Marks</h6>
                <form method="POST">
                    <input type="hidden" name="save_marks" value="1">
                    <div class="mb-2">
                        <label class="small fw-bold">Select Student</label>
                        <select name="student_id" class="form-select form-select-sm" required>
                            <?php foreach($students as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?> (Roll: <?= htmlspecialchars($st['roll_no']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Subject Name</label>
                        <input type="text" name="subject" class="form-control form-control-sm" placeholder="e.g. Engineering Mathematics" required>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Semester</label>
                        <input type="text" name="semester" class="form-control form-control-sm" placeholder="e.g. 3rd Sem" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small fw-bold">Theory (Out of 70)</label>
                            <input type="number" name="theory" max="70" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Practical (Out of 30)</label>
                            <input type="number" name="practical" max="30" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <button class="btn btn-success btn-sm w-100 rounded-pill">Submit & Publish Marks</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
