<?php $role = $_SESSION['user_role'] ?? ''; ?>
<div class="col-md-2 mb-4">
    <div class="list-group shadow-sm">
        <?php if ($role === 'admin'): ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="add_student.php" class="list-group-item list-group-item-action">Manage Students</a>
            <a href="manage_faculty.php" class="list-group-item list-group-item-action">Manage Faculty</a>
            <a href="post_notice.php" class="list-group-item list-group-item-action">Post Notice</a>
        <?php elseif ($role === 'faculty'): ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="mark_attendance.php" class="list-group-item list-group-item-action">Mark Attendance</a>
            <a href="submit_marks.php" class="list-group-item list-group-item-action">Upload Marks</a>
        <?php elseif ($role === 'student'): ?>
            <a href="dashboard.php" class="list-group-item list-group-item-action">Overview</a>
            <a href="attendance_report.php" class="list-group-item list-group-item-action">Attendance Report</a>
            <a href="view_result.php" class="list-group-item list-group-item-action">View Marks</a>
        <?php endif; ?>
    </div>
</div>
<div class="col-md-10">
