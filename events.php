<?php
// events.php
// Browse all available events — with real-time updates via SSE

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireLogin();

$userId = (int)$_SESSION['user_id'];
$conn   = getConnection();

$stmt = $conn->prepare("
    SELECT
        e.*,
        COUNT(r.id)                                         AS registered_count,
        MAX(CASE WHEN r.user_id = ? THEN 1 ELSE 0 END)     AS is_registered
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    WHERE e.date >= CURDATE()
    GROUP BY e.id
    ORDER BY e.date ASC
");

if (!$stmt) {
    error_log('events.php prepare failed: ' . $conn->error);
    $events = [];
} else {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();

function renderEventCard(array $ev): string {
    $isFull    = (int)$ev['registered_count'] >= (int)$ev['capacity'];
    $isReg     = (bool)(int)$ev['is_registered'];
    $searchStr = htmlspecialchars(strtolower($ev['title'] . ' ' . $ev['location'] . ' ' . ($ev['description'] ?? '')), ENT_QUOTES);
    $title     = htmlspecialchars($ev['title']);
    $desc      = htmlspecialchars($ev['description'] ?? '');
    $loc       = htmlspecialchars($ev['location']);
    $dateStr   = date('F j, Y', strtotime($ev['date']));
    $timeStr   = date('g:i A', strtotime($ev['time']));
    $id        = (int)$ev['id'];
    $regCount  = (int)$ev['registered_count'];
    $capacity  = (int)$ev['capacity'];

    if ($isFull && !$isReg) {
        $badge = '<span class="badge badge-cancelled">Full</span>';
    } elseif ($isReg) {
        $badge = '<span class="badge badge-registered">✓ Registered</span>';
    } else {
        $badge = '<span class="badge badge-upcoming">Open</span>';
    }

    $action = '';
    if (!$isFull && !$isReg) {
        $action = '<form method="POST" action="/event-system/actions/register_event.php">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="event_id" value="' . $id . '">
            <button type="submit" class="btn btn-primary btn-sm">Register →</button>
        </form>';
    }

    return '
    <div class="event-card" data-id="' . $id . '" data-search="' . $searchStr . '" data-date="' . htmlspecialchars($ev['date']) . '">
        <div class="event-card-accent" style="background: linear-gradient(90deg, var(--amber), var(--amber-light));"></div>
        <div class="event-card-body">
            <div class="event-card-toggle" onclick="toggleDesc(this)">
                <h3 class="event-card-title">' . $title . '</h3>
                <span class="event-card-chevron">▾</span>
            </div>
            <div class="event-card-desc-wrap">
                <p class="event-card-desc">' . $desc . '</p>
            </div>
            <div class="event-meta">
                <div class="event-meta-item">
                    <span class="event-meta-icon">◷</span>
                    <span>' . $dateStr . ' at ' . $timeStr . '</span>
                </div>
                <div class="event-meta-item">
                    <span class="event-meta-icon">◎</span>
                    <span>' . $loc . '</span>
                </div>
                <div class="event-meta-item">
                    <span class="event-meta-icon">👥</span>
                    <span>' . $regCount . ' / ' . $capacity . ' registered</span>
                </div>
            </div>
        </div>
        <div class="event-card-footer">
            ' . $badge . '
            ' . $action . '
        </div>
    </div>';
}

$pageTitle = 'Browse Events';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 1.75rem;">
        <div>
            <h1 class="page-title">Events</h1>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="search-bar">
        <div class="search-group">
            <label class="form-label" for="searchInput">Search Events</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Search by title or location...">
        </div>
        <div class="search-group" style="flex: 0 0 200px;">
            <label class="form-label" for="filterDate">Filter by Date</label>
            <input type="date" id="filterDate" class="form-control">
        </div>
    </div>

    <!-- Notification Banner -->
    <div id="updateBanner" class="update-banner" style="display:none; margin-bottom: 1.25rem;">
        <span id="updateBannerMsg">Events have been updated.</span>
    </div>

    <!-- Events Grid -->
    <div class="events-grid" id="eventsGrid">
        <?php if (empty($events)): ?>
            <div class="empty-state" id="emptyState" style="grid-column: 1 / -1;">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <p class="empty-title">No events available</p>
                <p class="empty-sub">Check back soon — new events are added regularly!</p>
            </div>
        <?php else: ?>
            <?php foreach ($events as $ev): ?>
                <?= renderEventCard($ev) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<style>
@keyframes cardFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.event-card { animation: cardFadeIn 0.35s ease both; }

/* Expand/Collapse */
.event-card-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    gap: 0.5rem;
    user-select: none;
}
.event-card-chevron {
    font-size: 1rem;
    color: var(--amber);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    line-height: 1;
}
.event-card-desc-wrap {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
}
.event-card-desc-wrap.open {
    max-height: 200px;
    opacity: 1;
}
</style>

<script>
/* Expand/collapse description */
window.toggleDesc = function(toggleEl) {
    var wrap    = toggleEl.nextElementSibling;
    var chevron = toggleEl.querySelector('.event-card-chevron');
    var isOpen  = wrap.classList.contains('open');
    wrap.classList.toggle('open', !isOpen);
    chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
};

(function () {
    'use strict';

    const grid       = document.getElementById('eventsGrid');
    const banner     = document.getElementById('updateBanner');
    const bannerMsg  = document.getElementById('updateBannerMsg');
    const searchInp  = document.getElementById('searchInput');
    const filterDate = document.getElementById('filterDate');

    function applyFilters() {
        var q    = searchInp.value.toLowerCase().trim();
        var date = filterDate.value;
        var hasVisible = false;

        grid.querySelectorAll('.event-card[data-id]').forEach(function(card) {
            var matchQ    = !q    || card.dataset.search.includes(q);
            var matchDate = !date || card.dataset.date === date;
            var show = matchQ && matchDate;
            card.style.display = show ? '' : 'none';
            if (show) hasVisible = true;
        });

        var noResults = document.getElementById('noResults');
        if (!hasVisible && grid.querySelectorAll('.event-card[data-id]').length > 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.id = 'noResults';
                noResults.className = 'empty-state';
                noResults.style.gridColumn = '1 / -1';
                noResults.innerHTML = '<div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>'
                    + '<p class="empty-title">No results found</p>'
                    + '<p class="empty-sub">Try adjusting your search or date filter.</p>';
                grid.appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    searchInp.addEventListener('input', applyFilters);
    filterDate.addEventListener('change', applyFilters);

    function escHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatTime(t) {
        if (!t) return '';
        var parts = t.split(':');
        var hr    = parseInt(parts[0], 10);
        var min   = parts[1] || '00';
        var ampm  = hr >= 12 ? 'PM' : 'AM';
        return (hr % 12 || 12) + ':' + min + ' ' + ampm;
    }

    function formatDate(d) {
        if (!d) return '';
        return new Date(d + 'T00:00:00').toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    function buildCard(ev) {
        var today  = new Date(); today.setHours(0, 0, 0, 0);
        var evDate = new Date((ev.date || '') + 'T00:00:00');
        var isPast = evDate < today;

        if (isPast) return null;

        var isFull = parseInt(ev.registered_count, 10) >= parseInt(ev.capacity, 10);
        var isReg  = parseInt(ev.is_registered, 10) === 1;

        var badge = (isFull && !isReg)
            ? '<span class="badge badge-cancelled">Full</span>'
            : isReg
                ? '<span class="badge badge-registered">✓ Registered</span>'
                : '<span class="badge badge-upcoming">Open</span>';

        var action = '';
        if (!isFull && !isReg) {
            action = '<form method="POST" action="/event-system/actions/register_event.php">'
                + '<input type="hidden" name="action" value="register">'
                + '<input type="hidden" name="event_id" value="' + escHtml(ev.id) + '">'
                + '<button type="submit" class="btn btn-primary btn-sm">Register →</button>'
                + '</form>';
        }

        var searchStr = ((ev.title || '') + ' ' + (ev.location || '') + ' ' + (ev.description || '')).toLowerCase();

        var div = document.createElement('div');
        div.className      = 'event-card';
        div.dataset.id     = ev.id;
        div.dataset.search = searchStr;
        div.dataset.date   = ev.date || '';
        div.innerHTML = ''
            + '<div class="event-card-accent" style="background: linear-gradient(90deg, var(--amber), var(--amber-light));"></div>'
            + '<div class="event-card-body">'
            +   '<div class="event-card-toggle" onclick="toggleDesc(this)">'
            +     '<h3 class="event-card-title">' + escHtml(ev.title) + '</h3>'
            +     '<span class="event-card-chevron">▾</span>'
            +   '</div>'
            +   '<div class="event-card-desc-wrap">'
            +     '<p class="event-card-desc">' + escHtml(ev.description) + '</p>'
            +   '</div>'
            +   '<div class="event-meta">'
            +     '<div class="event-meta-item"><span class="event-meta-icon">◷</span><span>' + formatDate(ev.date) + ' at ' + formatTime(ev.time) + '</span></div>'
            +     '<div class="event-meta-item"><span class="event-meta-icon">◎</span><span>' + escHtml(ev.location) + '</span></div>'
            +     '<div class="event-meta-item"><span class="event-meta-icon">👥</span><span>' + escHtml(ev.registered_count) + ' / ' + escHtml(ev.capacity) + ' registered</span></div>'
            +   '</div>'
            + '</div>'
            + '<div class="event-card-footer">' + badge + ' ' + action + '</div>';
        return div;
    }

    var bannerTimer = null;
    function showBanner(msg) {
        bannerMsg.textContent = msg;
        banner.style.display  = 'flex';
        if (bannerTimer) clearTimeout(bannerTimer);
        bannerTimer = setTimeout(function() { banner.style.display = 'none'; }, 5000);
    }

    var reconnectTimer = null;

    function connect() {
        if (!window.EventSource) return;
        var es;
        try { es = new EventSource('/event-system/actions/events_stream.php'); }
        catch (err) { return; }

        es.addEventListener('events_update', function(e) {
            var events;
            try { events = JSON.parse(e.data); }
            catch (err) { return; }
            if (!Array.isArray(events)) return;

            var existingCards = {};
            grid.querySelectorAll('.event-card[data-id]').forEach(function(c) {
                existingCards[c.dataset.id] = c;
            });

            var incomingIds = {};
            events.forEach(function(ev) {
                var today  = new Date(); today.setHours(0, 0, 0, 0);
                var evDate = new Date((ev.date || '') + 'T00:00:00');
                if (evDate >= today) incomingIds[String(ev.id)] = true;
            });

            var removed = 0;
            Object.keys(existingCards).forEach(function(id) {
                if (!incomingIds[id]) {
                    var card = existingCards[id];
                    card.style.transition = 'opacity 0.4s, transform 0.4s';
                    card.style.opacity    = '0';
                    card.style.transform  = 'scale(0.95)';
                    setTimeout(function() { if (card.parentNode) card.remove(); }, 420);
                    removed++;
                }
            });

            var added = 0;
            events.forEach(function(ev) {
                if (!existingCards[String(ev.id)]) {
                    var card = buildCard(ev);
                    if (card) { grid.appendChild(card); added++; }
                }
            });

            setTimeout(function() {
                var remaining  = grid.querySelectorAll('.event-card[data-id]').length;
                var emptyState = document.getElementById('emptyState');
                if (remaining === 0 && !emptyState) {
                    emptyState           = document.createElement('div');
                    emptyState.id        = 'emptyState';
                    emptyState.className = 'empty-state';
                    emptyState.style.gridColumn = '1 / -1';
                    emptyState.innerHTML = '<div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>'
                        + '<p class="empty-title">No events available</p>'
                        + '<p class="empty-sub">Check back soon!</p>';
                    grid.appendChild(emptyState);
                } else if (remaining > 0 && emptyState) {
                    emptyState.remove();
                }
                if (removed > 0) showBanner(removed === 1 ? 'An event was removed.' : removed + ' events were removed.');
                if (added > 0)   showBanner(added   === 1 ? 'A new event is available!' : added + ' new events are available!');
                applyFilters();
            }, 450);
        });

        es.onerror = function() {
            es.close();
            if (reconnectTimer) clearTimeout(reconnectTimer);
            reconnectTimer = setTimeout(connect, 5000);
        };
    }

    connect();
})();
</script>