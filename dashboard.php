<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$today_date = date('Y-m-d');

// 1. Student details safe fetch
$st = $db->prepare("SELECT * FROM users WHERE id = ?");
$st->execute([$student_id]);
$current_student = $st->fetch(PDO::FETCH_ASSOC);

if (!$current_student) {
    header("Location: ../login.php");
    exit;
}

// 2. Today's attendance status
$today_stmt = $db->prepare("SELECT status FROM attendance WHERE student_id = ? AND date = ?");
$today_stmt->execute([$student_id, $today_date]);
$today_record = $today_stmt->fetch(PDO::FETCH_ASSOC);

// 3. Overall Attendance Score
$total_classes = 0;
$total_present = 0;
$percentage = 0;

try {
    $t_stmt = $db->prepare("SELECT COUNT(*) as t FROM attendance WHERE student_id = ?");
    $t_stmt->execute([$student_id]);
    $total_classes = (int)($t_stmt->fetch(PDO::FETCH_ASSOC)['t'] ?? 0);

    $p_stmt = $db->prepare("SELECT COUNT(*) as p FROM attendance WHERE student_id = ? AND status = 'Present'");
    $p_stmt->execute([$student_id]);
    $total_present = (int)($p_stmt->fetch(PDO::FETCH_ASSOC)['p'] ?? 0);

    if ($total_classes > 0) {
        $percentage = round(($total_present / $total_classes) * 100, 1);
    }
} catch (Exception $e) {}

// 4. Attendance History Log
$history = [];
try {
    $records = $db->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 30");
    $records->execute([$student_id]);
    $history = $records->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// 5. Notices fetch
$notices = [];
try {
    $notices = $db->query("SELECT * FROM notices ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$uploads_system_path = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$has_photo = (!empty($current_student['id_proof']) && file_exists($uploads_system_path . $current_student['id_proof']));
?>

<div class="row">
    <!-- Today's Attendance Live Status Card -->
    <div class="col-12 mb-3">
        <div class="card card-custom p-3 bg-white border-start border-4 <?= $today_record ? ($today_record['status'] === 'Present' ? 'border-success' : 'border-danger') : 'border-warning' ?> shadow-sm">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Today's Attendance Status (<?= date('d M Y') ?>)</h6>
                    <?php if ($today_record): ?>
                        <span class="badge bg-<?= $today_record['status'] === 'Present' ? 'success' : 'danger' ?> fs-6 px-3 py-1 rounded-pill">
                            <i class="bi bi-<?= $today_record['status'] === 'Present' ? 'check-circle' : 'x-circle' ?> me-1"></i><?= $today_record['status'] ?>
                        </span>
                        <small class="text-muted ms-2">Faculty dwara mark kar di gayi hai.</small>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-1 rounded-pill">
                            <i class="bi bi-hourglass-split me-1"></i>Not Marked Yet
                        </span>
                        <small class="text-muted ms-2">Aaj ki attendance abhi faculty dwara nahi lagayi gayi hai.</small>
                    <?php endif; ?>
                </div>
                <i class="bi bi-calendar-check fs-2 text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Student Profile Hero Banner -->
    <div class="col-12 mb-4">
        <div class="card card-custom p-3 p-md-4 text-white shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div style="width: 75px; height: 90px; border-radius: 8px; border: 2px solid #f59e0b; overflow: hidden; background: #fff;">
                        <?php if ($has_photo): ?>
                            <img src="../uploads/<?= htmlspecialchars($current_student['id_proof']) ?>?t=<?= time() ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted" style="font-size: 11px;">
                                <i class="bi bi-person fs-3"></i>
                                <span>No Photo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col">
                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill mb-1 fw-bold" style="font-size: 11px;">Active Enrolled Student</span>
                    <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($current_student['name'] ?? 'Student') ?></h4>
                    <p class="mb-1 text-light opacity-90 small">
                        Father's Name: <strong><?= htmlspecialchars($current_student['father_name'] ?? 'N/A') ?></strong> | 
                        Roll No: <span class="badge bg-light text-dark fw-bold"><?= htmlspecialchars($current_student['roll_no'] ?? 'N/A') ?></span>
                    </p>
                    <small class="text-light opacity-75">
                        Course: <strong><?= htmlspecialchars($current_student['course'] ?? '') ?></strong> (<?= htmlspecialchars($current_student['course_year'] ?? '') ?> - <?= htmlspecialchars($current_student['semester'] ?? '') ?>)
                    </small>
                </div>
                <div class="col-12 col-md-auto text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <a href="id_card.php" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">
                        <i class="bi bi-person-badge-fill me-1"></i> My College ID Card
                    </a>
                    <a href="marksheet.php" class="btn btn-light btn-sm text-primary rounded-pill px-3 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> My Result
                    </a>
                    <a href="helpdesk.php" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-life-preserver me-1"></i> Helpdesk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Campus Notices Board -->
    <div class="col-12 mb-4">
        <div class="card card-custom p-3 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-megaphone-fill text-danger me-2"></i>Official University Notices
                </h6>
                <span class="badge bg-danger rounded-pill"><?= count($notices) ?> Circulars</span>
            </div>

            <?php if (count($notices) > 0): ?>
                <div class="row g-2">
                    <?php foreach ($notices as $nt): ?>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <strong class="text-primary"><?= htmlspecialchars($nt['title']) ?></strong>
                                    <small class="text-muted text-nowrap" style="font-size: 11px;">
                                        <?= !empty($nt['posted_date']) ? date('d M Y', strtotime($nt['posted_date'])) : '' ?>
                                    </small>
                                </div>
                                <p class="text-secondary small mb-2 mt-1" style="white-space: pre-wrap;"><?= htmlspecialchars($nt['content']) ?></p>
                            </div>
                            <?php if (!empty($nt['attachment'])): ?>
                                <div class="pt-2 border-top mt-2">
                                    <a href="../uploads/<?= htmlspecialchars($nt['attachment']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3">
                                        <i class="bi bi-paperclip me-1"></i> View / Download Document
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted small text-center mb-0 py-2">Koi naya notice uplabdh nahi hai.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attendance Performance & History -->
    <div class="col-md-4 mb-4">
        <div class="card card-custom p-4 bg-white text-center h-100 shadow-sm">
            <h6 class="text-muted mb-1">Overall Attendance Score</h6>
            <div class="display-5 fw-bold my-2 <?= $percentage >= 75 ? 'text-success' : 'text-danger' ?>">
                <?= $percentage ?>%
            </div>
            <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                <div class="progress-bar <?= $percentage >= 75 ? 'bg-success' : 'bg-danger' ?>" style="width: <?= $percentage ?>%"></div>
            </div>
            <small class="text-muted"><?= $total_present ?> Days Present out of <?= $total_classes ?> Total Days</small>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card card-custom p-3 bg-white h-100 shadow-sm">
            <h6 class="fw-bold text-dark mb-3">Recent Attendance History (Last 30 Days)</h6>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Class Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($history) > 0): ?>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['date']) ?></td>
                                <td>
                                    <span class="badge <?= $h['status'] === 'Present' ? 'bg-success' : 'bg-danger' ?> px-3 py-1 rounded-pill">
                                        <?= htmlspecialchars($h['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">No attendance recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
