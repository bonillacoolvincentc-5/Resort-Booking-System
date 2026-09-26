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
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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
            --text-secondary:  #cbd5e1;
            --text-muted:      #cbd5e1;
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
            align-items: center;
            gap: 1.75rem;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .stat-pill i {
            color: var(--accent-neon);
            font-size: 1.15rem;
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
        
        @media (max-width: 768px) {
            .trail-banner {
                padding: 1rem;
                margin-bottom: 1.25rem;
            }
            .trail-title {
                font-size: 1.1rem;
                gap: 0.4rem;
                margin-bottom: 0.25rem;
            }
            .trail-title i {
                font-size: 1rem;
            }
            .trail-subtitle {
                font-size: 0.7rem;
                margin-bottom: 1rem;
                line-height: 1.4;
            }
            .stats-badge-bar {
                gap: 0.5rem;
            }
            .stat-pill {
                font-size: 0.65rem;
                gap: 0.35rem;
            }
            .stat-pill i {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .calculator-card {
                padding: 0.75rem;
            }
            .card-heading {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
            }
            .form-label-custom {
                font-size: 0.6rem;
                margin-bottom: 0.2rem;
            }
            .form-select-modern {
                padding: 0.4rem 0.6rem;
                font-size: 0.75rem;
            }
            .pace-selector {
                gap: 0.3rem;
                margin-bottom: 0.75rem;
            }
            .pace-btn {
                padding: 0.35rem;
                font-size: 0.6rem;
            }
            .btn-calculate {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }
            .time-display {
                padding: 0.6rem;
                margin-bottom: 0.6rem;
            }
            .time-label {
                font-size: 0.55rem;
            }
            .time-val {
                font-size: 1.4rem;
            }
            .time-sub {
                font-size: 0.6rem;
            }
            .metrics-grid {
                gap: 0.4rem;
                margin-bottom: 0.5rem;
            }
            .metric-box {
                padding: 0.4rem;
            }
            .metric-lbl {
                font-size: 0.55rem;
            }
            .metric-num {
                font-size: 0.75rem;
            }
            .results-panel {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }
            .section-title {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
            }
            .expeditions-grid {
                gap: 0.75rem;
            }
            .expedition-card {
                padding: 0.75rem;
            }
            .exp-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.2rem;
                margin-bottom: 0.5rem;
            }
            .exp-title {
                font-size: 0.85rem;
            }
            .diff-badge {
                padding: 0.15rem 0.4rem;
                font-size: 0.55rem;
            }
            .exp-desc {
                font-size: 0.65rem;
                margin-bottom: 0.5rem;
                line-height: 1.3;
            }
            .exp-meta {
                gap: 0.4rem;
                margin-bottom: 0.5rem;
                padding-bottom: 0.5rem;
                flex-wrap: wrap;
            }
            .meta-item {
                font-size: 0.65rem;
                gap: 0.25rem;
            }
            .meta-item i {
                font-size: 0.75rem;
            }
            .btn-exp-plan {
                padding: 0.4rem 0.75rem;
                font-size: 0.7rem;
            }
            .safety-card {
                padding: 0.75rem;
                gap: 0.5rem;
                border-left-width: 3px;
                border-radius: 12px;
                align-items: flex-start;
            }
            .safety-icon {
                width: 28px;
                height: 28px;
                font-size: 1rem;
                border-radius: 8px;
            }
            .safety-content h5 {
                font-size: 0.8rem;
                margin-bottom: 0.2rem;
            }
            .safety-content p {
                font-size: 0.65rem;
                line-height: 1.35;
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

        .btn-calculate.active-tracking {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            animation: pulse-danger 1.5s infinite;
        }
        @keyframes pulse-danger {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
            70% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Custom Leaflet Live Radar Icon */
        .live-user-container {
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .live-user-dot {
            width: 18px; height: 18px;
            background: #10b981;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(16,185,129,0.8);
            position: relative;
            z-index: 2;
        }
        .live-user-pulse {
            position: absolute;
            width: 44px; height: 44px;
            background: rgba(16, 185, 129, 0.35);
            border: 2px solid rgba(16, 185, 129, 0.7);
            border-radius: 50%;
            animation: radar-pulse 1.8s ease-out infinite;
            z-index: 1;
        }
        @keyframes radar-pulse {
            0% { transform: scale(0.4); opacity: 1; }
            100% { transform: scale(2.4); opacity: 0; }
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

        /* Google Maps Style Floating Layer Switcher */
        .gmaps-layer-bar {
            position: absolute;
            bottom: 16px;
            left: 16px;
            z-index: 1000;
            background: #ffffff;
            border-radius: 12px;
            padding: 6px 10px;
            display: flex;
            gap: 6px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(0, 0, 0, 0.08);
            max-width: calc(100% - 32px);
            overflow-x: auto;
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }

        .gmaps-layer-card {
            background: transparent;
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 62px;
            color: #3c4043;
            outline: none;
        }

        .gmaps-layer-card:hover {
            background: #f1f3f4;
        }

        .gmaps-layer-card.active {
            border-color: #1a73e8;
            background: rgba(26, 115, 232, 0.08);
        }

        .layer-thumb {
            width: 44px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            margin-bottom: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .gmaps-layer-card span {
            font-size: 0.72rem;
            font-weight: 600;
            color: #3c4043;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        .gmaps-layer-card.active span {
            color: #1a73e8;
            font-weight: 700;
        }

        /* Thumbnail Backgrounds */
        .thumb-terrain {
            background: linear-gradient(135deg, #8dc26f 0%, #76b852 100%);
            color: #ffffff;
        }
        .thumb-sat {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
        }
        .thumb-road {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            color: #444444;
        }
        .thumb-topo {
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            color: #1e293b;
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
                    <i class="bi bi-map-fill"></i> Fixed Resort Trail Route
                </div>
            </div>
        </div>

        <!-- Guide & Map Grid -->
        <div class="guide-grid">
            
            <!-- Left: Live GPS Route Tracker -->
            <div class="calculator-card">
                <div class="card-heading">
                    <span><i class="bi bi-broadcast me-2 text-accent"></i>Live GPS Route Tracker</span>
                    <span class="badge bg-success" id="liveStatusBadge" style="font-size: 0.72rem;"><i class="bi bi-geo me-1"></i>Ready</span>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Start Point</label>
                    <select id="startPoint" class="form-select-modern" onchange="calculateRoute()">
                        <option value="wp1">Point A — Start Point</option>
                        <option value="wp2">Point B — Destination</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Destination Point</label>
                    <select id="endPoint" class="form-select-modern" onchange="calculateRoute()">
                        <option value="wp2" selected>Point B — Destination</option>
                        <option value="wp1">Point A — Start Point</option>
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

                <button class="btn-calculate mb-3" id="liveTrackBtn" onclick="toggleLiveTracking()">
                    <i class="bi bi-broadcast me-2"></i> Start Live GPS Tracking
                </button>

                <!-- Dynamic Results Panel -->
                <div id="resultsPanel" class="results-panel" style="display:block;">
                    <!-- Live GPS Telemetry Box -->
                    <div id="liveTelemetryBox" class="p-3 mb-3 rounded" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">
                                <i class="bi bi-record-circle-fill me-1"></i> Live GPS Feed
                            </span>
                            <span id="liveAccuracy" class="badge bg-success" style="font-size: 0.68rem;">±--m</span>
                        </div>
                        <div id="liveCoords" style="font-family: 'Poppins', monospace; font-size: 0.95rem; font-weight: 700; color: #f1f5f9;">
                            Waiting for satellite fix...
                        </div>
                    </div>

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
                        <div class="metric-box" style="grid-column: span 2;">
                            <div class="metric-lbl">Trail Route Type</div>
                            <div id="estDiff" class="metric-num"><span class="badge bg-success">Fixed Resort Route</span></div>
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
                        <span class="badge bg-success" style="padding: 6px 12px; font-size: 0.8rem;"><i class="bi bi-geo-alt-fill me-1"></i> Fixed Resort Trail Route</span>
                    </div>
                </div>

                <div class="map-container-wrapper" style="position: relative; flex: 1; display: flex; flex-direction: column; width: 100%;">
                    <div id="atvMap"></div>
                    <!-- Google Maps Style Bottom-Left Layer Selector Bar -->
                    <div class="gmaps-layer-bar">
                        <button class="gmaps-layer-card active" onclick="switchBaseMap('googleTerrain', this)">
                            <div class="layer-thumb thumb-terrain"><i class="bi bi-mountain"></i></div>
                            <span>Terrain</span>
                        </button>
                        <button class="gmaps-layer-card" onclick="switchBaseMap('googleSatellite', this)">
                            <div class="layer-thumb thumb-sat"><i class="bi bi-globe-americas"></i></div>
                            <span>Satellite</span>
                        </button>
                        <button class="gmaps-layer-card" onclick="switchBaseMap('googleRoadmap', this)">
                            <div class="layer-thumb thumb-road"><i class="bi bi-map"></i></div>
                            <span>Roadmap</span>
                        </button>
                        <button class="gmaps-layer-card" onclick="switchBaseMap('esriTopo', this)">
                            <div class="layer-thumb thumb-topo"><i class="bi bi-compass"></i></div>
                            <span>Topo Map</span>
                        </button>
                    </div>
                </div>
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
                        <div class="exp-title">Point A to Point B Express Route</div>
                        <span class="diff-badge diff-green">Resort Trail</span>
                    </div>
                    <p class="exp-desc">The direct adventure trail connecting Point A (Start Point) to Point B destination. Great for scenic off-road riding.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> ~15 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> ~1.2 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> +15m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp1', 'wp2')">
                        <i class="bi bi-compass"></i> Show on Map & Calculate
                    </button>
                </div>

                <div class="expedition-card">
                    <div class="exp-header">
                        <div class="exp-title">Point B Return Trail</div>
                        <span class="diff-badge diff-green">Return Trail</span>
                    </div>
                    <p class="exp-desc">The return ride from destination Point B back to your Point A trailhead starting point.</p>
                    <div class="exp-meta">
                        <div class="meta-item"><i class="bi bi-clock"></i> ~15 mins</div>
                        <div class="meta-item"><i class="bi bi-signpost"></i> ~1.2 km</div>
                        <div class="meta-item"><i class="bi bi-arrow-up-right"></i> -15m Elev</div>
                    </div>
                    <button class="btn-exp-plan" onclick="planExpedition('wp2', 'wp1')">
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
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // --- 1. WAYPOINTS DATABASE ---
        const waypoints = {
            wp1: { name: "Point A — Start Point", lat: 16.167724, lng: 120.002010, elev: 15, diff: "easy", desc: "Main Starting Point A for ATV trail riders." },
            wp2: { name: "Point B — Destination", lat: 16.175398, lng: 120.002073, elev: 35, diff: "inter", desc: "Northern trail destination Point B." }
        };

        // Complete ordered sequence of coordinates following the road from Point A to Point B
        const roadPathSequence = [
            [16.167724, 120.002010], // Point A
            [16.169675, 120.002960], // Location 1
            [16.169416, 120.003861], // Location 2
            [16.171344, 120.004537], // Location 3
            [16.171606, 120.004875], // Location 4
            [16.172495, 120.004919], // Location 5
            [16.172626, 120.005141], // Location 6
            [16.172967, 120.005350], // Location 7
            [16.174356, 120.005211], // Location 8
            [16.175712, 120.004043], // Location 9
            [16.175398, 120.002073]  // Point B
        ];

        let currentPace = "standard";
        let paceSpeed = 16; // km/h
        let map, markers = {}, currentRouteLine = null;
        let currentBaseLayer = null;
        const baseTileLayers = {};

        // --- 2. INITIALIZE LEAFLET MAP ---
        document.addEventListener("DOMContentLoaded", () => {
            // Center map on resort area
            map = L.map('atvMap', {
                center: [16.1716, 120.0019],
                zoom: 15,
                zoomControl: true,
                attributionControl: false
            });

            // Google Maps Terrain (Physical Map with contour lines, elevation hillshading & labels - EXACTLY like Google Maps)
            baseTileLayers['googleTerrain'] = L.tileLayer('https://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: 'Map data &copy; Google'
            });

            // Google Maps Hybrid Satellite (Aerial imagery with roads and labels)
            baseTileLayers['googleSatellite'] = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: 'Imagery &copy; Google'
            });

            // Google Maps Standard Roadmap
            baseTileLayers['googleRoadmap'] = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: 'Map data &copy; Google'
            });

            // Esri World Topo Map
            baseTileLayers['esriTopo'] = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: 'Tiles &copy; Esri'
            });

            // Set Google Maps Terrain as default active basemap
            currentBaseLayer = baseTileLayers['googleTerrain'].addTo(map);

            // Add Markers
            addMarkersToMap('all');

            // Trigger initial calculation
            calculateRoute();
        });

        // Function to switch basemap layers from the Google Maps style UI
        function switchBaseMap(layerKey, btn) {
            if (currentBaseLayer) {
                map.removeLayer(currentBaseLayer);
            }
            if (baseTileLayers[layerKey]) {
                currentBaseLayer = baseTileLayers[layerKey].addTo(map);
            }
            document.querySelectorAll('.gmaps-layer-card').forEach(card => {
                card.classList.remove('active');
            });
            if (btn) {
                btn.classList.add('active');
            }
        }

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

                let color = "#10b981"; // green
                let iconText = "🟢";

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
                    <div class="mb-2"><span class="popup-elev">Elev: ${wp.elev}m</span> <span class="popup-elev" style="background:${color}; color:#000;">FIXED ROUTE</span></div>
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

            // Calculate exact distance along the road path
            let totalDistKm = 0;
            let pathCoords = (startKey === 'wp1' && endKey === 'wp2') || (startKey === 'wp2' && endKey === 'wp1') 
                ? roadPathSequence 
                : [[p1.lat, p1.lng], [p2.lat, p2.lng]];
            
            for (let i = 0; i < pathCoords.length - 1; i++) {
                let dLat = (pathCoords[i+1][0] - pathCoords[i][0]) * 111;
                let dLng = (pathCoords[i+1][1] - pathCoords[i][1]) * 108;
                totalDistKm += Math.sqrt(dLat*dLat + dLng*dLng);
            }
            let trailDist = totalDistKm.toFixed(2); // exact road trail distance in km
            let trailMiles = (trailDist * 0.621371).toFixed(2);

            // Time calculation
            let timeHours = trailDist / paceSpeed;
            let timeMins = Math.round(timeHours * 60);
            if (timeMins < 10) timeMins = 12; // minimum realistic ride time

            // Elevation change
            let elevDiff = p2.elev - p1.elev;
            let elevText = elevDiff >= 0 ? `+${elevDiff}m Incline` : `${elevDiff}m Descent`;

            // Route assessment
            let diffHtml = `<span class="badge bg-success" style="padding: 6px 10px;">Fixed Resort Route</span>`;
            let gearHtml = `
                <span class="gear-tag"><i class="bi bi-person-bounding-box"></i> Helmet & Goggles</span>
                <span class="gear-tag"><i class="bi bi-droplet"></i> Water Bottle</span>
                <span class="gear-tag"><i class="bi bi-gloves"></i> Riding Gloves</span>
            `;

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

            let startKey = getWaypointKey(p1.name);
            let endKey = getWaypointKey(p2.name);

            let latlngs;
            if (startKey === 'wp1' && endKey === 'wp2') {
                latlngs = roadPathSequence;
            } else if (startKey === 'wp2' && endKey === 'wp1') {
                latlngs = [...roadPathSequence].reverse();
            } else {
                latlngs = [
                    [p1.lat, p1.lng],
                    [p2.lat, p2.lng]
                ];
            }

            // High-contrast vibrant blue polyline for terrain visibility
            currentRouteLine = L.polyline(latlngs, {
                color: '#2563eb',
                weight: 6,
                opacity: 0.95,
                smoothFactor: 1,
                dashArray: '12, 8'
            }).addTo(map);

            // Fit map zoom to route bounds with padding
            map.fitBounds(currentRouteLine.getBounds(), { padding: [60, 60], maxZoom: 16 });

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

        // --- 8. LIVE GPS ROUTE TRACKING SYSTEM ---
        let gpsWatchId = null;
        let liveUserMarker = null;
        let liveUserAccuracyCircle = null;
        let isLiveTracking = false;

        const liveUserIcon = L.divIcon({
            className: 'live-user-container',
            html: '<div class="live-user-pulse"></div><div class="live-user-dot"></div>',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        function toggleLiveTracking() {
            const btn = document.getElementById('liveTrackBtn');
            const badge = document.getElementById('liveStatusBadge');
            const telemetryBox = document.getElementById('liveTelemetryBox');

            if (!isLiveTracking) {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser or device.');
                    return;
                }

                btn.classList.add('active-tracking');
                btn.innerHTML = '<i class="bi bi-stop-circle-fill me-2"></i> Stop Live GPS Tracking';
                badge.className = 'badge bg-danger';
                badge.innerHTML = '<i class="bi bi-broadcast me-1"></i> LIVE';
                telemetryBox.style.display = 'block';

                isLiveTracking = true;

                gpsWatchId = navigator.geolocation.watchPosition(
                    onLocationSuccess,
                    onLocationError,
                    { enableHighAccuracy: true, maximumAge: 3000, timeout: 15000 }
                );
            } else {
                stopLiveTracking();
            }
        }

        function stopLiveTracking() {
            if (gpsWatchId !== null) {
                navigator.geolocation.clearWatch(gpsWatchId);
                gpsWatchId = null;
            }
            isLiveTracking = false;

            const btn = document.getElementById('liveTrackBtn');
            const badge = document.getElementById('liveStatusBadge');
            if (btn) {
                btn.classList.remove('active-tracking');
                btn.innerHTML = '<i class="bi bi-broadcast me-2"></i> Start Live GPS Tracking';
            }
            if (badge) {
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="bi bi-geo me-1"></i>Ready';
            }
        }

        function onLocationSuccess(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const acc = Math.round(pos.coords.accuracy || 10);

            // Update telemetry box
            const coordsEl = document.getElementById('liveCoords');
            const accEl = document.getElementById('liveAccuracy');
            if (coordsEl) coordsEl.textContent = `${lat.toFixed(5)}° N, ${lng.toFixed(5)}° E`;
            if (accEl) accEl.textContent = `±${acc}m`;

            // Create or move Live User Marker
            if (!liveUserMarker) {
                liveUserMarker = L.marker([lat, lng], { icon: liveUserIcon, zIndexOffset: 1000 })
                    .addTo(map)
                    .bindPopup('<b>🟢 You Are Here (Live GPS)</b><br>Real-time location active');
            } else {
                liveUserMarker.setLatLng([lat, lng]);
            }

            // Create or move Accuracy Circle
            if (!liveUserAccuracyCircle) {
                liveUserAccuracyCircle = L.circle([lat, lng], {
                    radius: acc,
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.15,
                    weight: 1
                }).addTo(map);
            } else {
                liveUserAccuracyCircle.setLatLng([lat, lng]);
                liveUserAccuracyCircle.setRadius(acc);
            }

            // Pan map smoothly to live location
            map.setView([lat, lng], Math.max(map.getZoom(), 16), { animate: true });

            // Dynamically calculate route distance from user's live position to selected destination
            updateLiveRouteFromUser(lat, lng);
        }

        function onLocationError(err) {
            console.warn('GPS error:', err);
            let msg = 'Could not retrieve your live location.';
            if (err.code === 1) msg = 'Location Access Denied. Please enable GPS.';
            else if (err.code === 2) msg = 'Location unavailable. Please check your GPS signal.';
            else if (err.code === 3) msg = 'Location request timed out.';
            alert(msg);
            stopLiveTracking();
        }

        let isFetchingRoute = false;

        async function updateLiveRouteFromUser(userLat, userLng) {
            let endKey = document.getElementById('endPoint').value;
            // Determine starting point of the trail
            let startWpKey = (endKey === 'wp2') ? 'wp1' : 'wp2';
            let startWp = waypoints[startWpKey];
            let dest = waypoints[endKey];
            
            if (!dest || !startWp) return;
            if (isFetchingRoute) return; // Prevent race conditions
            isFetchingRoute = true;

            try {
                // 1. Fetch route from user to startWp via OSRM
                let routeLatLngs = [];
                let osrmDistKm = 0;
                
                try {
                    const url = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${startWp.lng},${startWp.lat}?overview=full&geometries=geojson`;
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.routes && data.routes.length > 0) {
                        // OSRM coords are [lng, lat]
                        routeLatLngs = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        osrmDistKm = data.routes[0].distance / 1000;
                    } else {
                        throw new Error("No route found");
                    }
                } catch (error) {
                    console.error("OSRM Routing Error:", error);
                    // Fallback to straight line
                    routeLatLngs = [[userLat, userLng], [startWp.lat, startWp.lng]];
                    let dLat = (startWp.lat - userLat) * 111;
                    let dLng = (startWp.lng - userLng) * 108;
                    osrmDistKm = Math.sqrt(dLat*dLat + dLng*dLng);
                }

                // 2. Append the trail from startWp to dest
                let trailDistKm = 0;
                if (endKey === 'wp2') {
                    for (let i = 0; i < roadPathSequence.length; i++) {
                        routeLatLngs.push(roadPathSequence[i]);
                    }
                } else if (endKey === 'wp1') {
                    for (let i = roadPathSequence.length - 1; i >= 0; i--) {
                        routeLatLngs.push(roadPathSequence[i]);
                    }
                }

                // Calculate exact distance along the trail route for accurate metrics
                for (let i = 0; i < roadPathSequence.length - 1; i++) {
                    let dLat = (roadPathSequence[i+1][0] - roadPathSequence[i][0]) * 111;
                    let dLng = (roadPathSequence[i+1][1] - roadPathSequence[i][1]) * 108;
                    trailDistKm += Math.sqrt(dLat*dLat + dLng*dLng);
                }
                
                let totalDistKm = osrmDistKm + trailDistKm;
                let distKm = totalDistKm.toFixed(2);
                let distMi = (distKm * 0.621371).toFixed(2);

                // Assuming user drives a car/motorcycle to the starting point (~40km/h) 
                // and then rides the ATV on the trail at paceSpeed
                let timeHours = (osrmDistKm / 40) + (trailDistKm / paceSpeed);
                let timeMins = Math.max(2, Math.round(timeHours * 60));

                document.getElementById('estTime').innerText = `${timeMins} mins`;
                document.getElementById('estDist').innerText = `${distKm} km (${distMi} mi) from You`;
                document.getElementById('estSub').innerText = `Live route to ${dest.name}`;

                if (currentRouteLine) {
                    map.removeLayer(currentRouteLine);
                }

                currentRouteLine = L.polyline(routeLatLngs, {
                    color: '#10b981',
                    weight: 5,
                    opacity: 0.95,
                    dashArray: '10, 10'
                }).addTo(map);

            } finally {
                isFetchingRoute = false;
            }
        }
    </script>
</body>
</html>
