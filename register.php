<?php
require_once __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	header('Location: register.html');
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

if (!validate_username($username)) {
	die('Username must be 4-24 chars, letters/digits/underscore only');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	die('Invalid email address');
}
if (!validate_password($password)) {
	die('Password must be 8-64 chars, letters/digits/underscore only');
}
if ($password !== $confirm_password) {
	die('Passwords do not match');
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$conn = get_db_connection();

$check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$check->bind_param('ss', $username, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
	$check->close();
	$conn->close();
	die('Username or email already exists');
}
$check->close();

$stmt = $conn->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
$stmt->bind_param('sss', $username, $email, $password_hash);
if ($stmt->execute() === TRUE) {
    $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    echo 'Registration successful for user ' . $safeUser . ' (email: ' . $safeEmail . '). You can now log in.';
} else {
	echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();

?>
