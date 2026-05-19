<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

requireLogin();

if (isAdmin()) {
    header('Location: /event-system/admin/dashboard.php');
    exit();
}

unset(
    $_SESSION['flash_type'],
    $_SESSION['flash_message'],
    $_SESSION['flash'],
    $_SESSION['error'],
    $_SESSION['success']
);

$userId = (int)$_SESSION['user_id'];
$conn   = getConnection();

// Stats 
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

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-wrapper">

    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Dashboard</h1>
        </div>
    </div>

    <div class="stats-grid">

        <div class="stat-card amber">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="statTotal"><?= $totalEvents ?></div>
                <div class="stat-label">Available Events</div>
            </div>
        </div>

        <div class="stat-card teal">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="statRegs"><?= $myRegs ?></div>
                <div class="stat-label">My Registrations</div>
            </div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value" id="statUpcoming"><?= $upcoming ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
        </div>

    </div>

    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">My Registered Events</h2>
            <a href="/event-system/my_events.php" class="section-link">View all &rarr;</a>
        </div>

        <div id="eventsTableWrap">
            <?php if (empty($myEvents)): ?>
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:4rem 2rem; text-align:center;">

                    <svg width="180" height="160" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:2rem;">
                        <rect x="30" y="25" width="120" height="110" rx="12" fill="#fef3c7" stroke="#f59e0b" stroke-width="2.5"/>
                        <rect x="30" y="25" width="120" height="35" rx="12" fill="#f59e0b"/>
                        <rect x="30" y="45" width="120" height="15" fill="#f59e0b"/>
                        <rect x="60" y="14" width="8" height="22" rx="4" fill="#d97706"/>
                        <rect x="112" y="14" width="8" height="22" rx="4" fill="#d97706"/>
                        <circle cx="60" cy="80" r="5" fill="#fbbf24"/>
                        <circle cx="90" cy="80" r="5" fill="#fbbf24"/>
                        <circle cx="120" cy="80" r="5" fill="#f59e0b"/>
                        <circle cx="60" cy="105" r="5" fill="#fbbf24"/>
                        <circle cx="90" cy="105" r="5" fill="#fbbf24"/>
                        <circle cx="120" cy="105" r="5" fill="#fbbf24"/>
                        <circle cx="22" cy="30" r="4" fill="#fde68a" opacity="0.7"/>
                        <circle cx="158" cy="120" r="6" fill="#fde68a" opacity="0.5"/>
                        <circle cx="15" cy="100" r="3" fill="#f59e0b" opacity="0.4"/>
                        <circle cx="165" cy="45" r="3" fill="#f59e0b" opacity="0.4"/>
                        <line x1="50" y1="37" x2="130" y2="37" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
                        <line x1="50" y1="47" x2="100" y2="47" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.3"/>
                    </svg>

                    <h3 style="font-family:var(--font-serif); font-size:1.6rem; font-weight:400; color:var(--text-h); margin-bottom:0.5rem; letter-spacing:-0.01em;">
                        No events yet
                    </h3>
                    <p style="font-size:0.95rem; color:var(--text-m); max-width:320px; line-height:1.7; margin-bottom:2rem;">
                        You haven't joined any events yet. Discover exciting events happening around you and start registering today!
                    </p>

                    <a href="/event-system/events.php" class="btn btn-primary btn-lg" style="gap:0.6rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Discover Events
                    </a>

                </div>
            <?php else: ?>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Registered</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="eventsTableBody">
                            <?php foreach ($myEvents as $ev): ?>
                                <tr>
                                    <td class="td-title"><?= htmlspecialchars($ev['title']) ?></td>
                                    <td><?= date('M j, Y', strtotime($ev['date'])) ?></td>
                                    <td><?= date('g:i A', strtotime($ev['time'])) ?></td>
                                    <td><?= htmlspecialchars($ev['location']) ?></td>
                                    <td class="td-muted"><?= date('M j', strtotime($ev['registered_at'])) ?></td>
                                    <td>
                                        <form method="POST"
                                                action="/event-system/actions/register_event.php"
                                                onsubmit="return confirm('Cancel registration for this event?')"
                                                style="margin:0;">
                                            <input type="hidden" name="action"   value="cancel">
                                            <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                            <button type="submit" class="btn-cancel">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
(function () {
    const POLL_INTERVAL = 15000;

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function updateStats(stats) {
        var map = {
            statTotal:    stats.total_events,
            statRegs:     stats.my_regs,
            statUpcoming: stats.upcoming
        };
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.textContent !== String(map[id])) {
                el.style.transition = 'opacity 0.2s';
                el.style.opacity = '0';
                setTimeout(function() {
                    el.textContent = map[id];
                    el.style.opacity = '1';
                }, 200);
            }
        });
    }

    function renderRow(ev) {
        var dateStr = new Date(ev.date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        var timeStr = ev.time
            ? new Date('1970-01-01T' + ev.time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
            : '—';
        var regDate = new Date(ev.registered_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

        var cancelForm = '<form method="POST" action="/event-system/actions/register_event.php"'
            + ' onsubmit="return confirm(\'Cancel registration for this event?\')"'
            + ' style="margin:0;">'
            + '<input type="hidden" name="action" value="cancel">'
            + '<input type="hidden" name="event_id" value="' + parseInt(ev.id) + '">'
            + '<button type="submit" class="btn-cancel">Cancel</button>'
            + '</form>';

        return '<tr>'
            + '<td class="td-title">' + escHtml(ev.title) + '</td>'
            + '<td>' + escHtml(dateStr) + '</td>'
            + '<td>' + escHtml(timeStr) + '</td>'
            + '<td>' + escHtml(ev.location) + '</td>'
            + '<td class="td-muted">' + escHtml(regDate) + '</td>'
            + '<td>' + cancelForm + '</td>'
            + '</tr>';
    }

    function updateTable(events) {
        var wrap = document.getElementById('eventsTableWrap');
        if (!wrap) return;

        if (!events || events.length === 0) {
            wrap.innerHTML = '<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:4rem 2rem; text-align:center;">'
                + '<svg width="180" height="160" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:2rem;">'
                + '<rect x="30" y="25" width="120" height="110" rx="12" fill="#fef3c7" stroke="#f59e0b" stroke-width="2.5"/>'
                + '<rect x="30" y="25" width="120" height="35" rx="12" fill="#f59e0b"/>'
                + '<rect x="30" y="45" width="120" height="15" fill="#f59e0b"/>'
                + '<rect x="60" y="14" width="8" height="22" rx="4" fill="#d97706"/>'
                + '<rect x="112" y="14" width="8" height="22" rx="4" fill="#d97706"/>'
                + '<circle cx="60" cy="80" r="5" fill="#fbbf24"/>'
                + '<circle cx="90" cy="80" r="5" fill="#fbbf24"/>'
                + '<circle cx="120" cy="80" r="5" fill="#f59e0b"/>'
                + '<circle cx="60" cy="105" r="5" fill="#fbbf24"/>'
                + '<circle cx="90" cy="105" r="5" fill="#fbbf24"/>'
                + '<circle cx="120" cy="105" r="5" fill="#fbbf24"/>'
                + '</svg>'
                + '<h3 style="font-family:var(--font-serif); font-size:1.6rem; font-weight:400; color:var(--text-h); margin-bottom:0.5rem;">No events yet</h3>'
                + '<p style="font-size:0.95rem; color:var(--text-m); max-width:320px; line-height:1.7; margin-bottom:2rem;">You haven\'t joined any events yet. Discover exciting events happening around you and start registering today!</p>'
                + '<a href="/event-system/events.php" class="btn btn-primary btn-lg">Discover Events</a>'
                + '</div>';
            return;
        }

        var tbody = document.getElementById('eventsTableBody');
        if (tbody) tbody.innerHTML = events.map(renderRow).join('');
    }

    async function poll() {
        try {
            var res = await fetch('/event-system/actions/dashboard_data.php', { credentials: 'same-origin' });
            if (!res.ok) return;
            var data = await res.json();
            if (data.stats)                       updateStats(data.stats);
            if (data.my_events !== undefined)     updateTable(data.my_events);
        } catch (e) { /* silently ignore */ }
    }

    setInterval(poll, POLL_INTERVAL);
})();
</script>