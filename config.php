<?php

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'Manvel';
$DB_NAME = 'login_system';

$BASE_URL = 'http://localhost/interface';

function get_db_connection() {
	global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
	$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	if ($conn->connect_error) {
		die('Database connection failed: ' . $conn->connect_error);
	}
	$conn->set_charset('utf8mb4');
	return $conn;
}

session_start();
?>


