<?php

require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — EventHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/event-system/assets/css/style.css">

    <style>
        /* ── Auth Page Override — Dark Theme ── */
        html, body {
            margin: 0; padding: 0;
            background: #080A0E;
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── TOP NAV ── */
        .rg-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 40px;
            background: rgba(8,10,14,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .rg-nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .rg-nav-logo {
            width: 36px; height: 36px; border-radius: 9px;
            background: #F59E0B;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 20px rgba(245,158,11,0.4);
            flex-shrink: 0;
        }
        .rg-nav-logo svg { width: 18px; height: 18px; stroke: #111; stroke-width: 2.2; }
        .rg-nav-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.05rem; font-weight: 700;
            color: #fff; letter-spacing: -0.03em;
        }
        .rg-nav-links { display: flex; align-items: center; gap: 8px; }
        .rg-nav-link {
            padding: 8px 16px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.55);
            font-size: 0.875rem; font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .rg-nav-link:hover {
            color: #fff; background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.15);
        }
        .rg-nav-cta {
            padding: 9px 20px; border-radius: 8px;
            background: #F59E0B; color: #1A0E00;
            font-size: 0.875rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(245,158,11,0.35);
        }
        .rg-nav-cta:hover {
            background: #FBBF24; transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(245,158,11,0.5);
        }

        /* ── PAGE WRAPPER ── */
        .rg-page {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 100px 24px 60px;
            position: relative; overflow: hidden;
        }

        /* Ambient glow */
        .rg-page::before {
            content: '';
            position: absolute; width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%);
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            pointer-events: none;
        }
        /* Grid texture */
        .rg-page::after {
            content: '';
            position: absolute; inset: 0; pointer-events: none; opacity: 0.3;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, black 20%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, black 20%, transparent 100%);
        }

        /* ── CARD ── */
        .rg-card {
            position: relative; z-index: 2;
            width: 100%; max-width: 480px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 24px;
            padding: 44px 40px 40px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(245,158,11,0.08),
                0 24px 64px rgba(0,0,0,0.55),
                inset 0 1px 0 rgba(255,255,255,0.08);
            animation: rgFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }

        /* Amber top glow line */
        .rg-card::before {
            content: '';
            position: absolute; top: 0; left: 20%; right: 20%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(245,158,11,0.7), transparent);
            border-radius: 999px;
        }

        @keyframes rgFadeUp {
            from { opacity: 0; transform: translateY(24px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── CARD HEADER ── */
        .rg-icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.25);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(245,158,11,0.15);
        }
        .rg-icon-wrap svg { width: 24px; height: 24px; stroke: #F59E0B; stroke-width: 1.75; }

        .rg-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem; font-weight: 800;
            color: #fff; letter-spacing: -0.04em; line-height: 1.1;
            margin-bottom: 6px;
        }
        .rg-subtitle {
            font-size: 0.9rem; color: rgba(255,255,255,0.42);
            font-weight: 400; line-height: 1.6;
            margin-bottom: 32px;
        }

        /* ── FLASH ── */
        .rg-flash {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px; border-radius: 10px;
            font-size: 0.845rem; font-weight: 500; line-height: 1.5;
            margin-bottom: 20px;
            animation: rgFadeUp 0.3s ease both;
        }
        .rg-flash-error {
            background: rgba(220,38,38,0.1);
            border: 1px solid rgba(220,38,38,0.25);
            color: #fca5a5;
        }
        .rg-flash svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; stroke: currentColor; stroke-width: 2; }

        /* ── FORM ── */
        .rg-form { display: flex; flex-direction: column; gap: 18px; }

        .rg-field { display: flex; flex-direction: column; gap: 6px; }

        .rg-label {
            font-size: 0.72rem; font-weight: 700;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase; letter-spacing: 0.1em;
        }

        .rg-input-wrap { position: relative; }

        .rg-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.925rem; font-weight: 400;
            outline: none;
            transition: all 0.2s ease;
            -webkit-appearance: none;
        }
        .rg-input::placeholder { color: rgba(255,255,255,0.22); }
        .rg-input:focus {
            border-color: rgba(245,158,11,0.55);
            background: rgba(245,158,11,0.05);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
        }
        .rg-input:focus + .rg-input-icon { color: #F59E0B; }

        /* Input icon (left) */
        .rg-input-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
            transition: color 0.2s ease; pointer-events: none;
        }
        .rg-input-icon svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 1.75; display: block; }
        .rg-input.has-icon { padding-left: 40px; }

        /* Toggle password button */
        .rg-eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,0.25); padding: 4px;
            border-radius: 6px; line-height: 1;
            transition: color 0.2s ease;
            display: flex; align-items: center;
        }
        .rg-eye-btn:hover { color: rgba(255,255,255,0.7); }
        .rg-eye-btn svg { width: 17px; height: 17px; stroke: currentColor; stroke-width: 2; display: block; }

        .rg-input.has-icon.has-eye { padding-right: 40px; }

        /* Password strength bar */
        .rg-strength {
            display: flex; gap: 4px; margin-top: 6px;
        }
        .rg-strength-bar {
            flex: 1; height: 3px; border-radius: 99px;
            background: rgba(255,255,255,0.08);
            transition: background 0.3s ease;
        }
        .rg-strength-bar.weak   { background: #ef4444; }
        .rg-strength-bar.fair   { background: #f59e0b; }
        .rg-strength-bar.strong { background: #10b981; }

        .rg-strength-label {
            font-size: 0.72rem; font-weight: 600;
            color: rgba(255,255,255,0.35); margin-top: 4px;
            min-height: 16px; transition: color 0.3s ease;
        }

        /* ── PASSWORD MATCH INDICATOR ── */
        .rg-match {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 6px;
            transition: all 0.2s ease;
        }
        .rg-match.visible { display: flex; }
        .rg-match svg { width: 14px; height: 14px; stroke: currentColor; stroke-width: 2.5; flex-shrink: 0; }
        .rg-match.match    { color: #10b981; }
        .rg-match.no-match { color: #ef4444; }

        .rg-input.input-match    { border-color: rgba(16,185,129,0.5) !important; box-shadow: 0 0 0 3px rgba(16,185,129,0.1) !important; }
        .rg-input.input-no-match { border-color: rgba(239,68,68,0.5)  !important; box-shadow: 0 0 0 3px rgba(239,68,68,0.1)  !important; }

        /* ── DIVIDER ── */
        .rg-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 4px 0;
        }

        /* ── SUBMIT ── */
        .rg-submit {
            width: 100%; padding: 14px;
            border-radius: 12px; border: none;
            background: #F59E0B; color: #111827;
            font-family: 'Syne', sans-serif;
            font-size: 0.975rem; font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(245,158,11,0.38);
            transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .rg-submit:hover {
            background: #FBBF24; transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,158,11,0.52);
        }
        .rg-submit:active { transform: none; box-shadow: none; }
        .rg-submit svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 2.5; }

        /* ── FOOTER LINK ── */
        .rg-footer {
            text-align: center; margin-top: 24px;
            font-size: 0.875rem; color: rgba(255,255,255,0.35);
        }
        .rg-footer a {
            color: #F59E0B; font-weight: 700;
            text-decoration: none; transition: color 0.2s ease;
        }
        .rg-footer a:hover { color: #FBBF24; }

        /* ── FIELD ERROR ── */
        .rg-field-error {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #ef4444;
            margin-top: 5px;
        }
        .rg-field-error.visible { display: flex; }
        .rg-field-error svg { width: 13px; height: 13px; stroke: currentColor; stroke-width: 2.5; flex-shrink: 0; }
        .rg-input.input-error {
            border-color: rgba(239,68,68,0.5) !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1) !important;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 520px) {
            .rg-nav { padding: 0 20px; }
            .rg-nav-link { display: none; }
            .rg-page { padding: 88px 16px 48px; }
            .rg-card { padding: 32px 24px 28px; }
            .rg-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav class="rg-nav">
        <a class="rg-nav-brand" href="/event-system/index.php">
            <div class="rg-nav-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                    <line x1="3"  y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <span class="rg-nav-name">EventHub</span>
        </a>
        <div class="rg-nav-links">
            <a href="/event-system/login.php"    class="rg-nav-link">Sign In</a>
            <a href="/event-system/register.php" class="rg-nav-cta">Register</a>
        </div>
    </nav>

    <!-- PAGE -->
    <div class="rg-page">
        <div class="rg-card">

            <h1 class="rg-title">Create account</h1>
            <p class="rg-subtitle">Join EventHub and start discovering events</p>

            <!-- Flash error from session (set by register_action.php) -->
            <?php
            if (isset($_SESSION['flash_error'])):
                $err = htmlspecialchars($_SESSION['flash_error']);
                unset($_SESSION['flash_error']);
            ?>
                <div class="rg-flash rg-flash-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= $err ?>
                </div>
            <?php endif; ?>

            <form
                class="rg-form"
                method="POST"
                action="/event-system/actions/register_action.php"
                novalidate
            >
                <!-- Full Name -->
                <div class="rg-field">
                    <label class="rg-label" for="name">Full Name</label>
                    <div class="rg-input-wrap">
                        <span class="rg-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="rg-input has-icon"
                            placeholder="Maria Santos"
                            required
                            minlength="2"
                            autocomplete="name"
                            value="<?= htmlspecialchars($_SESSION['form_name'] ?? '') ?>"
                        >
                    </div>
                    <div class="rg-field-error" id="nameError">
                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="nameErrorText">Full name is required</span>
                    </div>
                </div>

                <!-- Email -->
                <div class="rg-field">
                    <label class="rg-label" for="email">Email Address</label>
                    <div class="rg-input-wrap">
                        <span class="rg-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="rg-input has-icon"
                            placeholder="you@example.com"
                            required
                            autocomplete="email"
                            value="<?= htmlspecialchars($_SESSION['form_email'] ?? '') ?>"
                        >
                    </div>
                    <div class="rg-field-error" id="emailError">
                        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span id="emailErrorText">Email address is required</span>
                    </div>
                </div>

                <div class="rg-divider"></div>

                <!-- Password -->
                <div class="rg-field">
                    <label class="rg-label" for="password">Password</label>
                    <div class="rg-input-wrap">
                        <span class="rg-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="rg-input has-icon has-eye"
                            placeholder="At least 6 characters"
                            required
                            minlength="6"
                            autocomplete="new-password"
                            oninput="checkStrength(this.value)"
                        >
                        <button
                            type="button"
                            class="rg-eye-btn"
                            aria-label="Toggle password visibility"
                            onclick="toggleEye('password','eyeIcon1')"
                        >
                            <svg id="eyeIcon1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Strength bars -->
                    <div class="rg-strength" id="strengthBars">
                        <div class="rg-strength-bar" id="bar1"></div>
                        <div class="rg-strength-bar" id="bar2"></div>
                        <div class="rg-strength-bar" id="bar3"></div>
                        <div class="rg-strength-bar" id="bar4"></div>
                    </div>
                    <div class="rg-strength-label" id="strengthLabel"></div>
                </div>

                <!-- Confirm Password -->
                <div class="rg-field">
                    <label class="rg-label" for="confirm_password">Confirm Password</label>
                    <div class="rg-input-wrap">
                        <span class="rg-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="rg-input has-icon has-eye"
                            placeholder="Repeat your password"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            class="rg-eye-btn"
                            aria-label="Toggle confirm password visibility"
                            onclick="toggleEye('confirm_password','eyeIcon2')"
                        >
                            <svg id="eyeIcon2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Match indicator -->
                    <div class="rg-match" id="matchIndicator">
                        <svg id="matchIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></svg>
                        <span id="matchText"></span>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="rg-submit" style="margin-top: 4px;">
                    Create Account
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <div class="rg-footer">
                Already have an account? <a href="/event-system/login.php">Sign in</a>
            </div>

        </div>
    </div>

<script>
(function () {
    'use strict';

    // Toggle password visibility 
    window.toggleEye = function (inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon  = document.getElementById(iconId);
        if (!input || !icon) return;

        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';

        // Switch between eye / eye-off SVG paths
        icon.innerHTML = isHidden
            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>' +
            '<line stroke-linecap="round" stroke-linejoin="round" x1="1" y1="1" x2="23" y2="23"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>' +
            '<circle cx="12" cy="12" r="3"/>';
    };

    // Track current password strength score 
    var currentStrengthScore = 0;

    // Helper: show/clear field error 
    function showFieldError(inputId, errorId, errorTextId, msg) {
        var input = document.getElementById(inputId);
        var errEl = document.getElementById(errorId);
        var errTxt = document.getElementById(errorTextId);
        if (input)  input.classList.add('input-error');
        if (errTxt) errTxt.textContent = msg;
        if (errEl)  errEl.classList.add('visible');
    }
    function clearFieldError(inputId, errorId) {
        var input = document.getElementById(inputId);
        var errEl = document.getElementById(errorId);
        if (input)  input.classList.remove('input-error');
        if (errEl)  errEl.classList.remove('visible');
    }

    // Block submit if any field is invalid 
    var form = document.querySelector('.rg-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            var nameInput  = document.getElementById('name');
            var emailInput = document.getElementById('email');
            var pw  = passwordInput.value;
            var cpw = confirmInput.value;
            var blocked = false;
            var firstErrorField = null;

            // Validate Full Name
            clearFieldError('name', 'nameError');
            if (!nameInput || nameInput.value.trim().length === 0) {
                e.preventDefault(); blocked = true;
                showFieldError('name', 'nameError', 'nameErrorText', 'Full name is required');
                if (!firstErrorField) firstErrorField = nameInput;
            } else if (nameInput.value.trim().length < 2) {
                e.preventDefault(); blocked = true;
                showFieldError('name', 'nameError', 'nameErrorText', 'Name must be at least 2 characters');
                if (!firstErrorField) firstErrorField = nameInput;
            }

            // Validate Email
            clearFieldError('email', 'emailError');
            var emailVal = emailInput ? emailInput.value.trim() : '';
            if (!emailVal) {
                e.preventDefault(); blocked = true;
                showFieldError('email', 'emailError', 'emailErrorText', 'Email address is required');
                if (!firstErrorField) firstErrorField = emailInput;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                e.preventDefault(); blocked = true;
                showFieldError('email', 'emailError', 'emailErrorText', 'Please enter a valid email address');
                if (!firstErrorField) firstErrorField = emailInput;
            }

            // Validate Password strength (score must be at least 2 = Fair)
            if (currentStrengthScore < 2) {
                e.preventDefault(); blocked = true;
                var label = document.getElementById('strengthLabel');
                if (label) {
                    label.textContent = pw.length === 0 ? 'Password is required' : 'Password is too weak';
                    label.style.color = '#ef4444';
                }
                var bars = ['bar1','bar2','bar3','bar4'];
                bars.forEach(function(id) {
                    var b = document.getElementById(id);
                    if (b) { b.className = 'rg-strength-bar weak'; }
                });
                if (!firstErrorField) firstErrorField = passwordInput;
            }

            // Validate password match
            if (pw !== cpw) {
                e.preventDefault(); blocked = true;
                checkMatch();
                if (!firstErrorField) firstErrorField = confirmInput;
            }

            // Focus the first invalid field
            if (blocked && firstErrorField) firstErrorField.focus();
        });

        // Clear errors on input
        var nameEl  = document.getElementById('name');
        var emailEl = document.getElementById('email');
        if (nameEl)  nameEl.addEventListener('input',  function() { clearFieldError('name',  'nameError'); });
        if (emailEl) emailEl.addEventListener('input',  function() { clearFieldError('email', 'emailError'); });
    }

    // Password Match 
    var passwordInput = document.getElementById('password');
    var confirmInput  = document.getElementById('confirm_password');
    var matchEl       = document.getElementById('matchIndicator');
    var matchIcon     = document.getElementById('matchIcon');
    var matchText     = document.getElementById('matchText');

    function checkMatch() {
        var pw  = passwordInput.value;
        var cpw = confirmInput.value;
        if (cpw.length === 0) {
            matchEl.className = 'rg-match';
            confirmInput.classList.remove('input-match', 'input-no-match');
            return;
        }
        var isMatch = pw === cpw;
        matchEl.className = 'rg-match visible ' + (isMatch ? 'match' : 'no-match');
        confirmInput.classList.toggle('input-match',    isMatch);
        confirmInput.classList.toggle('input-no-match', !isMatch);
        matchIcon.innerHTML = isMatch
            ? '<polyline points="20 6 9 17 4 12"/>'
            : '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>';
        matchText.textContent = isMatch ? 'Passwords match' : 'Passwords do not match';
    }

    if (confirmInput) confirmInput.addEventListener('input', checkMatch);
    if (passwordInput) passwordInput.addEventListener('input', checkMatch);
    window.checkStrength = function (val) {
        var bars  = [
            document.getElementById('bar1'),
            document.getElementById('bar2'),
            document.getElementById('bar3'),
            document.getElementById('bar4')
        ];
        var label = document.getElementById('strengthLabel');
        if (!bars[0] || !label) return;

        var score = 0;
        if (val.length >= 6)                        score++;
        if (val.length >= 10)                       score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

        // Store score globally so the submit handler can read it
        currentStrengthScore = score;

        var colorMap  = ['', 'weak', 'fair', 'fair', 'strong'];
        var labelMap  = ['', 'Weak — too easy to guess', 'Fair', 'Good', 'Strong'];
        var colorText = ['', '#ef4444', '#f59e0b', '#f59e0b', '#10b981'];

        for (var i = 0; i < 4; i++) {
            bars[i].className = 'rg-strength-bar' + (i < score ? ' ' + colorMap[score] : '');
        }

        label.textContent = val.length > 0 ? labelMap[score] : '';
        label.style.color = val.length > 0 ? colorText[score] : 'rgba(255,255,255,0.35)';
    };
})();
</script>

</body>
</html>
<?php
// Clean up form session values after rendering
unset($_SESSION['form_name'], $_SESSION['form_email']);
?>