<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Invalid request. Please try again.');
    redirect('index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (!preg_match('/^[A-Za-z0-9_]+$/', $username) || strlen($username) < 4 || strlen($username) > 24) {
    set_flash('error', 'Username must be 4–24 chars, letters/digits/underscore only.');
    redirect('index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Invalid email address.');
    redirect('index.php');
    exit;
}

if (strlen($password) < 8 || strlen($password) > 64) {
    set_flash('error', 'Password must be 8–64 characters.');
    redirect('index.php');
    exit;
}

if ($password !== $confirm_password) {
    set_flash('error', 'Passwords do not match.');
    redirect('index.php');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$conn = get_db_connection();

// Check duplicates
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    set_flash('error', 'Username or email already exists.');
    redirect('index.php');
    exit;
}
$stmt->close();

// Insert
$stmt = $conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $username, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;

    $token = bin2hex(random_bytes(32));
    $update_stmt = $conn->prepare('UPDATE users SET verification_token = ?, verified = 0 WHERE id = ?');
    $update_stmt->bind_param('si', $token, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    if (send_verification_email($email, $username, $token)) {
        set_flash('success', 'Registration successful! Please check your email to verify.');
    } else {
        set_flash('error', 'Registration successful, but verification email could not be sent. Please contact support.');
    }

    $stmt->close();
    $conn->close();
    redirect('login.php');
    exit;
} else {
    $stmt->close();
    $conn->close();
    set_flash('error', 'Database error. Please try again.');
    redirect('index.php');
    exit;
}
?>
