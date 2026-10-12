<?php
require_once 'config/db.php';

// Purane admin record ko update ya naya insert karein
$admin_email = 'admin@college.com';
$admin_pass  = 'admin123';

// Check if admin exists
$chk = $db->prepare("SELECT id FROM users WHERE role = 'admin'");
$chk->execute();
$admin = $chk->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    // Admin password update karein
    $up = $db->prepare("UPDATE users SET email = ?, password = ?, status = 'approved' WHERE id = ?");
    $up->execute([$admin_email, $admin_pass, $admin['id']]);
    echo "<h3 style='color:green;'>Admin Account Updated Successfully!</h3>";
} else {
    // Fresh admin insert karein
    $ins = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES ('University Admin', ?, ?, 'admin', 'approved')");
    $ins->execute([$admin_email, $admin_pass]);
    echo "<h3 style='color:green;'>Admin Account Created Successfully!</h3>";
}

echo "<p>Ab login karein:<br><b>Email / ID:</b> admin@college.com<br><b>Password:</b> admin123</p>";
echo "<a href='login.php'>Go to Login Page</a>";
?>
