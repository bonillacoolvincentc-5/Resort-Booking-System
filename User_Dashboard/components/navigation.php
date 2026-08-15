<?php
// Get current page filename for active state
$currentPage = basename($_SERVER['PHP_SELF']);

// Alias map: canonical page => list of filenames that should activate it
$pageAliases = [
    'dashboard'     => ['dashboard.php'],
    'book'          => ['create-booking.php', 'book.php', 'packages.php'],
    'bundle'        => ['bundle-booking.php', 'bundle.php', 'bundles.php'],
    'bookings'      => ['my-bookings.php', 'bookings.php', 'my_bookings.php'],
    'trail'         => ['trail-guide.php', 'trail.php', 'activities.php'],
    'profile'       => ['profile.php'],
];

// Helper: returns 'active' if $currentPage matches any alias for the given key
function navActive($key) {
    global $currentPage, $pageAliases;
    return in_array($currentPage, $pageAliases[$key] ?? []) ? 'active' : '';
}
?>
<!-- YP4K Resort — User Dashboard Navigation -->
<link rel="stylesheet" href="../css/resort-theme.css">
<style>
.resort-dash-nav {
    position: sticky;
    top: 0;
    z-index: 999;
    background: rgba(9, 18, 15, 0.92);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-bottom: 1px solid var(--border);
    padding: 0.7rem 0;
    transition: all 0.3s ease;
}
.resort-dash-nav.scrolled {
    background: rgba(6, 12, 9, 0.97);
    box-shadow: 0 4px 30px rgba(0,0,0,0.5);
}
.resort-dash-nav .navbar-brand {
    display: flex; align-items: center; gap: 0.55rem;
    font-weight: 700; font-size: 1.1rem;
    color: var(--text) !important; text-decoration: none;
}
.resort-dash-nav .brand-icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; color: #000; flex-shrink: 0;
}
.resort-dash-nav .nav-link {
    color: var(--text-muted) !important;
    font-weight: 500; font-size: 0.85rem;
    padding: 0.45rem 0.9rem !important;
    border-radius: 8px;
    transition: all 0.25s ease;
    position: relative;
    display: flex; align-items: center; gap: 0.4rem;
    text-decoration: none;
    margin: 0 0.05rem;
}
.resort-dash-nav .nav-link:hover {
    color: var(--text) !important;
    background: rgba(157,255,0,0.06);
}
.resort-dash-nav .nav-link.active {
    color: var(--accent) !important;
    background: rgba(157,255,0,0.1);
}
/* Desktop underline dot — shown only in the expanded (desktop) navbar */
.resort-dash-nav .navbar-collapse.show .nav-link.active::after,
.resort-dash-nav .navbar-collapse:not(.show) .nav-link.active::after {
    content: '';
    position: absolute; bottom: 4px; left: 50%;
    transform: translateX(-50%);
    width: 50%; height: 2px;
    background: var(--accent);
    border-radius: 2px;
}
/* On mobile the collapse is .show when open — suppress the ::after there
   so it never stretches across the full dropdown row */
@media (max-width: 767.98px) {
    .resort-dash-nav .nav-link.active::after {
        display: none !important;
    }
    /* Replace with a clean pill background for hamburger active items */
    .resort-dash-nav .nav-link.active {
        background: rgba(118, 255, 3, 0.15) !important;
        color: #76ff03 !important;
        border: 1px solid rgba(118, 255, 3, 0.3);
        border-radius: 8px;
    }
}
.resort-dash-nav .nav-logout {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    background: rgba(255,90,95,0.1);
    border: 1px solid rgba(255,90,95,0.2);
    color: var(--error) !important;
    border-radius: 8px;
    font-size: 0.85rem; font-weight: 600;
    text-decoration: none;
    transition: all 0.25s ease;
}
.resort-dash-nav .nav-logout:hover {
    background: rgba(255,90,95,0.18);
    border-color: var(--error);
    transform: translateY(-1px);
}
.resort-dash-nav .navbar-toggler {
    border: 1px solid var(--border);
    padding: 0.35rem 0.6rem;
    border-radius: 8px;
    color: var(--text-muted);
    background: transparent;
}
.resort-dash-nav .navbar-toggler:focus { box-shadow: none; }

/* ═══════════════════════════════════════════════════════
   MOBILE-ONLY OVERRIDES  (≤ 767.98px)
   — zero impact on desktop/tablet
═══════════════════════════════════════════════════════ */
@media (max-width: 767.98px) {

    /* 1. Push ALL page content above the fixed bottom bar */
    body {
        padding-bottom: calc(65px + env(safe-area-inset-bottom)) !important;
    }

    /* 2. Hide the 5 operational nav-items from the hamburger on mobile
          (they live in the bottom bar instead) */
    .mobile-hide-in-hamburger {
        display: none !important;
    }

    /* 3. Show the account section divider label inside hamburger */
    .mobile-hamburger-account-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 0.5rem 0.9rem 0.25rem;
        margin-top: 0.25rem;
    }

    /* 4. Fixed bottom navigation bar — 5 tabs only */
    .mobile-bottom-nav {
        display: flex !important;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1050;
        background: rgba(9, 18, 15, 0.97);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-top: 1px solid var(--border);
        padding-bottom: env(safe-area-inset-bottom);
        height: calc(65px + env(safe-area-inset-bottom));
        align-items: stretch;
        justify-content: stretch;
    }

    /* Each of the 5 tap targets — equal width share */
    .mobile-bottom-nav .mbn-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.18rem;
        flex: 1 1 0;          /* equal share across 5 items */
        padding: 0.4rem 0.1rem;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 0.6rem;    /* slightly compact so labels never clip */
        font-weight: 500;
        border-radius: 0;
        transition: color 0.2s ease, background 0.2s ease;
        -webkit-tap-highlight-color: transparent;
        min-width: 0;
        overflow: hidden;
    }

    /* Active bottom-bar tab icon wrapper pill */
    .mobile-bottom-nav .mbn-item .mbn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 1.75rem;
        border-radius: 8px;
        transition: background 0.2s ease, border-color 0.2s ease;
        flex-shrink: 0;
    }

    .mobile-bottom-nav .mbn-item .mbn-icon i {
        font-size: 1.15rem;
        line-height: 1;
    }

    .mobile-bottom-nav .mbn-item span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        text-align: center;
        display: block;
    }

    .mobile-bottom-nav .mbn-item:hover {
        color: var(--accent);
        background: rgba(157, 255, 0, 0.05);
    }

    /* Active bottom-bar tab: neon icon pill + bright label */
    .mobile-bottom-nav .mbn-item.active {
        color: #76ff03;
        background: transparent;
    }
    .mobile-bottom-nav .mbn-item.active .mbn-icon {
        background: rgba(118, 255, 3, 0.15);
        border: 1px solid rgba(118, 255, 3, 0.3);
    }
    .mobile-bottom-nav .mbn-item.active .mbn-icon i {
        color: #76ff03;
    }
    .mobile-bottom-nav .mbn-item.active span {
        font-weight: 700;
    }
}

/* Desktop/tablet guards — bottom bar and account label invisible */
.mobile-bottom-nav          { display: none; }
.mobile-hamburger-account-label { display: none; }
</style>

<nav class="resort-dash-nav navbar navbar-expand-lg" id="dashNav">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">
            <div class="brand-icon"><i class="bi bi-gem"></i></div>
            YP4K Resort
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashNavbar">
            <i class="bi bi-list" style="font-size:1.25rem;"></i>
        </button>
        <div class="collapse navbar-collapse" id="dashNavbar">
            <ul class="navbar-nav mx-auto gap-1">
                    <!-- Operational links: shown in hamburger on DESKTOP;
                     hidden on mobile (bottom bar handles them). -->
                <li class="nav-item mobile-hide-in-hamburger">
                    <a class="nav-link <?php echo navActive('dashboard'); ?>" href="dashboard.php">
                        <i class="bi bi-grid-3x3-gap"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mobile-hide-in-hamburger">
                    <a class="nav-link <?php echo navActive('book'); ?>" href="create-booking.php">
                        <i class="bi bi-plus-circle"></i> Create Booking
                    </a>
                </li>
                <li class="nav-item mobile-hide-in-hamburger">
                    <a class="nav-link <?php echo navActive('bundle'); ?>" href="bundle-booking.php">
                        <i class="bi bi-box-seam"></i> Bundle Booking
                    </a>
                </li>
                <li class="nav-item mobile-hide-in-hamburger">
                    <a class="nav-link <?php echo navActive('trail'); ?>" href="trail-guide.php">
                        <i class="bi bi-compass"></i> Trail Guide
                    </a>
                </li>
                <li class="nav-item mobile-hide-in-hamburger">
                    <a class="nav-link <?php echo navActive('bookings'); ?>" href="my-bookings.php">
                        <i class="bi bi-calendar-check"></i> My Bookings
                    </a>
                </li>

                <!-- Account items: visible in hamburger on BOTH mobile & desktop -->
                <span class="mobile-hamburger-account-label">Account</span>
                <li class="nav-item">
                    <a class="nav-link <?php echo navActive('profile'); ?>" href="profile.php">
                        <i class="bi bi-person"></i> Profile
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <a class="nav-logout" href="../phpAuthentication/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ═══ MOBILE BOTTOM NAV BAR — 5 operational tabs, mobile only ═══ -->
<div class="mobile-bottom-nav" id="mobileBottomNav">
    <a class="mbn-item <?php echo navActive('dashboard'); ?>" href="dashboard.php" id="mbn-dashboard">
        <span class="mbn-icon"><i class="bi bi-grid-3x3-gap"></i></span>
        <span>Dashboard</span>
    </a>
    <a class="mbn-item <?php echo navActive('book'); ?>" href="create-booking.php" id="mbn-create-booking">
        <span class="mbn-icon"><i class="bi bi-plus-circle"></i></span>
        <span>Book</span>
    </a>
    <a class="mbn-item <?php echo navActive('bundle'); ?>" href="bundle-booking.php" id="mbn-bundle">
        <span class="mbn-icon"><i class="bi bi-box-seam"></i></span>
        <span>Bundle</span>
    </a>
    <a class="mbn-item <?php echo navActive('bookings'); ?>" href="my-bookings.php" id="mbn-my-bookings">
        <span class="mbn-icon"><i class="bi bi-calendar-check"></i></span>
        <span>Bookings</span>
    </a>
    <a class="mbn-item <?php echo navActive('trail'); ?>" href="trail-guide.php" id="mbn-trail">
        <span class="mbn-icon"><i class="bi bi-compass"></i></span>
        <span>Trail</span>
    </a>
</div>

<script>
const dashNav = document.getElementById('dashNav');
if (dashNav) {
    window.addEventListener('scroll', () => {
        dashNav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
}
</script>
