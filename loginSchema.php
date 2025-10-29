<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: login.html');
	exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
	echo '<script>alert(Username and password are required)</script>';
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
	$conn->close();
	echo '<script>alert(Invalid username)</script>';
	exit ;
}

if (!password_verify($password, $user['password_hash'])) {
	$conn->close();
	echo '<script>alert(Invalid password)</script>';
	exit ;
}

if ($user['verified'] != 1) {
	$conn->close();
	echo '<script>alert(Account must be verified)</script>';
	exit ;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

$conn->close();
header('Location: dashboard.php');
exit;
?>
