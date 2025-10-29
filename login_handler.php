<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = 'Username and password are required.';
    header('Location: login.php');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$key = 'login_attempts_' . md5($ip);
if (isset($_SESSION[$key]) && $_SESSION[$key] >= 5) {
    $_SESSION['error'] = 'Too many failed attempts. Try again later.';
    header('Location: login.php');
    exit;
}

$conn = get_db_connection();
$stmt = $conn->prepare('SELECT id, username, email, password_hash, verified FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
    $_SESSION['error'] = 'Invalid username or password.';
    $conn->close();
    header('Location: login.php');
    exit;
}

if (!password_verify($password, $user['password_hash'])) {
    $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
    $_SESSION['error'] = 'Invalid username or password.';
    $conn->close();
    header('Location: login.php');
    exit;
}

if ($user['verified'] != 1) {
    $_SESSION['error'] = 'Account must be verified. Check your email.';
    $conn->close();
    header('Location: login.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
session_regenerate_id(true);

unset($_SESSION[$key]);

$conn->close();
header('Location: dashboard.php');
exit;
?>
