<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student'])) {
    $name     = trim($_POST['st_name']);
    $father   = trim($_POST['st_father']);
    $address  = trim($_POST['st_address']);
    $roll_no  = trim($_POST['st_roll']);
    $email    = trim($_POST['st_email']);
    $course   = trim($_POST['st_course']);
    $year     = trim($_POST['st_year']);
    $sem      = trim($_POST['st_sem']);
    $password = trim($_POST['st_password']);

    $chk = $db->prepare("SELECT id FROM users WHERE email = ? OR roll_no = ?");
    $chk->execute([$email, $roll_no]);

    if ($chk->fetch()) {
        $error = "Student Roll Number ya Email pehle se registered hai!";
    } else {
        // Proof optional hai
        $st_doc = '';
        if (isset($_FILES['st_id_proof']) && $_FILES['st_id_proof']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['st_id_proof']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array($ext, $allowed)) {
                $st_doc = 'st_doc_' . time() . '_' . rand(100, 999) . '.' . $ext;
                move_uploaded_file($_FILES['st_id_proof']['tmp_name'], __DIR__ . '/../uploads/' . $st_doc);
            }
        }

        // Direct add nahi hoga, status 'pending' rahega taaki review kiya ja sake
        $stmt = $db->prepare("INSERT INTO users 
            (name, father_name, address, email, roll_no, course, course_year, semester, id_proof, password, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 'pending')");
        $stmt->execute([$name, $father, $address, $email, $roll_no, $course, $year, $sem, $st_doc, $password]);
        
        header("Location: dashboard.php?msg=student_added");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 bg-white mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-mortarboard-fill text-success me-2"></i>New Student Admission Entry
                </h5>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back to Hub
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_student" value="1">
                
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Student Full Name *</label>
                        <input type="text" name="st_name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Father's Name *</label>
                        <input type="text" name="st_father" class="form-control" placeholder="Father's full name" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small fw-bold">Full Address *</label>
                        <input type="text" name="st_address" class="form-control" placeholder="Complete resident address" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Roll / Registration Number *</label>
                        <input type="text" name="st_roll" class="form-control" placeholder="e.g. FSU202611" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Email Address *</label>
                        <input type="email" name="st_email" class="form-control" placeholder="student@example.com" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Course *</label>
                        <input type="text" name="st_course" class="form-control" placeholder="e.g. BCA / B.Tech" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Course Year *</label>
                        <select name="st_year" class="form-select" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Semester *</label>
                        <select name="st_sem" class="form-select" required>
                            <option value="1st Sem">1st Sem</option>
                            <option value="2nd Sem">2nd Sem</option>
                            <option value="3rd Sem">3rd Sem</option>
                            <option value="4th Sem">4th Sem</option>
                            <option value="5th Sem">5th Sem</option>
                            <option value="6th Sem">6th Sem</option>
                            <option value="7th Sem">7th Sem</option>
                            <option value="8th Sem">8th Sem</option>
                        </select>
                    </div>
                    <!-- Proof Document Optional -->
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Admission ID Proof / Photo (Optional)</label>
                        <input type="file" name="st_id_proof" class="form-control" accept="image/*,.pdf">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small fw-bold">Account Password *</label>
                        <input type="password" name="st_password" class="form-control" placeholder="Create password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                    <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Submit for Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
