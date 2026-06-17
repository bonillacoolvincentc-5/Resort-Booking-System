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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: rgba(30, 41, 59, 0.8);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --border-color: rgba(51, 65, 85, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .menu-categories {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            height: fit-content;
        }

        .category-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }

        .category-list {
            list-style: none;
        }

        .category-item {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .category-item:hover,
        .category-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .category-item i {
            font-size: 1rem;
        }

        .category-name {
            font-weight: 500;
        }

        .menu-items {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .menu-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .add-item-btn {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-item-btn:hover {
            transform: translateY(-2px);
        }

        .items-grid {
            display: grid;
            gap: 1rem;
        }

        .menu-item-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem;
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .menu-item-card:hover {
            background: rgba(59, 130, 246, 0.05);
            border-color: var(--accent-primary);
        }

        .item-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .item-details {
            display: flex;
            flex-direction: column;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .item-description {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .item-price {
            font-weight: 700;
            color: var(--accent-success);
            font-size: 1.1rem;
        }

        .item-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.75rem;
        }

        .edit-btn {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-warning);
        }

        .edit-btn:hover {
            background: var(--accent-warning);
            color: white;
        }

        .delete-btn {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
        }

        .delete-btn:hover {
            background: var(--accent-danger);
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .menu-item-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div class="main-content">
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
                    <button class="add-item-btn">
                        <i class="bi bi-plus-circle"></i>
                        Add Item
                    </button>
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
        fetch('components/sidebar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('sidebar-container').innerHTML = html;
                // Set active navigation after sidebar loads
                setTimeout(setActiveNavigation, 50);
            });

        fetch('components/navbar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('navbar-container').innerHTML = html;
            });

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
