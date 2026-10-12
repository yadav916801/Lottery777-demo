<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $name         = trim($_POST['name']);
    $father_name  = trim($_POST['father_name']);
    $address      = trim($_POST['address']);
    $roll_no      = trim($_POST['roll_no']);
    $course       = trim($_POST['course']);
    $course_year  = trim($_POST['course_year']);
    $semester     = trim($_POST['semester']);
    $email        = trim($_POST['email']);
    $password     = trim($_POST['password']);

    // Check duplicate roll number or email
    $chk = $db->prepare("SELECT id FROM users WHERE email = ? OR roll_no = ?");
    $chk->execute([$email, $roll_no]);

    if ($chk->fetch()) {
        $error = "Roll Number ya Email pehle se registered hai!";
    } else {
        // Proof photo optional hai - jaruri nahi
        $id_filename = '';
        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array($ext, $allowed)) {
                $id_filename = 'id_' . time() . '_' . rand(100, 999) . '.' . $ext;
                move_uploaded_file($_FILES['id_proof']['tmp_name'], __DIR__ . '/uploads/' . $id_filename);
            }
        }

        // Direct add nahi hoga, status 'pending' set hoga
        $stmt = $db->prepare("INSERT INTO users 
            (name, father_name, address, email, roll_no, course, course_year, semester, id_proof, password, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 'pending')");
        
        if ($stmt->execute([$name, $father_name, $address, $email, $roll_no, $course, $course_year, $semester, $id_filename, $password])) {
            $success = "Registration form submit ho gaya hai! Admin se Approval milne ke baad hi aap login kar sakenge.";
        } else {
            $error = "Registration submit nahi ho saka. Kripya dobara koshish karein.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Admission Registration | F.S. University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body {
            max-width: 100vw !important;
            overflow-x: hidden !important;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .header-top {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            border-bottom: 3px solid #f59e0b;
            padding: 12px 16px;
        }
        .reg-card {
            border-radius: 16px;
            background: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .form-control, .form-select {
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header class="header-top text-white shadow-sm mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="bg-warning text-dark rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">F.S. UNIVERSITY</h6>
                <small class="text-warning text-uppercase" style="font-size: 10px;">Shikohabad (U.P.)</small>
            </div>
        </div>
        <a href="login.php" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </a>
    </div>
</header>

<div class="container py-2 pb-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="card reg-card p-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i>New Student Admission Form
                        </h5>
                        <small class="text-muted">Fill student details (Approval required)</small>
                    </div>
                    <a href="login.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Back</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small rounded-3"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success py-2 small rounded-3">
                        <?= $success ?>
                        <div class="mt-2">
                            <a href="login.php" class="btn btn-sm btn-success rounded-pill px-4">Back to Login</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="register_student" value="1">
                    
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold">Student Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold">Father's Name *</label>
                            <input type="text" name="father_name" class="form-control" placeholder="Father's full name" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="small fw-bold">Complete Resident Address *</label>
                            <input type="text" name="address" class="form-control" placeholder="Village/City, Post, District, State" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold">Roll / Registration Number *</label>
                            <input type="text" name="roll_no" class="form-control" placeholder="e.g. FSU202601" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small fw-bold">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="student@example.com" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold">Course Name *</label>
                            <input type="text" name="course" class="form-control" placeholder="e.g. BCA / B.Tech" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold">Course Year *</label>
                            <select name="course_year" class="form-select" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold">Semester *</label>
                            <select name="semester" class="form-select" required>
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
                            <input type="file" name="id_proof" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted" style="font-size: 11px;">Agar proof hai toh upload karein, dalna compulsory nahi hai.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">Create Account Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Set a secure password" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="login.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                            <i class="bi bi-send-check me-1"></i> Submit for Approval
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
