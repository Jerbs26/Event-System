<?php

require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
redirectIfLoggedIn();

// Must have passed OTP verification to reach this page
if (empty($_SESSION['otp_verified']) || empty($_SESSION['reset_email'])) {
    setFlash('error', 'Please verify your reset code first.');
    header('Location: /event-system/forgot_password.php');
    exit();
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password — EventHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

    <style>
        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
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
        .rg-nav-logo svg { width: 18px; height: 18px; stroke: #111; stroke-width: 2.2; fill: none; }
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

        /* ── CARD ICON ── */
        .rg-icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.25);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(245,158,11,0.15);
            font-size: 24px;
        }

        /* ── TITLES ── */
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
        .rg-flash-success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.25);
            color: #6ee7b7;
        }

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
            padding: 12px 14px 12px 40px;
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

        .rg-input-icon {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
            transition: color 0.2s ease; pointer-events: none;
            display: flex;
        }
        .rg-input-icon svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 1.75; fill: none; }
        .rg-input:focus ~ .rg-input-icon,
        .rg-input-wrap:focus-within .rg-input-icon { color: #F59E0B; }

        /* Toggle eye button */
        .rg-eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,0.25); padding: 4px;
            display: flex; align-items: center;
            transition: color 0.2s ease;
        }
        .rg-eye-btn:hover { color: rgba(255,255,255,0.6); }
        .rg-eye-btn svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 1.75; fill: none; }
        .rg-input.has-eye { padding-right: 40px; }

        /* Match message */
        .rg-match-msg {
            font-size: 0.8rem; min-height: 1rem;
            margin-top: 2px; font-weight: 500;
            transition: color 0.2s ease;
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
            margin-top: 4px;
        }
        .rg-submit:hover {
            background: #FBBF24; transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,158,11,0.52);
        }
        .rg-submit:active { transform: none; box-shadow: none; }
        .rg-submit svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 2.5; fill: none; }

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
            <a href="/event-system/login.php"    class="rg-nav-cta">Sign In</a>
            <a href="/event-system/register.php" class="rg-nav-link">Register</a>
        </div>
    </nav>

    <!-- PAGE -->
    <div class="rg-page">
        <div class="rg-card">
            <h1 class="rg-title">Set new password</h1>
            <p class="rg-subtitle">Choose a strong password for your account.</p>

            <?php if ($flash): ?>
                <div class="rg-flash rg-flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form
                class="rg-form"
                method="POST"
                action="/event-system/actions/new_password_action.php"
                novalidate
            >
                <!-- New Password -->
                <div class="rg-field">
                    <label class="rg-label" for="password">New Password</label>
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
                            class="rg-input has-eye"
                            placeholder="At least 6 characters"
                            required
                            minlength="6"
                            autocomplete="new-password"
                        >
                        <button type="button" class="rg-eye-btn" data-target="password" aria-label="Toggle password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="rg-field">
                    <label class="rg-label" for="confirm_password">Confirm New Password</label>
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
                            class="rg-input has-eye"
                            placeholder="Repeat new password"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="rg-eye-btn" data-target="confirm_password" aria-label="Toggle confirm password visibility">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <p id="matchMsg" class="rg-match-msg"></p>
                </div>

                <!-- Submit -->
                <button type="submit" class="rg-submit" id="submitBtn">
                    Reset Password
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <div class="rg-footer">
                Remembered it? <a href="/event-system/login.php">Sign in</a>
            </div>

        </div>
    </div>

<script>
(function () {
    const password  = document.getElementById('password');
    const confirm   = document.getElementById('confirm_password');
    const matchMsg  = document.getElementById('matchMsg');

    function validate() {
        if (!confirm.value) { matchMsg.textContent = ''; return; }
        if (password.value === confirm.value) {
            matchMsg.textContent = '✓ Passwords match';
            matchMsg.style.color = '#34d399';
        } else {
            matchMsg.textContent = '✗ Passwords do not match';
            matchMsg.style.color = '#f87171';
        }
    }

    password.addEventListener('input', validate);
    confirm.addEventListener('input', validate);

    document.querySelector('.rg-form').addEventListener('submit', (e) => {
        if (password.value.length < 6) {
            e.preventDefault();
            matchMsg.textContent = 'Password must be at least 6 characters.';
            matchMsg.style.color = '#f87171';
            return;
        }
        if (password.value !== confirm.value) {
            e.preventDefault();
            matchMsg.textContent = 'Passwords do not match.';
            matchMsg.style.color = '#f87171';
        }
    });

    // Eye toggle
    document.querySelectorAll('.rg-eye-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
})();
</script>

</body>
</html>