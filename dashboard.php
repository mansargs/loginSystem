<?php
require_once __DIR__ . '/config.php';
require_login();
session_regenerate_id(true);

$username = htmlspecialchars($_SESSION['username']);
$flash = get_flash();
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
    <div class="container">
        <h3>Welcome, <?php echo $username; ?>!</h3>
        <?php if ($flash): ?>
            <p class="flash <?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></p>
        <?php endif; ?>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</body>
</html>
