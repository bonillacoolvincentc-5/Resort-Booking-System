<?php
session_start();

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

require_once '../phpAuthentication/db_config.php';

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$userId    = $_SESSION['user_id'];
$userPhone = $_SESSION['user_phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Bundle Booking — YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary:      #0f172a;
            --bg-secondary:    #1e293b;
            --bg-tertiary:     #334155;
            --bg-card:         rgba(30, 41, 59, 0.85);
            --text-primary:    #f1f5f9;
            --text-secondary:  #cbd5e1;
            --text-muted:      #cbd5e1;
            --accent-primary:  #3b82f6;
            --accent-secondary:#8b5cf6;
            --accent-success:  #10b981;
            --accent-warning:  #f59e0b;
            --accent-danger:   #ef4444;
            --border-color:    rgba(51,65,85,0.6);
            --shadow-sm:  0 1px 2px rgba(0,0,0,.05);
            --shadow-md:  0 4px 6px -1px rgba(0,0,0,.12), 0 2px 4px -1px rgba(0,0,0,.07);
            --shadow-lg:  0 10px 15px -3px rgba(0,0,0,.12), 0 4px 6px -2px rgba(0,0,0,.05);
            --shadow-xl:  0 20px 25px -5px rgba(0,0,0,.14), 0 10px 10px -5px rgba(0,0,0,.06);
            --gradient-primary: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            --radius-card: 1rem;
            --radius-btn:  0.5rem;
            
            --cal-green: rgba(16, 185, 129, 0.2);
            --cal-green-border: #10b981;
            --cal-yellow: rgba(245, 158, 11, 0.2);
            --cal-yellow-border: #f59e0b;
            --cal-red: rgba(239, 68, 68, 0.2);
            --cal-red-border: #ef4444;
            --cal-gray: rgba(100, 116, 139, 0.2);
            --cal-gray-border: #64748b;
            --cal-blue-border: #3b82f6;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* NAVBAR */
        .navbar-modern {
            background: rgba(15,23,42,.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-brand { font-weight: 700; font-size: 1.25rem; color: var(--text-primary) !important; display: flex; align-items: center; gap:.5rem; }
        .navbar-brand i { color: var(--accent-primary); font-size:1.5rem; }
        .nav-link { color: var(--text-secondary) !important; font-weight:500; padding: .5rem 1rem !important; border-radius: var(--radius-btn); transition: all .3s ease; margin: 0 .25rem; }
        .nav-link:hover { color: var(--text-primary) !important; background: rgba(59,130,246,.1); }
        .nav-link.active { color: var(--text-primary) !important; background: var(--accent-primary); }
        .nav-link.btn-logout { background: var(--accent-danger); color: white !important; border: none; display: flex; align-items: center; justify-content: center; }
        .nav-link.btn-logout:hover { background: #dc2626; transform: translateY(-1px); }

        .page-wrap { padding: 2rem 0 4rem; }

        /* HEADER */
        .page-header {
            background: var(--bg-card); backdrop-filter: blur(12px);
            border: 1px solid var(--border-color); border-radius: var(--radius-card);
            padding: 2rem; margin-bottom: 2rem; position: relative; overflow: hidden;
        }
        .page-header::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: var(--gradient-primary); }
        .page-title { font-size: 2rem; font-weight:700; margin-bottom:.4rem; background: linear-gradient(135deg, var(--text-primary), var(--accent-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-subtitle { color: var(--text-secondary); font-size:1rem; }

        /* TWO COLUMN LAYOUT */
        .bundle-layout {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        .left-col { width: 38%; flex-shrink: 0; display: flex; flex-direction: column; height: calc(100vh - 180px); }
        .package-filters {
            flex-shrink: 0;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .search-wrap {
            position: relative;
        }
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .filter-search-input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border-radius: var(--radius-btn);
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.88rem;
            outline: none;
            transition: all 0.25s ease;
        }
        .filter-search-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .filter-search-input::placeholder {
            color: var(--text-muted);
        }
        .filter-pills {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            margin-top: 0.6rem;
        }
        .filter-pill {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .filter-pill:hover {
            color: var(--text-primary);
            background: rgba(59, 130, 246, 0.15);
            border-color: var(--accent-primary);
        }
        .filter-pill.active {
            background: var(--accent-primary);
            color: #ffffff;
            border-color: var(--accent-primary);
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.35);
        }
        .bundles-scroll-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding-right: 8px;
        }
        /* SCROLLBAR */
        .bundles-scroll-list::-webkit-scrollbar { width: 6px; }
        .bundles-scroll-list::-webkit-scrollbar-track { background: var(--bg-tertiary); border-radius: 4px; }
        .bundles-scroll-list::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
        .bundles-scroll-list::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* BUNDLE CARDS */
        .bundle-card {
            background: var(--bg-card); backdrop-filter: blur(10px);
            border: 2px solid var(--border-color); border-radius: var(--radius-card);
            overflow: hidden; transition: all .35s ease; cursor: pointer;
            display: flex; flex-direction: column;
            flex-shrink: 0;
        }
        .bundle-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); border-color: rgba(59,130,246, 0.5); }
        .bundle-card.active { border-color: var(--accent-primary); box-shadow: 0 0 0 4px rgba(59,130,246, 0.2); }
        
        .card-img-wrap { position: relative; height: 160px; overflow: hidden; flex-shrink:0; background: var(--bg-tertiary); }
        .card-img-wrap img { width:100%; height:100%; object-fit:cover; transition: transform .5s ease; }
        .bundle-card:hover .card-img-wrap img { transform: scale(1.06); }
        .status-badge { position: absolute; top:.75rem; right:.75rem; padding:.25rem .75rem; border-radius:2rem; font-size:.7rem; font-weight:700; text-transform:uppercase; backdrop-filter:blur(8px); z-index: 2; }
        .status-available { background:rgba(16,185,129,.9); color:#fff; }
        .status-fully-booked { background:rgba(239,68,68,.9); color:#fff; }
        .status-inactive { background:rgba(100,116,139,.9); color:#fff; }

        .type-badge {
            position: absolute;
            top: .75rem;
            left: .75rem;
            padding: .25rem .75rem;
            border-radius: 2rem;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(8px);
            z-index: 2;
        }
        .type-bundle { background: rgba(139, 92, 246, 0.9); color: #fff; }
        .type-accommodation { background: rgba(59, 130, 246, 0.9); color: #fff; }
        .type-activity { background: rgba(16, 185, 129, 0.9); color: #fff; }

        .placeholder-banner {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(30,41,59,0.95), rgba(15,23,42,0.95));
            color: var(--text-muted);
            gap: 0.5rem;
        }
        .placeholder-banner i { font-size: 2.8rem; color: var(--accent-primary); }
        .placeholder-banner span { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; }

        .card-content { padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .bundle-name { font-size:1.15rem; font-weight:700; color:var(--text-primary); }
        .bundle-price { font-size:1rem; font-weight:700; color:var(--accent-success); }
        .bundle-desc { font-size:.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom: 0.5rem; }
        
        .bundle-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .meta-pill { font-size: 0.75rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); padding: 0.2rem 0.6rem; border-radius: 1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.3rem; }
        .meta-pill i { color: var(--accent-primary); }

        .select-btn { width: 100%; padding: 0.75rem; border-radius: 0 0 calc(var(--radius-card) - 2px) calc(var(--radius-card) - 2px); border: none; background: var(--bg-tertiary); color: var(--text-primary); font-weight: 600; transition: all 0.3s ease; }
        .bundle-card.active .select-btn { background: var(--accent-primary); color: #fff; }

        /* CALENDAR */
        .calendar-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-card); padding: 1.5rem; box-shadow: var(--shadow-md); position: relative; }
        .calendar-overlay { position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px); z-index: 10; display: flex; align-items: center; justify-content: center; flex-direction: column; border-radius: var(--radius-card); transition: opacity 0.3s; }
        .calendar-overlay.hidden { opacity: 0; pointer-events: none; }
        .calendar-overlay h3 { color: white; font-weight: 600; }
        
        .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .cal-title { font-size: 1.25rem; font-weight: 700; }
        .cal-nav { background: transparent; border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-btn); padding: 0.4rem 0.8rem; cursor: pointer; transition: all 0.2s; }
        .cal-nav:hover { background: var(--bg-tertiary); }
        
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; text-align: center; }
        .cal-day-header { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); padding-bottom: 0.5rem; text-transform: uppercase; }
        .cal-cell { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; background: var(--bg-tertiary); color: var(--text-primary); }
        
        /* Calendar statuses */
        .cal-cell.empty { background: transparent; cursor: default; }
        .cal-cell.status-available { background: var(--cal-green); border-color: var(--cal-green-border); color: #fff; }
        .cal-cell.status-limited { background: var(--cal-yellow); border-color: var(--cal-yellow-border); color: #fff; }
        .cal-cell.status-full { background: var(--cal-red); border-color: var(--cal-red-border); color: #fff; cursor: not-allowed; opacity: 0.6; }
        .cal-cell.status-blocked { background: var(--cal-gray); border-color: var(--cal-gray-border); color: var(--text-muted); cursor: not-allowed; opacity: 0.6; }
        
        /* Selected date */
        .cal-cell.selected { box-shadow: 0 0 0 3px var(--bg-primary), 0 0 0 5px var(--cal-blue-border); transform: scale(1.05); z-index: 5; }

        .cal-legend { display: flex; gap: 1.5rem; margin-top: 1.5rem; justify-content: center; font-size: 0.8rem; color: var(--text-secondary); }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; }
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; border: 1px solid; }

        /* SUMMARY PANEL */
        .summary-panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-card); padding: 1.5rem; box-shadow: var(--shadow-md); display: flex; gap: 1.5rem; }
        .sum-img { width: 140px; height: 140px; border-radius: 0.75rem; object-fit: cover; flex-shrink: 0; background: var(--bg-tertiary); }
        .sum-details { flex-grow: 1; display: flex; flex-direction: column; }
        .sum-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem; }
        .sum-price { font-size: 1.1rem; color: var(--accent-success); font-weight: 700; margin-bottom: 0.5rem; }
        .sum-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--text-secondary); }
        .sum-grid-item strong { color: var(--text-primary); display: block; margin-bottom: 0.1rem; }
        
        .guest-input { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
        .guest-input button { background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 4px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .guest-input input { background: transparent; border: none; color: var(--text-primary); text-align: center; width: 40px; font-weight: 600; }
        
        .sum-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); }
        .sum-total-lbl { font-size: 0.9rem; color: var(--text-muted); }
        .sum-total-val { font-size: 1.5rem; font-weight: 800; color: var(--accent-primary); }
        
        .btn-continue { background: var(--gradient-primary); color: #fff; border: none; padding: 0.75rem 2rem; border-radius: var(--radius-btn); font-weight: 600; font-size: 1rem; transition: all 0.3s; }
        .btn-continue:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59,130,246,.3); }
        .btn-continue:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(100%); }

        @media (max-width: 992px) {
            .bundle-layout { flex-direction: column; }
            .left-col { width: 100%; height: auto; max-height: 500px; }
            .right-col { width: 100%; }
            .summary-panel { flex-direction: column; }
            .sum-img { width: 100%; height: 200px; }
        }

        .spinner-ring {
            width:48px; height:48px; border:4px solid var(--border-color);
            border-top-color:var(--accent-primary); border-radius:50%;
            animation: spin .8s linear infinite; margin: 0 auto;
        }
        @keyframes spin { to { transform:rotate(360deg); } }
        
        .d-none { display: none !important; }
    </style>
</head>
<body>
    <?php include 'components/navigation.php'; ?>

    <div class="container-fluid px-4 page-wrap">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-box-seam me-2"></i>Premium Bundle Booking</h1>
            <p class="page-subtitle">Select an exclusive resort package and check availability instantly.</p>
        </div>

        <div id="alertContainer"></div>

        <div class="bundle-layout">
            <!-- LEFT COLUMN -->
            <div class="left-col">
                <div class="package-filters">
                    <div class="search-wrap">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="searchInput" class="filter-search-input" placeholder="Search packages, rooms, cottages...">
                    </div>
                    <div class="filter-pills">
                        <button class="filter-pill active" data-filter="all">All (<span id="countAll">0</span>)</button>
                        <button class="filter-pill" data-filter="bundle">Bundles (<span id="countBundle">0</span>)</button>
                        <button class="filter-pill" data-filter="accommodation">Stays (<span id="countAcc">0</span>)</button>
                        <button class="filter-pill" data-filter="activity">Activities (<span id="countAct">0</span>)</button>
                    </div>
                </div>

                <div class="bundles-scroll-list" id="bundlesList">
                    <div class="spinner-ring mt-5"></div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="right-col">
                <!-- CALENDAR -->
                <div class="calendar-container">
                    <div class="calendar-overlay" id="calOverlay">
                        <i class="bi bi-calendar-check" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h3>Select a bundle first</h3>
                        <p class="text-secondary text-center">Choose a package from the left to view available dates.</p>
                    </div>

                    <div class="cal-header">
                        <button class="cal-nav" id="prevMonth"><i class="bi bi-chevron-left"></i></button>
                        <div class="cal-title" id="calTitle">June 2026</div>
                        <button class="cal-nav" id="nextMonth"><i class="bi bi-chevron-right"></i></button>
                    </div>

                    <div class="cal-grid" id="calGrid">
                        <div class="cal-day-header">Sun</div>
                        <div class="cal-day-header">Mon</div>
                        <div class="cal-day-header">Tue</div>
                        <div class="cal-day-header">Wed</div>
                        <div class="cal-day-header">Thu</div>
                        <div class="cal-day-header">Fri</div>
                        <div class="cal-day-header">Sat</div>
                        <!-- Dates injected via JS -->
                    </div>

                    <div class="cal-legend">
                        <div class="legend-item"><div class="legend-dot" style="background:var(--cal-green);border-color:var(--cal-green-border)"></div>Available</div>
                        <div class="legend-item"><div class="legend-dot" style="background:var(--cal-yellow);border-color:var(--cal-yellow-border)"></div>Limited</div>
                        <div class="legend-item"><div class="legend-dot" style="background:var(--cal-red);border-color:var(--cal-red-border)"></div>Full</div>
                        <div class="legend-item"><div class="legend-dot" style="background:var(--cal-gray);border-color:var(--cal-gray-border)"></div>Blocked</div>
                    </div>
                </div>

                <!-- SUMMARY PANEL -->
                <div class="summary-panel opacity-50" id="summaryPanel">
                    <img src="" alt="Bundle" class="sum-img d-none" id="sumImg">
                    <div class="sum-img" id="sumImgPlaceholder" style="display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--text-muted)"><i class="bi bi-image"></i></div>
                    
                    <div class="sum-details">
                        <div class="sum-title" id="sumTitle">Select a bundle</div>
                        <div class="sum-price" id="sumPrice">—</div>
                        
                        <div class="sum-grid">
                            <div class="sum-grid-item">
                                <strong>Date Selected</strong>
                                <span id="sumDate">—</span>
                            </div>
                            <div class="sum-grid-item">
                                <strong>Duration</strong>
                                <span id="sumDuration">Whole Day</span>
                            </div>
                            <div class="sum-grid-item" style="grid-column: span 2">
                                <strong>Inclusions</strong>
                                <span id="sumInclusions">—</span>
                            </div>
                        </div>

                        <div class="guest-input" style="opacity: 0.5; pointer-events: none;" id="guestControl">
                            <span class="text-secondary" style="font-size: 0.85rem">Guests:</span>
                            <button id="btnMinusGuest"><i class="bi bi-dash"></i></button>
                            <input type="number" id="guestCount" value="1" readonly>
                            <button id="btnPlusGuest"><i class="bi bi-plus"></i></button>
                            <span class="text-muted ms-2" style="font-size: 0.8rem" id="maxCapText">Max: -</span>
                        </div>

                        <div class="sum-footer">
                            <div>
                                <div class="sum-total-lbl">Estimated Total</div>
                                <div class="sum-total-val" id="sumTotal">₱0.00</div>
                            </div>
                            <button class="btn-continue" id="btnContinue" disabled>Continue Booking <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const DOM = {
            bundlesList: document.getElementById('bundlesList'),
            calOverlay: document.getElementById('calOverlay'),
            calTitle: document.getElementById('calTitle'),
            calGrid: document.getElementById('calGrid'),
            prevMonth: document.getElementById('prevMonth'),
            nextMonth: document.getElementById('nextMonth'),
            
            // Summary
            summaryPanel: document.getElementById('summaryPanel'),
            sumImg: document.getElementById('sumImg'),
            sumImgPlaceholder: document.getElementById('sumImgPlaceholder'),
            sumTitle: document.getElementById('sumTitle'),
            sumPrice: document.getElementById('sumPrice'),
            sumDate: document.getElementById('sumDate'),
            sumInclusions: document.getElementById('sumInclusions'),
            guestControl: document.getElementById('guestControl'),
            guestCount: document.getElementById('guestCount'),
            btnMinusGuest: document.getElementById('btnMinusGuest'),
            btnPlusGuest: document.getElementById('btnPlusGuest'),
            maxCapText: document.getElementById('maxCapText'),
            sumTotal: document.getElementById('sumTotal'),
            btnContinue: document.getElementById('btnContinue'),
            alertContainer: document.getElementById('alertContainer')
        };

        let bundles = [];
        let currentFilter = 'all';
        let searchQuery = '';
        let selectedBundle = null;
        let selectedDate = null;
        let currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth() + 1; // 1-12
        let availData = {};

        // Initialization
        async function init() {
            setupFilterControls();
            await fetchBundles();
            renderCalendarHeader();
            renderCalendarGrid(); // empty skeleton
            
            DOM.prevMonth.addEventListener('click', () => changeMonth(-1));
            DOM.nextMonth.addEventListener('click', () => changeMonth(1));
            
            DOM.btnMinusGuest.addEventListener('click', () => updateGuests(-1));
            DOM.btnPlusGuest.addEventListener('click', () => updateGuests(1));
            
            DOM.btnContinue.addEventListener('click', submitBooking);
        }

        function setupFilterControls() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value.trim().toLowerCase();
                    renderBundles();
                });
            }

            document.querySelectorAll('.filter-pill').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.getAttribute('data-filter');
                    renderBundles();
                });
            });
        }

        function showAlert(msg, type='danger') {
            DOM.alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="border-radius:var(--radius-btn); background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); margin-bottom: 1.5rem;">
                    ${msg}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => DOM.alertContainer.innerHTML = '', 5000);
        }

        async function fetchBundles() {
            try {
                const res = await fetch('../php/get_packages.php');
                const data = await res.json();
                if (data.success) {
                    bundles = data.packages || data.bundles || [];
                    updateFilterCounts();
                    renderBundles();
                } else {
                    DOM.bundlesList.innerHTML = `<p class="text-danger text-center mt-5">Failed to load bundles.</p>`;
                }
            } catch (err) {
                DOM.bundlesList.innerHTML = `<p class="text-danger text-center mt-5">Connection error.</p>`;
            }
        }

        function updateFilterCounts() {
            const countAll = document.getElementById('countAll');
            const countBundle = document.getElementById('countBundle');
            const countAcc = document.getElementById('countAcc');
            const countAct = document.getElementById('countAct');

            if (countAll) countAll.textContent = bundles.length;
            if (countBundle) countBundle.textContent = bundles.filter(b => b.package_type === 'bundle').length;
            if (countAcc) countAcc.textContent = bundles.filter(b => b.package_type === 'accommodation').length;
            if (countAct) countAct.textContent = bundles.filter(b => b.package_type === 'activity').length;
        }

        function renderBundles() {
            DOM.bundlesList.innerHTML = '';
            
            const filtered = bundles.filter(b => {
                const matchType = (currentFilter === 'all') || (b.package_type === currentFilter);
                const matchQuery = !searchQuery || 
                    (b.name && b.name.toLowerCase().includes(searchQuery)) ||
                    (b.description && b.description.toLowerCase().includes(searchQuery));
                return matchType && matchQuery;
            });

            if (filtered.length === 0) {
                DOM.bundlesList.innerHTML = `<div class="text-center mt-5 py-4"><i class="bi bi-box-seam d-block mb-2" style="font-size:2rem;color:var(--text-muted)"></i><p class="text-muted">No resort packages found.</p></div>`;
                return;
            }

            filtered.forEach(b => {
                const card = document.createElement('div');
                card.className = 'bundle-card' + (selectedBundle && selectedBundle.id === b.id && selectedBundle.package_type === b.package_type ? ' active' : '');
                card.onclick = () => selectBundle(b, card);
                
                const typeLabel = b.package_type === 'bundle' ? 'Bundle' : (b.package_type === 'accommodation' ? 'Stay-In / Cottage' : 'Activity');
                const typeClass = b.package_type === 'bundle' ? 'type-bundle' : (b.package_type === 'accommodation' ? 'type-accommodation' : 'type-activity');
                const fallbackIcon = b.icon || 'bi-box-seam';

                const imgHTML = b.image_url 
                    ? `<img src="${b.image_url}" alt="${b.name}" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                       <div class="placeholder-banner" style="display:none;"><i class="bi ${fallbackIcon}"></i><span>YP4K Resort</span></div>`
                    : `<div class="placeholder-banner"><i class="bi ${fallbackIcon}"></i><span>YP4K Resort</span></div>`;
                
                let combinedInclusions = [...(b.amenities || []), ...(b.inclusions || [])];
                combinedInclusions = combinedInclusions.filter(i => !i.toLowerCase().includes('capacity: for'));
                
                const inclusionsPreview = combinedInclusions.slice(0,3).join(' • ') + (combinedInclusions.length > 3 ? '...' : '');

                card.innerHTML = `
                    <div class="card-img-wrap">
                        <div class="type-badge ${typeClass}">${typeLabel}</div>
                        ${imgHTML}
                        <div class="status-badge status-available">Available</div>
                    </div>
                    <div class="card-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="bundle-name">${b.name}</div>
                            <div class="bundle-price">₱${parseFloat(b.price).toLocaleString()}</div>
                        </div>
                        <div class="bundle-desc">${b.short_description || 'A premium resort package.'}</div>
                        <div class="bundle-meta">
                            <div class="meta-pill"><i class="bi bi-people"></i> Up to ${b.capacity}</div>
                            ${inclusionsPreview ? `<div class="meta-pill"><i class="bi bi-star"></i> Included: ${inclusionsPreview}</div>` : ''}
                        </div>
                    </div>
                    <button class="select-btn">Select Package</button>
                `;
                DOM.bundlesList.appendChild(card);
            });
        }

        async function selectBundle(b, cardElement) {
            // Remove active from all
            document.querySelectorAll('.bundle-card').forEach(c => c.classList.remove('active'));
            cardElement.classList.add('active');

            selectedBundle = b;
            selectedDate = null; // reset date
            availData = {};

            // Construct full inclusions for summary
            let combinedInclusions = [...(b.amenities || []), ...(b.inclusions || [])].filter(i => !i.toLowerCase().includes('capacity: for'));

            // Update Summary
            DOM.summaryPanel.classList.remove('opacity-50');
            DOM.sumTitle.textContent = b.name;
            DOM.sumPrice.textContent = `₱${parseFloat(b.price).toLocaleString()}`;
            DOM.sumInclusions.textContent = combinedInclusions.length > 0 ? combinedInclusions.join(', ') : 'None specified';
            DOM.sumDate.textContent = 'Please select a date';
            DOM.guestCount.value = 1;
            DOM.maxCapText.textContent = `Max: ${b.capacity}`;
            DOM.guestControl.style.opacity = '1';
            DOM.guestControl.style.pointerEvents = 'auto';
            updateTotal();

            // Set Image
            // We prepend ../ as image_url from db is relative to root if it is in uploads
            // Check if image_url has uploads/ or ../
            let imgUrl = b.image_url;
            if (imgUrl) {
                if (!imgUrl.startsWith('http') && !imgUrl.startsWith('../')) {
                    imgUrl = '../' + imgUrl;
                }
                DOM.sumImg.src = imgUrl;
                DOM.sumImg.classList.remove('d-none');
                DOM.sumImgPlaceholder.classList.add('d-none');
            } else {
                DOM.sumImg.classList.add('d-none');
                DOM.sumImgPlaceholder.classList.remove('d-none');
            }

            // Hide overlay and load calendar
            DOM.calOverlay.classList.add('hidden');
            checkButtonState();
            await fetchAvailability();
        }

        async function fetchAvailability() {
            if (!selectedBundle) return;
            
            DOM.calGrid.style.opacity = '0.5';
            
            try {
                const res = await fetch(`../php/get_bundle_availability.php?bundle_id=${selectedBundle.id}&year=${currentYear}&month=${currentMonth}`);
                const data = await res.json();
                if (data.success) {
                    availData = data.days;
                } else {
                    showAlert(data.message || 'Failed to load availability');
                    availData = {};
                }
            } catch(e) {
                availData = {};
            }
            
            DOM.calGrid.style.opacity = '1';
            renderCalendarGrid();
        }

        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            renderCalendarHeader();
            if (selectedBundle) {
                fetchAvailability();
            } else {
                renderCalendarGrid();
            }
        }

        function renderCalendarHeader() {
            const date = new Date(currentYear, currentMonth - 1, 1);
            DOM.calTitle.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        }

        function renderCalendarGrid() {
            const headers = `
                <div class="cal-day-header">Sun</div><div class="cal-day-header">Mon</div>
                <div class="cal-day-header">Tue</div><div class="cal-day-header">Wed</div>
                <div class="cal-day-header">Thu</div><div class="cal-day-header">Fri</div>
                <div class="cal-day-header">Sat</div>
            `;
            
            let html = headers;
            
            const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            
            // Generate local midnight date string
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            for (let i = 0; i < firstDay; i++) {
                html += `<div class="cal-cell empty"></div>`;
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                
                let statusClass = '';
                let isSelectable = false;

                if (selectedBundle && availData[dateStr]) {
                    const status = availData[dateStr].status;
                    statusClass = `status-${status}`;
                    if (dateStr >= todayStr && (status === 'available' || status === 'limited')) {
                        isSelectable = true;
                    } else if (dateStr < todayStr) {
                        statusClass = 'status-blocked'; 
                    }
                } else if (dateStr < todayStr) {
                    statusClass = 'status-blocked';
                }

                let classes = `cal-cell ${statusClass}`;
                if (selectedDate === dateStr) classes += ' selected';

                let onClick = isSelectable ? `onclick="selectDate('${dateStr}')"` : '';

                html += `<div class="${classes}" ${onClick}>${d}</div>`;
            }

            DOM.calGrid.innerHTML = html;
        }

        function selectDate(dateStr) {
            selectedDate = dateStr;
            renderCalendarGrid(); 
            
            // Use timezone offset correction for display
            const d = new Date(dateStr + "T00:00:00");
            DOM.sumDate.textContent = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
            
            checkButtonState();
        }

        function updateGuests(delta) {
            if (!selectedBundle) return;
            let current = parseInt(DOM.guestCount.value);
            current += delta;
            if (current < 1) current = 1;
            if (current > selectedBundle.capacity) current = selectedBundle.capacity;
            DOM.guestCount.value = current;
        }

        function updateTotal() {
            if (selectedBundle) {
                DOM.sumTotal.textContent = `₱${parseFloat(selectedBundle.price).toLocaleString()}`;
            }
        }

        function checkButtonState() {
            if (selectedBundle && selectedDate) {
                DOM.btnContinue.disabled = false;
            } else {
                DOM.btnContinue.disabled = true;
            }
        }

        async function submitBooking() {
            if (!selectedBundle || !selectedDate) return;
            
            DOM.btnContinue.disabled = true;
            DOM.btnContinue.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...`;

            // Setup checkout as next day since bundle duration is typically a day event or 1 night
            const checkIn = new Date(selectedDate + "T00:00:00");
            const checkOut = new Date(checkIn);
            checkOut.setDate(checkOut.getDate() + 1);
            const checkOutStr = `${checkOut.getFullYear()}-${String(checkOut.getMonth()+1).padStart(2,'0')}-${String(checkOut.getDate()).padStart(2,'0')}`;

            const payload = {
                booking_type: 'bundle',
                bundle_id: selectedBundle.id,
                full_name: '<?= htmlspecialchars(addslashes($userName), ENT_QUOTES, 'UTF-8') ?>',
                email: '<?= htmlspecialchars(addslashes($userEmail), ENT_QUOTES, 'UTF-8') ?>',
                phone_number: '<?= htmlspecialchars(addslashes($userPhone), ENT_QUOTES, 'UTF-8') ?>' || 'N/A',
                check_in_date: selectedDate,
                check_out_date: checkOutStr,
                number_of_guests: parseInt(DOM.guestCount.value),
                special_requests: ''
            };

            try {
                const res = await fetch('../php/create_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || 'my-bookings.php';
                } else {
                    showAlert(data.message || 'Failed to create booking.');
                    DOM.btnContinue.disabled = false;
                    DOM.btnContinue.innerHTML = `Continue Booking <i class="bi bi-arrow-right ms-2"></i>`;
                }
            } catch(e) {
                showAlert('A network error occurred.');
                DOM.btnContinue.disabled = false;
                DOM.btnContinue.innerHTML = `Continue Booking <i class="bi bi-arrow-right ms-2"></i>`;
            }
        }

        // Run
        init();
    </script>
</body>
</html>
