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

<script>
const dashNav = document.getElementById('dashNav');
if (dashNav) {
    window.addEventListener('scroll', () => {
        dashNav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
}
</script>
