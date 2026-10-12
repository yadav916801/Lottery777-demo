<?php
$db_file = __DIR__ . '/college.db';
$db = new PDO("sqlite:" . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Uploads folder create karein files aur id proof ke liye
$upload_dir = __DIR__ . '/../uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Users table with complete profile & approval status
$db->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    father_name TEXT,
    address TEXT,
    email TEXT UNIQUE NOT NULL,
    roll_no TEXT UNIQUE,
    course TEXT,
    course_year TEXT,
    semester TEXT,
    id_proof TEXT,
    password TEXT NOT NULL,
    role TEXT NOT NULL,
    status TEXT DEFAULT 'approved'
);

CREATE TABLE IF NOT EXISTS attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    date TEXT NOT NULL,
    status TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS notices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    attachment TEXT,
    posted_date DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// Column updates agar pehle se table bani ho
$cols = [
    'father_name TEXT', 'address TEXT', 'course TEXT',
    'course_year TEXT', 'semester TEXT', 'id_proof TEXT',
    'status TEXT DEFAULT \'approved\''
];
foreach ($cols as $col) {
    try { $db->exec("ALTER TABLE users ADD COLUMN $col"); } catch(Exception $e) {}
}
try { $db->exec("ALTER TABLE notices ADD COLUMN attachment TEXT"); } catch(Exception $e) {}

// Default admin and faculty
$check = $db->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch(PDO::FETCH_ASSOC);
if ($check['count'] == 0) {
    $db->exec("
        INSERT INTO users (name, email, password, role, status) VALUES 
        ('Admin Sir', 'admin@college.com', 'admin123', 'admin', 'approved'),
        ('Prof. Sharma', 'faculty@college.com', 'faculty123', 'faculty', 'approved');
    ");
}
?>
