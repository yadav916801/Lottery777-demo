<?php
$host     = '127.0.0.1';
$dbname   = 'college_erp';
$username = 'dbviewer';
$password = 'dbviewer';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        father_name VARCHAR(100) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        roll_no VARCHAR(50) DEFAULT NULL UNIQUE,
        course VARCHAR(100) DEFAULT NULL,
        course_year VARCHAR(50) DEFAULT NULL,
        semester VARCHAR(50) DEFAULT NULL,
        id_proof VARCHAR(255) DEFAULT NULL,
        dob VARCHAR(20) DEFAULT NULL,
        blood_group VARCHAR(10) DEFAULT NULL,
        contact VARCHAR(20) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'faculty', 'student') NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reg_no VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        father_name VARCHAR(100) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        assigned_class VARCHAR(100) DEFAULT NULL,
        subject_taught VARCHAR(100) DEFAULT NULL,
        doc_proof VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        date DATE NOT NULL,
        status ENUM('Present', 'Absent') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY student_date (student_id, date)
    );

    CREATE TABLE IF NOT EXISTS teacher_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        date DATE NOT NULL,
        status ENUM('Present', 'Absent') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY teacher_date (teacher_id, date)
    );

    CREATE TABLE IF NOT EXISTS notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        attachment VARCHAR(255) DEFAULT NULL,
        posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        category VARCHAR(100) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        proof_doc VARCHAR(255) DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Pending Review',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    INSERT INTO users (name, email, password, role, status) 
    VALUES ('Super Admin', 'admin@fsu.edu.in', 'admin123', 'admin', 'approved')
    ON DUPLICATE KEY UPDATE id=id;
    ";

    $db->exec($sql);
    echo "<h2 style='color:green;'>Success: Sabhi Tables aur Admin account create ho gaye!</h2>";
    echo "<p>Ab aap <a href='login.php'>login.php</a> par ja kar login kar sakte hain.</p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Error: " . $e->getMessage() . "</h2>";
}
?>
