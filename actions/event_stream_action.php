<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Must be logged in (not necessarily admin)
if (!isLoggedIn()) {
    http_response_code(403);
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Disable output buffering at every level
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Keep the connection alive even if the client disconnects mid-loop
ignore_user_abort(true);

// SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// No time limit on this script
set_time_limit(0);


// Send one SSE event to the client.
function sendEvent(string $name, string $data): void {
    echo "event: {$name}\n";
    echo "data: {$data}\n\n";
    flush();
}

function fetchEvents(int $userId): array|false {
    $conn = getConnection();
    if (!$conn) return false;

    $stmt = $conn->prepare("
        SELECT
            e.id,
            e.title,
            e.description,
            e.date,
            e.time,
            e.location,
            e.capacity,
            COUNT(r.id)                                            AS registered_count,
            MAX(CASE WHEN r.user_id = ? THEN 1 ELSE 0 END)        AS is_registered
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        GROUP BY e.id
        ORDER BY e.date ASC
    ");

    if (!$stmt) {
        error_log('events_stream.php prepare failed: ' . $conn->error);
        $conn->close();
        return false;
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        error_log('events_stream.php execute failed: ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $rows;
}

// Main SSE loop 
$lastHash    = '';
$heartbeat   = 0;
$startTime   = time();
$INTERVAL    = 2;    
$HB_INTERVAL = 20;  
$MAX_RUNTIME = 3600; 

while (true) {
    $now = time();

    if ($now - $startTime >= $MAX_RUNTIME) break;

    if ($now - $heartbeat >= $HB_INTERVAL) {
        sendEvent('heartbeat', '{}');
        $heartbeat = $now;
    }

    // Fetch current events
    $events = fetchEvents($userId);

    if ($events !== false) {
        $json = json_encode(array_values($events), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            error_log('events_stream.php json_encode failed: ' . json_last_error_msg());
        } else {
            $hash = md5($json);
            // Only push to client when something actually changed
            if ($hash !== $lastHash) {
                sendEvent('events_update', $json);
                $lastHash = $hash;
            }
        }
    }

    sleep($INTERVAL);

    // Check disconnect AFTER sleep so flush() had time to detect it
    if (connection_aborted()) break;
}