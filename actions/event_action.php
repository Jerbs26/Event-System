<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireAdmin();

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');

$conn = getConnection();

// CREATE EVENT 
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = sanitize($_POST['title']       ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $date        = sanitize($_POST['date']         ?? '');
    $time        = sanitize($_POST['time']         ?? '');
    $location    = sanitize($_POST['location']     ?? '');
    $capacity    = (int)($_POST['capacity']        ?? 100);

    if (empty($title) || empty($date) || empty($time) || empty($location)) {
        setFlash('error', 'Title, date, time, and location are required.');
        header('Location: /event-system/admin/events.php');
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO events (title, description, date, time, location, capacity) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssi", $title, $description, $date, $time, $location, $capacity);

    if ($stmt->execute()) {
        setFlash('success', 'Event "' . $title . '" created successfully!');
    } else {
        setFlash('error', 'Failed to create event. Please try again.');
    }

    $stmt->close();
    $conn->close();
    header('Location: /event-system/admin/events.php');
    exit();
}

// UPDATE EVENT 
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = (int)($_POST['event_id']    ?? 0);
    $title       = sanitize($_POST['title']       ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $date        = sanitize($_POST['date']         ?? '');
    $time        = sanitize($_POST['time']         ?? '');
    $location    = sanitize($_POST['location']     ?? '');
    $capacity    = (int)($_POST['capacity']        ?? 100);

    if ($id <= 0 || empty($title) || empty($date) || empty($time) || empty($location)) {
        setFlash('error', 'All fields are required.');
        header('Location: /event-system/admin/events.php');
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare(
        "UPDATE events SET title=?, description=?, date=?, time=?, location=?, capacity=? WHERE id=?"
    );
    $stmt->bind_param("sssssii", $title, $description, $date, $time, $location, $capacity, $id);

    if ($stmt->execute()) {
        setFlash('success', 'Event updated successfully!');
    } else {
        setFlash('error', 'Failed to update event.');
    }

    $stmt->close();
    $conn->close();
    header('Location: /event-system/admin/events.php');
    exit();
}

// DELETE EVENT 
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        setFlash('error', 'Invalid event ID.');
        header('Location: /event-system/admin/events.php');
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        setFlash('success', 'Event deleted successfully.');
    } else {
        setFlash('error', 'Failed to delete event.');
    }

    $stmt->close();
    $conn->close();
    header('Location: /event-system/admin/events.php');
    exit();
}

// Fallback
$conn->close();
setFlash('error', 'Invalid action.');
header('Location: /event-system/admin/events.php');
exit();
?>
