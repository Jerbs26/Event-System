<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireAdmin();

$conn = getConnection();

// Selected event filter
$selectedEvent = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// All events for dropdown
$allEvents = $conn->query("SELECT id, title, date FROM events ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);

// Overview stats
$totalRegs   = $conn->query("SELECT COUNT(*) as c FROM registrations")->fetch_assoc()['c'];
$totalEvents = count($allEvents);
$totalUsers  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];

// Registrations per event
$perEvent = $conn->query("
    SELECT e.id, e.title, e.date, e.location, e.capacity,
           COUNT(r.id) AS reg_count,
           ROUND((COUNT(r.id) / e.capacity) * 100, 0) AS fill_pct
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY e.date DESC
")->fetch_all(MYSQLI_ASSOC);

// Participants for selected event
$participants = [];
if ($selectedEvent > 0) {
    $stmt = $conn->prepare("
        SELECT u.name, u.email, r.registered_at
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.event_id = ?
        ORDER BY r.registered_at ASC
    ");
    $stmt->bind_param("i", $selectedEvent);
    $stmt->execute();
    $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get event info
    $stmt2 = $conn->prepare("SELECT title, date, location FROM events WHERE id = ?");
    $stmt2->bind_param("i", $selectedEvent);
    $stmt2->execute();
    $selectedEventInfo = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();
}

$conn->close();

$pageTitle = 'Reports';
$base = '/';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <h1>Analytics</h1>
    </div>

    <div class="stats-grid" style="margin-bottom:2.5rem;">

        <div class="stat-card teal">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" data-count="<?= $totalEvents ?>"><?= $totalEvents ?></div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" data-count="<?= $totalUsers ?>"><?= $totalUsers ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" data-count="<?= $totalRegs ?>"><?= $totalRegs ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
        </div>

    </div>

    <div class="section-header">
        <h2 class="section-title">Registrations Per Event</h2>
    </div>

    <div class="table-wrap" style="margin-bottom:2.5rem;">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Registered</th>
                    <th>Capacity</th>
                    <th>Fill Rate</th>
                    <th>Participants</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perEvent as $ev): ?>
                <tr>
                    <td class="td-primary"><?= htmlspecialchars($ev['title']) ?></td>
                    <td><?= date('M j, Y', strtotime($ev['date'])) ?></td>
                    <td><?= htmlspecialchars($ev['location']) ?></td>
                    <td style="font-weight:700; color:var(--accent);"><?= $ev['reg_count'] ?></td>
                    <td><?= $ev['capacity'] ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <div style="flex:1; background:var(--border); border-radius:4px; height:6px; overflow:hidden; min-width:60px;">
                                <div style="width:<?= min($ev['fill_pct'], 100) ?>%; height:100%; background:<?= $ev['fill_pct'] >= 90 ? 'var(--error)' : ($ev['fill_pct'] >= 60 ? 'var(--warning)' : 'var(--success)') ?>; border-radius:4px;"></div>
                            </div>
                            <span style="font-size:0.8rem; font-weight:600; color:var(--text-secondary); min-width:30px;"><?= $ev['fill_pct'] ?>%</span>
                        </div>
                    </td>
                    <td>
                        <a href="/event-system/admin/reports.php?event_id=<?= $ev['id'] ?>"
                            class="btn btn-secondary btn-sm <?= $selectedEvent === $ev['id'] ? 'btn-primary' : '' ?>">
                            <?= $selectedEvent === $ev['id'] ? '▼ Viewing' : 'View List' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($selectedEvent > 0): ?>
    <div class="section-header">
        <div>
            <h2 class="section-title">Participants: <?= htmlspecialchars($selectedEventInfo['title']) ?></h2>
            <p style="color:var(--text-secondary); font-size:0.87rem; margin-top:0.25rem;">
                <?= date('F j, Y', strtotime($selectedEventInfo['date'])) ?> · <?= htmlspecialchars($selectedEventInfo['location']) ?> · <?= count($participants) ?> registered
            </p>
        </div>
        <a href="/event-system/admin/reports.php" class="btn btn-secondary btn-sm">Clear Filter</a>
    </div>

    <?php if (empty($participants)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👤</div>
            <h3>No participants yet</h3>
            <p>Nobody has registered for this event.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $i => $p): ?>
                    <tr>
                        <td style="color:var(--text-muted); font-size:0.8rem;"><?= $i + 1 ?></td>
                        <td class="td-primary"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="color:var(--text-secondary);"><?= htmlspecialchars($p['email']) ?></td>
                        <td style="color:var(--text-muted); font-size:0.82rem;"><?= date('M j, Y g:i A', strtotime($p['registered_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>