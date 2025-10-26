<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: login.html');
	exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
	die('Username and password are required');
}

$conn = get_db_connection();
$stmt = $conn->prepare('SELECT id, username, email FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
	$conn->close();
	die('Invalid credentials');
}

if (!password_verify($password, $user['password_hash'])) {
	$conn->close();
	die('Invalid credentials');
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

$conn->close();
header('Location: dashboard.php');
exit;
?>

