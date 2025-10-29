<?php
// session_start();
require_once __DIR__ . '/config.php';
generate_csrf_token();
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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php echo $flash; ?>
    <form action="register.php" method="post" id="reg-form">
        <input type="text" id="username" name="username" placeholder="Username" required>
        <input type="email" name="email" id="email" placeholder="Email" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="submit" value="Register">
        <a href="login.php">Already have an account? Login here</a>
    </form>
    <script>
        document.getElementById('reg-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (username.length < 4 || username.length > 24 || !/^[A-Za-z0-9_]+$/.test(username)) {
                alert('Username must be 4–24 chars, letters/digits/underscore only.');
                e.preventDefault();
                return;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Invalid email address.');
                e.preventDefault();
                return;
            }
            if (password.length < 8 || password.length > 64) {
                alert('Password must be 8–64 characters.');
                e.preventDefault();
                return;
            }
            if (password !== confirm) {
                alert('Passwords do not match.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
