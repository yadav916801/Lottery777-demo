<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $name     = trim($_POST['st_name']);
    $father   = trim($_POST['st_father']);
    $address  = trim($_POST['st_address']);
    $roll_no  = trim($_POST['st_roll']);
    $email    = trim($_POST['st_email']);
    $course   = trim($_POST['st_course']);
    $year     = trim($_POST['st_year']);
    $sem      = trim($_POST['st_sem']);
    $password = trim($_POST['st_password']);

    $chk = $db->prepare("SELECT id FROM users WHERE (email = ? OR roll_no = ?) AND id != ?");
    $chk->execute([$email, $roll_no, $id]);

    if ($chk->fetch()) {
        $error = "Yeh Roll Number ya Email kisi aur student ke pass pehle se registered hai!";
    } else {
        if (!empty($password)) {
            $up = $db->prepare("UPDATE users SET name = ?, father_name = ?, address = ?, roll_no = ?, email = ?, course = ?, course_year = ?, semester = ?, password = ? WHERE id = ?");
            $up->execute([$name, $father, $address, $roll_no, $email, $course, $year, $sem, $password, $id]);
        } else {
            $up = $db->prepare("UPDATE users SET name = ?, father_name = ?, address = ?, roll_no = ?, email = ?, course = ?, course_year = ?, semester = ? WHERE id = ?");
            $up->execute([$name, $father, $address, $roll_no, $email, $course, $year, $sem, $id]);
        }

        // Direct Home (Dashboard) Redirect
        header("Location: dashboard.php?msg=student_updated");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-success me-2"></i>Edit Student Profile
                </h5>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back to Hub
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="update_student" value="1">
                
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Student Full Name *</label>
                        <input type="text" name="st_name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Father's Name *</label>
                        <input type="text" name="st_father" class="form-control" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small fw-bold">Address *</label>
                        <input type="text" name="st_address" class="form-control" value="<?= htmlspecialchars($student['address'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Roll / Registration Number *</label>
                        <input type="text" name="st_roll" class="form-control" value="<?= htmlspecialchars($student['roll_no'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Email Address *</label>
                        <input type="email" name="st_email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Course *</label>
                        <input type="text" name="st_course" class="form-control" value="<?= htmlspecialchars($student['course'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Course Year *</label>
                        <select name="st_year" class="form-select" required>
                            <?php 
                            $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                            foreach($years as $yr): 
                            ?>
                                <option value="<?= $yr ?>" <?= ($student['course_year'] ?? '') === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small fw-bold">Semester *</label>
                        <select name="st_sem" class="form-select" required>
                            <?php 
                            $sems = ['1st Sem', '2nd Sem', '3rd Sem', '4th Sem', '5th Sem', '6th Sem', '7th Sem', '8th Sem'];
                            foreach($sems as $sm): 
                            ?>
                                <option value="<?= $sm ?>" <?= ($student['semester'] ?? '') === $sm ? 'selected' : '' ?>><?= $sm ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="small fw-bold">New Password</label>
                        <input type="password" name="st_password" class="form-control" placeholder="Khali chhod dein agar password nahi badalna">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
