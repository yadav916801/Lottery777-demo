<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_teacher'])) {
    $reg_no = trim($_POST['reg_no']);
    $name = trim($_POST['name']);
    $father = trim($_POST['father_name']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $assigned_class = trim($_POST['assigned_class']);
    $subject_taught = trim($_POST['subject_taught']);

    $doc = '';
    if (isset($_FILES['doc_proof']) && $_FILES['doc_proof']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['doc_proof']['name'], PATHINFO_EXTENSION));
        $doc = 't_doc_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['doc_proof']['tmp_name'], __DIR__ . '/../uploads/' . $doc);
    }

    $chk = $db->prepare("SELECT id FROM teachers WHERE reg_no = ? OR email = ?");
    $chk->execute([$reg_no, $email]);
    if ($chk->fetch()) {
        $error = "Teacher Reg No ya Email pehle se registered hai!";
    } else {
        $ins = $db->prepare("INSERT INTO teachers (reg_no, name, father_name, address, email, password, assigned_class, subject_taught, doc_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$reg_no, $name, $father, $address, $email, $password, $assigned_class, $subject_taught, $doc]);

        $u_ins = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'faculty', 'approved')");
        $u_ins->execute([$name, $email, $password]);

        header("Location: dashboard.php?msg=teacher_added");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i>Onboard New Faculty / Teacher
                </h5>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back to Hub
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_teacher" value="1">
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Teacher Reg Number *</label>
                        <input type="text" name="reg_no" class="form-control" placeholder="e.g. FSU-T-101" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Father's Name *</label>
                        <input type="text" name="father_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small fw-bold">Address *</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Assigned Class / Batch *</label>
                        <input type="text" name="assigned_class" class="form-control" placeholder="e.g. BCA 2nd Year" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Subject Taught *</label>
                        <input type="text" name="subject_taught" class="form-control" placeholder="e.g. Database Systems" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Government ID Proof / Photo *</label>
                        <input type="file" name="doc_proof" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold">Login Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                    <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save & Enroll Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
