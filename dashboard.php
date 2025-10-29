<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
	header('Location: login.html');
	exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? '');

$flash = '';
if (isset($_SESSION['reg_success'])) {
	$flash = '<p style="color: green;">' . htmlspecialchars($_SESSION['reg_success']) . '</p>';
	unset($_SESSION['reg_success']);
} elseif (isset($_SESSION['reg_error'])) {
	$flash = '<p style="color: red;">' . htmlspecialchars($_SESSION['reg_error']) . '</p>';
	unset($_SESSION['reg_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div style="display: flex; flex-direction: column; align-items: center; padding: 50px;">
		<h3>Welcome, <?php echo $username; ?></h3>
		<?php echo $flash; ?>
		<a href="logout.php">Logout</a>
	</div>
</body>
</html>
