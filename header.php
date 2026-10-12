<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Viewport jo mobile display ke size ko 100% lock karta hai -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>F.S. University, Shikohabad</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* Horizontal slide (scroll) ko permanent block karne ke liye */
        html, body {
            max-width: 100% !important;
            overflow-x: hidden !important;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }

        * {
            box-sizing: border-box;
        }

        /* Auto-fit container */
        .container-fluid, .container {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        /* Compact Header */
        .fsu-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            border-bottom: 3px solid #f59e0b;
            padding: 10px 12px;
        }

        .header-logo {
            width: 38px;
            height: 38px;
            font-size: 1.2rem;
        }

        /* Responsive Cards */
        .card-custom {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 12px;
            width: 100%;
        }

        /* Screen fit inputs */
        .form-control, .form-select, .btn {
            font-size: 14px;
        }

        /* Touch friendly short attendance buttons */
        .btn-check:checked + .btn-p { background-color: #10b981 !important; color: white !important; }
        .btn-check:checked + .btn-a { background-color: #ef4444 !important; color: white !important; }
        .btn-short { padding: 3px 10px; font-weight: 700; font-size: 12px; border-radius: 6px; }

        /* Tables ko screen ke andar rakhne ke liye */
        .table-responsive {
            width: 100%;
            margin-bottom: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100% !important;
            font-size: 13px;
        }

        /* Mobile specific auto-fit */
        @media (max-width: 576px) {
            .fsu-title { font-size: 1rem !important; }
            .fsu-sub { font-size: 9px !important; }
            .display-5 { font-size: 2rem !important; }
            .table th, .table td { padding: 6px 4px !important; }
        }
    </style>
</head>
<body>

<header class="fsu-header text-white shadow-sm mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="bg-warning text-dark rounded-circle me-2 d-flex align-items-center justify-content-center header-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold fsu-title">F.S. UNIVERSITY</h6>
                <div class="text-warning text-uppercase fw-semibold fsu-sub" style="font-size: 10px;">Shikohabad (U.P.)</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="badge bg-warning text-dark text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></span>
                <a href="../logout.php" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size: 12px;">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container-fluid">
