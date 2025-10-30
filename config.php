<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (empty($_ENV['BASE_URL'])) {
    $_ENV['BASE_URL'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
}

session_start();

function get_db_connection() {
    $conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
    if ($conn->connect_error) {
        error_log('DB Connection Error: ' . $conn->connect_error);
        set_flash('error', 'Internal server error. Please try again later.');
        redirect('login.php');
        exit();
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function send_verification_email($email, $username, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'LoginSystem');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';
        $verificationLink = $_ENV['BASE_URL'] . '/verify.php?token=' . $token;
        $mail->Body = "
            <h2>Welcome, $username!</h2>
            <p>Please click the link below to verify your email:</p>
            <a href='$verificationLink' style='background: #f50707ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a>
            <p>This link expires in 24 hours.</p>
        ";
        $mail->AltBody = "Welcome, $username! Verify your email: $verificationLink";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access this page.');
        redirect('login.php');
    }
}

function rate_limit_check($key, $max_attempts = 5, $cooldown = 900) { // 15 min cooldown
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }
    $data = $_SESSION[$key];
    if (time() - $data['time'] > $cooldown) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
        return true;
    }
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    return true;
}

function increment_rate_limit($key) {
    if (isset($_SESSION[$key])) {
        $_SESSION[$key]['count']++;
    }
}
?>
