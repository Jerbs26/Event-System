<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireAdmin();

$conn = getConnection();

$totalUsers  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalEvents = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
$totalRegs   = $conn->query("SELECT COUNT(*) as c FROM registrations")->fetch_assoc()['c'];
$upcomingEvt = $conn->query("SELECT COUNT(*) as c FROM events WHERE date >= CURDATE()")->fetch_assoc()['c'];

// Recent events with registration counts
$recentEvents = $conn->query("
    SELECT e.id, e.title, e.date, e.location, e.capacity,
    COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent registrations
$recentRegs = $conn->query("
    SELECT u.name AS user_name, e.title AS event_title, r.registered_at
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registered_at DESC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

$conn->close();

$pageTitle = 'Admin Dashboard';
$base = '/';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="stats-grid">

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

        <div class="stat-card blue">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" data-count="<?= $totalRegs ?>"><?= $totalRegs ?></div>
                <div class="stat-label">Registrations</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" data-count="<?= $upcomingEvt ?>"><?= $upcomingEvt ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
        </div>

    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; flex-wrap:wrap;" class="admin-two-col">

        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Events</span>
                <a href="/event-system/admin/events.php" class="btn btn-secondary btn-sm">Manage →</a>
            </div>
            <?php if (empty($recentEvents)): ?>
                <p style="color:var(--text-muted); font-size:0.87rem; text-align:center; padding:1.5rem 0;">No events yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php foreach ($recentEvents as $ev): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem; background:var(--bg-elevated); border-radius:var(--radius-sm);">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem; color:var(--text-primary);"><?= htmlspecialchars($ev['title']) ?></div>
                            <div style="font-size:0.78rem; color:var(--text-muted);"><?= date('M j, Y', strtotime($ev['date'])) ?> · <?= $ev['reg_count'] ?>/<?= $ev['capacity'] ?> registered</div>
                        </div>
                        <?php if (strtotime($ev['date']) >= strtotime('today')): ?>
                            <span class="badge badge-upcoming">Upcoming</span>
                        <?php else: ?>
                            <span class="badge badge-past">Past</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Registrations</span>
                <a href="/event-system/admin/reports.php" class="btn btn-secondary btn-sm">Full Report →</a>
            </div>
            <?php if (empty($recentRegs)): ?>
                <p style="color:var(--text-muted); font-size:0.87rem; text-align:center; padding:1.5rem 0;">No registrations yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:0.6rem;">
                    <?php foreach ($recentRegs as $reg): ?>
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0.75rem; background:var(--bg-elevated); border-radius:var(--radius-sm);">
                        <div>
                            <div style="font-size:0.87rem; font-weight:600; color:var(--text-primary);"><?= htmlspecialchars($reg['user_name']) ?></div>
                            <div style="font-size:0.77rem; color:var(--text-muted);"><?= htmlspecialchars($reg['event_title']) ?></div>
                        </div>
                        <span style="font-size:0.75rem; color:var(--text-muted);"><?= date('M j', strtotime($reg['registered_at'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <span class="card-title">Quick Actions</span>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="/event-system/admin/events.php" class="btn btn-primary">+ Create Event</a>
            <a href="/event-system/admin/users.php" class="btn btn-secondary">View All Users</a>
            <a href="/event-system/admin/reports.php" class="btn btn-secondary">View Reports</a>
        </div>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .admin-two-col { grid-template-columns: 1fr !important; }
}
</style>