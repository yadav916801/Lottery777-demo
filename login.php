<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect already logged in user
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'faculty') {
        header("Location: faculty/dashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'student') {
        header("Location: student/dashboard.php");
        exit;
    }
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Email aur password enter karein.";
    } else {
        // Check users table (Admin & Students)
        $stmt = $db->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $is_valid = ($password === $user['password']) || password_verify($password, $user['password']);

            if ($is_valid) {
                if ($user['role'] === 'student' && $user['status'] !== 'approved') {
                    $error = "Aapka account abhi approval ke liye pending hai.";
                } else {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: student/dashboard.php");
                    }
                    exit;
                }
            } else {
                $error = "Galat Password!";
            }
        } else {
            // Check teachers table (Faculty)
            $t_stmt = $db->prepare("SELECT id, name, email, password, assigned_class, subject_taught FROM teachers WHERE email = ? LIMIT 1");
            $t_stmt->execute([$email]);
            $teacher = $t_stmt->fetch();

            if ($teacher) {
                $t_valid = ($password === $teacher['password']) || password_verify($password, $teacher['password']);

                if ($t_valid) {
                    $_SESSION['user_id']        = $teacher['id'];
                    $_SESSION['user_name']      = $teacher['name'];
                    $_SESSION['user_role']      = 'faculty';
                    $_SESSION['assigned_class'] = $teacher['assigned_class'];
                    $_SESSION['subject_taught'] = $teacher['subject_taught'];

                    header("Location: faculty/dashboard.php");
                    exit;
                } else {
                    $error = "Galat Password!";
                }
            } else {
                $error = "Yeh Email register nahi hai!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Portal - Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1d4ed8;
            --dark-blue: #0f172a;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 15px;
        }
        .portal-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(29, 78, 216, 0.12);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            background: #ffffff;
        }
        .header-brand {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            padding: 28px 20px;
            text-align: center;
        }
        .header-brand .logo-circle {
            width: 60px;
            height: 60px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #1e3a8a;
            font-size: 28px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }
        .btn-blue {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 15px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-blue:hover {
            background-color: #1e40af;
            color: #ffffff;
        }
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(29, 78, 216, 0.15);
        }
    </style>
</head>
<body>

<div class="portal-card">
    <div class="header-brand">
        <div class="logo-circle">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h4 class="fw-bold mb-0">COLLEGE ERP</h4>
        <small class="text-light opacity-75" style="letter-spacing: 0.5px;">Student, Faculty & Admin Portal</small>
    </div>

    <div class="p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small rounded-3 mb-3 d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope-at text-muted"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="admin@fsu.edu.in" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-blue w-100 mt-2 shadow-sm">
                Sign In <i class="bi bi-box-arrow-in-right ms-1"></i>
            </button>
        </form>

        <div class="border-top mt-4 pt-3 text-center">
            <p class="small text-muted mb-2">New Student?</p>
            <a href="register.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Student Registration
            </a>
        </div>
    </div>
</div>

</body>
</html>
