<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$st = $db->query("SELECT * FROM users WHERE id = $student_id")->fetch(PDO::FETCH_ASSOC);
$marks = $db->query("SELECT * FROM marks WHERE student_id = $student_id")->fetchAll(PDO::FETCH_ASSOC);

$total_obtained = 0;
$total_max = count($marks) * 100;
?>

<div class="container pb-5">
    <div class="card card-custom p-4 bg-white border shadow-sm mx-auto" style="max-width: 850px;">
        <div class="text-center border-bottom pb-3 mb-3">
            <h4 class="fw-bold mb-0 text-dark">F.S. UNIVERSITY, SHIKOHABAD</h4>
            <p class="small text-muted mb-1">OFFICIAL STATEMENT OF MARKS / GRADE CARD</p>
            <span class="badge bg-dark px-3 py-1"><?= htmlspecialchars($st['course'] ?? '') ?> - <?= htmlspecialchars($st['semester'] ?? '') ?></span>
        </div>

        <div class="row small mb-3">
            <div class="col-6"><strong>Student Name:</strong> <?= htmlspecialchars($st['name']) ?></div>
            <div class="col-6 text-end"><strong>Roll No:</strong> <?= htmlspecialchars($st['roll_no']) ?></div>
            <div class="col-6"><strong>Father's Name:</strong> <?= htmlspecialchars($st['father_name'] ?? 'N/A') ?></div>
            <div class="col-6 text-end"><strong>Course Year:</strong> <?= htmlspecialchars($st['course_year'] ?? '') ?></div>
        </div>

        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-start">Subject</th>
                    <th>Theory (70)</th>
                    <th>Practical (30)</th>
                    <th>Total (100)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($marks) > 0): ?>
                    <?php foreach($marks as $m): 
                        $sub_total = $m['theory_marks'] + $m['practical_marks'];
                        $total_obtained += $sub_total;
                    ?>
                    <tr>
                        <td class="text-start fw-semibold"><?= htmlspecialchars($m['subject']) ?></td>
                        <td><?= $m['theory_marks'] ?></td>
                        <td><?= $m['practical_marks'] ?></td>
                        <td><strong><?= $sub_total ?></strong></td>
                        <td>
                            <span class="badge bg-<?= $sub_total >= 40 ? 'success' : 'danger' ?>">
                                <?= $sub_total >= 40 ? 'PASSED' : 'FAILED' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Aggregate Marks</td>
                        <td><?= $total_obtained ?> / <?= $total_max ?></td>
                        <td><?= $total_max > 0 ? round(($total_obtained / $total_max)*100, 1).'%' : 'N/A' ?></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="5" class="text-muted py-4">No examination marks uploaded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between mt-4 pt-4 border-top text-muted small">
            <div>Result Prepared By: Academic Desk</div>
            <div>Controller of Examinations</div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
