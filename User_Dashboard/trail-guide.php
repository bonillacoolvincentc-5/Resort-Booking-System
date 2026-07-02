<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

require_once '../phpAuthentication/db_config.php';

// Get user info
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATV Trail Guide & Route Planner — YP4K Resort</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Resort & Dashboard Theme -->
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --bg-primary:      #0f172a;
            --bg-secondary:    #1e293b;
            --bg-tertiary:     #334155;
            --bg-card:         rgba(30, 41, 59, 0.85);
            --text-primary:    #f1f5f9;
            --text-secondary:  #94a3b8;
            --text-muted:      #64748b;
            --accent-primary:  #3b82f6;
            --accent-secondary:#8b5cf6;
            --accent-success:  #10b981;
            --accent-warning:  #f59e0b;
            --accent-danger:   #ef4444;
            --accent-neon:     #9DFF00;
            --border-color:    rgba(51,65,85,0.6);
            --shadow-lg:       0 10px 25px -3px rgba(0,0,0,0.4), 0 4px 6px -2px rgba(0,0,0,0.2);
            --radius-card:     1rem;
            --radius-btn:      0.6rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Banner Section */
        .trail-banner {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 41, 59, 0.95) 100%),
                        radial-gradient(circle at 80% 20%, rgba(157, 255, 0, 0.15) 0%, transparent 40%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .trail-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .trail-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.25rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .trail-subtitle {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 750px;
            margin-bottom: 1.5rem;
        }

        .stats-badge-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-pill {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(157, 255, 0, 0.25);
            padding: 0.5rem 1.1rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ffffff;
            backdrop-filter: blur(8px);
        }

        .stat-pill i {
            color: var(--accent-neon);
            font-size: 1.1rem;
        }

        /* Layout Grid */
        .guide-grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 1.75rem;
            margin-bottom: 2.5rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .guide-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Route Calculator Card */
        .calculator-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 1.75rem;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(16px);
        }

        .card-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-label-custom {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            display: block;
        }

        .form-select-modern {
            background-color: var(--bg-primary);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-radius: var(--radius-btn);
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .form-select-modern:focus {
            outline: none;
            border-color: var(--accent-neon);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.15);
            background-color: var(--bg-primary);
            color: #ffffff;
        }

        .pace-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .pace-btn {
            background: var(--bg-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 0.6rem 0.4rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pace-btn:hover {
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        .pace-btn.active {
            background: rgba(157, 255, 0, 0.15);
            border-color: var(--accent-neon);
            color: var(--accent-neon);
        }

        .btn-calculate {
            background: linear-gradient(135deg, var(--accent-neon), #6EDC45);
            color: #000000;
            font-weight: 800;
            border: none;
            border-radius: var(--radius-btn);
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(157, 255, 0, 0.25);
            transition: all 0.25s ease;
        }

        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(157, 255, 0, 0.4);
            background: linear-gradient(135deg, #a8ff1a, #7ce653);
        }

        /* Results Panel inside Calculator */
        .results-panel {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.15);
            display: none;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .time-display {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(157, 255, 0, 0.3);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .time-display::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-neon);
        }

        .time-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .time-val {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--accent-neon);
            line-height: 1.1;
            margin: 0.2rem 0;
        }

        .time-sub {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .metric-box {
            background: var(--bg-primary);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 10px;
            padding: 0.75rem;
        }

        .metric-lbl {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }

        .metric-num {
            font-size: 1.05rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0.15rem;
        }

        .gear-section {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
            padding: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .gear-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .gear-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .gear-tag {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Map Card */
        .map-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 600px;
        }

        .map-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .map-filter-btns {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .filter-chip {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-chip:hover, .filter-chip.active {
            background: rgba(157, 255, 0, 0.15);
            border-color: var(--accent-neon);
            color: var(--accent-neon);
        }

        #atvMap {
            width: 100%;
            flex: 1;
            min-height: 480px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
        }

        /* Custom Leaflet Dark Popup */
        .leaflet-popup-content-wrapper {
            background: #1e293b !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(157, 255, 0, 0.3) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.7) !important;
            padding: 0.5rem !important;
        }
        .leaflet-popup-tip {
            background: #1e293b !important;
        }
        .popup-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--accent-neon);
            margin-bottom: 0.3rem;
        }
        .popup-desc {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-bottom: 0.6rem;
            line-height: 1.4;
        }
        .popup-elev {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #e2e8f0;
        }
        .popup-btn {
            display: block;
            width: 100%;
            margin-top: 0.6rem;
            padding: 0.4rem;
            background: rgba(157, 255, 0, 0.15);
            border: 1px solid var(--accent-neon);
            color: var(--accent-neon);
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
        }
        .popup-btn:hover {
            background: var(--accent-neon);
            color: #000;
        }

        /* Expeditions Grid */
        .expeditions-section {
            margin-top: 1rem;
            margin-bottom: 3rem;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .expeditions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 1.5rem;
        }

        .expedition-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 1.4rem;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            position: relative;
            overflow: hidden;
        }

        .expedition-card:hover {
            transform: translateY(-4px);
            border-color: rgba(157, 255, 0, 0.3);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        .exp-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .exp-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
        }

        .diff-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }

        .diff-green { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .diff-yellow { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
        .diff-red { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

        .exp-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin: 0;
        }

        .exp-meta {
            display: flex;
            gap: 1rem;
            padding: 0.75rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #e2e8f0;
            font-weight: 600;
        }

        .meta-item i {
            color: var(--accent-neon);
        }

        .btn-exp-plan {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: auto;
            text-decoration: none;
        }

        .btn-exp-plan:hover {
            background: rgba(157, 255, 0, 0.15);
            border-color: var(--accent-neon);
            color: var(--accent-neon);
        }

        /* Safety card */
        .safety-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-left: 4px solid #3b82f6;
            border-radius: var(--radius-card);
            padding: 1.5rem;
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
        }

        .safety-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .safety-content h5 {
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.4rem;
        }

        .safety-content p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <?php include 'components/navigation.php'; ?>

    <!-- Main Container -->
    <div class="container-fluid px-4 py-4">
        
        <!-- Intro Banner -->
        <div class="trail-banner">
            <h1 class="trail-title">
                <i class="bi bi-compass"></i> ATV Trail Guide & Route Planner
            </h1>
            <p class="trail-subtitle">
                Explore YP4K Resort's wilderness trails, calculate travel times between waypoints, and plan your scenic off-road expeditions with our interactive topographical map.
            </p>
            <div class="stats-badge-bar">
                <div class="stat-pill">
                    <i class="bi bi-geo-alt-fill"></i> 10 Scenic Waypoints
                </div>
                <div class="stat-pill">
                    <i class="bi bi-signpost-split"></i> 45 km Wilderness Tracks
                </div>
                <div class="stat-pill">
                    <i class="bi bi-speedometer2"></i> Beginner to Extreme Terrain
                </div>
                <div class="stat-pill" style="border-color: #10b981; color: #10b981;">
                    <i class="bi bi-check-circle-fill"></i> Trails Open & Dry Today
                </div>
            </div>
        </div>

        <!-- Guide & Map Grid -->
        <div class="guide-grid">
            
            <!-- Left: Route Calculator -->
            <div class="calculator-card">
                <div class="card-heading">
                    <span><i class="bi bi-calculator me-2 text-accent"></i>Route Calculator</span>
                    <span class="badge bg-secondary" style="font-size: 0.7rem;">Live GPS Est.</span>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Start Point (Point 1)</label>
                    <select id="startPoint" class="form-select-modern">
                        <option value="wp1">Resort Base Camp (Elev: 120m)</option>
                        <option value="wp3">Clubhouse & Rental Station (Elev: 135m)</option>
                        <option value="wp2">North Gate Entry (Elev: 110m)</option>
                        <option value="wp4">Mountain View Lodge (Elev: 210m)</option>
                        <option value="wp5">Muddy Creek Crossing (Elev: 150m)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Destination (Point 2)</label>
                    <select id="endPoint" class="form-select-modern">
                        <option value="wp8" selected>Eagle's Peak Summit (Elev: 540m)</option>
                        <option value="wp6">Hidden Waterfall Trailhead (Elev: 280m)</option>
                        <option value="wp7">Sunset Ridge Lookout (Elev: 420m)</option>
                        <option value="wp5">Muddy Creek Crossing (Elev: 150m)</option>
                        <option value="wp9">Pine Forest Wilderness Loop (Elev: 310m)</option>
                        <option value="wp10">Canyon Rim Overlook (Elev: 480m)</option>
                    </select>
                </div>

                <label class="form-label-custom">Riding Pace</label>
                <div class="pace-selector">
                    <div class="pace-btn" data-pace="scenic" onclick="setPace('scenic')">
                        <div>Scenic</div>
                        <div style="font-size:0.68rem; opacity:0.8;">~10 km/h</div>
                    </div>
                    <div class="pace-btn active" data-pace="standard" onclick="setPace('standard')">
                        <div>Standard</div>
                        <div style="font-size:0.68rem; opacity:0.8;">~16 km/h</div>
                    </div>
                    <div class="pace-btn" data-pace="thrill" onclick="setPace('thrill')">
                        <div>Thrill</div>
                        <div style="font-size:0.68rem; opacity:0.8;">~24 km/h</div>
                    </div>
                </div>

                <button class="btn-calculate" onclick="calculateRoute()">
                    <i class="bi bi-map"></i> Calculate Route & Time
                </button>

                <!-- Dynamic Results Panel -->
                <div id="resultsPanel" class="results-panel">
                    <div class="time-display">
                        <div class="time-label">Estimated Travel Time</div>
                        <div id="estTime" class="time-val">-- mins</div>
                        <div id="estSub" class="time-sub">Based on Standard Riding Pace</div>
                    </div>

                    <div class="metrics-grid">
                        <div class="metric-box">
                            <div class="metric-lbl">Total Distance</div>
                            <div id="estDist" class="metric-num">-- km</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-lbl">Elevation Gain</div>
                            <div id="estElev" class="metric-num">-- m</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-lbl">Difficulty Rating</div>
                            <div id="estDiff" class="metric-num">--</div>
                        </div>
                        <div class="metric-box">
                            <div class="metric-lbl">Trail Condition</div>
                            <div class="metric-num" style="color:#10b981; font-size:0.85rem;">🟢 Open & Dry</div>
                        </div>
                    </div>

                    <div class="gear-section">
                        <div class="gear-title"><i class="bi bi-shield-check text-accent"></i> Recommended Gear Required:</div>
                        <div id="gearTags" class="gear-tags">
                            <span class="gear-tag"><i class="bi bi-person-bounding-box"></i> Helmet & Goggles</span>
                            <span class="gear-tag"><i class="bi bi-droplet"></i> Water Bottle</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Interactive Map -->
            <div class="map-card">
                <div class="map-header">
                    <div class="card-heading mb-0">
                        <span><i class="bi bi-map-fill me-2" style="color:var(--accent-neon);"></i>Interactive Trail Map</span>
                    </div>
                    <div class="map-filter-btns">
                        <button class="filter-chip active" onclick="filterMap('all', this)">All Waypoints</button>
                        <button class="filter-chip" onclick="filterMap('easy', this)">Beginner 🟢</button>
                        <button class="filter-chip" onclick="filterMap('inter', this)">Intermediate 🟡</button>
                        <button class="filter-chip" onclick="filterMap('extreme', this)">Extreme 🔴</button>
                    </div>
                </div>

                <div id="atvMap"></div>
            </div>

        </div>

        <!-- Popular ATV Expeditions Grid -->
        <div class="expeditions-section">
            <h3 class="section-title">
                <i class="bi bi-stars" style="color:var(--accent-neon);"></i> Popular Pre-Mapped ATV Expeditions
            </h3>

            <div class="expeditions-grid">
                
                <div class="expedition-card">
                    <div class="exp-header">
                        <div class="exp-title">The Muddy Creek Express</div>
                        <span class="diff-badge diff-green">Beginner</span>
                    </div>
                    <p class="exp-desc">A fun, accessible introductory ride from Base Camp to the thrilling riverbanks of Muddy Creek. Great for families and first-time riders.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> 18 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> 3.2 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> +30m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp1', 'wp5')">
                        <i class="bi bi-compass"></i> Show on Map & Calculate
                    </button>
                </div>

                <div class="expedition-card">
                    <div class="exp-header">
                        <div class="exp-title">Eagle's Peak Summit Climb</div>
                        <span class="diff-badge diff-red">Extreme</span>
                    </div>
                    <p class="exp-desc">Our most challenging off-road ascent! Navigate steep rocky switchbacks up to the resort's highest summit for 360-degree mountain vistas.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> 42 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> 6.8 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> +420m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp1', 'wp8')">
                        <i class="bi bi-compass"></i> Show on Map & Calculate
                    </button>
                </div>

                <div class="expedition-card">
                    <div class="exp-header">
                        <div class="exp-title">Sunset Ridge Scenic Loop</div>
                        <span class="diff-badge diff-yellow">Intermediate</span>
                    </div>
                    <p class="exp-desc">A balanced expedition winding through pine forests up to Sunset Ridge Lookout. Best experienced in the late afternoon for golden hour photos.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> 32 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> 5.4 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> +300m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp3', 'wp7')">
                        <i class="bi bi-compass"></i> Show on Map & Calculate
                    </button>
                </div>

                <div class="expedition-card">
                    <div class="exp-header">
                        <div class="exp-title">Hidden Waterfall Exploration</div>
                        <span class="diff-badge diff-yellow">Intermediate</span>
                    </div>
                    <p class="exp-desc">Descend deep into the canyon wilderness to reach the hidden waterfall trailhead. Includes mud tracks and shaded forest trails.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> 28 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> 4.5 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> +160m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp2', 'wp6')">
                        <i class="bi bi-compass"></i> Show on Map & Calculate
                    </button>
                </div>

            </div>
        </div>

        <!-- Safety Guidelines Card -->
        <div class="safety-card">
            <div class="safety-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="safety-content">
                <h5>Wilderness Safety & Off-Road Trail Rules</h5>
                <p>
                    All riders must wear resort-issued safety helmets and eye protection at all times while operating ATV vehicles. Maintain a minimum safe distance of 10 meters from the vehicle ahead. Never deviate from marked trail paths or attempt unauthorized river crossings. In case of emergency or mechanical issue, activate the GPS emergency beacon located on your ATV handlebar or contact Base Camp at #911 from your resort wristband transceiver.
                </p>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // --- 1. WAYPOINTS DATABASE ---
        const waypoints = {
            wp1: { name: "Resort Base Camp", lat: 14.1234, lng: 121.2345, elev: 120, diff: "easy", desc: "Main starting hub, ATV garage, and safety check area." },
            wp2: { name: "North Gate Entry", lat: 14.1300, lng: 121.2300, elev: 110, diff: "easy", desc: "Northern access trail connecting to resort perimeter." },
            wp3: { name: "Clubhouse & Rental Station", lat: 14.1210, lng: 121.2400, elev: 135, diff: "easy", desc: "Equipment rental and briefing center." },
            wp4: { name: "Mountain View Lodge", lat: 14.1150, lng: 121.2450, elev: 210, diff: "inter", desc: "Scenic mid-altitude rest stop with refreshments." },
            wp5: { name: "Muddy Creek Crossing", lat: 14.1350, lng: 121.2200, elev: 150, diff: "inter", desc: "Thrilling shallow water and mud bank crossing." },
            wp6: { name: "Hidden Waterfall Trailhead", lat: 14.1400, lng: 121.2400, elev: 280, diff: "inter", desc: "Lush tropical canyon trail leading to the natural falls." },
            wp7: { name: "Sunset Ridge Lookout", lat: 14.1100, lng: 121.2250, elev: 420, diff: "inter", desc: "Western ridge offering panoramic sunset views." },
            wp8: { name: "Eagle's Peak Summit", lat: 14.1450, lng: 121.2550, elev: 540, diff: "extreme", desc: "Highest elevation trail with steep rock switchbacks." },
            wp9: { name: "Pine Forest Loop", lat: 14.1250, lng: 121.2600, elev: 310, diff: "easy", desc: "Smooth dirt trail through dense mountain pine trees." },
            wp10:{ name: "Canyon Rim Overlook", lat: 14.1050, lng: 121.2500, elev: 480, diff: "extreme", desc: "Cliffside adventure track with breathtaking canyon views." }
        };

        let currentPace = "standard";
        let paceSpeed = 16; // km/h
        let map, markers = {}, currentRouteLine = null;

        // --- 2. INITIALIZE LEAFLET MAP ---
        document.addEventListener("DOMContentLoaded", () => {
            // Center map on resort area
            map = L.map('atvMap', {
                center: [14.1250, 121.2400],
                zoom: 13,
                zoomControl: true,
                attributionControl: false
            });

            // CartoDB Dark Matter Basemap Tiles
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 18,
                subdomains: 'abcd'
            }).addTo(map);

            // Add Markers
            addMarkersToMap('all');

            // Trigger initial calculation
            calculateRoute();
        });

        // --- 3. CUSTOM MARKERS & POPUPS ---
        function addMarkersToMap(filter) {
            // Clear existing markers
            for (let key in markers) {
                map.removeLayer(markers[key]);
            }
            markers = {};

            for (let key in waypoints) {
                let wp = waypoints[key];
                if (filter !== 'all' && wp.diff !== filter) continue;

                // Color code by difficulty
                let color = "#10b981"; // green
                let iconText = "🟢";
                if (wp.diff === "inter") { color = "#f59e0b"; iconText = "🟡"; }
                if (wp.diff === "extreme") { color = "#ef4444"; iconText = "🔴"; }

                let customIcon = L.divIcon({
                    className: 'custom-pin',
                    html: `<div style="background:${color}; width:28px; height:28px; border-radius:50%; border:2px solid #fff; display:flex; align-items:center; justify-content:center; box-shadow:0 0 12px ${color}; font-size:12px;">🚵</div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -16]
                });

                let popupContent = `
                    <div class="popup-title">${wp.name}</div>
                    <div class="popup-desc">${wp.desc}</div>
                    <div class="mb-2"><span class="popup-elev">Elev: ${wp.elev}m</span> <span class="popup-elev" style="background:${color}; color:#000;">${wp.diff.toUpperCase()}</span></div>
                    <button class="popup-btn" onclick="setAsDestination('${key}')">Set as Route Destination 🎯</button>
                `;

                let marker = L.marker([wp.lat, wp.lng], { icon: customIcon })
                    .bindPopup(popupContent)
                    .addTo(map);
                
                markers[key] = marker;
            }
        }

        // --- 4. PACE SELECTION ---
        function setPace(pace) {
            currentPace = pace;
            document.querySelectorAll('.pace-btn').forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-pace') === pace);
            });
            if (pace === "scenic") paceSpeed = 10;
            else if (pace === "standard") paceSpeed = 16;
            else if (pace === "thrill") paceSpeed = 24;

            calculateRoute();
        }

        // --- 5. ROUTE CALCULATION LOGIC ---
        function calculateRoute() {
            let startKey = document.getElementById('startPoint').value;
            let endKey = document.getElementById('endPoint').value;

            if (startKey === endKey) {
                alert("Please select two different waypoints to calculate a route.");
                return;
            }

            let p1 = waypoints[startKey];
            let p2 = waypoints[endKey];

            // Distance calculation (Haversine approx * terrain winding factor 1.45)
            let dLat = (p2.lat - p1.lat) * 111; // km per lat deg
            let dLng = (p2.lng - p1.lng) * 108; // km per lng deg at this latitude
            let straightDist = Math.sqrt(dLat*dLat + dLng*dLng);
            let trailDist = (straightDist * 1.45).toFixed(1); // realistic trail distance in km
            let trailMiles = (trailDist * 0.621371).toFixed(1);

            // Time calculation
            let timeHours = trailDist / paceSpeed;
            let timeMins = Math.round(timeHours * 60);
            if (timeMins < 10) timeMins = 12; // minimum realistic ride time

            // Elevation change
            let elevDiff = p2.elev - p1.elev;
            let elevText = elevDiff >= 0 ? `+${elevDiff}m Incline` : `${elevDiff}m Descent`;

            // Difficulty assessment
            let diffHtml = `<span class="badge bg-success">Beginner</span>`;
            let gearHtml = `
                <span class="gear-tag"><i class="bi bi-person-bounding-box"></i> Helmet & Goggles</span>
                <span class="gear-tag"><i class="bi bi-droplet"></i> Water Bottle</span>
            `;

            if (p2.diff === "extreme" || p1.diff === "extreme" || elevDiff > 250 || trailDist > 5.5) {
                diffHtml = `<span class="badge bg-danger">Extreme Terrain 🔴</span>`;
                gearHtml += `
                    <span class="gear-tag"><i class="bi bi-shield-shaded"></i> Chest Armor</span>
                    <span class="gear-tag"><i class="bi bi-boot"></i> Mud Boots</span>
                    <span class="gear-tag"><i class="bi bi-broadcast"></i> GPS Transceiver</span>
                `;
            } else if (p2.diff === "inter" || p1.diff === "inter" || elevDiff > 120 || trailDist > 3.5) {
                diffHtml = `<span class="badge bg-warning text-dark">Intermediate 🟡</span>`;
                gearHtml += `
                    <span class="gear-tag"><i class="bi bi-boot"></i> Mud Boots</span>
                    <span class="gear-tag"><i class="bi bi-gloves"></i> Riding Gloves</span>
                `;
            }

            // Update DOM
            document.getElementById('estTime').innerText = `${timeMins} mins`;
            document.getElementById('estSub').innerText = `Based on ${currentPace.toUpperCase()} Pace (~${paceSpeed} km/h)`;
            document.getElementById('estDist').innerText = `${trailDist} km (${trailMiles} mi)`;
            document.getElementById('estElev').innerText = elevText;
            document.getElementById('estDiff').innerHTML = diffHtml;
            document.getElementById('gearTags').innerHTML = gearHtml;
            document.getElementById('resultsPanel').style.display = 'block';

            // Draw polyline on Map
            drawRouteOnMap(p1, p2);
        }

        // --- 6. DRAW ROUTE LINE ON MAP ---
        function drawRouteOnMap(p1, p2) {
            if (currentRouteLine) {
                map.removeLayer(currentRouteLine);
            }

            // Create a curved midpoint to simulate wilderness trail winding
            let midLat = (p1.lat + p2.lat) / 2 + (p2.lng - p1.lng) * 0.18;
            let midLng = (p1.lng + p2.lng) / 2 - (p2.lat - p1.lat) * 0.18;

            let latlngs = [
                [p1.lat, p1.lng],
                [midLat, midLng],
                [p2.lat, p2.lng]
            ];

            // Neon green polyline with glow effect
            currentRouteLine = L.polyline(latlngs, {
                color: '#9DFF00',
                weight: 5,
                opacity: 0.85,
                smoothFactor: 1,
                dashArray: '10, 8'
            }).addTo(map);

            // Fit map zoom to route bounds with padding
            map.fitBounds(currentRouteLine.getBounds(), { padding: [60, 60], maxZoom: 15 });

            // Open popup on destination
            if (markers[getWaypointKey(p2.name)]) {
                markers[getWaypointKey(p2.name)].openPopup();
            }
        }

        function getWaypointKey(name) {
            for (let key in waypoints) {
                if (waypoints[key].name === name) return key;
            }
            return null;
        }

        // --- 7. HELPER FUNCTIONS ---
        function planExpedition(start, end) {
            document.getElementById('startPoint').value = start;
            document.getElementById('endPoint').value = end;
            calculateRoute();
            
            // Smooth scroll to map
            document.querySelector('.calculator-card').scrollIntoView({ behavior: 'smooth' });
        }

        function setAsDestination(key) {
            document.getElementById('endPoint').value = key;
            map.closePopup();
            calculateRoute();
            document.querySelector('.calculator-card').scrollIntoView({ behavior: 'smooth' });
        }

        function filterMap(type, btn) {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            addMarkersToMap(type);
        }
    </script>
</body>
</html>
