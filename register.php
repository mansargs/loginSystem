<?php
require_once __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['alert'] = '<script>(Invalid request. Please try again.)</script>';
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}

function validate_username($str) {
    return preg_match('/^[A-Za-z0-9_]+$/', $str) === 1 && strlen($str) >= 4 && strlen($str) <= 24;
}

function validate_password($str) {
    return strlen($str) >= 8 && strlen($str) <= 64;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (!validate_username($username)) {
    $_SESSION['alert'] = '<script>(Username must be 4–24 chars, letters/digits/underscore only.)</script>';
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alert'] = '<script>(Invalid email address.)</script>';
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}

if (!validate_password($password)) {
    $_SESSION['alert'] = '<script>(Password must be 8–64 characters.)</script>';
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['alert'] = '<script>(Passwords do not match.)</script>';
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);  // Use DEFAULT for future-proof

$conn = get_db_connection();

// Check duplicates
$check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$check->bind_param('ss', $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    $_SESSION['alert'] = "<script>alert(Username or email already exists.)</script>";
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}
$check->close();

// Insert
$stmt = $conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $username, $email, $password_hash);

if ($stmt->execute() === TRUE) {
    $user_id = $conn->insert_id;

    $token = bin2hex(random_bytes(32));
    $update_stmt = $conn->prepare('UPDATE users SET verification_token = ?, verified = 0 WHERE id = ?');
    $update_stmt->bind_param('si', $token, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    if (send_verification_email($email, $username, $token)) {
        $_SESSION['alert'] = "<script>alert(Registration successful! Please check your email to verify.)</script>";
        $_SESSION['alert_type'] = 'green';
    } else {
        $_SESSION['alert'] = "<script>alert(Registration successful, but verification email could not be sent. Please contact support.)</script>";
        $_SESSION['alert_type'] = 'orange';
    }

    $stmt->close();
    $conn->close();

    header('Location: login.php');
    exit;
} else {
    $stmt->close();
    $conn->close();
    $_SESSION['alert'] = "<script>alert(Database error. Please try again.)</script>";
    $_SESSION['alert_type'] = 'red';
    header('Location: index.php');
    exit;
}
?>
