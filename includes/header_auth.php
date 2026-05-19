<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$base        = '/event-system/';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
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
<body class="auth-layout">

<header class="topnav">
    <a class="topnav-brand" href="<?= $base ?>index.php">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <span class="brand-name">EventHub</span>
    </a>

    <nav class="topnav-links" aria-label="Primary navigation">
        <a href="<?= $base ?>login.php"    class="topnav-link <?= $currentPage === 'login'    ? 'active' : '' ?>">Sign In</a>
        <a href="<?= $base ?>register.php" class="topnav-btn-primary">Register</a>
    </nav>
</header>

<main class="auth-main">