<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireLogin();

$userId = (int)$_SESSION['user_id'];
$conn   = getConnection();

$stmt = $conn->prepare("
    SELECT e.id, e.title, e.description, e.date, e.time, e.location, e.capacity,
    r.registered_at,
    (SELECT COUNT(*) FROM registrations WHERE event_id = e.id) AS registered_count
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    ORDER BY e.date ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$myEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$pageTitle = 'My Events';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 1.75rem;">
        <div>
            <h1 class="page-title">My Events</h1>
        </div>
        <a href="/event-system/events.php" class="btn btn-primary">Browse More Events</a>
    </div>

    <?php if (empty($myEvents)): ?>
        <div class="section-card">
            <div class="empty-state" style="padding: 4rem 2rem;">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h6"/>
                        <path d="M15 3h6v6"/><path d="M10 14L21 3"/>
                    </svg>
                </div>
                <p class="empty-title">No registrations yet</p>
                <p class="empty-sub">You haven't signed up for any events. Go explore what's available!</p>
                <a href="/event-system/events.php" class="btn btn-primary" style="margin-top: 1.25rem;">Browse Events</a>
            </div>
        </div>

    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($myEvents as $ev):
                $isPast = strtotime($ev['date']) < strtotime('today');
            ?>
            <div class="event-card">
                <div class="event-card-accent" style="background: <?= $isPast
                    ? 'var(--border)'
                    : 'linear-gradient(90deg, var(--amber), var(--amber-light))' ?>;"></div>

                <div class="event-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.25rem;">
                        <h3 class="event-card-title" style="margin: 0;"><?= htmlspecialchars($ev['title']) ?></h3>
                        <?php if ($isPast): ?>
                            <span class="badge badge-past" style="flex-shrink: 0;">Past</span>
                        <?php else: ?>
                            <span class="badge badge-registered" style="flex-shrink: 0;">✓ Registered</span>
                        <?php endif; ?>
                    </div>

                    <p class="event-card-desc"><?= htmlspecialchars($ev['description']) ?></p>

                    <div class="event-meta">
                        <div class="event-meta-item">
                            <span class="event-meta-icon">◷</span>
                            <span><?= date('F j, Y', strtotime($ev['date'])) ?> at <?= date('g:i A', strtotime($ev['time'])) ?></span>
                        </div>
                        <div class="event-meta-item">
                            <span class="event-meta-icon">◎</span>
                            <span><?= htmlspecialchars($ev['location']) ?></span>
                        </div>
                        <div class="event-meta-item">
                            <span class="event-meta-icon">📋</span>
                            <span>Registered on <?= date('M j, Y', strtotime($ev['registered_at'])) ?></span>
                        </div>
                        <div class="event-meta-item">
                            <span class="event-meta-icon">👥</span>
                            <span><?= (int)$ev['registered_count'] ?> / <?= (int)$ev['capacity'] ?> attending</span>
                        </div>
                    </div>
                </div>

                <?php if (!$isPast): ?>
                <div class="event-card-footer">
                    <span class="event-count-badge"><?= (int)$ev['registered_count'] ?>/<?= (int)$ev['capacity'] ?> spots filled</span>
                    <form method="POST" action="/event-system/actions/register_event.php">
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-confirm-delete">Cancel Registration</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="event-card-footer">
                    <span style="font-size: 0.775rem; color: var(--text-m); font-style: italic;">This event has ended.</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>