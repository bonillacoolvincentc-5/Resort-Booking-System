<?php
// Get current page filename for active state
$currentPage = basename($_SERVER['PHP_SELF']);
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
.resort-dash-nav .nav-link.active::after {
    content: '';
    position: absolute; bottom: 4px; left: 50%;
    transform: translateX(-50%);
    width: 50%; height: 2px;
    background: var(--accent);
    border-radius: 2px;
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
   MOBILE BOTTOM NAV BAR — mobile only, zero desktop impact
   @media max-width: 767.98px
═══════════════════════════════════════════════════════ */
@media (max-width: 767.98px) {
    /* Push page content above the fixed bar */
    body {
        padding-bottom: calc(65px + env(safe-area-inset-bottom)) !important;
    }

    /* The fixed bar itself */
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
        justify-content: space-around;
    }

    /* Each tap target */
    .mobile-bottom-nav .mbn-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        flex: 1;
        padding: 0.45rem 0.15rem;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 0.65rem;
        font-weight: 500;
        border-radius: 0;
        transition: color 0.2s ease, background 0.2s ease;
        -webkit-tap-highlight-color: transparent;
        min-width: 0;
    }

    .mobile-bottom-nav .mbn-item i {
        font-size: 1.2rem;
        line-height: 1;
    }

    .mobile-bottom-nav .mbn-item span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        text-align: center;
    }

    .mobile-bottom-nav .mbn-item:hover,
    .mobile-bottom-nav .mbn-item.active {
        color: var(--accent);
        background: rgba(157, 255, 0, 0.07);
    }

    /* Logout tab uses the error color */
    .mobile-bottom-nav .mbn-item.mbn-logout {
        color: var(--error, #ff5a5f);
    }

    .mobile-bottom-nav .mbn-item.mbn-logout:hover {
        color: var(--error, #ff5a5f);
        background: rgba(255, 90, 95, 0.1);
    }
}

/* Hide bottom nav on tablet/desktop */
.mobile-bottom-nav {
    display: none;
}
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
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-grid-3x3-gap"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'create-booking.php' ? 'active' : ''; ?>" href="create-booking.php">
                        <i class="bi bi-plus-circle"></i> Create Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'bundle-booking.php' ? 'active' : ''; ?>" href="bundle-booking.php">
                        <i class="bi bi-box-seam"></i> Bundle Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'trail-guide.php' ? 'active' : ''; ?>" href="trail-guide.php">
                        <i class="bi bi-compass"></i> Trail Guide
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'my-bookings.php' ? 'active' : ''; ?>" href="my-bookings.php">
                        <i class="bi bi-calendar-check"></i> My Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
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

<!-- ═══ MOBILE BOTTOM NAV BAR (mobile only — hidden on desktop) ═══ -->
<div class="mobile-bottom-nav" id="mobileBottomNav">
    <a class="mbn-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php" id="mbn-dashboard">
        <i class="bi bi-grid-3x3-gap"></i>
        <span>Dashboard</span>
    </a>
    <a class="mbn-item <?php echo $currentPage === 'create-booking.php' ? 'active' : ''; ?>" href="create-booking.php" id="mbn-create-booking">
        <i class="bi bi-plus-circle"></i>
        <span>Book</span>
    </a>
    <a class="mbn-item <?php echo $currentPage === 'bundle-booking.php' ? 'active' : ''; ?>" href="bundle-booking.php" id="mbn-bundle">
        <i class="bi bi-box-seam"></i>
        <span>Bundle</span>
    </a>
    <a class="mbn-item <?php echo $currentPage === 'my-bookings.php' ? 'active' : ''; ?>" href="my-bookings.php" id="mbn-my-bookings">
        <i class="bi bi-calendar-check"></i>
        <span>Bookings</span>
    </a>
    <a class="mbn-item <?php echo $currentPage === 'trail-guide.php' ? 'active' : ''; ?>" href="trail-guide.php" id="mbn-trail">
        <i class="bi bi-compass"></i>
        <span>Trail</span>
    </a>
    <a class="mbn-item <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php" id="mbn-profile">
        <i class="bi bi-person"></i>
        <span>Profile</span>
    </a>
    <a class="mbn-item mbn-logout" href="../phpAuthentication/logout.php" id="mbn-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
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
