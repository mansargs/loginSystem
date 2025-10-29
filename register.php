<?php
session_start();
require_once __DIR__ . '/config.php';

// only accept POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
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

// 🧩 Validation with session-based alerts
if (!validate_username($username)) {
	$_SESSION['alert'] = "Username must be 4–24 chars, letters/digits/underscore only.";
	header('Location: index.php');
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$_SESSION['alert'] = "Invalid email address.";
	header('Location: index.php');
	exit;
}

if (!validate_password($password)) {
	$_SESSION['alert'] = "Password must be 8–64 characters.";
	header('Location: index.php');
	exit;
}

if ($password !== $confirm_password) {
	$_SESSION['alert'] = "Passwords do not match.";
	header('Location: index.php');
	exit;
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$conn = get_db_connection();

// check if username or email exists
$check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$check->bind_param('ss', $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
	$check->close();
	$conn->close();
	$_SESSION['alert'] = "Username or email already exists.";
	header('Location: index.php');
	exit;
}

$check->close();

// insert user
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
		$_SESSION['alert'] = "Registration successful! Please check your email to verify.";
	} else {
		$_SESSION['alert'] = "Registration successful, but verification email could not be sent.";
	}

	$stmt->close();
	$conn->close();

	header('Location: login.html');
	exit;
} else {
	$stmt->close();
	$conn->close();
	$_SESSION['alert'] = "Database error: " . $stmt->error;
	header('Location: index.php');
	exit;
}
?>
