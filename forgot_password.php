<?php
// forgot_password.php
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — EventHub</title>
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
        .rg-flash-success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.25);
            color: #6ee7b7;
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
            box-sizing: border-box;
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

            <h1 class="rg-title">Forgot password?</h1>
            <p class="rg-subtitle">Enter your email and we'll send you a reset code.</p>

            <?php if (!empty($error)): ?>
                <div class="rg-flash rg-flash-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="rg-flash rg-flash-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form
                class="rg-form"
                method="POST"
                action="/event-system/actions/forgot_password_action.php"
                novalidate
            >
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
                        >
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="rg-submit">
                    Send Reset Code
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

</body>
</html>