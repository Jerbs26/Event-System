<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireAdmin();

$conn = getConnection();
$events = $conn->query("
    SELECT e.*, COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
$conn->close();

$pageTitle = 'Manage Events';
$base = '/';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header" style="margin-bottom:1.75rem;">
        <div>
            <h1>Manage Events</h1>
        </div>
        <button type="button" class="btn btn-primary" data-modal-open="createEventModal">+ New Event</button>
    </div>

    <div class="search-bar">
        <div class="search-group">
            <label class="form-label" for="searchInput">Search Events</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Search by title or location...">
        </div>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>No events yet</h3>
            <p>Create your first event to get started.</p>
            <button type="button" class="btn btn-primary" style="margin-top:1rem;" data-modal-open="createEventModal">+ Create Event</button>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Date &amp; Time</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $i => $ev): ?>
                    <?php
                        $evId = (int) $ev['id'];
                        $evTitle = htmlspecialchars($ev['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $evDesc = htmlspecialchars($ev['description'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $evLocation = htmlspecialchars($ev['location'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $evCapacity = (int) $ev['capacity'];
                        $evRegCount = (int) $ev['reg_count'];
                        $evDate = $ev['date'];
                        $evTime = !empty($ev['time']) ? substr($ev['time'], 0, 5) : '';
                        $evDateFmt = $evDate ? date('M j, Y', strtotime($evDate)) : '—';
                        $evTimeFmt = $evTime ? date('g:i A', strtotime($evTime))  : '—';
                        $isUpcoming = $evDate && strtotime($evDate) >= strtotime('today');
                        $searchStr = htmlspecialchars(strtolower($ev['title'] . ' ' . $ev['location']), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr data-search="<?= $searchStr ?>">
                        <td style="color:var(--text-m); font-size:0.8rem;"><?= $i + 1 ?></td>
                        <td class="td-primary"><?= htmlspecialchars($ev['title'], ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= $evDateFmt ?><br><small style="color:var(--text-m);"><?= $evTimeFmt ?></small></td>
                        <td><?= htmlspecialchars($ev['location'], ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= $evCapacity ?></td>
                        <td>
                            <span style="color:<?= $evRegCount >= $evCapacity ? 'var(--red)' : 'var(--green)' ?>; font-weight:600;">
                                <?= $evRegCount ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isUpcoming): ?>
                                <span class="badge badge-upcoming">Upcoming</span>
                            <?php else: ?>
                                <span class="badge badge-past">Past</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                <button type="button" class="btn btn-ghost btn-sm btn-edit-event"
                                    data-id="<?= $evId ?>"
                                    data-title="<?= $evTitle ?>"
                                    data-desc="<?= $evDesc ?>"
                                    data-date="<?= htmlspecialchars($evDate, ENT_QUOTES, 'UTF-8') ?>"
                                    data-time="<?= htmlspecialchars($evTime, ENT_QUOTES, 'UTF-8') ?>"
                                    data-location="<?= $evLocation ?>"
                                    data-capacity="<?= $evCapacity ?>">
                                    Edit
                                </button>
                                <a href="/event-system/actions/event_action.php?action=delete&amp;id=<?= $evId ?>"
                                    class="btn btn-danger btn-sm btn-confirm-delete">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- CREATE EVENT MODAL -->
<div class="modal-overlay" id="createEventModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Create New Event</h2>
            <button type="button" class="modal-close" data-modal-close>×</button>
        </div>
        <form method="POST" action="/event-system/actions/event_action.php">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Tech Conference 2025" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Describe the event..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time *</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Location *</label>
                <input type="text" name="location" class="form-control" placeholder="Venue name, address..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-control" value="100" min="1" required>
            </div>
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:0.5rem;">
                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editEventModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Edit Event</h2>
            <button type="button" class="modal-close" data-modal-close>×</button>
        </div>
        <form method="POST" action="/event-system/actions/event_action.php" id="editEventForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="event_id" value="">
            <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time *</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Location *</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-control" min="1" required>
            </div>
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:0.5rem;">
                <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Open modal 
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(this.getAttribute('data-modal-open'));
            if (target) target.classList.add('open');
        });
    });

    // Close modal 
    function closeAllModals() {
        document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
            m.classList.remove('open');
        });
    }

    // ONLY the X button and Cancel button close the modal — nothing else
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', closeAllModals);
    });

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // Edit event: fill modal fields 
    document.querySelectorAll('.btn-edit-event').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('editEventForm');
            if (!form) return;
            form.querySelector('[name="event_id"]').value    = this.dataset.id       || '';
            form.querySelector('[name="title"]').value       = this.dataset.title    || '';
            form.querySelector('[name="description"]').value = this.dataset.desc     || '';
            form.querySelector('[name="date"]').value        = this.dataset.date     || '';
            form.querySelector('[name="time"]').value        = this.dataset.time     || '';
            form.querySelector('[name="location"]').value    = this.dataset.location || '';
            form.querySelector('[name="capacity"]').value    = this.dataset.capacity || '';
            document.getElementById('editEventModal').classList.add('open');
        });
    });

    // Delete confirmation 
    document.querySelectorAll('.btn-confirm-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this event? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Live search filter 
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('tbody tr[data-search]').forEach(function (row) {
                row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
            });
        });
    }

});
</script>