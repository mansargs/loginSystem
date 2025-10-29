<?php
require_once __DIR__ . '/config.php';
generate_csrf_token();

$flash = '';
if(isset($_SERVER['alert'])) {
    echo $_SERVER['alert'];
    unset($_SERVER['alert']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form action="login_handler.php" method="post" id="login-form">
        <input type="text" id="username" name="username" placeholder="Username" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="submit" value="Login">
        <a href="index.php">Don't have an account? Register here</a>
    </form>
    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            if (!username || !password) {
                alert('Username and password are required.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
