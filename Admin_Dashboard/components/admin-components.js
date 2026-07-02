/* ═══════════════════════════════════════════════════════════════════
   YP4K Resort — Admin Shared Component JS
   admin-components.js  |  Loaded by ALL Admin_Dashboard pages

   WHY THIS EXISTS:
   When HTML is injected via element.innerHTML = "...", any <script>
   tags inside that HTML are NOT executed by the browser. So all
   functions called by the component HTML (onclick="toggleSidebar()",
   onclick="toggleNotifications()", etc.) must be defined here, in the
   host page's own script context.
═══════════════════════════════════════════════════════════════════ */

/* ── loadHTML helper ────────────────────────────────────────────── */
function loadHTML(url, containerId, afterLoad) {
    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status + ' loading ' + url);
            return r.text();
        })
        .then(html => {
            document.getElementById(containerId).innerHTML = html;
            if (typeof afterLoad === 'function') afterLoad();
        })
        .catch(err => console.error('[admin-components] Failed to load ' + url, err));
}

/* ── setActiveNavigation ────────────────────────────────────────── */
function setActiveNavigation() {
    const currentPage = window.location.pathname.split('/').pop().replace('.php', '');
    document.querySelectorAll('.nav-link[data-page]').forEach(link => {
        link.classList.toggle('active', link.dataset.page === currentPage);
    });
}

/* ── setBreadcrumb ──────────────────────────────────────────────── */
function setBreadcrumb() {
    const currentPage = window.location.pathname.split('/').pop().split('.')[0];
    const breadcrumbText = document.getElementById('breadcrumbText');
    const pageNames = {
        'dashboard':  'Dashboard',
        'bookings':   'Bookings Management',
        'calendar':   'Calendar',
        'activities': 'Activities Management',
        'bundles':    'Bundle Packages',
        'menu':       'Menu Management',
        'users':      'User Management',
        'payments':   'Payments',
        'reports':    'Reports & Analytics'
    };
    if (breadcrumbText && pageNames[currentPage]) {
        breadcrumbText.textContent = pageNames[currentPage];
    }
}

/* ── toggleSidebar ──────────────────────────────────────────────── */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const isCollapsed = sidebar.classList.toggle('collapsed');
    const root   = document.documentElement;
    const full   = getComputedStyle(root).getPropertyValue('--sidebar-width').trim()           || '255px';
    const small  = getComputedStyle(root).getPropertyValue('--sidebar-collapsed-width').trim() || '72px';
    const active = isCollapsed ? small : full;

    const main   = document.getElementById('mainContent');
    if (main)   main.style.marginLeft = active;

    const navbar = document.querySelector('.top-navbar');
    if (navbar) navbar.style.left = active;
}

/* ── toggleNotifications ────────────────────────────────────────── */
function toggleNotifications() {
    const dropdown        = document.getElementById('notificationDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    if (!dropdown) return;
    if (profileDropdown) profileDropdown.classList.remove('show');
    dropdown.classList.toggle('show');

    // Close on outside click
    setTimeout(() => {
        document.addEventListener('click', function closeNotif(e) {
            if (!e.target.closest('.notification-section')) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeNotif);
            }
        });
    }, 0);
}

/* ── toggleProfileMenu ──────────────────────────────────────────── */
function toggleProfileMenu() {
    const dropdown             = document.getElementById('profileDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (!dropdown) return;
    if (notificationDropdown) notificationDropdown.classList.remove('show');
    dropdown.classList.toggle('show');

    // Close on outside click
    setTimeout(() => {
        document.addEventListener('click', function closeProfile(e) {
            if (!e.target.closest('.admin-profile-section')) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeProfile);
            }
        });
    }, 0);
}

/* ── Auto-boot: load sidebar + navbar on DOMContentLoaded ────────── */
document.addEventListener('DOMContentLoaded', function () {
    // Load sidebar first, then set active link + breadcrumb
    loadHTML('components/sidebar.html', 'sidebar-container', function () {
        setActiveNavigation();
        setBreadcrumb();          // breadcrumb lives in navbar, but set it
    });

    // Load navbar independently
    loadHTML('components/navbar.html', 'navbar-container', function () {
        setBreadcrumb();          // also try after navbar loads (element now in DOM)
    });
});
