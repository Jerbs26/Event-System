<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isLoggedIn() || isAdmin()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$conn   = getConnection();

$totalEvents = 0;
$result = $conn->query("SELECT COUNT(*) AS c FROM events WHERE date >= CURDATE()");
if ($result) {
    $totalEvents = (int)$result->fetch_assoc()['c'];
    $result->free();
}

$myRegs = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM registrations WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $myRegs = (int)($row['c'] ?? 0);
    $stmt->close();
}

$upcoming = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) AS c
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ? AND e.date >= CURDATE()
");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $upcoming = (int)($row['c'] ?? 0);
    $stmt->close();
}

// Registered events 
$myEvents = [];
$stmt = $conn->prepare("
    SELECT e.id, e.title, e.date, e.time, e.location, r.registered_at
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    ORDER BY e.date ASC
    LIMIT 5
");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $myEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'stats' => [
        'total_events' => $totalEvents,
        'my_regs'      => $myRegs,
        'upcoming'     => $upcoming,
    ],
    'my_events' => $myEvents,
], JSON_UNESCAPED_UNICODE);