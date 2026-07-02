<?php
session_start();

// Include admin configuration
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

// Include database configuration
admin_include('db_config.php');

// Get admin info
$adminName = $_SESSION['admin_name'];
$adminEmail = $_SESSION['admin_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Menu Management</h1>
                <p class="text-muted">Manage food, beverages, and dining items for the resort</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>
        </div>

        <div class="menu-grid">
            <div class="menu-categories">
                <h3 class="category-title">Categories</h3>
                <ul class="category-list">
                    <li class="category-item active">
                        <i class="bi bi-cup-hot"></i>
                        <span class="category-name">Main Course</span>
                    </li>
                    <li class="category-item">
                        <i class="bi bi-cup-straw"></i>
                        <span class="category-name">Beverages</span>
                    </li>
                    <li class="category-item">
                        <i class="bi bi-cake"></i>
                        <span class="category-name">Desserts</span>
                    </li>
                    <li class="category-item">
                        <i class="bi bi-egg-fried"></i>
                        <span class="category-name">Appetizers</span>
                    </li>
                    <li class="category-item">
                        <i class="bi bi-droplet"></i>
                        <span class="category-name">Cocktails</span>
                    </li>
                </ul>
            </div>

            <div class="menu-items">
                <div class="menu-header">
                    <h3 class="menu-title">Main Course Items</h3>
                </div>

                <div class="items-grid">
                    <div class="menu-item-card">
                        <div class="item-image">
                            <i class="bi bi-egg-fried"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-name">Grilled Chicken</div>
                            <div class="item-description">Herb-marinated grilled chicken with vegetables</div>
                        </div>
                        <div class="item-price">$18.99</div>
                        <div class="item-actions">
                            <button class="action-btn edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="menu-item-card">
                        <div class="item-image">
                            <i class="bi bi-fish"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-name">Grilled Salmon</div>
                            <div class="item-description">Fresh Atlantic salmon with lemon butter</div>
                        </div>
                        <div class="item-price">$24.99</div>
                        <div class="item-actions">
                            <button class="action-btn edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="menu-item-card">
                        <div class="item-image">
                            <i class="bi bi-cup-hot"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-name">Pasta Carbonara</div>
                            <div class="item-description">Classic Italian pasta with creamy sauce</div>
                        </div>
                        <div class="item-price">$16.99</div>
                        <div class="item-actions">
                            <button class="action-btn edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="menu-item-card">
                        <div class="item-image">
                            <i class="bi bi-egg-fried"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-name">Beef Steak</div>
                            <div class="item-description">Premium cut beef with grilled vegetables</div>
                        </div>
                        <div class="item-price">$28.99</div>
                        <div class="item-actions">
                            <button class="action-btn edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="menu-item-card">
                        <div class="item-image">
                            <i class="bi bi-flower1"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-name">Vegetarian Bowl</div>
                            <div class="item-description">Fresh vegetables with quinoa and tahini</div>
                        </div>
                        <div class="item-price">$14.99</div>
                        <div class="item-actions">
                            <button class="action-btn edit-btn">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Category switching
        document.addEventListener('click', function(e) {
            if (e.target.closest('.category-item')) {
                document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
                e.target.closest('.category-item').classList.add('active');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



