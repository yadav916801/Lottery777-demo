<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$st = $db->query("SELECT * FROM users WHERE id = $student_id")->fetch(PDO::FETCH_ASSOC);
$msg = '';

// Submit Complaint with Proof Document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $category = trim($_POST['category']);
    $sub = trim($_POST['subject']);
    $desc = trim($_POST['description']);

    $proof_doc = '';
    if (isset($_FILES['proof_doc']) && $_FILES['proof_doc']['error'] === 0) {
        $ext = pathinfo($_FILES['proof_doc']['name'], PATHINFO_EXTENSION);
        $proof_doc = 'proof_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['proof_doc']['tmp_name'], __DIR__ . '/../uploads/' . $proof_doc);
    }

    $stmt = $db->prepare("INSERT INTO complaints (student_id, category, subject, description, proof_doc) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $category, $sub, $desc, $proof_doc]);
    $msg = "Aapki problem/grievance report jama ho gayi hai!";
}

$my_complaints = $db->query("SELECT * FROM complaints WHERE student_id = $student_id ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// QR Code URL (Student Roll Number encoded)
$qr_data = urlencode($st['roll_no']);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . $qr_data;
?>

<div class="container pb-5">
    <?php if($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

    <div class="row g-4">
        <!-- 1. Digital Student ID Card With Official QR Code -->
        <div class="col-md-5">
            <div class="card card-custom p-4 bg-white border text-center shadow-sm" id="idCardArea">
                <div class="bg-primary text-white py-2 rounded-top mb-3">
                    <h6 class="fw-bold mb-0">F.S. UNIVERSITY, SHIKOHABAD</h6>
                    <small style="font-size: 11px;">STUDENT IDENTITY CARD</small>
                </div>
                
                <div class="my-2">
                    <?php if(!empty($st['id_proof'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($st['id_proof']) ?>" class="rounded-circle border" style="width: 90px; height: 90px; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle display-4 text-secondary"></i>
                    <?php endif; ?>
                </div>
                
                <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($st['name']) ?></h5>
                <span class="badge bg-warning text-dark my-2">Roll No: <?= htmlspecialchars($st['roll_no']) ?></span>
                
                <div class="small text-start px-3 mt-2">
                    <div><strong>Father:</strong> <?= htmlspecialchars($st['father_name'] ?? 'N/A') ?></div>
                    <div><strong>Course:</strong> <?= htmlspecialchars($st['course'] ?? '') ?> (<?= htmlspecialchars($st['course_year'] ?? '') ?>)</div>
                    <div><strong>Semester:</strong> <?= htmlspecialchars($st['semester'] ?? '') ?></div>
                </div>

                <!-- Digital Attendance QR Code Section -->
                <div class="my-3 p-2 bg-light rounded-3 border d-inline-block">
                    <img src="<?= $qr_url ?>" alt="Student Roll No QR" style="width: 120px; height: 120px;" class="img-fluid">
                    <div class="text-muted fw-semibold mt-1" style="font-size: 10px;">SCAN FOR ATTENDANCE</div>
                </div>

                <div class="mt-2 pt-2 border-top">
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill w-100 mb-2">
                        <i class="bi bi-printer me-1"></i> Print / Download ID Card
                    </button>
                    <a href="marksheet.php" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> View Semester Result
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Helpdesk / Grievance Desk -->
        <div class="col-md-7">
            <div class="card card-custom p-4 bg-white shadow-sm">
                <h6 class="fw-bold mb-3"><i class="bi bi-question-diamond-fill text-danger me-2"></i>Student Helpdesk & Grievance Cell</h6>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="submit_complaint" value="1">
                    <div class="mb-2">
                        <label class="small fw-bold">Problem Category</label>
                        <select name="category" class="form-select form-select-sm" required>
                            <option value="Attendance Dispute">Attendance Issue</option>
                            <option value="Marks / Result Issue">Marks / Result Dispute</option>
                            <option value="Fee / Account">Fee / Examination Slip</option>
                            <option value="Profile Correction">Profile Correction</option>
                            <option value="Other">Other Academic Issue</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Subject / Title</label>
                        <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject of grievance" required>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Detailed Explanation</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Explain your issue..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Attach Proof (Photo / Document - Optional)</label>
                        <input type="file" name="proof_doc" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <button class="btn btn-danger btn-sm rounded-pill px-4">Submit Grievance</button>
                </form>

                <hr>
                <h6 class="fw-bold mb-2 small text-muted">My Submitted Grievances</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-light"><tr><th>Category</th><th>Subject</th><th>Proof</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if(count($my_complaints) > 0): ?>
                                <?php foreach($my_complaints as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['category']) ?></td>
                                    <td><?= htmlspecialchars($c['subject']) ?></td>
                                    <td>
                                        <?php if($c['proof_doc']): ?>
                                            <a href="../uploads/<?= htmlspecialchars($c['proof_doc']) ?>" target="_blank">View</a>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($c['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-muted text-center">No complaints filed.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
