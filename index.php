<?php
// index.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$conn   = getConnection();
$result = $conn->query("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3");
$events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub — Find &amp; Join Events That Matter</title>
    <meta name="description" content="EventHub connects people through memorable experiences. Browse upcoming events, register in seconds, and never miss what's happening around you.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/event-system/assets/css/style.css">

    <style>
        /* ── Base hidden states — elements start invisible ── */
        .anim-el {
            will-change: opacity, transform;
            transition-property: opacity, transform;
            transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
            transition-duration: 0.65s;
        }
        .anim-fade-up   { opacity: 0; transform: translateY(36px); }
        .anim-fade-left { opacity: 0; transform: translateX(-28px); }
        .anim-scale-up  { opacity: 0; transform: scale(0.93) translateY(16px); }
        .anim-slide-l   { opacity: 0; transform: translateX(-36px) translateY(14px); }
        .anim-slide-r   { opacity: 0; transform: translateX( 36px) translateY(14px); }

        /* Visible — overrides all transforms */
        .anim-el.in {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>
</head>
<body>

<div class="lp-body">

    <!-- NAV -->
    <nav class="lp-nav">
        <a class="lp-nav-brand" href="/event-system/index.php">
            <div class="lp-nav-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                    <line x1="3"  y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <span class="lp-nav-name">EventHub</span>
        </a>
        <div class="lp-nav-links">
            <a href="#features" class="lp-nav-link">Features</a>
            <a href="#events"   class="lp-nav-link">Events</a>
            <a href="/event-system/login.php"    class="lp-nav-link">Sign In</a>
            <a href="/event-system/register.php" class="lp-nav-cta">
                Get Started
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </nav>

    <!-- ═══════ HERO ═══════ -->
    <section class="lp-hero">
        <div class="lp-hero-grid"></div>
        <div class="lp-hero-inner">


            <h1 class="lp-hero-title">
                Find &amp; Join Events<br>
                That <span class="accent">Matter</span> to You
            </h1>

            <p class="lp-hero-sub">
                EventHub connects people through memorable experiences. Browse upcoming events,
                register in seconds, and never miss what's happening around you.
            </p>

            <div class="lp-hero-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="/event-system/<?= isAdmin() ? 'admin/dashboard.php' : 'dashboard.php' ?>" class="lp-btn-primary">
                        Go to Dashboard
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                <?php else: ?>
                    <a href="/event-system/register.php" class="lp-btn-primary">
                        Get Started
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="/event-system/login.php" class="lp-btn-secondary">Sign In</a>
                <?php endif; ?>
            </div>

            <div class="lp-proof">
                <div class="lp-proof-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Free to register
                </div>
                <div class="lp-proof-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Instant confirmation
                </div>
                <div class="lp-proof-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Manage all your events in one place
                </div>
            </div>

        </div>
    </section>

    <!-- ═══════ FEATURES ═══════ -->
    <section class="lp-features" id="features">

        <p  class="lp-section-label anim-el anim-fade-left">Why EventHub</p>
        <h2 class="lp-section-title anim-el anim-fade-up" data-delay="0.5">
            Everything you need,<br>nothing you don't
        </h2>
        <p  class="lp-section-sub anim-el anim-fade-up" data-delay="1">
            A clean, focused platform built for people who want to discover
            and attend events — not wrestle with software.
        </p>

        <div class="lp-features-grid">
            <div class="lp-feature-card anim-el anim-fade-up" data-delay="0">
                <div class="lp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </div>
                <div class="lp-feature-title">Discover Events</div>
                <p class="lp-feature-desc">Browse and search events by name, location, or date. Stay current with everything happening in your area.</p>
            </div>
            <div class="lp-feature-card anim-el anim-fade-up" data-delay="1">
                <div class="lp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <div class="lp-feature-title">Instant Registration</div>
                <p class="lp-feature-desc">Register for events in one click. All your upcoming events are tracked in your personal dashboard automatically.</p>
            </div>
            <div class="lp-feature-card anim-el anim-fade-up" data-delay="2">
                <div class="lp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <div class="lp-feature-title">Admin Controls</div>
                <p class="lp-feature-desc">Organizers get a full-featured dashboard to create, edit, and manage events and participant lists with ease.</p>
            </div>
            <div class="lp-feature-card anim-el anim-fade-up" data-delay="3">
                <div class="lp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div class="lp-feature-title">Insightful Reports</div>
                <p class="lp-feature-desc">View detailed reports on registrations per event. Make data-driven decisions about future events and capacity.</p>
            </div>
        </div>
    </section>

    <!-- ═══════ EVENTS ═══════ -->
    <?php if (!empty($events)): ?>
    <section class="lp-events" id="events">

        <p  class="lp-section-label anim-el anim-fade-left">Don't miss out</p>
        <h2 class="lp-section-title anim-el anim-fade-up" data-delay="0.5">Upcoming Events</h2>
        <p  class="lp-section-sub anim-el anim-fade-up" data-delay="1">
            Spots fill up fast. See what's coming and secure your place today.
        </p>

        <div class="lp-events-grid">
            <?php foreach ($events as $i => $event): ?>
            <div class="lp-event-card anim-el <?= $i % 2 === 0 ? 'anim-slide-l' : 'anim-slide-r' ?>" data-delay="<?= $i ?>">
                <div class="lp-event-accent"></div>
                <div class="lp-event-body">
                    <h3 class="lp-event-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="lp-event-desc"><?= htmlspecialchars($event['description']) ?></p>
                    <div class="lp-event-meta">
                        <div class="lp-event-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?= date('F j, Y', strtotime($event['date'])) ?> at <?= date('g:i A', strtotime($event['time'])) ?>
                        </div>
                        <div class="lp-event-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($event['location']) ?>
                        </div>
                    </div>
                </div>
                <div class="lp-event-footer">
                    <span class="lp-badge-upcoming">Upcoming</span>
                    <?php if (isLoggedIn()): ?>
                        <a href="/event-system/events.php" class="lp-event-btn">View &amp; Register</a>
                    <?php else: ?>
                        <a href="/event-system/login.php" class="lp-event-btn-ghost">Sign in to Join</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="lp-events-footer">
            <a href="/event-system/<?= isLoggedIn() ? 'events.php' : 'login.php' ?>"
               class="lp-btn-secondary anim-el anim-fade-up">
                Browse All Events
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>

    </section>
    <?php endif; ?>

    <!-- ═══════ CTA ═══════ -->
    <section class="lp-cta">
        <div class="lp-cta-inner">
            <div class="lp-cta-box anim-el anim-scale-up">
                <h2 class="lp-cta-title">Ready to get started?</h2>
                <p class="lp-cta-sub">Join EventHub today and never miss an event that matters to you. It's completely free.</p>
                <div class="lp-cta-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="/event-system/<?= isAdmin() ? 'admin/dashboard.php' : 'dashboard.php' ?>" class="lp-btn-primary">
                            Go to Dashboard
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    <?php else: ?>
                        <a href="/event-system/register.php" class="lp-btn-primary">
                            Create Free Account
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="/event-system/login.php" class="lp-btn-secondary">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════ FOOTER ═══════ -->
<footer class="lp-footer anim-el anim-fade-up">
    <span class="lp-footer-copy">&copy; <?= date('Y') ?> EventHub. All rights reserved.</span>
</footer>

</div>

<script>
(function () {
    'use strict';

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var STAGGER = 0.1; /* seconds per data-delay unit */

    /* ── Scroll Progress Bar ── */
    var bar = document.createElement('div');
    bar.style.cssText = 'position:fixed;top:0;left:0;height:2px;width:0%;background:linear-gradient(90deg,#F59E0B,#FBBF24);z-index:9999;pointer-events:none;transition:width 0.08s linear;';
    document.body.appendChild(bar);

    var nav       = document.querySelector('.lp-nav');
    var heroInner = document.querySelector('.lp-hero-inner');
    var heroGrid  = document.querySelector('.lp-hero-grid');

    /* ─────────────────────────────────────────────────────────────────
       BIDIRECTIONAL INTERSECTION OBSERVER
       Key principle: NO unobserve() — observer watches forever.

       isIntersecting = true  → element entering viewport → add .in
       isIntersecting = false → element leaving viewport  → remove .in
                                (transition-delay: 0s so it snaps out
                                 instantly, ready to re-animate on next entry)

       Result: every scroll up OR down re-triggers the animation.
    ───────────────────────────────────────────────────────────────── */
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            var el    = entry.target;
            var delay = parseFloat(el.dataset.delay || 0) * STAGGER;

            if (entry.isIntersecting) {
                /* Coming into view — animate in with stagger delay */
                el.style.transitionDelay = reduced ? '0s' : delay + 's';
                el.classList.add('in');
            } else {
                /* Going out of view — remove class immediately (no delay)
                   so when it re-enters it starts from the hidden state */
                el.style.transitionDelay = '0s';
                el.classList.remove('in');
            }
        });
    }, {
        threshold: 0.12,
        /* rootMargin gives a small grace zone so elements don't
           flicker at the exact edge of the viewport */
        rootMargin: '0px 0px -40px 0px'
    });

    /* Register every animated element */
    document.querySelectorAll('.anim-el').forEach(function (el) {
        if (reduced) {
            /* Accessibility: skip animations for users who prefer it */
            el.style.transition = 'none';
            el.classList.add('in');
        } else {
            observer.observe(el);
        }
    });

    /* ── rAF-throttled scroll (progress bar + nav + parallax) ── */
    var ticking = false;

    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            var st   = window.pageYOffset || document.documentElement.scrollTop;
            var docH = document.documentElement.scrollHeight - window.innerHeight;

            /* Progress bar */
            bar.style.width = (docH > 0 ? Math.min(st / docH * 100, 100) : 0) + '%';

            /* Nav darkens on scroll */
            if (nav) {
                if (st > 20) {
                    nav.style.background = 'rgba(8,10,14,0.97)';
                    nav.style.boxShadow  = '0 1px 0 rgba(255,255,255,0.06),0 4px 24px rgba(0,0,0,0.4)';
                } else {
                    nav.style.background = 'rgba(8,10,14,0.85)';
                    nav.style.boxShadow  = 'none';
                }
            }

            /* Hero parallax + opacity fade */
            if (!reduced) {
                var vh = window.innerHeight;
                if (st < vh * 1.3) {
                    if (heroInner) {
                        heroInner.style.transform = 'translateY(' + (st * 0.28) + 'px)';
                        heroInner.style.opacity   = Math.max(0, 1 - st / (vh * 0.82)).toFixed(3);
                    }
                    if (heroGrid) {
                        heroGrid.style.transform = 'translateY(' + (st * 0.11) + 'px)';
                    }
                }
            }

            ticking = false;
        });
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    /* ── Smooth anchor scroll ── */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var t = document.querySelector(this.getAttribute('href'));
            if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });

    /* ── Magnetic cursor on CTA buttons ── */
    if (!reduced) {
        document.querySelectorAll('.lp-btn-primary, .lp-nav-cta').forEach(function (btn) {
            btn.addEventListener('mousemove', function (e) {
                var r  = btn.getBoundingClientRect();
                var dx = (e.clientX - (r.left + r.width  / 2)) * 0.22;
                var dy = (e.clientY - (r.top  + r.height / 2)) * 0.22;
                btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px) translateY(-2px)';
            });
            btn.addEventListener('mouseleave', function () {
                btn.style.transition = 'transform 0.45s cubic-bezier(0.22,1,0.36,1)';
                btn.style.transform  = '';
                setTimeout(function () { btn.style.transition = ''; }, 450);
            });
        });
    }

})();
</script>

</body>
</html>