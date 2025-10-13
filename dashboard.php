<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
	header('Location: login.html');
	exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? '');
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
	<form>
		<h3>Welcome, <?php echo $username; ?></h3>
		<a href="logout.php">Logout</a>
	</form>
</body>
</html>


