<?php

require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
redirectIfLoggedIn();

if (empty($_SESSION['reset_email'])) {
    setFlash('error', 'Please request a password reset first.');
    header('Location: /event-system/forgot_password.php');
    exit();
}

$email       = $_SESSION['reset_email'];
$maskedEmail = preg_replace('/(?<=.{2}).(?=.*@)/u', '*', $email);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Reset Code — EventHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/event-system/assets/css/style.css">

    <style>
        html, body {
            margin: 0; padding: 0;
            background: #080A0E;
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── NAV ── */
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
        .rg-nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .rg-nav-logo {
            width: 36px; height: 36px; border-radius: 9px;
            background: #F59E0B;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 20px rgba(245,158,11,0.4); flex-shrink: 0;
        }
        .rg-nav-logo svg { width: 18px; height: 18px; stroke: #111; stroke-width: 2.2; }
        .rg-nav-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.05rem; font-weight: 700;
            color: #fff; letter-spacing: -0.03em;
        }
        .rg-nav-links { display: flex; align-items: center; gap: 8px; }
        .rg-nav-cta {
            padding: 9px 20px; border-radius: 8px;
            background: #F59E0B; color: #1A0E00;
            font-size: 0.875rem; font-weight: 700;
            text-decoration: none; transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(245,158,11,0.35);
        }
        .rg-nav-cta:hover { background: #FBBF24; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.5); }
        .rg-nav-link {
            padding: 8px 16px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.55);
            font-size: 0.875rem; font-weight: 500;
            text-decoration: none; transition: all 0.2s ease;
        }
        .rg-nav-link:hover { color: #fff; background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); }

        /* ── PAGE ── */
        .rg-page {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 100px 24px 60px;
            position: relative; overflow: hidden;
        }
        .rg-page::before {
            content: '';
            position: absolute; width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%);
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            pointer-events: none;
        }
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
            text-align: center;
            box-shadow:
                0 0 0 1px rgba(245,158,11,0.08),
                0 24px 64px rgba(0,0,0,0.55),
                inset 0 1px 0 rgba(255,255,255,0.08);
            animation: rgFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }
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

        /* ── ICON ── */
        .rg-icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.25);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 0 20px rgba(245,158,11,0.15);
        }
        .rg-icon-wrap svg { width: 24px; height: 24px; stroke: #F59E0B; stroke-width: 1.75; }

        .rg-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem; font-weight: 800;
            color: #fff; letter-spacing: -0.04em; line-height: 1.1;
            margin-bottom: 8px;
        }
        .rg-subtitle {
            font-size: 0.9rem; color: rgba(255,255,255,0.42);
            font-weight: 400; line-height: 1.6;
            margin-bottom: 8px;
        }
        .rg-email {
            font-size: 0.925rem; font-weight: 700;
            color: #F59E0B; word-break: break-all;
            display: block; margin-bottom: 28px;
        }

        /* ── FLASH ── */
        .rg-flash {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px; border-radius: 10px;
            font-size: 0.845rem; font-weight: 500; line-height: 1.5;
            margin-bottom: 20px; text-align: left;
            animation: rgFadeUp 0.3s ease both;
        }
        .rg-flash-error  { background: rgba(220,38,38,0.1);  border: 1px solid rgba(220,38,38,0.25);  color: #fca5a5; }
        .rg-flash-success{ background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); color: #6ee7b7; }
        .rg-flash svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; stroke: currentColor; stroke-width: 2; }

        /* ── OTP INPUTS ── */
        .rg-otp-inputs {
            display: flex; justify-content: center; gap: 10px;
            margin-bottom: 16px;
        }
        .rg-otp-digit {
            width: 52px; height: 58px;
            text-align: center;
            font-size: 24px; font-weight: 700;
            font-family: 'Syne', monospace;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            color: #fff;
            caret-color: transparent;
            outline: none;
            transition: all 0.18s ease;
            -moz-appearance: textfield;
        }
        .rg-otp-digit::-webkit-outer-spin-button,
        .rg-otp-digit::-webkit-inner-spin-button { -webkit-appearance: none; }
        .rg-otp-digit:focus {
            border-color: rgba(245,158,11,0.6);
            background: rgba(245,158,11,0.07);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
            transform: translateY(-2px);
        }
        .rg-otp-digit.filled {
            border-color: #F59E0B;
            background: rgba(245,158,11,0.15);
            color: #F59E0B;
        }
        .rg-otp-digit.error {
            border-color: #ef4444;
            animation: rgShake 0.35s ease;
        }
        @keyframes rgShake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-6px); }
            40%     { transform: translateX(6px); }
            60%     { transform: translateX(-4px); }
            80%     { transform: translateX(4px); }
        }

        /* ── HINT / TIMER ── */
        .rg-hint {
            font-size: 0.82rem; color: rgba(255,255,255,0.32);
            margin-bottom: 20px;
        }
        .rg-hint #otpTimer { font-weight: 700; color: rgba(255,255,255,0.6); }
        .rg-hint #otpTimer.danger { color: #ef4444; }

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
            margin-bottom: 20px;
        }
        .rg-submit:hover:not(:disabled) {
            background: #FBBF24; transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(245,158,11,0.52);
        }
        .rg-submit:active:not(:disabled) { transform: none; box-shadow: none; }
        .rg-submit:disabled { opacity: 0.45; cursor: not-allowed; }
        .rg-submit svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 2.5; }

        /* ── FOOTER LINKS ── */
        .rg-footer {
            font-size: 0.875rem; color: rgba(255,255,255,0.35);
            margin-bottom: 10px;
        }
        .rg-footer-link {
            background: none; border: none; padding: 0;
            color: #F59E0B; font-weight: 700; font-size: inherit;
            font-family: inherit; cursor: pointer; text-decoration: none;
            transition: color 0.2s ease;
        }
        .rg-footer-link:hover { color: #FBBF24; }
        .rg-footer-link:disabled { color: rgba(255,255,255,0.25); cursor: not-allowed; }
        .rg-cooldown { font-size: 0.82rem; color: rgba(255,255,255,0.3); }

        .rg-back {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.28);
            text-decoration: none;
            transition: color 0.2s ease;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .rg-back:hover { color: rgba(255,255,255,0.6); }

        /* ── RESPONSIVE ── */
        @media (max-width: 520px) {
            .rg-nav { padding: 0 20px; }
            .rg-nav-link { display: none; }
            .rg-page { padding: 88px 16px 48px; }
            .rg-card { padding: 32px 24px 28px; }
            .rg-title { font-size: 1.5rem; }
            .rg-otp-digit { width: 44px; height: 52px; font-size: 20px; }
            .rg-otp-inputs { gap: 7px; }
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

            <div class="rg-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>

            <h1 class="rg-title">Check your email</h1>
            <p class="rg-subtitle">We sent a 6-digit code to</p>
            <span class="rg-email"><?= htmlspecialchars($maskedEmail) ?></span>

            <?php $flash = getFlash(); if ($flash): ?>
                <div class="rg-flash rg-flash-<?= htmlspecialchars($flash['type']) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- OTP Form -->
            <form method="POST" action="/event-system/actions/verify_otp_action.php" id="otpForm" novalidate>
                <div class="rg-otp-inputs" id="otpInputs">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input
                            type="text"
                            name="otp_<?= $i ?>"
                            id="otp<?= $i ?>"
                            class="rg-otp-digit"
                            maxlength="1"
                            inputmode="numeric"
                            pattern="[0-9]"
                            autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>"
                            required
                            aria-label="Digit <?= $i ?>"
                        >
                    <?php endfor; ?>
                </div>

                <p class="rg-hint">Code expires in <span id="otpTimer">10:00</span></p>

                <button type="submit" class="rg-submit" id="verifyBtn" disabled>
                    Verify Code
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <!-- Resend -->
            <form method="POST" action="/event-system/actions/resend_reset_action.php" id="resendForm">
                <p class="rg-footer">
                    Didn't receive the code?
                    <button type="submit" class="rg-footer-link" id="resendBtn">Resend code</button>
                    <span id="resendCooldown" class="rg-cooldown" style="display:none;"></span>
                </p>
            </form>

            <a href="/event-system/forgot_password.php" class="rg-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Use a different email
            </a>

        </div>
    </div>

<script>
(function () {
    const inputs     = Array.from(document.querySelectorAll('.rg-otp-digit'));
    const timer      = document.getElementById('otpTimer');
    const verifyBtn  = document.getElementById('verifyBtn');
    const resendBtn  = document.getElementById('resendBtn');
    const cooldownEl = document.getElementById('resendCooldown');

    inputs[0]?.focus();

    function checkComplete() {
        verifyBtn.disabled = !inputs.every(inp => inp.value.trim() !== '');
    }

    inputs.forEach((inp, idx) => {
        inp.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                if (inp.value) { inp.value = ''; inp.classList.remove('filled'); }
                else if (idx > 0) { inputs[idx-1].focus(); inputs[idx-1].value = ''; inputs[idx-1].classList.remove('filled'); }
                checkComplete(); e.preventDefault(); return;
            }
            if (e.key === 'ArrowLeft'  && idx > 0) { inputs[idx-1].focus(); e.preventDefault(); }
            if (e.key === 'ArrowRight' && idx < 5) { inputs[idx+1].focus(); e.preventDefault(); }
        });
        inp.addEventListener('input', () => {
            const val = inp.value.replace(/\D/g,'').slice(-1);
            inp.value = val;
            inp.classList.toggle('filled', !!val);
            if (val && idx < 5) inputs[idx+1].focus();
            checkComplete();
        });
        inp.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
            pasted.split('').forEach((ch, i) => { if (inputs[i]) { inputs[i].value = ch; inputs[i].classList.add('filled'); } });
            inputs[Math.min(pasted.length-1,5)].focus();
            checkComplete();
        });
    });

    // Countdown
    let remaining = 10 * 60;
    const tick = setInterval(() => {
        remaining--;
        if (remaining <= 0) { clearInterval(tick); timer.textContent = '0:00'; timer.classList.add('danger'); return; }
        const m = Math.floor(remaining / 60), s = String(remaining % 60).padStart(2,'0');
        timer.textContent = m + ':' + s;
        if (remaining <= 60) timer.classList.add('danger');
    }, 1000);

    // Resend cooldown
    resendBtn.addEventListener('click', () => {
        resendBtn.style.display = 'none'; cooldownEl.style.display = 'inline';
        let cd = 60; cooldownEl.textContent = '(Resend in ' + cd + 's)';
        const cdTimer = setInterval(() => {
            cd--;
            if (cd <= 0) { clearInterval(cdTimer); cooldownEl.style.display = 'none'; resendBtn.style.display = 'inline'; return; }
            cooldownEl.textContent = '(Resend in ' + cd + 's)';
        }, 1000);
    });
})();
</script>

</body>
</html>