<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_system');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
            <h2>Database Connection Failed</h2>
            <p>Could not connect to the database. Please check your configuration in <code>config/db.php</code>.</p>
            <p><strong>Error:</strong> ' . htmlspecialchars($conn->connect_error) . '</p>
        </div>');
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
