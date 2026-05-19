document.addEventListener('DOMContentLoaded', function () {

    // Breakpoint must match CSS @media (max-width: 768px)
    var MOBILE_BP = 768;

    // Sidebar toggle (mobile) 
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    var toggle   = document.getElementById('sidebarToggle');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            sidebar && sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on nav link click (mobile)
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-link, .sidebar-logout').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= MOBILE_BP) closeSidebar();
            });
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // Close sidebar automatically when window is resized to desktop width
    window.addEventListener('resize', function () {
        if (window.innerWidth > MOBILE_BP) closeSidebar();
    });

    // Auto-dismiss flash messages 
    var flashMsg = document.getElementById('flashMsg');
    if (flashMsg) {
        setTimeout(function () {
            flashMsg.style.transition = 'opacity 0.4s ease';
            flashMsg.style.opacity = '0';
            setTimeout(function () {
                if (flashMsg.parentNode) flashMsg.parentNode.removeChild(flashMsg);
            }, 400);
        }, 4500);
    }

    // data-confirm dialogs 
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // Active link safety net 
    var currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(function (link) {
        try {
            var linkPath = new URL(link.href, window.location.origin).pathname;
            if (linkPath === currentPath) link.classList.add('active');
        } catch (_) { /* ignore */ }
    });

});