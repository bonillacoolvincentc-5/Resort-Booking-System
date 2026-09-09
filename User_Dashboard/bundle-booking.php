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
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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

        .page-wrap {
            padding: 1rem 0 0;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 62px);
            overflow: hidden;
        }

        /* TWO COLUMN LAYOUT */
        .bundle-layout {
            display: flex;
            gap: 2rem;
            align-items: stretch;
            flex: 1;
            overflow: hidden;
            min-height: 0;
            padding-bottom: 1.5rem;
        }
        .left-col { width: 42%; flex-shrink: 0; display: flex; flex-direction: column; height: 100%; min-height: 0; }
        .right-col { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100%; min-height: 0; overflow-y: auto; }
        .package-filters {
            flex-shrink: 0;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
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
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border-radius: var(--radius-btn);
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.9rem;
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
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.85rem;
        }
        .filter-pill {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.45rem 1rem;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.25s ease;
            letter-spacing: 0.2px;
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
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.4);
        }
        .bundles-scroll-list {
            flex: 1;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            align-content: start;
            padding-right: 4px;
            padding-bottom: 1rem;
        }
        /* SCROLLBAR */
        .bundles-scroll-list::-webkit-scrollbar { width: 6px; }
        .bundles-scroll-list::-webkit-scrollbar-track { background: var(--bg-tertiary); border-radius: 4px; }
        .bundles-scroll-list::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
        .bundles-scroll-list::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* BUNDLE CARDS */
        .bundle-card {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(51, 65, 85, 0.8);
            border-radius: var(--radius-card);
            overflow: hidden;
            transition: all .35s cubic-bezier(0.4,0,0.2,1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            min-height: 260px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .bundle-card:hover { transform: translateY(-4px); box-shadow: 0 14px 36px rgba(0,0,0,0.4); border-color: rgba(59,130,246, 0.6); }
        .bundle-card.active { border-color: var(--accent-primary); border-width: 2px; box-shadow: 0 0 0 3px rgba(59,130,246, 0.25), 0 8px 24px rgba(59,130,246,0.2); }
        
        .card-img-wrap { position: relative; height: 120px; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 100%); }
        .card-img-wrap img { width:100%; height:100%; object-fit:cover; transition: transform .5s ease; }
        .bundle-card:hover .card-img-wrap img { transform: scale(1.06); }
        .status-badge { position: absolute; top:.5rem; right:.5rem; padding:.15rem .5rem; border-radius:2rem; font-size:.6rem; font-weight:700; text-transform:uppercase; backdrop-filter:blur(8px); z-index: 2; }
        .status-available { background:rgba(16,185,129,.9); color:#fff; }
        .status-fully-booked { background:rgba(239,68,68,.9); color:#fff; }
        .status-inactive { background:rgba(100,116,139,.9); color:#fff; }

        .type-badge {
            position: absolute;
            top: .5rem;
            left: .5rem;
            padding: .15rem .5rem;
            border-radius: 2rem;
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
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
        .placeholder-banner i { font-size: 2rem; color: var(--accent-primary); opacity: 0.8; }
        .placeholder-banner span { font-size: 0.65rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1.5px; margin-top: 0.15rem; }

        .card-content { padding: 0.85rem; display: flex; flex-direction: column; gap: 0.4rem; flex: 1; }
        .card-name-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.3rem; }
        .bundle-name { font-size:0.82rem; font-weight:700; color:var(--text-primary); line-height: 1.3; }
        .bundle-price { font-size:0.82rem; font-weight:800; color:var(--accent-success); letter-spacing: -0.3px; white-space: nowrap; flex-shrink: 0; }
        .bundle-desc { display: none; } /* hidden in grid view to save space */
        
        .bundle-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .meta-pill { font-size: 0.68rem; background: rgba(51,65,85,0.5); border: 1px solid var(--border-color); padding: 0.2rem 0.55rem; border-radius: 1rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.25rem; }
        .meta-pill i { color: var(--accent-primary); font-size: 0.7rem; }
        /* hide the long inclusions pill, only show capacity */
        .meta-pill.meta-inclusions { display: none; }

        .select-btn {
            width: 100%;
            padding: 0.6rem;
            border-radius: 0 0 calc(var(--radius-card) - 2px) calc(var(--radius-card) - 2px);
            border: none;
            border-top: 1px solid rgba(51, 65, 85, 0.8);
            background: rgba(51, 65, 85, 0.7);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: auto;
        }
        .bundle-card:hover .select-btn { background: rgba(59,130,246,0.12); color: var(--accent-primary); border-color: rgba(59,130,246,0.3); }
        .bundle-card.active .select-btn { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); box-shadow: 0 4px 15px rgba(59,130,246,0.35); }

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
        .cal-cell { aspect-ratio: 1; min-height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; background: var(--bg-tertiary); color: var(--text-primary); }
        
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
            .page-wrap { height: auto; overflow: visible; }
            .bundle-layout { flex-direction: column; flex: none; overflow: visible; }
            .left-col { width: 100%; height: auto; min-height: 0; }
            .right-col { width: 100%; height: auto; overflow-y: visible; }
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

        /* ═══════════════════════════════════════
           GCASH PAYMENT MODAL
        ═══════════════════════════════════════ */
        .pay-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(5, 10, 25, 0.85);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.35s ease;
        }
        .pay-modal-backdrop.open {
            opacity: 1; pointer-events: all;
        }
        .pay-modal {
            background: linear-gradient(160deg, #1a2744 0%, #0f172a 100%);
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 1.25rem;
            padding: 2rem;
            width: min(520px, 94vw);
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(59,130,246,0.15);
            transform: translateY(24px) scale(0.97);
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
        }
        .pay-modal-backdrop.open .pay-modal {
            transform: translateY(0) scale(1);
        }
        .pay-modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem; padding-bottom: 1rem;
            border-bottom: 1px solid rgba(51,65,85,0.6);
        }
        .pay-modal-title {
            font-size: 1.2rem; font-weight: 700;
            background: linear-gradient(135deg, #f1f5f9, #3b82f6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .pay-modal-close {
            background: rgba(51,65,85,0.5); border: none; color: #cbd5e1;
            border-radius: 50%; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem; transition: all 0.2s;
        }
        .pay-modal-close:hover { background: rgba(239,68,68,0.3); color: #ef4444; }

        /* QR Code area */
        .qr-section {
            text-align: center; margin-bottom: 1.5rem;
        }
        .qr-label {
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; color: #10b981; margin-bottom: 0.75rem;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .qr-frame {
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff;
            border-radius: 1rem;
            padding: 12px;
            box-shadow: 0 8px 32px rgba(16,185,129,0.2), 0 0 0 3px rgba(16,185,129,0.15);
        }
        .qr-frame img {
            width: 180px; height: 180px;
            object-fit: contain; display: block; border-radius: 0.5rem;
        }
        .qr-missing {
            width: 180px; height: 180px;
            background: linear-gradient(135deg, #1e3a5f, #0f2744);
            border-radius: 0.5rem;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #94a3b8; gap: 0.5rem;
        }
        .qr-missing i { font-size: 2.5rem; color: #3b82f6; }
        .qr-missing span { font-size: 0.7rem; text-align: center; max-width: 120px; line-height: 1.4; }

        /* Fee section */
        .fee-section {
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.25);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .fee-label { font-size: 0.85rem; color: #cbd5e1; }
        .fee-label strong { display: block; font-size: 0.7rem; color: #94a3b8; margin-bottom: 0.15rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .fee-amount { font-size: 1.6rem; font-weight: 800; color: #10b981; letter-spacing: -0.5px; }

        /* Upload section */
        .upload-section { margin-bottom: 1.5rem; }
        .upload-label {
            font-size: 0.8rem; font-weight: 600; color: #cbd5e1;
            margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem;
        }
        .upload-label i { color: #3b82f6; }
        .upload-row {
            display: flex; gap: 0.75rem; align-items: flex-start;
        }
        .upload-drop-area {
            flex: 1;
            border: 2px dashed rgba(59,130,246,0.4);
            border-radius: 0.75rem;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            background: rgba(15,23,42,0.6);
            position: relative;
        }
        .upload-drop-area:hover, .upload-drop-area.dragover {
            border-color: rgba(59,130,246,0.8);
            background: rgba(59,130,246,0.05);
        }
        .upload-drop-area i { font-size: 1.8rem; color: #3b82f6; margin-bottom: 0.4rem; display: block; }
        .upload-drop-area p { font-size: 0.78rem; color: #94a3b8; margin: 0; line-height: 1.4; }
        .upload-drop-area p strong { color: #cbd5e1; }
        #proofFileInput { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

        /* Thumbnail preview */
        .upload-thumb {
            width: 80px; height: 80px; flex-shrink: 0;
            border-radius: 0.6rem;
            border: 2px solid rgba(51,65,85,0.6);
            overflow: hidden;
            background: rgba(15,23,42,0.8);
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .upload-thumb img {
            width: 100%; height: 100%; object-fit: cover; display: none;
        }
        .upload-thumb .thumb-placeholder {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 0.2rem; color: #475569; text-align: center; padding: 0.5rem;
        }
        .upload-thumb .thumb-placeholder i { font-size: 1.4rem; }
        .upload-thumb .thumb-placeholder span { font-size: 0.55rem; line-height: 1.3; }
        .upload-thumb.has-image { border-color: #10b981; }
        .thumb-check {
            position: absolute; top: 2px; right: 2px;
            background: #10b981; color: white;
            border-radius: 50%; width: 16px; height: 16px;
            font-size: 0.55rem; display: none;
            align-items: center; justify-content: center;
        }
        .upload-thumb.has-image .thumb-check { display: flex; }
        .upload-filename {
            font-size: 0.7rem; color: #64748b; margin-top: 0.4rem; text-align: center;
            word-break: break-all;
        }

        /* Submit button */
        .pay-submit-btn {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            box-shadow: 0 4px 20px rgba(16,185,129,0.3);
        }
        .pay-submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(16,185,129,0.45);
        }
        .pay-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; filter: grayscale(30%); }

        /* ═══════════════════════════════════════
           SUCCESS OVERLAY
        ═══════════════════════════════════════ */
        .success-overlay {
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at center, rgba(10,25,50,0.97) 0%, rgba(5,10,20,0.99) 100%);
            z-index: 3000;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
        }
        .success-overlay.show { opacity: 1; pointer-events: all; }
        .success-icon-wrap {
            width: 110px; height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.05));
            border: 3px solid rgba(16,185,129,0.4);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            animation: successPulse 2s ease infinite;
        }
        .success-icon-wrap i { font-size: 3.5rem; color: #10b981; }
        @keyframes successPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            50% { box-shadow: 0 0 0 20px rgba(16,185,129,0); }
        }
        .success-title {
            font-size: 2rem; font-weight: 800; color: #f1f5f9;
            margin-bottom: 0.5rem; text-align: center;
        }
        .success-ref {
            font-size: 0.9rem; color: #10b981;
            font-weight: 600; margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .success-sub {
            font-size: 0.9rem; color: #94a3b8;
            text-align: center; max-width: 360px; line-height: 1.6;
            margin-bottom: 2rem;
        }
        .success-btn {
            padding: 0.8rem 2.5rem;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff; border: none; border-radius: 0.75rem;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 24px rgba(59,130,246,0.35);
        }
        .success-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(59,130,246,0.5); }

        /* Confetti particles */
        .confetti-container {
            position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 2999;
        }
        .confetti-piece {
            position: absolute;
            width: 10px; height: 10px;
            opacity: 0;
            animation: confettiFall 3s ease forwards;
        }
        @keyframes confettiFall {
            0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }

        /* ═══════════════════════════════════════
           SUBMISSION LOADER OVERLAY
        ═══════════════════════════════════════ */
        .submit-loader {
            position: fixed; inset: 0;
            background: rgba(5, 8, 20, 0.92);
            backdrop-filter: blur(12px);
            z-index: 4000;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 1.5rem;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .submit-loader.show {
            opacity: 1; pointer-events: all;
        }
        .loader-ring-wrap {
            position: relative;
            width: 90px; height: 90px;
        }
        .loader-ring {
            width: 90px; height: 90px;
            border-radius: 50%;
            border: 4px solid rgba(59,130,246,0.15);
            border-top-color: #3b82f6;
            border-right-color: #8b5cf6;
            animation: loaderSpin 0.9s linear infinite;
        }
        .loader-ring-inner {
            position: absolute; inset: 10px;
            border-radius: 50%;
            border: 3px solid rgba(16,185,129,0.15);
            border-bottom-color: #10b981;
            animation: loaderSpin 1.4s linear infinite reverse;
        }
        @keyframes loaderSpin { to { transform: rotate(360deg); } }
        .loader-icon {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: #3b82f6;
        }
        .loader-text {
            font-size: 1.05rem; font-weight: 600;
            color: #e2e8f0; text-align: center;
            letter-spacing: 0.3px;
        }
        .loader-dots span {
            display: inline-block;
            animation: dotBounce 1.2s ease infinite;
            opacity: 0;
        }
        .loader-dots span:nth-child(1) { animation-delay: 0s; }
        .loader-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loader-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotBounce {
            0%, 80%, 100% { opacity: 0; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-4px); }
        }
        .loader-sub {
            font-size: 0.8rem; color: #64748b;
            text-align: center; max-width: 260px; line-height: 1.5;
        }

        /* ═══════════════════════════════════════
           RESPONSIVE / PWA MOBILE VIEW
        ═══════════════════════════════════════ */
        @media (max-width: 991.98px) {
            .page-wrap { height: auto; overflow: visible; }
            .bundle-layout {
                flex-direction: column;
                gap: 1.75rem;
                flex: none;
                overflow: visible;
            }
            .left-col {
                width: 100%;
                height: auto;
                min-height: 0;
            }
            .bundles-scroll-list {
                max-height: 540px;
                overflow-y: auto;
            }
            .right-col {
                width: 100%;
                height: auto;
                overflow-y: visible;
            }
        }

        @media (max-width: 767.98px) {
            .page-wrap {
                padding: 1rem 0;
            }
            .page-header {
                padding: 1.35rem;
                margin-bottom: 1.25rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .filter-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.35rem;
            }
            .filter-pill {
                white-space: nowrap;
                flex-shrink: 0;
            }
        }

        @media (max-width: 575.98px) {
            .bundles-scroll-list {
                grid-template-columns: 1fr;
                max-height: 480px;
            }
            .summary-panel {
                flex-direction: column;
                padding: 1.25rem;
                gap: 1.25rem;
            }
            .sum-img {
                width: 100%;
                height: 170px;
            }
            .sum-grid {
                grid-template-columns: 1fr;
                gap: 0.6rem;
            }
            .sum-footer {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            .sum-footer .btn-continue {
                width: 100%;
                justify-content: center;
            }
            .calendar-container {
                padding: 1.15rem;
            }
            .cal-cell {
                min-height: 44px;
                font-size: 0.85rem;
            }
            .cal-legend {
                flex-wrap: wrap;
                gap: 0.75rem;
                font-size: 0.75rem;
            }
            .pay-modal {
                width: 95vw;
                padding: 1.25rem;
                border-radius: 1rem;
            }
            .fee-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .upload-row {
                flex-direction: column;
                align-items: stretch;
            }
            .upload-thumb {
                width: 100%;
                height: 130px;
            }
            .qr-frame img, .qr-missing {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/navigation.php'; ?>

    <div class="container-fluid px-4 page-wrap">
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



<!-- ═══════════════════════════════════════════════════
         GCASH PAYMENT MODAL
    ═══════════════════════════════════════════════════ -->
    <div class="pay-modal-backdrop" id="payModalBackdrop">
        <div class="pay-modal">
            <div class="pay-modal-header">
                <div class="pay-modal-title"><i class="bi bi-phone me-2"></i>GCash Payment</div>
                <button class="pay-modal-close" id="payModalClose"><i class="bi bi-x-lg"></i></button>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <div class="qr-label"><i class="bi bi-qr-code"></i> Scan to Pay via GCash</div>
                <div class="qr-frame">
                    <img src="../assets/gcash/gcash_qr.png" alt="GCash QR" id="gcashQrImg"
                         onerror="this.style.display='none';document.getElementById('gcashQrMissing').style.display='flex';">
                    <div class="qr-missing" id="gcashQrMissing" style="display:none;">
                        <i class="bi bi-qr-code-scan"></i>
                        <span>QR code will be placed here by the admin</span>
                    </div>
                </div>
            </div>

            <!-- Reservation Fee -->
            <div class="fee-section">
                <div class="fee-label">
                    <strong>Reservation Fee</strong>
                    Send exactly this amount via GCash
                </div>
                <div class="fee-amount" id="modalFeeAmount">₱0</div>
            </div>

            <!-- Upload Proof -->
            <div class="upload-section">
                <div class="upload-label">
                    <i class="bi bi-image-fill"></i>
                    Attach GCash Payment Screenshot
                    <span style="color:#ef4444;margin-left:2px;">*</span>
                </div>
                <div class="upload-row">
                    <div class="upload-drop-area" id="uploadDropArea">
                        <input type="file" id="proofFileInput" accept="image/*">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p><strong>Click or drag & drop</strong><br>PNG, JPG, GIF up to 5MB</p>
                    </div>
                    <div>
                        <div class="upload-thumb" id="uploadThumb">
                            <img id="thumbImg" src="" alt="Preview">
                            <div class="thumb-placeholder" id="thumbPlaceholder">
                                <i class="bi bi-image"></i>
                                <span>Preview here</span>
                            </div>
                            <div class="thumb-check"><i class="bi bi-check"></i></div>
                        </div>
                        <div class="upload-filename" id="uploadFilename">No file</div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button class="pay-submit-btn" id="paySubmitBtn" disabled>
                <i class="bi bi-send-fill"></i> Submit Reservation
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         SUCCESS OVERLAY
    ═══════════════════════════════════════════════════ -->
    <!-- SUBMISSION LOADER -->
    <div class="submit-loader" id="submitLoader">
        <div class="loader-ring-wrap">
            <div class="loader-ring"></div>
            <div class="loader-ring-inner"></div>
            <div class="loader-icon"><i class="bi bi-send"></i></div>
        </div>
        <div>
            <div class="loader-text">
                Processing your reservation<span class="loader-dots"><span>.</span><span>.</span><span>.</span></span>
            </div>
            <div class="loader-sub">Uploading payment proof &amp; securing your booking. Please don't close this page.</div>
        </div>
    </div>

    <div class="confetti-container" id="confettiContainer"></div>
    <div class="success-overlay" id="successOverlay">
        <div class="success-icon-wrap">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="success-title">Reservation Submitted! 🎉</div>
        <div class="success-ref" id="successRef"></div>
        <p class="success-sub">
            Your booking is now <strong style="color:#10b981;">pending admin review</strong>.
            You'll be notified once it's confirmed. Thank you!
        </p>
        <button class="success-btn" id="successGoBtn">
            <i class="bi bi-journal-check me-2"></i>View My Bookings
        </button>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const DOM = {
            bundlesList:      document.getElementById('bundlesList'),
            calOverlay:       document.getElementById('calOverlay'),
            calTitle:         document.getElementById('calTitle'),
            calGrid:          document.getElementById('calGrid'),
            prevMonth:        document.getElementById('prevMonth'),
            nextMonth:        document.getElementById('nextMonth'),
            summaryPanel:     document.getElementById('summaryPanel'),
            sumImg:           document.getElementById('sumImg'),
            sumImgPlaceholder:document.getElementById('sumImgPlaceholder'),
            sumTitle:         document.getElementById('sumTitle'),
            sumPrice:         document.getElementById('sumPrice'),
            sumDate:          document.getElementById('sumDate'),
            sumInclusions:    document.getElementById('sumInclusions'),
            guestControl:     document.getElementById('guestControl'),
            guestCount:       document.getElementById('guestCount'),
            btnMinusGuest:    document.getElementById('btnMinusGuest'),
            btnPlusGuest:     document.getElementById('btnPlusGuest'),
            maxCapText:       document.getElementById('maxCapText'),
            sumTotal:         document.getElementById('sumTotal'),
            btnContinue:      document.getElementById('btnContinue'),
            alertContainer:   document.getElementById('alertContainer'),
            // Modal
            payModalBackdrop: document.getElementById('payModalBackdrop'),
            payModalClose:    document.getElementById('payModalClose'),
            modalFeeAmount:   document.getElementById('modalFeeAmount'),
            proofFileInput:   document.getElementById('proofFileInput'),
            uploadDropArea:   document.getElementById('uploadDropArea'),
            uploadThumb:      document.getElementById('uploadThumb'),
            thumbImg:         document.getElementById('thumbImg'),
            thumbPlaceholder: document.getElementById('thumbPlaceholder'),
            uploadFilename:   document.getElementById('uploadFilename'),
            paySubmitBtn:     document.getElementById('paySubmitBtn'),
            submitLoader:     document.getElementById('submitLoader'),
            // Success
            confettiContainer:document.getElementById('confettiContainer'),
            successOverlay:   document.getElementById('successOverlay'),
            successRef:       document.getElementById('successRef'),
            successGoBtn:     document.getElementById('successGoBtn'),
        };

        let bundles = [];
        let currentFilter = 'all';
        let searchQuery = '';
        let selectedBundle = null;
        let selectedDate = null;
        let currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth() + 1;
        let availData = {};
        let proofFile = null;

        // ──────────────────────────────────────────────────
        // INIT
        // ──────────────────────────────────────────────────
        async function init() {
            setupFilterControls();
            await fetchBundles();
            renderCalendarHeader();
            renderCalendarGrid();
            DOM.prevMonth.addEventListener('click', () => changeMonth(-1));
            DOM.nextMonth.addEventListener('click', () => changeMonth(1));
            DOM.btnMinusGuest.addEventListener('click', () => updateGuests(-1));
            DOM.btnPlusGuest.addEventListener('click', () => updateGuests(1));
            DOM.btnContinue.addEventListener('click', openPayModal);
            setupModal();
        }

        // ──────────────────────────────────────────────────
        // FILTER CONTROLS
        // ──────────────────────────────────────────────────
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
                <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="border-radius:var(--radius-btn);background:var(--bg-card);border:1px solid var(--border-color);color:var(--text-primary);margin-bottom:1.5rem;">
                    ${msg}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            `;
            setTimeout(() => DOM.alertContainer.innerHTML = '', 6000);
        }

        // ──────────────────────────────────────────────────
        // FETCH BUNDLES
        // ──────────────────────────────────────────────────
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
            } catch(err) {
                DOM.bundlesList.innerHTML = `<p class="text-danger text-center mt-5">Connection error.</p>`;
            }
        }

        function updateFilterCounts() {
            document.getElementById('countAll').textContent    = bundles.length;
            document.getElementById('countBundle').textContent = bundles.filter(b => b.package_type === 'bundle').length;
            document.getElementById('countAcc').textContent    = bundles.filter(b => b.package_type === 'accommodation').length;
            document.getElementById('countAct').textContent    = bundles.filter(b => b.package_type === 'activity').length;
        }

        function renderBundles() {
            DOM.bundlesList.innerHTML = '';
            const filtered = bundles.filter(b => {
                const matchType  = (currentFilter === 'all') || (b.package_type === currentFilter);
                const matchQuery = !searchQuery ||
                    (b.name && b.name.toLowerCase().includes(searchQuery)) ||
                    (b.description && b.description.toLowerCase().includes(searchQuery));
                return matchType && matchQuery;
            });

            if (filtered.length === 0) {
                DOM.bundlesList.innerHTML = `<div class="text-center mt-5 py-4" style="grid-column:span 2"><i class="bi bi-box-seam d-block mb-2" style="font-size:2rem;color:var(--text-muted)"></i><p class="text-muted">No resort packages found.</p></div>`;
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
                        <div class="status-badge status-available">✓</div>
                    </div>
                    <div class="card-content">
                        <div class="card-name-row">
                            <div class="bundle-name">${b.name}</div>
                            <div class="bundle-price">₱${parseFloat(b.price).toLocaleString()}</div>
                        </div>
                        <div class="bundle-desc">${b.short_description || 'A premium resort package.'}</div>
                        <div class="bundle-meta">
                            <div class="meta-pill"><i class="bi bi-people"></i> ${b.capacity}</div>
                            ${inclusionsPreview ? `<div class="meta-pill meta-inclusions"><i class="bi bi-star"></i> ${inclusionsPreview}</div>` : ''}
                        </div>
                    </div>
                    <button class="select-btn"><i class="bi bi-check-circle me-1"></i>Select</button>
                `;
                DOM.bundlesList.appendChild(card);
            });
        }

        // ──────────────────────────────────────────────────
        // SELECT BUNDLE
        // ──────────────────────────────────────────────────
        async function selectBundle(b, cardElement) {
            document.querySelectorAll('.bundle-card').forEach(c => c.classList.remove('active'));
            cardElement.classList.add('active');
            selectedBundle = b;
            selectedDate = null;
            availData = {};

            let combinedInclusions = [...(b.amenities || []), ...(b.inclusions || [])].filter(i => !i.toLowerCase().includes('capacity: for'));

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

            let imgUrl = b.image_url;
            if (imgUrl) {
                if (!imgUrl.startsWith('http') && !imgUrl.startsWith('../')) imgUrl = '../' + imgUrl;
                DOM.sumImg.src = imgUrl;
                DOM.sumImg.classList.remove('d-none');
                DOM.sumImgPlaceholder.classList.add('d-none');
            } else {
                DOM.sumImg.classList.add('d-none');
                DOM.sumImgPlaceholder.classList.remove('d-none');
            }

            DOM.calOverlay.classList.add('hidden');
            checkButtonState();
            await fetchAvailability();
        }

        // ──────────────────────────────────────────────────
        // CALENDAR
        // ──────────────────────────────────────────────────
        async function fetchAvailability() {
            if (!selectedBundle) return;
            DOM.calGrid.style.opacity = '0.5';
            try {
                const res = await fetch(`../php/get_bundle_availability.php?bundle_id=${selectedBundle.id}&year=${currentYear}&month=${currentMonth}`);
                const data = await res.json();
                availData = data.success ? data.days : {};
            } catch(e) { availData = {}; }
            DOM.calGrid.style.opacity = '1';
            renderCalendarGrid();
        }

        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 12) { currentMonth = 1; currentYear++; }
            else if (currentMonth < 1) { currentMonth = 12; currentYear--; }
            renderCalendarHeader();
            selectedBundle ? fetchAvailability() : renderCalendarGrid();
        }

        function renderCalendarHeader() {
            const d = new Date(currentYear, currentMonth - 1, 1);
            DOM.calTitle.textContent = d.toLocaleString('default', { month: 'long', year: 'numeric' });
        }

        function renderCalendarGrid() {
            const headers = `<div class="cal-day-header">Sun</div><div class="cal-day-header">Mon</div>
                <div class="cal-day-header">Tue</div><div class="cal-day-header">Wed</div>
                <div class="cal-day-header">Thu</div><div class="cal-day-header">Fri</div>
                <div class="cal-day-header">Sat</div>`;
            let html = headers;
            const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;

            for (let i = 0; i < firstDay; i++) html += `<div class="cal-cell empty"></div>`;

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${currentYear}-${String(currentMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                let statusClass = '', isSelectable = false;
                if (selectedBundle && availData[dateStr]) {
                    const status = availData[dateStr].status;
                    statusClass = `status-${status}`;
                    if (dateStr >= todayStr && (status === 'available' || status === 'limited')) isSelectable = true;
                    else if (dateStr < todayStr) statusClass = 'status-blocked';
                } else if (dateStr < todayStr) statusClass = 'status-blocked';

                let classes = `cal-cell ${statusClass}`;
                if (selectedDate === dateStr) classes += ' selected';
                const onClick = isSelectable ? `onclick="selectDate('${dateStr}')"` : '';
                html += `<div class="${classes}" ${onClick}>${d}</div>`;
            }
            DOM.calGrid.innerHTML = html;
        }

        function selectDate(dateStr) {
            selectedDate = dateStr;
            renderCalendarGrid();
            const d = new Date(dateStr + 'T00:00:00');
            DOM.sumDate.textContent = d.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric', year:'numeric' });
            checkButtonState();
        }

        function updateGuests(delta) {
            if (!selectedBundle) return;
            let v = parseInt(DOM.guestCount.value) + delta;
            if (v < 1) v = 1;
            if (v > selectedBundle.capacity) v = selectedBundle.capacity;
            DOM.guestCount.value = v;
        }

        function updateTotal() {
            if (selectedBundle) DOM.sumTotal.textContent = `₱${parseFloat(selectedBundle.price).toLocaleString()}`;
        }

        function checkButtonState() {
            DOM.btnContinue.disabled = !(selectedBundle && selectedDate);
        }

        // ──────────────────────────────────────────────────
        // GCASH PAYMENT MODAL
        // ──────────────────────────────────────────────────
        function setupModal() {
            // Close
            DOM.payModalClose.addEventListener('click', closePayModal);
            DOM.payModalBackdrop.addEventListener('click', (e) => {
                if (e.target === DOM.payModalBackdrop) closePayModal();
            });

            // File input change
            DOM.proofFileInput.addEventListener('change', handleFileSelect);

            // Drag & drop
            DOM.uploadDropArea.addEventListener('dragover', (e) => { e.preventDefault(); DOM.uploadDropArea.classList.add('dragover'); });
            DOM.uploadDropArea.addEventListener('dragleave', () => DOM.uploadDropArea.classList.remove('dragover'));
            DOM.uploadDropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                DOM.uploadDropArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file) processFile(file);
            });

            // Submit
            DOM.paySubmitBtn.addEventListener('click', submitWithProof);

            // Success go button
            DOM.successGoBtn.addEventListener('click', () => { window.location.href = 'my-bookings.php'; });
        }

        function openPayModal() {
            if (!selectedBundle || !selectedDate) return;
            // Set fee
            DOM.modalFeeAmount.textContent = `₱${parseFloat(selectedBundle.price).toLocaleString()}`;
            // Reset file state
            proofFile = null;
            DOM.proofFileInput.value = '';
            DOM.thumbImg.style.display = 'none';
            DOM.thumbPlaceholder.style.display = 'flex';
            DOM.uploadThumb.classList.remove('has-image');
            DOM.uploadFilename.textContent = 'No file';
            DOM.paySubmitBtn.disabled = true;
            // Open
            DOM.payModalBackdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closePayModal() {
            DOM.payModalBackdrop.classList.remove('open');
            document.body.style.overflow = '';
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) processFile(file);
        }

        function processFile(file) {
            if (!file.type.startsWith('image/')) {
                showAlert('Please select a valid image file (JPG, PNG, GIF, WEBP).');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showAlert('File too large. Max 5MB.');
                return;
            }
            proofFile = file;
            // Show thumbnail preview
            const reader = new FileReader();
            reader.onload = (e) => {
                DOM.thumbImg.src = e.target.result;
                DOM.thumbImg.style.display = 'block';
                DOM.thumbPlaceholder.style.display = 'none';
                DOM.uploadThumb.classList.add('has-image');
            };
            reader.readAsDataURL(file);
            // Show filename (truncated)
            const name = file.name.length > 18 ? file.name.substring(0,15) + '...' : file.name;
            DOM.uploadFilename.textContent = name;
            DOM.paySubmitBtn.disabled = false;
        }

        function showLoader() {
            DOM.submitLoader.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function hideLoader() {
            DOM.submitLoader.classList.remove('show');
            document.body.style.overflow = '';
        }

        async function submitWithProof() {
            if (!proofFile || !selectedBundle || !selectedDate) {
                console.warn("Submit aborted: missing fields", { proofFile, selectedBundle, selectedDate });
                return;
            }

            console.log("Starting submitWithProof...", {
                bundle_id: selectedBundle.id,
                selectedDate: selectedDate,
                file: proofFile.name,
                fileSize: proofFile.size
            });

            // Disable button + show full-screen loader
            DOM.paySubmitBtn.disabled = true;
            DOM.paySubmitBtn.innerHTML = `<i class="bi bi-send-fill"></i> Submitting...`;
            closePayModal();
            showLoader();

            const checkIn  = new Date(selectedDate + 'T00:00:00');
            const checkOut = new Date(checkIn);
            checkOut.setDate(checkOut.getDate() + 1);
            const checkOutStr = `${checkOut.getFullYear()}-${String(checkOut.getMonth()+1).padStart(2,'0')}-${String(checkOut.getDate()).padStart(2,'0')}`;

            const fd = new FormData();
            fd.append('booking_type',    'bundle');
            fd.append('bundle_id',       selectedBundle.id);
            fd.append('full_name',       '<?= htmlspecialchars(addslashes($userName), ENT_QUOTES, "UTF-8") ?>');
            fd.append('email',           '<?= htmlspecialchars(addslashes($userEmail), ENT_QUOTES, "UTF-8") ?>');
            fd.append('phone_number',    '<?= htmlspecialchars(addslashes($userPhone), ENT_QUOTES, "UTF-8") ?>' || 'N/A');
            fd.append('check_in_date',   selectedDate);
            fd.append('check_out_date',  checkOutStr);
            fd.append('number_of_guests',DOM.guestCount.value);
            fd.append('special_requests','');
            fd.append('payment_proof',   proofFile, proofFile.name);

            try {
                console.log("Sending fetch request to ../php/upload_payment_proof.php...");
                const res = await fetch('../php/upload_payment_proof.php', { method: 'POST', body: fd });
                console.log("Server HTTP Status:", res.status);
                
                const rawText = await res.text();
                console.log("Server Raw Response:", rawText);

                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    console.error("JSON Parse Error! Server returned non-JSON:", rawText, parseErr);
                    throw new Error("Server response was not JSON: " + rawText.substring(0, 150));
                }

                if (data.success) {
                    console.log("Booking SUCCESS:", data);
                    hideLoader();
                    showSuccessOverlay(data.booking_reference);
                } else {
                    console.error("Server reported failure:", data.message);
                    hideLoader();
                    DOM.payModalBackdrop.classList.add('open');
                    document.body.style.overflow = 'hidden';
                    showAlert("Server Error: " + (data.message || 'Failed to submit booking.'));
                    DOM.paySubmitBtn.disabled = false;
                    DOM.paySubmitBtn.innerHTML = `<i class="bi bi-send-fill"></i> Submit Reservation`;
                }
            } catch(e) {
                console.error("Submit Exception/Network Error:", e);
                hideLoader();
                DOM.payModalBackdrop.classList.add('open');
                document.body.style.overflow = 'hidden';
                showAlert('Error details: ' + (e.message || e));
                DOM.paySubmitBtn.disabled = false;
                DOM.paySubmitBtn.innerHTML = `<i class="bi bi-send-fill"></i> Submit Reservation`;
            }
        }

        // ──────────────────────────────────────────────────
        // SUCCESS ANIMATION
        // ──────────────────────────────────────────────────
        function showSuccessOverlay(ref) {
            DOM.successRef.textContent = `Booking Reference: ${ref || ''}`.trim();
            DOM.successOverlay.classList.add('show');
            launchConfetti();
        }

        function launchConfetti() {
            const colors = ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#f1f5f9'];
            DOM.confettiContainer.innerHTML = '';
            for (let i = 0; i < 80; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                const color = colors[Math.floor(Math.random() * colors.length)];
                const left  = Math.random() * 100;
                const delay = Math.random() * 1.5;
                const size  = 6 + Math.random() * 10;
                const shape = Math.random() > 0.5 ? '50%' : '0%';
                piece.style.cssText = `
                    left: ${left}vw;
                    top: -20px;
                    width: ${size}px;
                    height: ${size}px;
                    background: ${color};
                    border-radius: ${shape};
                    animation-delay: ${delay}s;
                    animation-duration: ${2 + Math.random() * 2}s;
                `;
                DOM.confettiContainer.appendChild(piece);
            }
        }

        // Run
        init();
    </script>
</body>
</html>
