<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$navbarStaffName  = $_SESSION['admin_name']  ?? 'Staff';
$navbarStaffEmail = $_SESSION['admin_email'] ?? '';
?>
<!-- YP4K Resort — Staff Top Navbar Component -->
<nav class="top-navbar">
    <div class="navbar-content">
        <div class="navbar-left">
            <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="breadcrumb-section">
                <span class="breadcrumb-text" id="breadcrumbText">Staff Panel</span>
            </div>
        </div>

        <div class="navbar-right">
            <div class="admin-profile-section">
                <button class="admin-profile-btn" onclick="toggleProfileMenu()">
                    <div class="admin-avatar-small">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="admin-info-small">
                        <div class="admin-name-small"><?php echo htmlspecialchars($navbarStaffName, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-header">
                        <div class="profile-avatar-large"><i class="bi bi-person-circle"></i></div>
                        <div class="profile-info">
                            <div class="profile-name"><?php echo htmlspecialchars($navbarStaffName, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="profile-email"><?php echo htmlspecialchars($navbarStaffEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                    <div class="profile-menu">
                        <a href="settings.php" class="profile-menu-item">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                        <div class="profile-divider"></div>
                        <a href="../phpAuthentication/logout.php" class="profile-menu-item logout-item text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
