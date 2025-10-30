<?php
require_once __DIR__ . '/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    set_flash('error', 'Invalid verification link.');
    redirect('login.php');
    exit;
}

$token = $_GET['token'];
$conn = get_db_connection();

$stmt = $conn->prepare('SELECT id, username, verified, created_at FROM users WHERE verification_token = ? AND verified = 0 LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    set_flash('error', 'Invalid or expired verification token.');
    redirect('login.php');
    exit;
}

$created_at = new DateTime($user['created_at']);
$now = new DateTime();
if (($now->getTimestamp() - $created_at->getTimestamp()) > (24 * 60 * 60)) {
    $conn->close();
    set_flash('error', 'Verification token has expired. Please register again.');
    redirect('index.php');
    exit;
}

$update_stmt = $conn->prepare('UPDATE users SET verified = 1, verified_at = NOW(), verification_token = NULL WHERE id = ?');
$update_stmt->bind_param('i', $user['id']);
$update_stmt->execute();
$update_stmt->close();

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username']; // Fetch username separately if needed
session_regenerate_id(true);

$conn->close();
set_flash('success', 'Email verified successfully! Welcome aboard.');
redirect('dashboard.php');
exit;
?>
