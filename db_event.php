<?php
require_once __DIR__ . '/config.php';

$conn = get_db_connection();
$conn->query("SET GLOBAL event_scheduler = ON");

$createEventSql = <<<SQL
CREATE EVENT IF NOT EXISTS delete_expired_users
ON SCHEDULE EVERY 12 HOUR
DO
  DELETE FROM users
  WHERE verified = 0
    AND created_at < NOW() - INTERVAL 24 HOUR;
SQL;

if ($conn->query($createEventSql)) {
    echo "Event created successfully or already exists.\n";
} else {
    echo "Error creating event: " . $conn->error;
}
$conn->close();
