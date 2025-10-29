<?php
require_once __DIR__ . '/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
	header('Location: login.html?error=invalid_token');
	exit;
}

$token = $_GET['token'];
$conn = get_db_connection();

$stmt = $conn->prepare('SELECT id, verified, created_at FROM users WHERE verification_token = ? AND verified = 0 LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
	$conn->close();
	die('Invalid or expired verification token.');
}

$created_at = new DateTime($user['created_at']);
$now = new DateTime();
if (($now->getTimestamp() - $created_at->getTimestamp()) > (24 * 60 * 60)) {
	$conn->close();
	die('Verification token has expired. Please register again.');
}

$update_stmt = $conn->prepare('UPDATE users SET verified = 1, verified_at = NOW(), verification_token = NULL WHERE id = ?');
$update_stmt->bind_param('i', $user['id']);
$update_stmt->execute();
$update_stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Email Verified</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div style="display: flex; flex-direction: column; align-items: center; padding: 50px; text-align: center;">
		<h2>Email Verified Successfully!</h2>
		<p>You can now <a href="login.html">log in</a> to your account.</p>
	</div>
</body>
</html>
