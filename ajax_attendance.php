<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['faculty', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = trim($_POST['roll_no'] ?? '');
    $date    = trim($_POST['date'] ?? date('Y-m-d'));

    if (empty($roll_no)) {
        echo json_encode(['status' => 'error', 'message' => 'Roll number not found in QR']);
        exit;
    }

    // Find student by roll number
    $stmt = $db->prepare("SELECT id, name, roll_no, course FROM users WHERE roll_no = ? AND role = 'student' AND status = 'approved'");
    $stmt->execute([$roll_no]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['status' => 'error', 'message' => "Roll No '$roll_no' ka koi approved student nahi mila!"]);
        exit;
    }

    $student_id = $student['id'];

    // Check existing attendance for this date
    $chk = $db->prepare("SELECT id, status FROM attendance WHERE student_id = ? AND date = ?");
    $chk->execute([$student_id, $date]);
    $existing = $chk->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $up = $db->prepare("UPDATE attendance SET status = 'Present' WHERE student_id = ? AND date = ?");
        $up->execute([$student_id, $date]);
    } else {
        $ins = $db->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, 'Present')");
        $ins->execute([$student_id, $date]);
    }

    echo json_encode([
        'status' => 'success',
        'student_name' => $student['name'],
        'roll_no' => $student['roll_no'],
        'course' => $student['course'],
        'message' => "Present Marked Successfully!"
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
exit;
?>
