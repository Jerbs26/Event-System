<?php
// includes/header.php
// Sidebar layout for authenticated pages

if (session_status() === PHP_SESSION_NONE) session_start();

$base        = '/event-system/';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$inAdmin     = strpos($_SERVER['REQUEST_URI'], '/event-system/admin/') !== false;
$userName    = isset($_SESSION['name']) ? htmlspecialchars(explode(' ', $_SESSION['name'])[0]) : 'User';
$userRole    = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — EventHub' : 'EventHub' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <script src="<?= $base ?>assets/js/script.js" defer></script>
</head>
<body>

<div class="app-shell">

    <!-- ── SIDEBAR ── -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= $userName ?></div>
                <div class="sidebar-user-role"><?= ucfirst($userRole) ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php if ($userRole === 'admin'): ?>
                <div class="sidebar-section-label">Admin</div>
                <a href="<?= $base ?>admin/dashboard.php" class="sidebar-link <?= ($inAdmin && $currentPage === 'dashboard') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="<?= $base ?>admin/events.php" class="sidebar-link <?= ($inAdmin && $currentPage === 'events') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Events
                </a>
                <a href="<?= $base ?>admin/users.php" class="sidebar-link <?= ($inAdmin && $currentPage === 'users') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Users
                </a>
                <a href="<?= $base ?>admin/reports.php" class="sidebar-link <?= ($inAdmin && $currentPage === 'reports') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Reports
                </a>
            <?php else: ?>
                <div class="sidebar-section-label">Menu</div>
                <a href="<?= $base ?>dashboard.php" class="sidebar-link <?= (!$inAdmin && $currentPage === 'dashboard') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="<?= $base ?>events.php" class="sidebar-link <?= (!$inAdmin && $currentPage === 'events') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Events
                </a>
                <a href="<?= $base ?>my_events.php" class="sidebar-link <?= (!$inAdmin && $currentPage === 'my_events') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    My Events
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= $base ?>logout.php" class="sidebar-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sign Out
            </a>
        </div>

    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── MAIN AREA ── -->
    <div class="main-area">

        <div class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'EventHub' ?></span>
        </div>

        <div class="page-content">

<?php
if (function_exists('getFlash')) {
    $flash = getFlash();
    if ($flash): ?>
        <div class="flash-message flash-<?= htmlspecialchars($flash['type']) ?>" id="flashMsg">
            <span><?= htmlspecialchars($flash['message']) ?></span>
            <button onclick="this.parentElement.remove()" class="flash-close" aria-label="Dismiss">&times;</button>
        </div>
    <?php endif;
}
?>