<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireLogin();

$action  = sanitize($_POST['action']   ?? '');
$eventId = (int)($_POST['event_id']    ?? 0);
$userId  = (int)$_SESSION['user_id'];

if ($eventId <= 0 || !in_array($action, ['register', 'cancel'])) {
    setFlash('error', 'Invalid request.');
    header('Location: /event-system/events.php');
    exit();
}

$conn = getConnection();

// Verify event exists
$stmt = $conn->prepare("SELECT id, title, date, capacity FROM events WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    setFlash('error', 'Event not found.');
    $conn->close();
    header('Location: /event-system/events.php');
    exit();
}

// REGISTER 
if ($action === 'register') {

    // Check event hasn't passed
    if (strtotime($event['date']) < strtotime('today')) {
        setFlash('error', 'This event has already passed and is no longer accepting registrations.');
        $conn->close();
        header('Location: /event-system/events.php');
        exit();
    }

    // Check capacity
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM registrations WHERE event_id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    if ($count >= $event['capacity']) {
        setFlash('warning', 'Sorry, this event is at full capacity.');
        $conn->close();
        header('Location: /event-system/events.php');
        exit();
    }

    // Check already registered
    $stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1");
    $stmt->bind_param("ii", $userId, $eventId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        setFlash('warning', 'You are already registered for this event.');
        $conn->close();
        header('Location: /event-system/events.php');
        exit();
    }
    $stmt->close();

    // Insert registration
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $eventId);

    if ($stmt->execute()) {
        setFlash('success', 'You have successfully registered for "' . $event['title'] . '"!');
    } else {
        setFlash('error', 'Registration failed. Please try again.');
    }
    $stmt->close();

    $conn->close();
    header('Location: /event-system/events.php');
    exit();
}

// CANCEL 
if ($action === 'cancel') {
    $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $userId, $eventId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        setFlash('success', 'Your registration for "' . $event['title'] . '" has been cancelled.');
    } else {
        setFlash('error', 'Could not cancel registration. It may have already been cancelled.');
    }
    $stmt->close();

    $conn->close();
    header('Location: /event-system/dashboard.php');
    exit();
}

// Fallback
$conn->close();
setFlash('error', 'Invalid action.');
header('Location: /event-system/events.php');
exit();
?>