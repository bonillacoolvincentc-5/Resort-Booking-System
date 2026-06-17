<?php
// Get current page filename for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Navigation -->
<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-gem"></i>
            YP4K Resort
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'create-booking.php' ? 'active' : ''; ?>" href="create-booking.php">
                        <i class="bi bi-plus-circle me-2"></i>Create Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'my-bookings.php' ? 'active' : ''; ?>" href="my-bookings.php">
                        <i class="bi bi-calendar-check me-2"></i>Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-logout" href="../phpAuthentication/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
