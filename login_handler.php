<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Invalid request. Please try again.');
    redirect('login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    set_flash('error', 'Username and password are required.');
    redirect('login.php');
}

$ip = $_SERVER['REMOTE_ADDR'];
$key = 'login_attempts_' . md5($ip);
if (!rate_limit_check($key)) {
    set_flash('error', 'Too many failed attempts. Try again in 15 minutes.');
    redirect('login.php');
}

$conn = get_db_connection();
$stmt = $conn->prepare('SELECT id, username, password_hash, verified FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    increment_rate_limit($key);
    set_flash('error', 'Invalid username or password.');
    $conn->close();
    redirect('login.php');
}

if ($user['verified'] != 1) {
    set_flash('error', 'Account must be verified. Check your email.');
    $conn->close();
    redirect('login.php');
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
session_regenerate_id(true);

unset($_SESSION[$key]);

$conn->close();
set_flash('success', 'Login successful!');
redirect('dashboard.php');
?>
