<?php
session_start();

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

require_once '../phpAuthentication/db_config.php';

/** @var mysqli $conn */
global $conn;

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$userId    = $_SESSION['user_id'];
$userPhone = $_SESSION['user_phone'] ?? '';

if (empty($userPhone) && $userId) {
    $stmtP = $conn->prepare("SELECT phone_number FROM users WHERE id = ?");
    $stmtP->bind_param("i", $userId);
    $stmtP->execute();
    $rowP = $stmtP->get_result()->fetch_assoc();
    $userPhone = $rowP['phone_number'] ?? '';
}

// ── Sidebar: fetch up to 4 random active activities for "Other Activities" panel
$sidebarActivities = [];
$stmtSide = $conn->prepare(
    "SELECT id, name, description, price_per_hour, price_type, image_url
     FROM activities
     WHERE is_active = 1
     ORDER BY RAND()
     LIMIT 4"
);
if ($stmtSide) {
    $stmtSide->execute();
    $sidebarActivities = $stmtSide->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtSide->close();
}

// ── Fetch ALL active time slots (keyed by activity_id, used by JS) ────
$timeSlots = [];
$stmtSlots = $conn->query(
    "SELECT id, activity_id, label, start_time, end_time
     FROM time_slots
     WHERE is_active = 1
     ORDER BY start_time ASC"
);
if ($stmtSlots) {
    $timeSlots = $stmtSlots->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Booking — YP4K Resort</title>
    <meta name="description" content="Browse and book resort packages at YP4K Resort. Select from activities and accommodations with real-time pricing.">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════════════════════
           DESIGN SYSTEM — matches dashboard.php / my-bookings.php
        ════════════════════════════════════════════════════════════ */
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
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ── NAVBAR (same as all other pages) ─────────────────── */
        .navbar-modern {
            background: rgba(15,23,42,.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-brand {
            font-weight: 700; font-size: 1.25rem;
            color: var(--text-primary) !important;
            display: flex; align-items: center; gap:.5rem;
        }
        .navbar-brand i { color: var(--accent-primary); font-size:1.5rem; }
        .nav-link {
            color: var(--text-secondary) !important; font-weight:500;
            padding: .5rem 1rem !important; border-radius: var(--radius-btn);
            transition: all .3s ease; margin: 0 .25rem;
        }
        .nav-link:hover { color: var(--text-primary) !important; background: rgba(59,130,246,.1); }
        .nav-link.active { color: var(--text-primary) !important; background: var(--accent-primary); }
        .nav-link.btn-logout {
            background: var(--accent-danger); color: white !important; border: none;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-link.btn-logout:hover { background: #dc2626; transform: translateY(-1px); }

        /* ── PAGE LAYOUT ────────────────────────────────────────── */
        .page-wrap { padding: 2rem 0 4rem; }

        /* ── PAGE HEADER (same as my-bookings) ─────────────────── */
        .page-header {
            background: var(--bg-card); backdrop-filter: blur(12px);
            border: 1px solid var(--border-color); border-radius: var(--radius-card);
            padding: 2rem; margin-bottom: 2rem; position: relative; overflow: hidden;
        }
        .page-header::before {
            content:''; position:absolute; top:0; left:0; right:0; height:3px;
            background: var(--gradient-primary);
        }
        .page-title {
            font-size: 2rem; font-weight:700; margin-bottom:.4rem;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .page-subtitle { color: var(--text-secondary); font-size:1rem; }

        /* ── STEP BREADCRUMB ─────────────────────────────────────── */
        .step-bar {
            display: flex; align-items: center; gap:.75rem; margin-bottom:2rem;
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: var(--radius-card); padding: 1.25rem 1.5rem;
        }
        .step-item {
            display: flex; align-items:center; gap:.5rem;
            color: var(--text-muted); font-size:.875rem; font-weight:500;
        }
        .step-item.active  { color: var(--accent-primary); }
        .step-item.done    { color: var(--accent-success); }
        .step-dot {
            width:28px; height:28px; border-radius:50%;
            border: 2px solid var(--border-color); display:flex;
            align-items:center; justify-content:center; font-size:.75rem;
            font-weight:700; flex-shrink:0;
        }
        .step-item.active  .step-dot { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(59,130,246,.1); }
        .step-item.done    .step-dot { border-color: var(--accent-success); background: var(--accent-success); color:#fff; }
        .step-separator { flex:1; height:1px; background: var(--border-color); }

        /* ── ALERTS ──────────────────────────────────────────────── */
        .alert-modern { border-radius: var(--radius-btn); border:none; padding:1rem 1.5rem; margin-bottom:1.5rem; }
        .alert-success-modern { background:rgba(16,185,129,.1); color:var(--accent-success); border:1px solid rgba(16,185,129,.2); }
        .alert-danger-modern  { background:rgba(239,68,68,.1);  color:var(--accent-danger);  border:1px solid rgba(239,68,68,.2);  }
        .alert-info-modern    { background:rgba(59,130,246,.1);  color:var(--accent-primary); border:1px solid rgba(59,130,246,.2); }

        /* ── FILTER BAR ─────────────────────────────────────────── */
        .filter-bar {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: var(--radius-card); padding:1.25rem 1.5rem; margin-bottom:2rem;
        }
        .filter-pill {
            display:inline-flex; align-items:center; gap:.4rem;
            padding:.45rem 1rem; border-radius:2rem; font-size:.8125rem; font-weight:500;
            border: 1px solid var(--border-color); background:transparent; color:var(--text-secondary);
            cursor:pointer; transition:all .25s ease;
        }
        .filter-pill:hover, .filter-pill.active {
            background: var(--accent-primary); border-color: var(--accent-primary); color:#fff;
        }

        /* ── PACKAGE CARDS GRID ─────────────────────────────────── */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.75rem;
        }

        .package-card {
            background: var(--bg-card); backdrop-filter: blur(10px);
            border: 1px solid var(--border-color); border-radius: var(--radius-card);
            overflow: hidden; transition: all .35s ease;
            display: flex; flex-direction: column;
        }
        .package-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent-primary);
        }

        /* Card image */
        .card-img-wrap {
            position: relative; height: 200px; overflow: hidden; flex-shrink:0;
        }
        .card-img-wrap img {
            width:100%; height:100%; object-fit:cover;
            transition: transform .5s ease;
        }
        .package-card:hover .card-img-wrap img { transform: scale(1.06); }

        .card-img-placeholder {
            width:100%; height:100%;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:.75rem;
        }
        .card-img-placeholder i { font-size:3rem; }

        .card-type-badge {
            position: absolute; top:.75rem; left:.75rem;
            padding:.25rem .75rem; border-radius:2rem; font-size:.7rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em; backdrop-filter:blur(8px);
        }
         .badge-activity      { background:rgba(59,130,246,.9); color:#fff; }
         .badge-accommodation { background:rgba(139,92,246,.9); color:#fff; }
         .badge-bundle        { background:rgba(16,185,129,.9); color:#fff; }

        /* Card body */
        .card-content { padding:1.25rem 1.25rem .75rem; flex:1; display:flex; flex-direction:column; }
        .card-pkg-name { font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:.4rem; }
        .card-pkg-desc { font-size:.8125rem; color:var(--text-secondary); line-height:1.55; flex:1; margin-bottom:1rem; }

        /* Card meta row */
        .card-meta { display:flex; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
        .card-meta-item {
            display:flex; align-items:center; gap:.35rem;
            font-size:.75rem; color:var(--text-muted);
        }
        .card-meta-item i { color:var(--accent-primary); }

        /* Card buttons */
        .card-actions { display:flex; gap:.6rem; padding:0 1.25rem 1.25rem; }

        /* ── BUTTONS (reuse existing) ────────────────────────────── */
        .btn-modern {
            padding:.6rem 1.1rem; border-radius:var(--radius-btn);
            font-weight:500; font-size:.875rem; border:none;
            transition: all .3s ease; display:inline-flex;
            align-items:center; gap:.4rem; cursor:pointer; text-decoration:none;
        }
        .btn-primary-modern {
            background: var(--gradient-primary); color:#fff;
        }
        .btn-primary-modern:hover { transform:translateY(-2px); box-shadow:0 10px 20px rgba(59,130,246,.3); color:#fff; }
        .btn-secondary-modern {
            background:transparent; border:1px solid var(--border-color); color:var(--text-primary);
        }
        .btn-secondary-modern:hover { background:var(--bg-tertiary); border-color:var(--accent-primary); color:var(--accent-primary); }
        .btn-success-modern  { background:var(--accent-success); color:#fff; }
        .btn-success-modern:hover  { background:#059669; transform:translateY(-2px); color:#fff; }
        .btn-danger-modern   { background:var(--accent-danger); color:#fff; }
        .btn-danger-modern:hover   { background:#dc2626; transform:translateY(-2px); }
        .btn-ghost {
            background:transparent; border:1px solid transparent; color:var(--text-secondary); padding:.5rem 1rem;
        }
        .btn-ghost:hover { background:rgba(59,130,246,.1); color:var(--accent-primary); }
        .btn-full { width:100%; justify-content:center; }

        /* ── CARD-MODERN (reuse for booking form) ───────────────── */
        .card-modern {
            background: var(--bg-card); backdrop-filter:blur(10px);
            border:1px solid var(--border-color); border-radius:var(--radius-card);
            box-shadow: var(--shadow-md); overflow:hidden;
        }
        .card-header-modern {
            background:transparent; border-bottom:1px solid var(--border-color);
            padding:1.25rem 1.5rem; font-weight:600; color:var(--text-primary);
            display:flex; align-items:center; gap:.5rem;
        }
        .card-header-modern i { color:var(--accent-primary); }
        .card-body-modern { padding:1.5rem; }

        /* ── FORM CONTROLS (reuse) ───────────────────────────────── */
        .form-label-modern { color:var(--text-primary); font-weight:500; margin-bottom:.4rem; font-size:.875rem; }
        .form-control-modern, .form-select-modern {
            background:var(--bg-tertiary); border:1px solid var(--border-color);
            color:var(--text-primary); border-radius:var(--radius-btn);
            padding:.75rem 1rem; font-size:.875rem; width:100%; transition:all .3s ease;
        }
        .form-control-modern:focus, .form-select-modern:focus {
            outline:none; border-color:var(--accent-primary); box-shadow:0 0 0 3px rgba(59,130,246,.1);
            background:var(--bg-tertiary); color:var(--text-primary);
        }
        .form-select-modern option { background:var(--bg-secondary); color:var(--text-primary); }
        .form-control-modern::placeholder { color:var(--text-muted); }
        .form-control-modern.error { border-color:var(--accent-danger); box-shadow:0 0 0 3px rgba(239,68,68,.1); }

        /* Make date and time picker icons white & easily visible on dark background */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.9;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover,
        input[type="month"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            transform: scale(1.15);
        }

        /* ── PRICE SUMMARY PANEL ─────────────────────────────────── */
        .price-summary {
            background: linear-gradient(135deg, rgba(59,130,246,.1), rgba(139,92,246,.1));
            border:1px solid rgba(59,130,246,.25); border-radius:var(--radius-card);
            padding:1.5rem; margin-top:1.5rem;
        }
        .price-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:.6rem; font-size:.875rem; }
        .price-row.total { padding-top:.75rem; border-top:1px solid var(--border-color); margin-top:.75rem; margin-bottom:0; }
        .price-row.total .price-val { font-size:1.25rem; font-weight:700; color:var(--accent-success); }
        .price-label { color:var(--text-secondary); }
        .price-val   { color:var(--text-primary); font-weight:500; }

        /* ── SELECTED PACKAGE HEADER ─────────────────────────────── */
        .selected-pkg-header {
            display:flex; align-items:center; gap:1rem;
            background:rgba(59,130,246,.08); border:1px solid rgba(59,130,246,.2);
            border-radius:.75rem; padding:1rem 1.25rem; margin-bottom:1.5rem;
        }
        .selected-pkg-img { width:64px; height:64px; border-radius:.75rem; object-fit:cover; flex-shrink:0; }
        .selected-pkg-img-placeholder {
            width:64px; height:64px; border-radius:.75rem; flex-shrink:0;
            background: var(--gradient-primary);
            display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.5rem;
        }
        .selected-pkg-name { font-weight:700; color:var(--text-primary); font-size:1rem; }
        .selected-pkg-price { color:var(--accent-success); font-size:.875rem; font-weight:600; }

        /* ── ADDON CHECKLIST ─────────────────────────────────────── */
        .addon-item {
            display:flex; align-items:center; gap:1rem;
            background:var(--bg-tertiary); border:1px solid var(--border-color);
            border-radius:.75rem; padding:.9rem 1rem; margin-bottom:.6rem;
            cursor:pointer; transition:all .25s ease;
        }
        .addon-item:hover { border-color:var(--accent-primary); }
        .addon-item.selected { border-color:var(--accent-primary); background:rgba(59,130,246,.08); }
        .addon-check { flex-shrink:0; }
        .addon-check input[type=checkbox] { width:18px; height:18px; accent-color:var(--accent-primary); cursor:pointer; }
        .addon-name  { flex:1; font-weight:500; color:var(--text-primary); font-size:.875rem; }
        .addon-price { font-weight:700; color:var(--accent-success); font-size:.875rem; }

        /* ── EMPTY STATE ─────────────────────────────────────────── */
        .empty-state { text-align:center; padding:4rem 2rem; color:var(--text-secondary); }
        .empty-state-icon { font-size:4rem; color:var(--text-muted); margin-bottom:1rem; }
        .empty-state-title { font-size:1.5rem; font-weight:700; color:var(--text-primary); margin-bottom:.5rem; }

        /* ── MODAL ───────────────────────────────────────────────── */
        .modal-content-modern {
            background: var(--bg-secondary); border:1px solid var(--border-color);
            border-radius:var(--radius-card); backdrop-filter:blur(20px);
        }
        .modal-header-modern {
            background:var(--bg-tertiary); border-bottom:1px solid var(--border-color);
            padding:1.25rem 1.5rem; border-radius:var(--radius-card) var(--radius-card) 0 0;
        }
        .modal-body-modern { padding:1.5rem; }
        .modal-footer-modern {
            background:var(--bg-tertiary); border-top:1px solid var(--border-color);
            padding:1.25rem 1.5rem; border-radius:0 0 var(--radius-card) var(--radius-card);
            display:flex; gap:.75rem; justify-content:flex-end;
        }
        .modal-pkg-img { width:100%; border-radius:.75rem; max-height:260px; object-fit:cover; margin-bottom:1.5rem; }
        .modal-pkg-img-placeholder {
            width:100%; height:200px; border-radius:.75rem; margin-bottom:1.5rem;
            display:flex; align-items:center; justify-content:center;
            font-size:4rem;
        }
        .detail-section { margin-bottom:1.5rem; }
        .detail-section-title {
            font-size:.8rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.06em; color:var(--accent-primary); margin-bottom:.75rem;
            display:flex; align-items:center; gap:.5rem;
        }
        .detail-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.4rem; }
        .detail-list li {
            display:flex; align-items:flex-start; gap:.6rem;
            font-size:.875rem; color:var(--text-secondary);
        }
        .detail-list li i { color:var(--accent-success); margin-top:.2rem; flex-shrink:0; }
        .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
        .detail-stat {
            background:var(--bg-tertiary); border:1px solid var(--border-color);
            border-radius:.75rem; padding:1rem; text-align:center;
        }
        .detail-stat-val { font-size:1.25rem; font-weight:700; color:var(--text-primary); }
        .detail-stat-lbl { font-size:.75rem; color:var(--text-muted); margin-top:.25rem; }
        .detail-price-tag {
            background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2);
            border-radius:.75rem; padding:1rem; text-align:center;
        }
        .detail-price-tag .price-main { font-size:1.75rem; font-weight:800; color:var(--accent-success); }
        .detail-price-tag .price-sub  { font-size:.8rem; color:var(--text-muted); }

        /* ── SECTION SWITCH ANIMATION ────────────────────────────── */
        .section-transition { animation: fadeSlideIn .35s ease forwards; }
        @keyframes fadeSlideIn {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── STAT CARDS (reuse from dashboard) ──────────────────── */
        .stat-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);
            border:1px solid var(--border-color); border-radius:var(--radius-card);
            padding:1.25rem; text-align:center; transition:all .3s ease;
            position:relative; overflow:hidden;
        }
        .stat-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:3px;
            background: var(--gradient-primary);
        }
        .stat-icon {
            width:50px; height:50px; border-radius:.75rem;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto .75rem; font-size:1.25rem;
        }
        .stat-icon.primary { background:rgba(59,130,246,.2); color:var(--accent-primary); }
        .stat-icon.success { background:rgba(16,185,129,.2); color:var(--accent-success); }

        /* ── LOADING SPINNER ────────────────────────────────────── */
        .spinner-wrap { display:flex; justify-content:center; padding:4rem 0; }
        .spinner-ring {
            width:48px; height:48px; border:4px solid var(--border-color);
            border-top-color:var(--accent-primary); border-radius:50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ── RESPONSIVE ─────────────────────────────────────────── */
        @media (max-width:768px) {
            .packages-grid { grid-template-columns:1fr; }
            .detail-grid   { grid-template-columns:1fr; }
            .page-title    { font-size:1.5rem; }
            .step-bar      { flex-wrap:wrap; gap:.5rem; }
            .step-separator { display:none; }
        }

        /* ── STAY / TOUR TYPE SELECTION (STEP 1) ─────────────────── */
        .tour-type-card {
            cursor: pointer;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-card, 0.75rem);
            padding: 1.15rem 1.25rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            user-select: none;
        }
        .tour-type-card:hover {
            border-color: rgba(59,130,246,0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .tour-type-card.active-daytour {
            border-color: #f59e0b !important;
            background: linear-gradient(135deg, rgba(245,158,11,0.14) 0%, rgba(15,23,42,0.85) 100%) !important;
            box-shadow: 0 4px 20px rgba(245,158,11,0.25);
        }
        .tour-type-card.active-overnight {
            border-color: #3b82f6 !important;
            background: linear-gradient(135deg, rgba(59,130,246,0.16) 0%, rgba(15,23,42,0.85) 100%) !important;
            box-shadow: 0 4px 20px rgba(59,130,246,0.25);
        }
        .tour-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .daytour-icon {
            background: rgba(245,158,11,0.2);
            color: #f59e0b;
        }
        .overnight-icon {
            background: rgba(59,130,246,0.2);
            color: #3b82f6;
        }
        /* ═══════════════════════════════════════
           GCASH MODAL & PAYMENT STYLES
        ═══════════════════════════════════════ */
        .pay-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(5, 10, 25, 0.85);
            backdrop-filter: blur(8px);
            z-index: 99999 !important;
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
            color: #f1f5f9;
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
        .qr-section { text-align: center; margin-bottom: 1.5rem; }
        .qr-label {
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1px; color: #10b981; margin-bottom: 0.75rem;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }
        .qr-frame {
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; border-radius: 1rem; padding: 12px;
            box-shadow: 0 8px 32px rgba(16,185,129,0.2), 0 0 0 3px rgba(16,185,129,0.15);
        }
        .qr-frame img { width: 180px; height: 180px; object-fit: contain; display: block; border-radius: 0.5rem; }
        .qr-missing {
            width: 180px; height: 180px;
            background: linear-gradient(135deg, #1e3a5f, #0f2744);
            border-radius: 0.5rem; display: flex; flex-direction: column;
            align-items: center; justify-content: center; color: #94a3b8; gap: 0.5rem;
        }
        .qr-missing i { font-size: 2.5rem; color: #3b82f6; }
        .fee-section {
            background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);
            border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .fee-label { font-size: 0.85rem; color: #cbd5e1; }
        .fee-label strong { display: block; font-size: 0.7rem; color: #94a3b8; margin-bottom: 0.15rem; text-transform: uppercase; }
        .fee-amount { font-size: 1.6rem; font-weight: 800; color: #10b981; }
        .upload-section { margin-bottom: 1.5rem; }
        .upload-label {
            font-size: 0.8rem; font-weight: 600; color: #cbd5e1;
            margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem;
        }
        .upload-row { display: flex; gap: 0.75rem; align-items: flex-start; }
        .upload-drop-area {
            flex: 1; border: 2px dashed rgba(59,130,246,0.4);
            border-radius: 0.75rem; padding: 1.25rem; text-align: center;
            cursor: pointer; transition: all 0.25s ease;
            background: rgba(15,23,42,0.6); position: relative;
        }
        .upload-drop-area:hover, .upload-drop-area.dragover {
            border-color: rgba(59,130,246,0.8); background: rgba(59,130,246,0.05);
        }
        .upload-drop-area i { font-size: 1.8rem; color: #3b82f6; margin-bottom: 0.4rem; display: block; }
        .upload-drop-area p { font-size: 0.78rem; color: #94a3b8; margin: 0; line-height: 1.4; }
        #proofFileInput { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .upload-thumb {
            width: 80px; height: 80px; flex-shrink: 0; border-radius: 0.6rem;
            border: 2px solid rgba(51,65,85,0.6); overflow: hidden;
            background: rgba(15,23,42,0.8); display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .upload-thumb img { width: 100%; height: 100%; object-fit: cover; display: none; }
        .upload-thumb .thumb-placeholder {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 0.2rem; color: #475569; text-align: center; padding: 0.5rem;
        }
        .upload-thumb.has-image { border-color: #10b981; }
        .thumb-check {
            position: absolute; top: 2px; right: 2px; background: #10b981; color: white;
            border-radius: 50%; width: 16px; height: 16px; font-size: 0.55rem; display: none;
            align-items: center; justify-content: center;
        }
        .upload-thumb.has-image .thumb-check { display: flex; }
        .upload-filename { font-size: 0.7rem; color: #64748b; margin-top: 0.4rem; text-align: center; word-break: break-all; }
        .pay-submit-btn {
            width: 100%; padding: 0.9rem; border: none; border-radius: 0.75rem;
            background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            box-shadow: 0 4px 20px rgba(16,185,129,0.3);
        }
        .pay-submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(16,185,129,0.45); }
        .pay-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        /* Loader & Success Overlays */
        .submit-loader {
            position: fixed; inset: 0; background: rgba(5, 8, 20, 0.92); backdrop-filter: blur(12px);
            z-index: 4000; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 1.5rem; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .submit-loader.show { opacity: 1; pointer-events: all; }
        .loader-ring-wrap { position: relative; width: 90px; height: 90px; }
        .loader-ring {
            width: 90px; height: 90px; border-radius: 50%;
            border: 4px solid rgba(59,130,246,0.15); border-top-color: #3b82f6;
            animation: spin 0.9s linear infinite;
        }
        .loader-icon {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: #3b82f6;
        }
        .loader-text { font-size: 1.05rem; font-weight: 600; color: #e2e8f0; }
        .success-overlay {
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at center, rgba(10,25,50,0.97) 0%, rgba(5,10,20,0.99) 100%);
            z-index: 3000; display: flex; flex-direction: column; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.4s ease;
        }
        .success-overlay.show { opacity: 1; pointer-events: all; }
        .success-icon-wrap {
            width: 110px; height: 110px; border-radius: 50%;
            background: rgba(16,185,129,0.15); border: 3px solid rgba(16,185,129,0.4);
            display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;
        }
        .success-icon-wrap i { font-size: 3.5rem; color: #10b981; }
        .success-title { font-size: 2rem; font-weight: 800; color: #f1f5f9; margin-bottom: 0.5rem; }
        .success-ref { font-size: 0.9rem; color: #10b981; font-weight: 600; margin-bottom: 0.5rem; }
        .success-sub { font-size: 0.9rem; color: #94a3b8; text-align: center; max-width: 360px; margin-bottom: 2rem; }
        .success-btn {
            padding: 0.8rem 2.5rem; background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff; border: none; border-radius: 0.75rem; font-weight: 700; cursor: pointer;
        }

        /* ── Add Another Room button ───────────────────────────────── */
        #add-room-btn {
            background: transparent;
            border: 1px solid var(--accent-success);
            color: var(--accent-success);
            padding: .6rem 1.25rem;
            border-radius: var(--radius-btn);
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: background .25s ease, color .25s ease, transform .2s ease;
            text-decoration: none;
        }
        #add-room-btn:hover {
            background: rgba(16,185,129,.1);
            color: #34d399;
            border-color: #34d399;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
<?php include 'components/navigation.php'; ?>

<div class="container-fluid px-4 page-wrap">

    <!-- ── ALERTS ──────────────────────────────────────────────────── -->
    <div id="successAlert" class="alert alert-modern alert-success-modern alert-dismissible d-none" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <span id="successMessage"></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <div id="errorAlert" class="alert alert-modern alert-danger-modern alert-dismissible d-none" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span id="errorMessage"></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>

    <!-- ── PAGE HEADER ─────────────────────────────────────────────── -->
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-compass me-2"></i>Create Booking</h1>
        <p class="page-subtitle">Browse our resort packages and reserve your perfect getaway</p>
    </div>

    <!-- ── STEP INDICATOR ──────────────────────────────────────────── -->
    <div class="step-bar" id="stepBar">
        <div class="step-item active" id="step1Indicator">
            <div class="step-dot" id="step1Dot">1</div>
            <span>Select Package</span>
        </div>
        <div class="step-separator"></div>
        <div class="step-item" id="step2Indicator">
            <div class="step-dot" id="step2Dot">2</div>
            <span>Booking Details</span>
        </div>
        <div class="step-separator"></div>
        <div class="step-item" id="step3Indicator">
            <div class="step-dot" id="step3Dot">3</div>
            <span>Confirmation</span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         STEP 1 — PACKAGE SELECTION
    ═══════════════════════════════════════════════════════════════ -->
    <div id="step1Section" id="selection-container">

        <!-- Stay / Tour Type Selector (Day Tour vs Overnight) — First Step before looking for rooms/activities -->
        <div class="mb-4" id="experienceSelectSection">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h5 class="mb-1" style="font-weight:700; color:var(--text-primary); font-size:1.15rem;">
                        <i class="bi bi-compass me-2 text-primary"></i>Choose Your Experience
                    </h5>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">
                        Select whether you are visiting for a day tour or staying overnight before browsing rooms and activities
                    </p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="tour-type-card active-daytour" id="topTourDayTour" onclick="chooseInitialTourType('daytour')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tour-card-icon daytour-icon">
                                <i class="bi bi-sun-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0 fw-bold" style="color:var(--text-primary); font-size:1.05rem;">Day Tour</h6>
                                    <span class="badge rounded-pill bg-warning text-dark px-2 py-1" style="font-size:0.75rem;">Daytime Visit</span>
                                </div>
                                <p class="mb-0 text-muted" style="font-size:0.82rem; line-height:1.4;">
                                    Cottages, pavilions, ATV rides, and daytime recreation (morning to dusk).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tour-type-card" id="topTourOvernight" onclick="chooseInitialTourType('overnight')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tour-card-icon overnight-icon">
                                <i class="bi bi-moon-stars-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0 fw-bold" style="color:var(--text-primary); font-size:1.05rem;">Overnight</h6>
                                    <span class="badge rounded-pill bg-primary px-2 py-1" style="font-size:0.75rem;">Overnight Stay</span>
                                </div>
                                <p class="mb-0 text-muted" style="font-size:0.82rem; line-height:1.4;">
                                    Relax in air-conditioned rooms, villas, suites, and overnight accommodations.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted me-1" style="font-size:.85rem;"><i class="bi bi-funnel me-1"></i>Filter:</span>
            <button class="filter-pill active" onclick="filterPackages('all',this)" id="filterAll">
                <i class="bi bi-grid-3x3-gap"></i> All Packages
            </button>
            <button class="filter-pill" onclick="filterPackages('activity',this)" id="filterActivity">
                <i class="bi bi-activity"></i> Activities
            </button>
            <button class="filter-pill" onclick="filterPackages('accommodation',this)" id="filterAccommodation">
                <i class="bi bi-house-door"></i> Accommodations
            </button>
            <button class="filter-pill" onclick="filterPackages('bundle',this)" id="filterBundle">
                <i class="bi bi-box-seam"></i> Bundles
            </button>
            <div class="ms-auto">
                <span id="packageCount" class="text-muted" style="font-size:.8rem;"></span>
            </div>
        </div>

        <!-- Package grid -->
        <div id="packagesGrid">
            <!-- Loading -->
            <div class="spinner-wrap" id="packagesLoading">
                <div class="spinner-ring"></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         STEP 2 — BOOKING DETAILS FORM
    ═══════════════════════════════════════════════════════════════ -->
    <div id="step2Section" class="d-none">
        <div class="row g-4">
            <!-- Main form -->
            <div class="col-lg-8">
                <div class="card-modern">
                    <div class="card-header-modern">
                        <i class="bi bi-calendar-plus"></i> Booking Details
                    </div>
                    <div class="card-body-modern">
                        <!-- Selected package preview -->
                        <div class="selected-pkg-header" id="selectedPkgPreview"></div>

                        <!-- Activity & Room Availability Calendar -->
                        <div id="activity-calendar"></div>

                        <form id="bookingForm" novalidate>
                            <!-- Hidden tour type from Step 1 selection -->
                            <input type="hidden" id="selectedTourType" name="tour_type" value="daytour">

                            <div class="row g-3 mb-4">
                                <!-- Check-in / Visit date -->
                                <!-- NOTE: type="hidden" — the calendar sets these values; the native date picker UI is
                                     intentionally suppressed to avoid duplicate/confusing calendar widgets. -->
                                <div class="col-md-6" id="checkinSection">
                                    <label class="form-label-modern" id="checkinLabel">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <span id="checkinLabelText">Check-in Date</span>
                                    </label>
                                    <input type="hidden" id="bookingDate" name="booking_date" required>
                                    <!-- Visual read-only display of the calendar-selected date -->
                                    <div id="checkinDisplay" class="form-control-modern" style="cursor:default;display:flex;align-items:center;gap:.5rem;min-height:46px;font-size:0.85rem;">
                                        <i class="bi bi-calendar-event" style="color:var(--accent-primary);"></i>
                                        <span id="checkinDisplayText" style="color:var(--text-muted);">Click date on calendar</span>
                                    </div>
                                    <div class="mt-1" id="checkinHint" style="font-size:.7rem;color:var(--text-muted);">
                                        Select your preferred check-in date
                                    </div>
                                </div>

                                <!-- Check-out date — shown only for overnight accommodations -->
                                <!-- type="hidden": set by the 2-click calendar range logic below -->
                                <div class="col-md-6 d-none" id="checkoutSection">
                                    <label class="form-label-modern"><i class="bi bi-calendar3 me-1"></i>Check-out Date</label>
                                    <input type="hidden" id="checkoutDate" name="check_out_date">
                                    <!-- Visual read-only display of the calendar-selected checkout date -->
                                    <div id="checkoutDisplay" class="form-control-modern" style="cursor:default;display:flex;align-items:center;gap:.5rem;min-height:46px;font-size:0.85rem;">
                                        <i class="bi bi-calendar-check" style="color:var(--accent-success);"></i>
                                        <span id="checkoutDisplayText" style="color:var(--text-muted);">Click 2nd date (check-out)</span>
                                    </div>
                                    <div class="mt-1" style="font-size:.7rem;color:var(--text-muted);">
                                        Select your check-out date (minimum 1 night)
                                    </div>
                                </div>
                            </div>

                            <!-- Time range — shown only for activities -->
                            <div id="timeRangeSection" class="mb-4 d-none">
                                <label class="form-label-modern"><i class="bi bi-clock me-1"></i>Time Slot</label>
                                <select id="timeSlotSelect" class="form-control-modern form-select-modern"
                                        style="appearance:auto;cursor:pointer;"
                                        onchange="onTimeSlotChange(this)">
                                    <option value="" data-in="" data-out="">— Select a package first —</option>
                                </select>
                                <!-- Hidden fields consumed by the JS submit handler (keeps fd.append logic intact) -->
                                <input type="hidden" id="timeIn"  name="time_in">
                                <input type="hidden" id="timeOut" name="time_out">
                                <div class="mt-2 p-2 rounded" id="durationDisplay"
                                     style="background:var(--bg-tertiary);border:1px solid var(--border-color);font-size:.8rem;color:var(--text-muted);text-align:center;">
                                    Duration: —
                                </div>
                                <div class="alert-modern alert-danger-modern mt-2 d-none" id="timeValidation"
                                     style="padding:.5rem 1rem;font-size:.8rem;"></div>
                            </div>

                            <!-- Number of guests -->
                            <div class="mb-4">
                                <label class="form-label-modern"><i class="bi bi-people me-1"></i>Number of Guests</label>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="adjustGuests(-1)" id="guestMinus" title="Decrease guests">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" id="numberOfGuests" name="number_of_guests"
                                           class="form-control-modern text-center" value="1" min="1" max="100"
                                           style="width:80px;"
                                           oninput="clampGuestInput(this)">
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="adjustGuests(1)" id="guestPlus" title="Increase guests">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="setGuestsMax()" id="guestMax"
                                            title="Set to maximum capacity"
                                            style="font-size:.75rem;font-weight:700;padding:.35rem .65rem;letter-spacing:.04em;">MAX</button>
                                    <span id="capacityHint" class="text-muted" style="font-size:.8rem;"></span>
                                </div>
                            </div>

                            <!-- Room / cottage preference — for accommodations -->
                            <div id="roomPrefSection" class="mb-4 d-none">
                                <label class="form-label-modern"><i class="bi bi-door-open me-1"></i>Room / Cottage Preference</label>
                                <select id="roomPref" name="room_preference" class="form-select-modern">
                                    <option value="">No preference</option>
                                </select>
                            </div>

                            <!-- Add-on activities -->
                            <div id="addonsSection" class="mb-4">
                                <label class="form-label-modern"><i class="bi bi-plus-circle me-1"></i>Add-on Activities <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                <div id="addonsList">
                                    <p class="text-muted" style="font-size:.8rem;">Loading available add-ons…</p>
                                </div>
                            </div>

                            <!-- Special requests -->
                            <div class="mb-4">
                                <label class="form-label-modern"><i class="bi bi-chat-text me-1"></i>Special Requests <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                <textarea id="specialRequests" name="special_requests"
                                          class="form-control-modern" rows="3"
                                          placeholder="Any dietary needs, accessibility requirements, celebrations…"></textarea>
                            </div>

                            <!-- Extra rooms added via "Add Another Room" -->
                            <div id="extraRoomsContainer"></div>

                            <!-- Hidden fields -->
                            <input type="hidden" id="selectedPackageId"   name="package_id">
                            <input type="hidden" id="selectedPackageType" name="package_type">

                        </form>
                    </div>
                </div>

                <!-- Price Summary — moved here from sidebar -->
                <div class="card-modern mt-4">
                    <div class="card-header-modern">
                        <i class="bi bi-receipt"></i> Price Summary
                    </div>
                    <div class="card-body-modern">
                        <div class="price-summary" id="priceSummaryContainer">
                            <!-- Dynamically generated by recalcPrice -->
                            <div class="price-row">
                                <span class="price-label" id="basePriceLabel">Base price</span>
                                <span class="price-val" id="basePriceVal">—</span>
                            </div>
                            <div class="price-row total">
                                <span class="price-label" style="font-weight:700;color:var(--text-primary);">Total</span>
                                <span class="price-val" id="grandTotal">—</span>
                            </div>
                        </div>

                        <div class="mt-3" style="font-size:.78rem;color:var(--text-muted);">
                            <i class="bi bi-info-circle me-1"></i>
                            Final amount calculated upon admin review. Prices in Philippine Peso (₱).
                        </div>
                    </div>
                </div>

                <!-- Action buttons — below Price Summary, right-aligned -->
                <div class="d-flex gap-3 flex-wrap align-items-center mt-3">
                    <button type="button" id="add-room-btn">
                        <i class="bi bi-plus-circle"></i> Add Another Room
                    </button>
                    <div class="ms-auto d-flex gap-3 flex-wrap">
                        <button type="button" class="btn-modern btn-secondary-modern" onclick="goToStep1()">
                            <i class="bi bi-arrow-left"></i> Back to Packages
                        </button>
                        <button type="submit" form="bookingForm" class="btn-modern btn-primary-modern" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Confirm Booking
                        </button>
                    </div>
                </div>

            </div>

            <!-- Sidebar — Other Activities -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="card-modern" style="position:sticky;top:1.5rem;align-self:flex-start;">
                    <div class="card-header-modern">
                        <i class="bi bi-stars"></i> Other Activities
                    </div>
                    <div class="card-body-modern" style="padding-top:.75rem;">

                        <?php if (!empty($sidebarActivities)): ?>
                        <?php
                        // Cycle through gradient palettes for visual variety
                        $gradients = [
                            'linear-gradient(135deg,rgba(59,130,246,.3),rgba(139,92,246,.3))',
                            'linear-gradient(135deg,rgba(16,185,129,.3),rgba(59,130,246,.25))',
                            'linear-gradient(135deg,rgba(245,158,11,.25),rgba(239,68,68,.2))',
                            'linear-gradient(135deg,rgba(139,92,246,.3),rgba(16,185,129,.25))',
                        ];
                        $icons = ['bi-activity','bi-water','bi-tree','bi-controller'];
                        foreach ($sidebarActivities as $idx => $act):
                            $isLast   = ($idx === count($sidebarActivities) - 1);
                            $grad     = $gradients[$idx % count($gradients)];
                            $icon     = $icons[$idx % count($icons)];
                            $imgSrc   = !empty($act['image_url'])
                                        ? '../' . htmlspecialchars($act['image_url'], ENT_QUOTES)
                                        : '';
                            $priceLabel = '₱' . number_format($act['price_per_hour'], 0)
                                        . ($act['price_type'] === 'per_head' ? ' / person' : ' / session');
                            $desc = mb_strimwidth($act['description'] ?? '', 0, 100, '…');
                        ?>
                        <a href="create-booking.php?id=<?= (int)$act['id'] ?>&type=activity" class="text-decoration-none d-block" style="margin-bottom:<?= $isLast ? '.25rem' : '1rem' ?>;">
                        <div class="other-activity-card" style="
                            background:var(--bg-tertiary);
                            border:1px solid var(--border-color);
                            border-radius:.75rem;
                            overflow:hidden;
                            transition:all .25s ease;
                        " onmouseover="this.style.borderColor='var(--accent-primary)';this.style.transform='translateY(-3px)'"
                           onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='translateY(0)'">
                            <?php if ($imgSrc): ?>
                            <div style="height:110px;overflow:hidden;">
                                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($act['name'], ENT_QUOTES) ?>"
                                     style="width:100%;height:100%;object-fit:cover;"
                                     onerror="this.parentElement.style.background='<?= $grad ?>';this.style.display='none';">
                            </div>
                            <?php else: ?>
                            <div style="height:110px;background:<?= $grad ?>;display:flex;align-items:center;justify-content:center;font-size:2.25rem;">
                                <i class="bi <?= $icon ?>" style="color:rgba(255,255,255,.7);"></i>
                            </div>
                            <?php endif; ?>
                            <div style="padding:.85rem 1rem;">
                                <div style="font-weight:700;color:var(--text-primary);font-size:.9rem;margin-bottom:.25rem;">
                                    <?= htmlspecialchars($act['name'], ENT_QUOTES) ?>
                                </div>
                                <div style="font-size:.78rem;color:var(--text-secondary);line-height:1.5;">
                                    <?= htmlspecialchars($desc, ENT_QUOTES) ?>
                                </div>
                                <div style="margin-top:.6rem;font-size:.75rem;font-weight:700;color:var(--accent-success);">
                                    From <?= $priceLabel ?>
                                </div>
                            </div>
                        </div>
                        </a>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p style="font-size:.8rem;color:var(--text-muted);text-align:center;padding:1rem 0;">
                            <i class="bi bi-info-circle me-1"></i>No other activities available right now.
                        </p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         STEP 3 — SUCCESS CONFIRMATION
    ═══════════════════════════════════════════════════════════════ -->
    <div id="step3Section" class="d-none">
        <div class="card-modern text-center" style="padding:4rem 2rem;">
            <div style="width:80px;height:80px;border-radius:50%;background:rgba(16,185,129,.15);border:2px solid var(--accent-success);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem;color:var(--accent-success);">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 style="font-size:1.75rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem;">Booking Confirmed!</h2>
            <p class="text-muted mb-1" id="bookingRefDisplay"></p>
            <p style="color:var(--text-secondary);font-size:.9rem;max-width:480px;margin:0 auto 2rem;">
                Your booking is pending admin review. You'll receive a confirmation once it's approved. Redirecting you to your dashboard…
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="dashboard.php" class="btn-modern btn-primary-modern">
                    <i class="bi bi-grid-3x3-gap"></i> Go to Dashboard
                </a>
                <a href="my-bookings.php" class="btn-modern btn-secondary-modern">
                    <i class="bi bi-calendar-check"></i> View My Bookings
                </a>
            </div>
        </div>
    </div>

</div><!-- /container -->

<!-- ══════════════════════════════════════════════════════════════════
     PACKAGE DETAILS MODAL
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="packageDetailModal" tabindex="-1" aria-labelledby="packageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-700 text-primary" id="packageDetailModalLabel" style="color:var(--text-primary);font-weight:700;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-modern" id="packageDetailBody">
                <!-- Injected by JS -->
            </div>
            <div class="modal-footer modal-footer-modern" style="z-index: 10;">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Close
                </button>
                <button type="button" class="btn-modern btn-primary-modern" id="modalBookNowBtn" style="padding: 0.75rem 1.75rem; font-weight: 600; box-shadow: 0 4px 15px rgba(59,130,246,0.3);">
                    <i class="bi bi-calendar-plus me-1"></i> Book Now
                </button>
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
                <strong>Total Amount / Fee</strong>
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
                    <input type="file" id="proofFileInput" accept="image/jpeg,image/jpg,image/png,image/webp">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p><strong>Click or drag &amp; drop</strong><br>JPG, PNG, or WEBP &mdash; max 1MB</p>
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

<!-- SUBMISSION LOADER -->
<div class="submit-loader" id="submitLoader">
    <div class="loader-ring-wrap">
        <div class="loader-ring"></div>
        <div class="loader-icon"><i class="bi bi-send"></i></div>
    </div>
    <div>
        <div class="loader-text">
            Processing your reservation…
        </div>
        <div class="loader-sub" style="font-size:0.8rem;color:#64748b;margin-top:0.4rem;text-align:center;">Uploading payment proof &amp; securing your booking. Please don't close this page.</div>
    </div>
</div>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="successOverlay">
    <div class="success-icon-wrap">
        <i class="bi bi-check-circle-fill"></i>
    </div>
    <div class="success-title">Reservation Submitted! 🎉</div>
    <div class="success-ref" id="successRef"></div>
    <p class="success-sub">
        Your booking is now <strong style="color:#10b981;">pending admin review</strong>.
        You'll receive a confirmation once it's approved. Thank you!
    </p>
    <button class="success-btn" id="successGoBtn">
        <i class="bi bi-journal-check me-2"></i>View My Bookings
    </button>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
/* ══════════════════════════════════════════════════════════════════
   YP4K Resort — Create Booking JS
══════════════════════════════════════════════════════════════════ */
'use strict';

// ── "Add Another Room" state ──────────────────────────────
let isAddingExtraRoom = false;   // true while user is picking an additional room
let extraRooms        = [];      // [{pkg, guests, date, checkOut}]
let cartCounts        = {};      // {roomId: quantity}

document.getElementById('add-room-btn').addEventListener('click', function (e) {
    e.preventDefault();
    isAddingExtraRoom = true;
    
    // Hide bundle and activity filter pills
    const bundleBtn = document.getElementById('filterBundle');
    if (bundleBtn) bundleBtn.style.display = 'none';
    const filterActivityBtn = document.getElementById('filterActivity');
    if (filterActivityBtn) filterActivityBtn.style.display = 'none';
    
    // If current filter is bundle or activity, reset to all
    if (currentFilter === 'bundle' || currentFilter === 'activity') {
        currentFilter = 'all';
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        const allBtn = document.getElementById('filterAll');
        if (allBtn) allBtn.classList.add('active');
    }
    applyPackageFilters();
    goToStep1();
});

// ── State ──────────────────────────────────────────────────────────
let allPackages     = [];
let allActivities   = [];
let selectedPkg     = null;
let addonsMap       = {};      // {id: {name, price, price_type, selected, qty}}
let currentTourType = 'daytour'; // 'daytour' or 'overnight'

// ── Choose Initial Tour Type (Step 1 Top Section) ──────────────────
function chooseInitialTourType(type) {
    currentTourType = type;
    const hiddenInp = document.getElementById('selectedTourType');
    if (hiddenInp) hiddenInp.value = type;

    const btnDay = document.getElementById('topTourDayTour');
    const btnNight = document.getElementById('topTourOvernight');

    if (type === 'daytour') {
        if (btnDay) {
            btnDay.classList.add('active-daytour');
            btnDay.classList.remove('active-overnight');
        }
        if (btnNight) {
            btnNight.classList.remove('active-overnight');
            btnNight.classList.remove('active-daytour');
        }
    } else {
        if (btnNight) {
            btnNight.classList.add('active-overnight');
            btnNight.classList.remove('active-daytour');
        }
        if (btnDay) {
            btnDay.classList.remove('active-daytour');
            btnDay.classList.remove('active-overnight');
        }
    }

    applyPackageFilters();
}

// ── Filter Packages based on chosen experience & category pill ─────
function applyPackageFilters() {
    if (!allPackages || !allPackages.length) return;

    const filterActivityBtn = document.getElementById('filterActivity');

    if (currentTourType === 'overnight') {
        // ATV / activities are ONLY available in Day Tour and NOT in Overnight
        if (filterActivityBtn) {
            filterActivityBtn.style.display = 'none';
            if (currentFilter === 'activity') {
                currentFilter = 'all';
                document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                const allBtn = document.getElementById('filterAll');
                if (allBtn) allBtn.classList.add('active');
            }
        }
    } else {
        if (filterActivityBtn && !isAddingExtraRoom) {
            filterActivityBtn.style.display = '';
        }
    }

    const filtered = allPackages.filter(pkg => {
        // Hide ATV Adventure rides from main packages list as they are handled via Add-ons
        if (pkg.name && pkg.name.toLowerCase().includes('atv')) return false;

        // When adding an extra room, bundles and activities are never shown
        if (isAddingExtraRoom && (pkg.package_type === 'bundle' || pkg.package_type === 'activity')) return false;

        // 1. Tour Type filtering based on admin configuration
        if (pkg.package_type === 'accommodation') {
            const tour = String(pkg.tour_type || '').toLowerCase();
            const dayRate = parseFloat(pkg.price_per_day) || 0;
            const nightRate = parseFloat(pkg.price_per_night) || 0;

            if (currentTourType === 'daytour') {
                const isDay = tour ? tour.includes('day') : (dayRate > 0);
                if (!isDay) return false;
            } else {
                const isOvernight = tour ? tour.includes('overnight') : (nightRate > 0);
                if (!isOvernight) return false;
            }
        } else if (pkg.package_type === 'activity') {
            // ATV and activities are ONLY available in Day Tour and NOT in Overnight
            if (currentTourType !== 'daytour') {
                return false;
            }
        } else if (pkg.package_type === 'bundle') {
            const tour = String(pkg.tour_type || 'both').toLowerCase();
            if (currentTourType === 'daytour') {
                if (tour !== 'both' && tour !== 'day_tour') return false;
            } else {
                if (tour !== 'both' && tour !== 'overnight') return false;
            }
        }

        // 2. Category pill filter ('all', 'activity', 'accommodation', 'bundle')
        if (currentFilter !== 'all' && pkg.package_type !== currentFilter) {
            return false;
        }

        return true;
    });

    renderPackages(filtered);
    const countEl = document.getElementById('packageCount');
    if (countEl) countEl.textContent = `${filtered.length} package${filtered.length === 1 ? '' : 's'}`;
}

// ── Time slot data (PHP → JS) — grouped by activity_id ────────────
const allTimeSlots = (function() {
    const raw = <?php
        // Build a map: { activityId: [ {id, label, start_time, end_time}, ... ] }
        $slotMap = [];
        foreach ($timeSlots as $s) {
            $aid = (int)$s['activity_id'];
            $slotMap[$aid][] = [
                'id'         => (int)$s['id'],
                'label'      => $s['label'],
                'start_time' => $s['start_time'],   // "HH:MM:SS"
                'end_time'   => $s['end_time'],
                'start_fmt'  => date('g:i A', strtotime($s['start_time'])),
                'end_fmt'    => date('g:i A', strtotime($s['end_time'])),
                'start_val'  => date('H:i',   strtotime($s['start_time'])),
                'end_val'    => date('H:i',   strtotime($s['end_time'])),
            ];
        }
        echo json_encode($slotMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?>;
    return raw;
})();

// ── Populate time slot <select> for the given activity ID ──────────
function populateTimeSlots(activityId) {
    const sel    = document.getElementById('timeSlotSelect');
    const timeIn  = document.getElementById('timeIn');
    const timeOut = document.getElementById('timeOut');
    const disp    = document.getElementById('durationDisplay');
    if (!sel) return;

    // Reset hidden fields & duration display
    if (timeIn)  timeIn.value  = '';
    if (timeOut) timeOut.value = '';
    if (disp)    disp.textContent = 'Duration: —';
    document.getElementById('timeValidation')?.classList.add('d-none');

    const slots = allTimeSlots[activityId] || [];
    sel.innerHTML = ''; // clear old options

    if (!slots.length) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.dataset.in  = '';
        opt.dataset.out = '';
        opt.textContent = '— No time slots for this activity —';
        sel.appendChild(opt);
        return;
    }

    // Placeholder
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.dataset.in  = '';
    placeholder.dataset.out = '';
    placeholder.textContent = '— Select a time slot —';
    sel.appendChild(placeholder);

    slots.forEach(s => {
        const label = s.label
            ? `${s.label}\u00a0(${s.start_fmt} – ${s.end_fmt})`
            : `${s.start_fmt} – ${s.end_fmt}`;
        const opt = document.createElement('option');
        opt.value        = s.id;
        opt.dataset.in   = s.start_val;
        opt.dataset.out  = s.end_val;
        opt.textContent  = label;
        sel.appendChild(opt);
    });
}
let currentFilter = 'all';
let proofFile     = null;

const detailModal = new bootstrap.Modal(document.getElementById('packageDetailModal'));

// ── Boot ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadPackages();
    initDateInputs();
    initPaymentModal();
    document.getElementById('bookingForm').addEventListener('submit', handleBookingSubmit);
    document.getElementById('bookingDate').addEventListener('change', recalcPrice);
    document.getElementById('checkoutDate').addEventListener('change',  recalcPrice);
    // timeSlotSelect drives validation & recalc (timeIn/timeOut hidden fields are set by onTimeSlotChange)
    const _tss = document.getElementById('timeSlotSelect');
    if (_tss) _tss.addEventListener('change', () => { validateTime(); recalcPrice(); });
    const guestsInput = document.getElementById('numberOfGuests');
    if (guestsInput) guestsInput.addEventListener('input', recalcPrice);

    // Show success toast if redirected from login
    const params = new URLSearchParams(window.location.search);
    if (params.get('success')) showSuccess(decodeURIComponent(params.get('success')));
});

// ── Load packages from API ─────────────────────────────────────────
async function loadPackages() {
    try {
        const res  = await fetch('../php/get_packages.php');
        const data = await res.json();

        document.getElementById('packagesLoading').remove();

        if (!data.success) { renderEmptyState('Could not load packages.'); return; }

        // Store all existing activities configured by admin in activities.php
        allActivities = data.activities || [];

        // In Step 1 (Main package selection grid), only ATV is offered as a standalone bookable activity package:
        // Pool, Basketball, Videoke, and other resort amenities are reserved as add-on activities
        const NON_STANDALONE_ACTIVITIES = ['pool', 'swimming', 'basketball', 'videoke', 'billiards', 'fitness gym', 'playground', 'pickleball'];
        allPackages = (data.packages || []).filter(p => {
            // Bundles are now shown in the grid (no longer excluded)
            if (p.package_type === 'activity') {
                const lowerName = (p.name || '').toLowerCase();
                if (NON_STANDALONE_ACTIVITIES.some(kw => lowerName.includes(kw))) {
                    return false;
                }
            }
            return true;
        });

        // Initialize ALL admin activities as potential add-ons
        addonsMap = {};
        allActivities.forEach(a => {
            const lowerName = (a.name || '').toLowerCase();
            addonsMap[a.id] = {
                id: a.id,
                name: a.name,
                price: parseFloat(a.price) || 0,
                price_type: a.price_type || 'flat_rate',
                starting_price: a.starting_price || ('₱' + Number(a.price).toLocaleString('en-PH', {minimumFractionDigits: 2})),
                is_atv: lowerName.includes('atv'),
                qty: 1,
                selected: false
            };
        });

        if (!allPackages.length) { renderEmptyState('No packages available yet. Check back soon!'); return; }

        // Check URL params for direct selection or tour type
        const _p = new URLSearchParams(window.location.search);
        const _tourParam = _p.get('tour_type');
        const _id   = _p.get('id');
        const _type = _p.get('type') || 'activity';

        let initTour = 'daytour';
        if (_tourParam === 'overnight') {
            initTour = 'overnight';
        } else if (_type === 'activity') {
            // ATV and activities are Day Tour only
            initTour = 'daytour';
        } else if (_id && _type === 'accommodation') {
            const matchPkg = allPackages.find(p => p.id == _id && p.package_type === _type);
            if (matchPkg) {
                const t = String(matchPkg.tour_type || '').toLowerCase();
                if (t.includes('overnight') && !t.includes('day')) {
                    initTour = 'overnight';
                }
            }
        }

        chooseInitialTourType(initTour);

        if (_id) {
            const _pkg = allPackages.find(p => p.id == _id && p.package_type === _type);
            if (_pkg) selectPackage(_pkg.id, _pkg.package_type);
        }
    } catch(e) {
        console.error(e);
        document.getElementById('packagesLoading').remove();
        renderEmptyState('Failed to load packages. Please refresh the page.');
    }
}

// ── Render package grid ────────────────────────────────────────────
function renderPackages(pkgs) {
    const grid = document.getElementById('packagesGrid');
    grid.innerHTML = '';

    if (!pkgs.length) { grid.innerHTML = emptyStateHTML('No packages match the current selection.'); return; }

    const container = document.createElement('div');
    container.className = 'packages-grid section-transition';

    pkgs.forEach(pkg => {
        const card = document.createElement('div');
        card.className = 'package-card';
        card.dataset.type = pkg.package_type;
        card.dataset.id   = pkg.id;

        // Gradient fallback colours per type
        const gradients = {
            activity:      'linear-gradient(135deg,#1e3a5f,#0f172a)',
            accommodation: 'linear-gradient(135deg,#2d1f3d,#0f172a)',
            bundle:        'linear-gradient(135deg,#1e3a2f,#0f172a)',
        };

        const imgHtml = pkg.image_url
            ? `<img src="${escHtml(pkg.image_url)}" alt="${escHtml(pkg.name)}" loading="lazy"
                    onerror="this.parentElement.innerHTML=getPlaceholderHTML('${escHtml(pkg.icon||'bi-star')}','${escHtml(pkg.package_type)}');">`
            : `<div class="card-img-placeholder" style="background:${gradients[pkg.package_type] || gradients.activity};">
                   <i class="bi ${escHtml(pkg.icon||'bi-star')}" style="color:rgba(255,255,255,.5);"></i>
                   <span style="color:rgba(255,255,255,.35);font-size:.75rem;">No image</span>
               </div>`;

        // Appropriate price label for current tour type
        let displayPrice = pkg.price_label;
        if (pkg.package_type === 'accommodation') {
            if (currentTourType === 'daytour') {
                const dayRate = parseFloat(pkg.price_per_day) || parseFloat(pkg.price_per_night) || 0;
                displayPrice = dayRate > 0 ? `₱${dayRate.toLocaleString('en-PH', {minimumFractionDigits:2})}/day` : pkg.price_label;
            } else {
                const nightRate = parseFloat(pkg.price_per_night) || parseFloat(pkg.price) || 0;
                displayPrice = nightRate > 0 ? `₱${nightRate.toLocaleString('en-PH', {minimumFractionDigits:2})}/night` : pkg.price_label;
            }
        }

        const badgeLabel = pkg.package_type === 'activity' 
            ? 'Activity' 
            : (pkg.package_type === 'bundle' 
                ? 'Bundle' 
                : (currentTourType === 'daytour' ? 'Day Tour' : 'Overnight Stay'));

        // Check if limit reached for UI
        const cartCount = typeof extraRooms !== 'undefined' ? 
            (extraRooms.filter(r => r.pkg.id === pkg.id).length + (typeof selectedPkg !== 'undefined' && selectedPkg && selectedPkg.id === pkg.id ? 1 : 0)) : 0;
        const unitLimit = pkg.booking_limit || 1;
        const isLimitReached = cartCount >= unitLimit;

        card.innerHTML = `
            <div class="card-img-wrap">
                ${imgHtml}
                <span class="card-type-badge badge-${escHtml(pkg.package_type)}">
                    ${badgeLabel}
                </span>
            </div>
            <div class="card-content">
                <div class="card-pkg-name">${escHtml(pkg.name)}</div>
                <div class="card-meta">
                    <div class="card-meta-item">
                        <i class="bi bi-tag"></i>
                        <span>${escHtml(displayPrice)}</span>
                    </div>
                    <div class="card-meta-item" style="color:var(--text-secondary); margin-top:4px;">
                        <i class="bi bi-bar-chart-fill" style="color:var(--accent-primary);"></i>
                        <span style="font-size:0.8rem;">Today: <strong>${pkg.booked_today_count || 0}</strong> / ${pkg.booking_limit || 1} booked</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <button class="btn-modern btn-secondary-modern" style="flex:1;"
                        onclick="openDetailModal(${pkg.id},'${escHtml(pkg.package_type)}')">
                    <i class="bi bi-eye"></i> View Details
                </button>
                <button class="btn-modern btn-primary-modern btn-book-now" style="flex:1;" ${isLimitReached ? 'disabled' : ''}>
                    <i class="bi ${isLimitReached ? 'bi-check-circle' : 'bi-calendar-plus'}"></i> ${isLimitReached ? 'Selected' : 'Book Now'}
                </button>
            </div>
        `;
        // Wire up card buttons — bundles use selectBundle, others use selectPackage
        const bookBtn = card.querySelector('.btn-book-now');
        if (bookBtn && !isLimitReached) {
            if (pkg.package_type === 'bundle') {
                bookBtn.onclick = () => selectBundle(pkg.id);
            } else {
                bookBtn.onclick = () => selectPackage(pkg.id, pkg.package_type);
            }
        }

        container.appendChild(card);
    });

    grid.appendChild(container);
}

function emptyStateHTML(msg) {
    return `<div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-compass"></i></div>
        <div class="empty-state-title">No Packages Found</div>
        <p>${escHtml(msg)}</p>
    </div>`;
}
function renderEmptyState(msg) {
    document.getElementById('packagesGrid').innerHTML = emptyStateHTML(msg);
}
function getPlaceholderHTML(icon, type) {
    const g = type === 'activity' ? 'linear-gradient(135deg,#1e3a5f,#0f172a)' : 'linear-gradient(135deg,#2d1f3d,#0f172a)';
    return `<div class="card-img-placeholder" style="background:${g};">
        <i class="bi ${icon}" style="color:rgba(255,255,255,.5);"></i>
    </div>`;
}

// ── Filter ─────────────────────────────────────────────────────────
function filterPackages(type, btn) {
    currentFilter = type;
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    applyPackageFilters();
}

// ── Select Bundle — auto-checks included add-on activities ──────────
/**
 * Advances to Step 2, selects the bundle as the current package, then
 * automatically ticks every add-on checkbox that corresponds to an activity
 * the bundle already includes, so the user doesn't have to select them manually.
 *
 * @param {number} bundleId  - The id of the bundle being booked.
 */
function selectBundle(bundleId) {
    const pkg = allPackages.find(p => p.id == bundleId && p.package_type === 'bundle');
    if (!pkg) { console.warn('selectBundle: bundle not found', bundleId); return; }

    // 1. Run the standard selectPackage flow (sets step 2, renders addons, etc.)
    selectPackage(pkg.id, 'bundle');

    // 2. After the DOM is updated, auto-check the included activity add-ons.
    //    renderAddons() is synchronous so the checkboxes already exist.
    const includedIds = Array.isArray(pkg.included_activity_ids) ? pkg.included_activity_ids : [];
    if (!includedIds.length) return;

    includedIds.forEach(actId => {
        const chk = document.getElementById('addonChk-' + actId);
        if (chk && !chk.checked) {
            chk.checked = true;
            // Reuse existing toggleAddon to update addonsMap + price + UI
            toggleAddon(actId, (addonsMap[actId] ? addonsMap[actId].price : 0), chk);
        }
    });
}

// ── Open Detail Modal ──────────────────────────────────────────────
function openDetailModal(id, type) {
    const pkg = allPackages.find(p => p.id == id && p.package_type === type);
    if (!pkg) return;

    document.getElementById('packageDetailModalLabel').textContent = pkg.name;

    // "Book Now" inside modal
    document.getElementById('modalBookNowBtn').onclick = () => {
        detailModal.hide();
        selectPackage(id, type);
    };

    const body = document.getElementById('packageDetailBody');

    const imgHtml = pkg.image_url
        ? `<img src="${escHtml(pkg.image_url)}" alt="${escHtml(pkg.name)}" class="modal-pkg-img"
                onerror="this.style.display='none';">`
        : `<div class="modal-pkg-img-placeholder" style="background:linear-gradient(135deg,#1e3a5f,#0f172a);">
               <i class="bi ${escHtml(pkg.icon||'bi-star')}" style="font-size:4rem;color:rgba(255,255,255,.4);"></i>
           </div>`;

    const amenitiesHtml = pkg.amenities && pkg.amenities.length
        ? pkg.amenities.map(a => `<li><i class="bi bi-check-circle-fill"></i>${escHtml(a)}</li>`).join('')
        : '<li><i class="bi bi-info-circle"></i>Contact us for amenities details</li>';

    const inclusionsHtml = (pkg.inclusions || [])
        .map(inc => `<li><i class="bi bi-check-circle-fill"></i>${escHtml(inc)}</li>`).join('');

    let modalPrice = pkg.starting_price;
    let modalSub = pkg.price_label;
    if (pkg.package_type === 'accommodation') {
        if (currentTourType === 'daytour') {
            const dayRate = parseFloat(pkg.price_per_day) || parseFloat(pkg.price_per_night) || 0;
            if (dayRate > 0) {
                modalPrice = `₱${dayRate.toLocaleString('en-PH', {minimumFractionDigits:2})}`;
                modalSub = '₱' + dayRate.toLocaleString('en-PH', {minimumFractionDigits:2}) + ' / day';
            }
        } else {
            const nightRate = parseFloat(pkg.price_per_night) || parseFloat(pkg.price) || 0;
            if (nightRate > 0) {
                modalPrice = `₱${nightRate.toLocaleString('en-PH', {minimumFractionDigits:2})}`;
                modalSub = '₱' + nightRate.toLocaleString('en-PH', {minimumFractionDigits:2}) + ' / night';
            }
        }
    }

    body.innerHTML = `
        ${imgHtml}
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-file-text"></i> Description</div>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">${escHtml(pkg.description || 'No description available.')}</p>
        </div>
        <div class="detail-grid mb-4">
            <div class="detail-stat">
                <div class="detail-stat-val"><i class="bi bi-people" style="color:var(--accent-primary);"></i> ${pkg.capacity}</div>
                <div class="detail-stat-lbl">Max Guests</div>
            </div>
            <div class="detail-stat">
                <div class="detail-stat-val">${escHtml(ucfirst(pkg.package_type === 'activity' ? 'Activity' : (currentTourType === 'daytour' ? 'Day Tour' : 'Overnight Stay')))}</div>
                <div class="detail-stat-lbl">Type</div>
            </div>
        </div>
        <div class="detail-price-tag mb-4">
            <div class="price-main">${escHtml(modalPrice)}</div>
            <div class="price-sub">${escHtml(modalSub)}</div>
        </div>
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-gift"></i> What's Included</div>
            <ul class="detail-list">${inclusionsHtml}</ul>
        </div>
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-star"></i> Amenities</div>
            <ul class="detail-list">${amenitiesHtml}</ul>
        </div>
        ${pkg.package_type === 'activity' ? `
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-clock"></i> Duration</div>
            <p style="color:var(--text-secondary);font-size:.875rem;">
                Minimum: <strong style="color:var(--text-primary);">${pkg.min_duration_hours}h</strong> &nbsp;|&nbsp;
                Maximum: <strong style="color:var(--text-primary);">${pkg.max_duration_hours}h</strong>
            </p>
        </div>` : ''}
    `;

    detailModal.show();
}

// ── Select Package → Step 2 ────────────────────────────────────────
function selectPackage(id, type) {
    const pkg = allPackages.find(p => p.id == id && p.package_type === type);
    if (!pkg) return;

    if (isAddingExtraRoom) {
        // ── Validation using live capacity tracker ──
        const currentCount = cartCounts[pkg.id] || 0;
        const unitLimit = pkg.booking_limit || 1;
        
        if (unitLimit - currentCount <= 0) {
            alert(`"${pkg.name}" is fully booked or already added to its maximum capacity.`);
            isAddingExtraRoom = false;
            const bundleBtn = document.getElementById('filterBundle');
            if (bundleBtn) bundleBtn.style.display = '';
            goToStep2();
            return;
        }

        // Increment tracker
        cartCounts[pkg.id] = currentCount + 1;

        // ── Append Extra Room ──
        isAddingExtraRoom = false;
        const bundleBtn = document.getElementById('filterBundle');
        if (bundleBtn) bundleBtn.style.display = '';

        const primaryDate    = document.getElementById('bookingDate').value;
        const primaryCheckOut = document.getElementById('checkoutDate').value;
        const primaryGuests  = parseInt(document.getElementById('numberOfGuests').value) || 1;

        const roomIndex = extraRooms.length + 2; 
        const roomEntry = {
            index: roomIndex,
            pkg: pkg,
            guests: primaryGuests,
            date: primaryDate,
            checkOut: primaryCheckOut
        };

        extraRooms.push(roomEntry);
        renderExtraRoomCard(roomEntry);
        recalcPrice();
        goToStep2();
        return;
    }

    // ── Normal (first) room selection ──
    cartCounts = {};
    cartCounts[pkg.id] = 1;
    extraRooms = [];
    const container = document.getElementById('extraRoomsContainer');
    if (container) container.innerHTML = '';

    // ATV and activities are strictly Day Tour only
    if (pkg.package_type === 'activity') {
        currentTourType = 'daytour';
        const topDay = document.getElementById('topTourDayTour');
        const topNight = document.getElementById('topTourOvernight');
        if (topDay) {
            topDay.classList.add('active-daytour');
            topDay.classList.remove('active-overnight');
        }
        if (topNight) {
            topNight.classList.remove('active-overnight');
            topNight.classList.remove('active-daytour');
        }
    }

    selectedPkg = pkg;
    document.getElementById('selectedPackageId').value   = pkg.id;
    document.getElementById('selectedPackageType').value = pkg.package_type;
    const hiddenTourInp = document.getElementById('selectedTourType');
    if (hiddenTourInp) hiddenTourInp.value = currentTourType;

    // Appropriate price label for preview
    let displayPrice = pkg.price_label;
    if (pkg.package_type === 'accommodation') {
        if (currentTourType === 'daytour') {
            const dayRate = parseFloat(pkg.price_per_day) || parseFloat(pkg.price_per_night) || 0;
            displayPrice = dayRate > 0 ? `₱${dayRate.toLocaleString('en-PH', {minimumFractionDigits:2})}/day` : pkg.price_label;
        } else {
            const nightRate = parseFloat(pkg.price_per_night) || parseFloat(pkg.price) || 0;
            displayPrice = nightRate > 0 ? `₱${nightRate.toLocaleString('en-PH', {minimumFractionDigits:2})}/night` : pkg.price_label;
        }
    }

    // Update selected pkg header with tour badge
    const imgEl = pkg.image_url
        ? `<img src="${escHtml(pkg.image_url)}" alt="${escHtml(pkg.name)}" class="selected-pkg-img"
                onerror="this.style.display='none';">`
        : `<div class="selected-pkg-img-placeholder"><i class="bi ${escHtml(pkg.icon||'bi-star')}"></i></div>`;

    const tourBadge = pkg.package_type === 'accommodation'
        ? (currentTourType === 'daytour'
            ? '<span class="badge rounded-pill bg-warning text-dark ms-2"><i class="bi bi-sun-fill me-1"></i>Day Tour</span>'
            : '<span class="badge rounded-pill bg-primary ms-2"><i class="bi bi-moon-stars-fill me-1"></i>Overnight</span>')
        : '<span class="badge rounded-pill bg-success ms-2"><i class="bi bi-activity me-1"></i>Day Tour Activity</span>';

    document.getElementById('selectedPkgPreview').innerHTML = `
        ${imgEl}
        <div>
            <div class="selected-pkg-name">${escHtml(pkg.name)} ${tourBadge}</div>
            <div class="selected-pkg-price">${escHtml(displayPrice)}</div>
        </div>
    `;

    // Configure inputs based on package type & currentTourType
    const labelText = document.getElementById('checkinLabelText');
    const hintText = document.getElementById('checkinHint');
    const checkoutSec = document.getElementById('checkoutSection');
    const checkoutInp = document.getElementById('checkoutDate');

    if (pkg.package_type === 'activity') {
        document.getElementById('timeRangeSection').classList.remove('d-none');
        if (checkoutSec) checkoutSec.classList.add('d-none');
        if (checkoutInp) checkoutInp.value = '';
        if (labelText) labelText.textContent = 'Date of Visit';
        if (hintText) hintText.textContent = 'Select your activity date';
        populateTimeSlots(pkg.id);
    } else if (pkg.package_type === 'accommodation') {
        document.getElementById('timeRangeSection').classList.add('d-none');

        if (currentTourType === 'daytour') {
            // Day tour: change the check in word to date of visit and remove the check out date
            if (labelText) labelText.textContent = 'Date of Visit';
            if (hintText) hintText.textContent = 'Select your preferred date of visit';
            if (checkoutSec) checkoutSec.classList.add('d-none');
            if (checkoutInp) checkoutInp.value = '';
        } else {
            // Overnight: show the check in and check out date and hide the date of visit word
            if (labelText) labelText.textContent = 'Check-in Date';
            if (hintText) hintText.textContent = 'Select your preferred check-in date';
            if (checkoutSec) checkoutSec.classList.remove('d-none');
        }
    } else {
        // Bundle
        document.getElementById('timeRangeSection').classList.add('d-none');
        if (checkoutSec) checkoutSec.classList.remove('d-none');
        if (labelText) labelText.textContent = 'Check-in Date';
        if (hintText) hintText.textContent = 'Select your check-in date';
    }

    // Capacity hint
    document.getElementById('capacityHint').textContent = `(max ${pkg.capacity})`;
    document.getElementById('numberOfGuests').max = pkg.capacity;

    // Inclusions (element removed from DOM; guard against null to avoid crashing the handler)
    const inclHtml = (pkg.inclusions||[]).map(i => `<li><i class="bi bi-check-circle-fill"></i>${escHtml(i)}</li>`).join('');
    const inclEl = document.getElementById('inclusionsList');
    if (inclEl) inclEl.innerHTML = inclHtml || '<li><i class="bi bi-dash"></i>No inclusions listed</li>';

    // Add-on activities (only available in Day Tour; strictly excluded for overnight stays)
    renderAddons(pkg);
    if (typeof fetchAndRenderActivityCalendar === 'function') {
        fetchAndRenderActivityCalendar(pkg.id, pkg.package_type);
    }

    recalcPrice();
    goToStep2();
}

// ── Add-ons ────────────────────────────────────────────────────────
function renderAddons(currentPkg) {
    const secEl  = document.getElementById('addonsSection');
    const listEl = document.getElementById('addonsList');

    if (!secEl || !listEl) return;

    // Filter available activities from allActivities (configured by admin in activities.php):
    // 1. Exclude the currently selected package if it's an activity (cannot add-on same activity)
    // 2. ATV is Day Tour only; so exclude ATV if staying overnight. All other activities (videoke, swimming pool, basketball, gym, etc.) are available!
    const available = allActivities.filter(a => {
        if (currentPkg.package_type === 'activity' && a.id == currentPkg.id) {
            return false;
        }
        const lowerName = (a.name || '').toLowerCase();
        const isAtv = lowerName.includes('atv');
        if (currentTourType === 'overnight' && isAtv) {
            return false; // ATV only available in day tour
        }
        return true;
    });

    if (!available.length) {
        secEl.classList.add('d-none');
        listEl.innerHTML = '<p style="font-size:.8rem;color:var(--text-muted);">No add-on activities available.</p>';
        return;
    }

    secEl.classList.remove('d-none');

    const guestsEl = document.getElementById('numberOfGuests');
    const maxQty = Math.max(1, parseInt(guestsEl ? guestsEl.value : 1, 10) || 1);

    listEl.innerHTML = available.map(a => {
        const priceType = a.price_type || 'flat_rate';
        const formattedPrice = a.starting_price || ('₱' + Number(a.price).toLocaleString('en-PH', {minimumFractionDigits: 2}));
        const suffix = priceType === 'per_head' ? ' / person' : (priceType === 'per_hour' ? ' / hr' : '');
        const isSelected = !!(addonsMap[a.id] && addonsMap[a.id].selected);
        const qtyVal = (addonsMap[a.id] && addonsMap[a.id].qty) || 1;

        let noteHtml = '';
        const lowerName = (a.name || '').toLowerCase();
        if (lowerName.includes('atv')) {
            noteHtml = '<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px; font-weight: normal; line-height: 1.2;">Note: Scheduled upon arrival. Subject to machine cooling periods for safety.</div>';
        } else if (lowerName.includes('videoke')) {
            noteHtml = '<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px; font-weight: normal; line-height: 1.2;">Note: First-come, first-served. Maximum of 3hours per group to allow other guests to enjoy.</div>';
        } else if (lowerName.includes('basketball')) {
            noteHtml = '<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px; font-weight: normal; line-height: 1.2;">Note: First-come, first-served. Maximum of 2hours per group to allow other guests to enjoy.</div>';
        } else if (lowerName.includes('billiard')) {
            noteHtml = '<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 3px; font-weight: normal; line-height: 1.2;">Note: Queueing system upon arrival. Maximum of 2 hours per group to allow other guests to enjoy.</div>';
        }

        return `
        <div class="addon-item ${isSelected ? 'selected' : ''}" id="addon-${a.id}" data-type="${escHtml(priceType)}" onclick="clickAddonCard(event, ${a.id})">
            <div class="addon-check">
                <input type="checkbox" id="addonChk-${a.id}" value="${a.id}" ${isSelected ? 'checked' : ''}
                       onchange="toggleAddon(${a.id},${a.price},this)">
            </div>
            <div class="addon-name">
                <div>
                    ${escHtml(a.name)}
                    ${priceType === 'per_head' ? '<span class="badge bg-secondary ms-1" style="font-size:.68rem;">Per Person</span>' : ''}
                </div>
                ${noteHtml}
            </div>
            <input type="number" class="addon-qty" id="addonQty-${a.id}" min="1" max="${maxQty}" value="${qtyVal}" step="1"
                   style="display:${(isSelected && priceType !== 'flat_rate') ? 'inline-block' : 'none'};width:4.5rem;text-align:center;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);border-radius:.5rem;padding:.25rem .35rem;"
                   title="Quantity for this add-on"
                   onclick="event.stopPropagation()"
                   oninput="onAddonQtyChange(${a.id}, this)">
            <div class="addon-price">+${escHtml(formattedPrice)}${escHtml(suffix)}</div>
        </div>`;
    }).join('');
}

function clickAddonCard(e, id) {
    if (e.target.tagName === 'INPUT' || e.target.classList.contains('addon-qty')) return;
    const chk = document.getElementById('addonChk-' + id);
    if (chk) {
        chk.checked = !chk.checked;
        toggleAddon(id, (addonsMap[id] ? addonsMap[id].price : 0), chk);
    }
}

function onAddonQtyChange(id, input) {
    if (addonsMap[id]) {
        addonsMap[id].qty = Math.max(1, parseInt(input.value, 10) || 1);
    }
    recalcPrice();
}

function toggleAddon(id, price, chk) {
    const item = document.getElementById('addon-' + id);
    const qtyEl = document.getElementById('addonQty-' + id);
    const priceType = (item && item.dataset.type) || (addonsMap[id] && addonsMap[id].price_type) || 'flat_rate';
    if (chk.checked) {
        if (item) item.classList.add('selected');
        const qtyVal = Math.max(1, parseInt(qtyEl ? qtyEl.value : 1, 10) || 1);
        addonsMap[id] = { ...addonsMap[id], selected: true, price: parseFloat(price) || 0, price_type: priceType, qty: qtyVal };
        if (qtyEl) qtyEl.style.display = (priceType === 'flat_rate') ? 'none' : 'inline-block';
    } else {
        if (item) item.classList.remove('selected');
        if (addonsMap[id]) addonsMap[id].selected = false;
        if (qtyEl) qtyEl.style.display = 'none';
    }
    recalcPrice();
}

// ── Price calculation ──────────────────────────────────────────────
function recalcPrice() {
    if (!selectedPkg) return;

    const guestsEl = document.getElementById('numberOfGuests');
    const guests   = Math.max(1, parseInt(guestsEl ? guestsEl.value : 1) || 1);
    document.querySelectorAll('.addon-qty').forEach(qtyEl => {
        qtyEl.max = guests;
        const current = parseInt(qtyEl.value, 10) || 1;
        if (current > guests) qtyEl.value = guests;
        if (current < 1) qtyEl.value = 1;
    });

    let base = 0;
    let baseLabel = 'Base price';

    if (selectedPkg.package_type === 'activity') {
        const tIn  = document.getElementById('timeIn').value;
        const tOut = document.getElementById('timeOut').value;
        let hrs = 1;
        if (tIn && tOut) {
            const mins = toMinutes(tOut) - toMinutes(tIn);
            if (mins > 0) hrs = mins / 60;
        }

        const pType = selectedPkg.price_type || 'flat_rate';
        if (pType === 'per_head') {
            base = selectedPkg.price * guests;
            baseLabel = `₱${selectedPkg.price.toLocaleString('en-PH', {minimumFractionDigits:2})} × ${guests} guest${guests > 1 ? 's' : ''}`;
        } else if (pType === 'per_hour') {
            base = selectedPkg.price * hrs;
            baseLabel = `₱${selectedPkg.price.toLocaleString('en-PH', {minimumFractionDigits:2})}/hr × ${hrs.toFixed(1)}h`;
        } else {
            // flat_rate
            base = selectedPkg.price;
            baseLabel = `₱${selectedPkg.price.toLocaleString('en-PH', {minimumFractionDigits:2})} (Session Rate)`;
        }
    } else if (selectedPkg.package_type === 'bundle') {
        base = selectedPkg.price * guests;
        baseLabel = `${selectedPkg.price_label} × ${guests} guest${guests > 1 ? 's' : ''}`;
    } else {
        // Accommodation — Day Tour vs Overnight
        const ci = document.getElementById('bookingDate').value;
        const co = document.getElementById('checkoutDate').value;
        const baseCap = parseInt(selectedPkg.base_capacity) || parseInt(selectedPkg.capacity) || 1;
        const extraFeePerPerson = parseFloat(selectedPkg.extra_person_fee) || 0;

        if (currentTourType === 'daytour') {
            const dayRate = parseFloat(selectedPkg.price_per_day) > 0 
                ? parseFloat(selectedPkg.price_per_day) 
                : parseFloat(selectedPkg.price);
            base = dayRate;
            baseLabel = `₱${dayRate.toLocaleString('en-PH', {minimumFractionDigits:2})} (Day Tour)`;

            if (guests > baseCap && extraFeePerPerson > 0) {
                const extraCount = guests - baseCap;
                const totalExtra = extraCount * extraFeePerPerson;
                window.extraGuestsCost = totalExtra;
                window.extraGuestsLabel = `${extraCount} extra guest${extraCount > 1 ? 's' : ''} (₱${extraFeePerPerson.toLocaleString('en-PH', {minimumFractionDigits:2})}/ea)`;
            } else {
                window.extraGuestsCost = 0;
            }
        } else {
            // Overnight
            const nightRate = parseFloat(selectedPkg.price_per_night) > 0 
                ? parseFloat(selectedPkg.price_per_night) 
                : parseFloat(selectedPkg.price);
            let nights = 1;
            if (ci && co) {
                nights = Math.max(1, Math.round((new Date(co) - new Date(ci)) / 86400000));
            }
            base = nightRate * nights;
            baseLabel = `₱${nightRate.toLocaleString('en-PH', {minimumFractionDigits:2})}/night × ${nights} night${nights > 1 ? 's' : ''}`;

            if (guests > baseCap && extraFeePerPerson > 0) {
                const extraCount = guests - baseCap;
                const totalExtra = extraCount * extraFeePerPerson * nights;
                window.extraGuestsCost = totalExtra;
                window.extraGuestsLabel = `${extraCount} extra guest${extraCount > 1 ? 's' : ''} (₱${extraFeePerPerson.toLocaleString('en-PH', {minimumFractionDigits:2})}/ea × ${nights} night${nights > 1 ? 's' : ''})`;
            } else {
                window.extraGuestsCost = 0;
            }
        }
    }

    let summaryHtml = '';
    let grandTotal = base;

    // 1. Base Price
    summaryHtml += `
        <div class="price-row">
            <span class="price-label">${baseLabel}</span>
            <span class="price-val">₱${base.toFixed(2)}</span>
        </div>
    `;

    // 2. Extra Guests Fee
    if (window.extraGuestsCost > 0) {
        summaryHtml += `
            <div class="price-row" style="color: var(--text-secondary); font-size: 0.9rem;">
                <span class="price-label" style="padding-left: 0.5rem; border-left: 2px solid var(--border-color);"><i class="bi bi-people me-1"></i>${window.extraGuestsLabel}</span>
                <span class="price-val">₱${window.extraGuestsCost.toFixed(2)}</span>
            </div>
        `;
        grandTotal += window.extraGuestsCost;
    }
    // Clean up window variables just in case
    delete window.extraGuestsCost;
    delete window.extraGuestsLabel;

    // 3. Add-ons
    Object.values(addonsMap).filter(a => a.selected).forEach(a => {
        const type = a.price_type || 'flat_rate';
        let cost = 0;
        let label = a.name;
        
        if (type === 'flat_rate') {
            cost = Number(a.price);
        } else {
            const qtyEl = document.getElementById('addonQty-' + a.id);
            let qty = Math.max(1, parseInt(qtyEl ? qtyEl.value : 1, 10) || 1);
            if (qtyEl) {
                qtyEl.max = guests;
                if (qty > guests) {
                    qty = guests;
                    qtyEl.value = guests;
                }
            }
            cost = Number(a.price) * qty;
            label = `${a.name} (x${qty})`;
        }
        
        summaryHtml += `
            <div class="price-row" style="color: var(--text-secondary); font-size: 0.9rem;">
                <span class="price-label" style="padding-left: 0.5rem; border-left: 2px solid var(--border-color);"><i class="bi bi-plus me-1"></i>${label}</span>
                <span class="price-val">₱${cost.toFixed(2)}</span>
            </div>
        `;
        grandTotal += cost;
    });

    // 4. Extra rooms
    extraRooms.forEach(room => {
        const r = room.pkg;
        let roomCost = 0;
        let roomLabel = `Room ${room.index}: ${r.name}`;
        if (r.package_type === 'accommodation') {
            if (currentTourType === 'daytour') {
                roomCost = parseFloat(r.price_per_day) || parseFloat(r.price) || 0;
            } else {
                const nightRate = parseFloat(r.price_per_night) || parseFloat(r.price) || 0;
                let nights = 1;
                if (room.date && room.checkOut) {
                    nights = Math.max(1, Math.round((new Date(room.checkOut) - new Date(room.date)) / 86400000));
                }
                roomCost = nightRate * nights;
            }
        } else {
            roomCost = parseFloat(r.price) || 0;
        }
        summaryHtml += `
            <div class="price-row" style="color:var(--text-secondary);font-size:0.9rem;">
                <span class="price-label" style="padding-left:.5rem;border-left:2px solid var(--accent-success);"><i class="bi bi-door-open me-1"></i>${escHtml(roomLabel)}</span>
                <span class="price-val">₱${roomCost.toFixed(2)}</span>
            </div>`;
        grandTotal += roomCost;
    });

    // Total
    summaryHtml += `
        <div class="price-row total">
            <span class="price-label" style="font-weight:700;color:var(--text-primary);">Total</span>
            <span class="price-val" id="grandTotal">₱${grandTotal.toFixed(2)}</span>
        </div>
    `;

    const summaryContainer = document.getElementById('priceSummaryContainer');
    if (summaryContainer) {
        summaryContainer.innerHTML = summaryHtml;
    }
}

// ── Time slot select handler ────────────────────────────────────────
function onTimeSlotChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('timeIn').value  = opt.dataset.in  || '';
    document.getElementById('timeOut').value = opt.dataset.out || '';
    validateTime();
    recalcPrice();
}

// ── Time validation ────────────────────────────────────────────────
function validateTime() {
    const tIn  = document.getElementById('timeIn').value;
    const tOut = document.getElementById('timeOut').value;
    const date = document.getElementById('bookingDate').value;
    const msg  = document.getElementById('timeValidation');
    const disp = document.getElementById('durationDisplay');

    msg.classList.add('d-none');
    if (!tIn || !tOut) { disp.textContent = 'Duration: \u2014'; return true; }

    // ── Advance booking rule: Time-In must be at least 2 hours from now ──
    if (date && tIn) {
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        if (date === todayStr) {
            const BUFFER_HOURS = 2;
            const [selH, selM] = tIn.split(':').map(Number);
            const nowMins = now.getHours() * 60 + now.getMinutes();
            const selMins = selH * 60 + selM;
            if (selMins < nowMins + (BUFFER_HOURS * 60)) {
                const earliest = new Date(now.getTime() + BUFFER_HOURS * 60 * 60 * 1000);
                const earliestFmt = earliest.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
                msg.textContent = `⚠️ Bookings must be made at least ${BUFFER_HOURS} hours in advance. Earliest available Time-In today is ${earliestFmt}.`;
                msg.classList.remove('d-none');
                disp.textContent = 'Duration: —';
                return false;
            }
        }
    }

    const mIn  = toMinutes(tIn);
    const mOut = toMinutes(tOut);
    const diff = mOut - mIn;

    if (diff <= 0) {
        msg.textContent = 'Time-Out must be after Time-In.';
        msg.classList.remove('d-none');
        disp.textContent = 'Duration: \u2014';
        return false;
    }
    if (diff < 30) {
        msg.textContent = 'Minimum booking duration is 30 minutes.';
        msg.classList.remove('d-none');
        return false;
    }

    const h = Math.floor(diff/60), m = diff % 60;
    disp.textContent = `Duration: ${h}h ${m}m`;
    return true;
}

// ── Form submit & GCash Modal ──────────────────────────────────────
async function handleBookingSubmit(e) {
    e.preventDefault();
    hideAllAlerts();

    console.log("==========================================");
    console.log(" [DEBUG] Confirm Booking clicked!");
    console.log(" [DEBUG] Selected Package:", selectedPkg);
    console.log("==========================================");

    if (!selectedPkg) { 
        console.warn(" [DEBUG] No selectedPkg found.");
        showError('Please select a package first.'); 
        return; 
    }

    const date     = document.getElementById('bookingDate')?.value;
    const guestsEl = document.getElementById('numberOfGuests');
    const guests   = parseInt(guestsEl ? guestsEl.value : 1);
    const tIn      = document.getElementById('timeIn')?.value;
    const tOut     = document.getElementById('timeOut')?.value;
    const checkOut = document.getElementById('checkoutDate')?.value;

    console.log(" [DEBUG] Form Values =>", { date, guests, tIn, tOut, checkOut });

    if (!date) { showError('Please select a booking date.'); return; }

    // ── Advance booking rule: Time-In must be at least 2 hours from now ──
    if (selectedPkg.package_type === 'activity' && tIn) {
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        if (date === todayStr) {
            const BUFFER_HOURS = 2;
            const [selH, selM] = tIn.split(':').map(Number);
            const nowMins = now.getHours() * 60 + now.getMinutes();
            const selMins = selH * 60 + selM;
            if (selMins < nowMins + (BUFFER_HOURS * 60)) {
                const earliest = new Date(now.getTime() + BUFFER_HOURS * 60 * 60 * 1000);
                const earliestFmt = earliest.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
                showError(`⚠️ Bookings must be made at least ${BUFFER_HOURS} hours in advance. Earliest available Time-In today is ${earliestFmt}.`);
                return;
            }
        }
    }

    if (selectedPkg.package_type === 'activity' && (!tIn || !tOut)) {
        showError('Please select both Time-In and Time-Out.'); return;
    }
    if (selectedPkg.package_type === 'activity' && !validateTime()) return;
    if (selectedPkg.package_type === 'accommodation') {
        if (currentTourType === 'overnight' && !checkOut) {
            showError('Please select a check-out date for overnight stay.');
            return;
        }
    } else if (selectedPkg.package_type === 'bundle' && !checkOut) {
        showError('Please select a check-out date.');
        return;
    }
    if (guests < 1) { showError('At least 1 guest is required.'); return; }

    // Display total amount in modal & open GCash modal
    const totalEl = document.getElementById('grandTotal');
    const totalTxt = totalEl ? totalEl.textContent : '₱0';
    console.log(" [DEBUG] Calculated Total:", totalTxt);

    const feeEl = document.getElementById('modalFeeAmount');
    if (feeEl) feeEl.textContent = totalTxt;

    console.log(" [DEBUG] Now calling openGcashModal()...");
    openGcashModal();
}

function initPaymentModal() {
    console.log(" [DEBUG] Initializing GCash Payment Modal listeners...");
    const backdrop = document.getElementById('payModalBackdrop');
    const closeBtn = document.getElementById('payModalClose');
    const dropArea = document.getElementById('uploadDropArea');
    const fileInp  = document.getElementById('proofFileInput');
    const submitBtn = document.getElementById('paySubmitBtn');

    if (closeBtn) closeBtn.addEventListener('click', closePayModal);
    if (backdrop) {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closePayModal();
        });
    }

    if (fileInp) {
        fileInp.addEventListener('change', e => {
            if (e.target.files && e.target.files[0]) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }

    if (dropArea) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropArea.addEventListener(evt, e => { e.preventDefault(); dropArea.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropArea.addEventListener(evt, e => { e.preventDefault(); dropArea.classList.remove('dragover'); });
        });
        dropArea.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                fileInp.files = files;
                handleFileSelect(files[0]);
            }
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', submitWithProof);
    }
}

function openGcashModal() {
    console.log(" [DEBUG] openGcashModal() called!");
    const backdrop = document.getElementById('payModalBackdrop');
    if (backdrop) {
        console.log(" [DEBUG] #payModalBackdrop found! Adding 'open' class...");
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    } else {
        console.error(" [DEBUG ERROR] #payModalBackdrop element NOT FOUND in DOM!");
    }
}

function closePayModal() {
    console.log(" [DEBUG] closePayModal() called.");
    const backdrop = document.getElementById('payModalBackdrop');
    if (backdrop) {
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function handleFileSelect(file) {
    const allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!allowedMimes.includes(file.type)) {
        showError('Invalid file type. Please select a JPG, PNG, or WEBP image only.');
        return;
    }
    compressGcashProof(file).then(compressed => {
        proofFile = compressed;
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumbImg = document.getElementById('thumbImg');
            const placeholder = document.getElementById('thumbPlaceholder');
            const thumb = document.getElementById('uploadThumb');
            const filenameEl = document.getElementById('uploadFilename');
            const submitBtn = document.getElementById('paySubmitBtn');

            thumbImg.src = e.target.result;
            thumbImg.style.display = 'block';
            placeholder.style.display = 'none';
            thumb.classList.add('has-image');

            const name = compressed.name.length > 18 ? compressed.name.substring(0, 15) + '...' : compressed.name;
            filenameEl.textContent = name;
            submitBtn.disabled = false;
        };
        reader.readAsDataURL(compressed);
    }).catch(err => {
        proofFile = null;
        const submitBtn = document.getElementById('paySubmitBtn');
        if (submitBtn) submitBtn.disabled = true;
        alert(err.message || 'Could not process the image.');
        showError(err.message || 'Could not process the image.');
    });
}

const GCASH_MAX_BYTES = 1048576;

function compressGcashProof(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('Could not read the image file.'));
        reader.onload = () => {
            const img = new Image();
            img.onload = () => {
                let width = img.naturalWidth || img.width;
                let height = img.naturalHeight || img.height;
                const scale = Math.min(1, 854 / width, 480 / height);
                width = Math.max(1, Math.round(width * scale));
                height = Math.max(1, Math.round(height * scale));
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const toBlob = (type, quality) => new Promise(res => canvas.toBlob(res, type, quality));
                (async () => {
                    let blob = await toBlob('image/webp', 0.82);
                    let mime = 'image/webp';
                    let ext = 'webp';
                    if (!blob) {
                        blob = await toBlob('image/jpeg', 0.82);
                        mime = 'image/jpeg';
                        ext = 'jpg';
                    }
                    let quality = 0.7;
                    while (blob && blob.size > GCASH_MAX_BYTES && quality >= 0.4) {
                        const next = await toBlob(mime, quality);
                        if (next) blob = next;
                        quality -= 0.15;
                    }
                    if (!blob || blob.size > GCASH_MAX_BYTES) {
                        reject(new Error('Image is still larger than 1MB after compression. Please choose a smaller screenshot.'));
                        return;
                    }
                    resolve(new File([blob], 'gcash-proof.' + ext, { type: blob.type || mime, lastModified: Date.now() }));
                })().catch(() => reject(new Error('Could not compress the image.')));
            };
            img.onerror = () => reject(new Error('Could not load the image.'));
            img.src = reader.result;
        };
        reader.readAsDataURL(file);
    });
}

function showLoader() {
    document.getElementById('submitLoader').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function hideLoader() {
    document.getElementById('submitLoader').classList.remove('show');
    document.body.style.overflow = '';
}

async function submitWithProof() {
    if (!proofFile || !selectedPkg) {
        showError('Please select an image screenshot of your GCash payment first.');
        return;
    }
    if (proofFile.size > 1048576) {
        alert('Image is still larger than 1MB after compression. Please choose a smaller screenshot.');
        showError('Payment screenshot must be 1MB or smaller.');
        return;
    }

    const submitBtn = document.getElementById('paySubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-send-fill"></i> Submitting...';

    closePayModal();
    showLoader();

    const date     = document.getElementById('bookingDate').value;
    const guests   = parseInt(document.getElementById('numberOfGuests').value);
    const tIn      = document.getElementById('timeIn').value;
    const tOut     = document.getElementById('timeOut').value;
    const checkOut = document.getElementById('checkoutDate').value;

    const fd = new FormData();
    fd.append('booking_type',     selectedPkg.package_type);
    fd.append('tour_type',        currentTourType);
    fd.append('full_name',        '<?php echo addslashes($userName); ?>');
    fd.append('email',            '<?php echo addslashes($userEmail); ?>');
    fd.append('phone_number',     '<?php echo addslashes($userPhone); ?>');
    fd.append('check_in_date',    date);
    fd.append('check_out_date',   (currentTourType === 'daytour' ? '' : checkOut) || '');
    fd.append('check_in_time',    tIn || '');
    fd.append('check_out_time',   tOut || '');
    fd.append('number_of_guests', guests);
    fd.append('special_requests', document.getElementById('specialRequests').value || '');
    fd.append('payment_proof',    proofFile, proofFile.name);

    const selectedAddons = Object.values(addonsMap).filter(a => a.selected).map(a => {
        const qtyEl = document.getElementById('addonQty-' + a.id);
        const qty = (a.price_type === 'flat_rate') ? 1 : Math.max(1, parseInt(qtyEl ? qtyEl.value : (a.qty || 1), 10) || 1);
        return { id: a.id, name: a.name, price: a.price, qty: qty, price_type: a.price_type };
    });
    fd.append('addons', JSON.stringify(selectedAddons));

    // Extra rooms
    if (extraRooms.length) {
        const extraRoomsPayload = extraRooms.map(room => ({
            package_id:   room.pkg.id,
            package_type: room.pkg.package_type,
            guests:       parseInt(document.getElementById('extraRoomGuests-' + room.index)?.value) || room.guests,
            check_in_date:  document.getElementById('extraRoomDate-' + room.index)?.value || room.date,
            check_out_date: document.getElementById('extraRoomCheckOut-' + room.index)?.value || room.checkOut || ''
        }));
        fd.append('extra_rooms', JSON.stringify(extraRoomsPayload));
    }

    if (selectedPkg.package_type === 'activity') {
        fd.append('activity_id', selectedPkg.id);
    } else if (selectedPkg.package_type === 'bundle') {
        fd.append('bundle_id', selectedPkg.id);
    } else {
        fd.append('accommodation_id', selectedPkg.id);
    }

    try {
        const res = await fetch('../php/upload_payment_proof.php', { method: 'POST', body: fd });
        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch(e) {
            console.error("Server raw response:", rawText);
            hideLoader();
            openGcashModal();
            showError('Server error: ' + rawText.substring(0, 100));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Reservation';
            return;
        }

        if (data.success) {
            hideLoader();
            showSuccessOverlay(data.booking_reference);
        } else {
            hideLoader();
            openGcashModal();
            showError(data.message || 'Failed to submit booking.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Reservation';
        }
    } catch (err) {
        console.error(err);
        hideLoader();
        openGcashModal();
        showError('Network error. Please check your internet and try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Reservation';
    }
}

function showSuccessOverlay(ref) {
    document.getElementById('successRef').textContent = `Booking Reference: ${ref || ''}`;
    document.getElementById('successOverlay').classList.add('show');
    // Redirect to my-bookings or dashboard after 4.5 seconds
    setTimeout(() => { window.location.href = 'my-bookings.php'; }, 4500);
}

// ── Guest counter ──────────────────────────────────────────────────
function adjustGuests(delta) {
    const input = document.getElementById('numberOfGuests');
    const max   = parseInt(input.max) || 100;
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(max, val));
    input.value = val;
    recalcPrice();
}

// Called on direct keyboard entry — clamps without blocking typing mid-digit
function clampGuestInput(input) {
    const max = parseInt(input.max) || 100;
    const min = parseInt(input.min) || 1;
    let val = parseInt(input.value);
    if (!isNaN(val)) {
        if (val > max) input.value = max;
        if (val < min) input.value = min;
    }
    recalcPrice();
}

// Sets guest count to the current maximum capacity
function setGuestsMax() {
    const input = document.getElementById('numberOfGuests');
    const max   = parseInt(input.max) || 100;
    input.value = max;
    recalcPrice();
}

// ── Extra room card helpers ────────────────────────────────────────
function renderExtraRoomCard(room) {
    const container = document.getElementById('extraRoomsContainer');
    if (!container) return;
    const r = room.pkg;
    const isOvernight = (r.package_type === 'accommodation' && currentTourType === 'overnight');
    const imgHtml = r.image_url
        ? `<img src="${escHtml(r.image_url)}" alt="${escHtml(r.name)}" class="selected-pkg-img" onerror="this.style.display='none'">`
        : `<div class="selected-pkg-img-placeholder"><i class="bi ${escHtml(r.icon||'bi-door-open')}"></i></div>`;
    const primaryDate     = document.getElementById('bookingDate').value;
    const primaryCheckOut = document.getElementById('checkoutDate').value;
    const primaryGuests   = document.getElementById('numberOfGuests').value;
    const card = document.createElement('div');
    card.id = `extraRoomCard-${room.index}`;
    card.style.cssText = 'background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.25);border-radius:.75rem;padding:1.25rem;margin-top:1.25rem;';
    card.innerHTML = `
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
            ${imgHtml}
            <div style="flex:1;">
                <div style="font-weight:700;color:var(--text-primary);font-size:.95rem;">
                    <span style="color:var(--accent-success);margin-right:.4rem;"><i class="bi bi-door-open"></i> Room ${room.index}:</span>
                    ${escHtml(r.name)}
                </div>
                <div style="color:var(--accent-success);font-size:.8rem;margin-top:.2rem;">${escHtml(r.price_label||'')}</div>
            </div>
            <button type="button" onclick="removeExtraRoom(${room.index})" title="Remove"
                style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#ef4444;border-radius:.5rem;padding:.3rem .6rem;cursor:pointer;font-size:.8rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label-modern" style="font-size:.8rem;"><i class="bi bi-calendar3 me-1"></i>Date of Visit</label>
                <input type="date" id="extraRoomDate-${room.index}" class="form-control-modern" value="${primaryDate}" style="font-size:.85rem;" oninput="recalcPrice()">
            </div>
            ${isOvernight ? `
            <div class="col-md-6">
                <label class="form-label-modern" style="font-size:.8rem;"><i class="bi bi-calendar-check me-1"></i>Check-out Date</label>
                <input type="date" id="extraRoomCheckOut-${room.index}" class="form-control-modern" value="${primaryCheckOut}" style="font-size:.85rem;" oninput="recalcPrice()">
            </div>` : '<div class="col-md-6"></div>'}
            <div class="col-md-6">
                <label class="form-label-modern" style="font-size:.8rem;"><i class="bi bi-people me-1"></i>Guests for this Room</label>
                <input type="number" id="extraRoomGuests-${room.index}" class="form-control-modern" value="${primaryGuests}" min="1" max="${r.capacity||100}" style="font-size:.85rem;" oninput="recalcPrice()">
            </div>
        </div>`;
    container.appendChild(card);
}

function removeExtraRoom(index) {
    const rm = extraRooms.find(r => r.index === index);
    if (rm && rm.pkg) {
        cartCounts[rm.pkg.id] = Math.max(0, (cartCounts[rm.pkg.id] || 0) - 1);
    }
    extraRooms = extraRooms.filter(r => r.index !== index);
    const card = document.getElementById('extraRoomCard-' + index);
    if (card) card.remove();
    recalcPrice();
}

// ── Step navigation ────────────────────────────────────────────────
function goToStep1() {
    document.getElementById('step1Section').classList.remove('d-none');
    document.getElementById('step2Section').classList.add('d-none');
    document.getElementById('step3Section').classList.add('d-none');
    setStepActive(1);
    window.scrollTo({top:0, behavior:'smooth'});
}
function goToStep2() {
    document.getElementById('step1Section').classList.add('d-none');
    document.getElementById('step2Section').classList.remove('d-none');
    document.getElementById('step2Section').classList.add('section-transition');
    document.getElementById('step3Section').classList.add('d-none');
    setStepActive(2);
    window.scrollTo({top:0, behavior:'smooth'});
}
function goToStep3() {
    document.getElementById('step1Section').classList.add('d-none');
    document.getElementById('step2Section').classList.add('d-none');
    document.getElementById('step3Section').classList.remove('d-none');
    document.getElementById('step3Section').classList.add('section-transition');
    setStepActive(3);
    window.scrollTo({top:0, behavior:'smooth'});
}
function setStepActive(n) {
    [1,2,3].forEach(i => {
        const ind = document.getElementById('step'+i+'Indicator');
        const dot = document.getElementById('step'+i+'Dot');
        ind.classList.remove('active','done');
        if (i < n)  { ind.classList.add('done');   dot.innerHTML = '<i class="bi bi-check"></i>'; }
        if (i === n) { ind.classList.add('active'); }
    });
}

// ── Dates ──────────────────────────────────────────────────────────
function initDateInputs() {
    const tomorrow = new Date();
    tomorrow.setHours(0, 0, 0, 0);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = [
        tomorrow.getFullYear(),
        String(tomorrow.getMonth() + 1).padStart(2, '0'),
        String(tomorrow.getDate()).padStart(2, '0')
    ].join('-');
    document.getElementById('bookingDate').min = minDate;
    document.getElementById('bookingDate').value = minDate;
    document.getElementById('checkoutDate').min  = minDate;

    document.getElementById('bookingDate').addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            const nextDayStr = nextDay.toISOString().split('T')[0];
            document.getElementById('checkoutDate').min = nextDayStr;
            if (document.getElementById('checkoutDate').value && document.getElementById('checkoutDate').value <= this.value) {
                document.getElementById('checkoutDate').value = nextDayStr;
            }
        }
        recalcPrice();
    });
}

// ── Alerts ─────────────────────────────────────────────────────────
function showSuccess(msg) {
    hideAllAlerts();
    document.getElementById('successMessage').textContent = msg;
    document.getElementById('successAlert').classList.remove('d-none');
    window.scrollTo({top:0,behavior:'smooth'});
    setTimeout(() => document.getElementById('successAlert').classList.add('d-none'), 6000);
}
function showError(msg) {
    hideAllAlerts();
    document.getElementById('errorMessage').textContent = msg;
    document.getElementById('errorAlert').classList.remove('d-none');
    window.scrollTo({top:0,behavior:'smooth'});
    setTimeout(() => document.getElementById('errorAlert').classList.add('d-none'), 6000);
}
function hideAllAlerts() {
    document.getElementById('successAlert').classList.add('d-none');
    document.getElementById('errorAlert').classList.add('d-none');
}

// ── Utilities ──────────────────────────────────────────────────────
function toMinutes(t) { const [h,m] = t.split(':').map(Number); return h*60+m; }
function ucfirst(s)   { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function escHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Interactive Availability Calendar (Activities & Rooms) ─────────
let calYear = new Date().getFullYear();
let calMonth = new Date().getMonth() + 1;
let currentCalPkgId = null;
let currentCalPkgType = null;

async function fetchAndRenderActivityCalendar(pkgId, pkgType, year, month) {
    currentCalPkgId = pkgId || currentCalPkgId;
    currentCalPkgType = pkgType || currentCalPkgType || 'activity';
    calYear = year || calYear;
    calMonth = month || calMonth;

    const container = document.getElementById('activity-calendar');
    if (!container) return;

    if (!currentCalPkgId) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="act-cal-box text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">
            <span class="spinner-border spinner-border-sm me-2 text-primary" role="status"></span>
            Loading availability calendar...
        </div>
    `;

    try {
        const res = await fetch(`../php/fetch-calendar-data.php?package_id=${currentCalPkgId}&package_type=${currentCalPkgType}&year=${calYear}&month=${calMonth}`);
        const data = await res.json();

        if (!data.success) {
            container.innerHTML = `<div class="act-cal-box text-danger p-2 small">${escHtml(data.message || 'Failed to load availability.')}</div>`;
            return;
        }

        renderCalendarView(data, container);
    } catch (err) {
        console.error("Calendar fetch error:", err);
        container.innerHTML = `<div class="act-cal-box text-muted p-2 small">Calendar preview currently unavailable.</div>`;
    }
}

function renderCalendarView(data, container) {
    const monthName = new Date(calYear, calMonth - 1, 1).toLocaleString('default', { month: 'long', year: 'numeric' });
    const firstDay = new Date(calYear, calMonth - 1, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth, 0).getDate();
    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
    const selectedDate = document.getElementById('bookingDate')?.value || '';

    let html = `
        <div class="act-cal-box">
            <div class="act-cal-header">
                <button type="button" class="act-cal-nav-btn" id="actCalPrev" title="Previous Month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="act-cal-title">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> ${escHtml(monthName)} — ${escHtml(data.item_name)}
                </div>
                <button type="button" class="act-cal-nav-btn" id="actCalNext" title="Next Month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="act-cal-grid">
                <div class="act-cal-day-head">Sun</div>
                <div class="act-cal-day-head">Mon</div>
                <div class="act-cal-day-head">Tue</div>
                <div class="act-cal-day-head">Wed</div>
                <div class="act-cal-day-head">Thu</div>
                <div class="act-cal-day-head">Fri</div>
                <div class="act-cal-day-head">Sat</div>
    `;

    for (let i = 0; i < firstDay; i++) {
        html += `<div class="act-cal-cell empty"></div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${calYear}-${String(calMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const dayData = (data.days && data.days[dateStr]) || { booked: 0, capacity: data.capacity, status: 'available', available: true };

        const isPast = dateStr < todayStr;
        let statusClass = dayData.status;
        let isClickable = dayData.available && !isPast && dayData.status !== 'blocked';
        if (isPast) statusClass = 'blocked';

        const isSelected = selectedDate === dateStr ? 'selected' : '';
        const capToShow = dayData.global_cap ? dayData.global_cap : dayData.capacity;

        html += `
            <div class="act-cal-cell ${statusClass} ${isSelected}"
                 ${isClickable ? `data-date="${dateStr}" role="button" tabindex="0"` : `aria-disabled="true"`}
                 title="Date: ${dateStr} | Booked: ${dayData.booked} / Cap: ${capToShow}">
                <span class="act-cal-day-num">${d}</span>
                <span class="act-cal-meta">
                    <span class="meta-full">Booked: ${dayData.booked} / Cap: ${capToShow}</span>
                    <span class="meta-short">${dayData.booked}/${capToShow}</span>
                </span>
            </div>
        `;
    }

    html += `
            </div>
            <div class="act-cal-legend">
                <span class="act-cal-legend-item"><span class="act-cal-legend-dot available"></span> Available</span>
                <span class="act-cal-legend-item"><span class="act-cal-legend-dot limited"></span> Limited</span>
                <span class="act-cal-legend-item"><span class="act-cal-legend-dot full"></span> Full</span>
                <span class="act-cal-legend-item"><span class="act-cal-legend-dot blocked"></span> Unavailable</span>
            </div>
        </div>
    `;

    container.innerHTML = html;

    // Month Navigation
    document.getElementById('actCalPrev')?.addEventListener('click', () => {
        let m = calMonth - 1, y = calYear;
        if (m < 1) { m = 12; y--; }
        fetchAndRenderActivityCalendar(currentCalPkgId, currentCalPkgType, y, m);
    });

    document.getElementById('actCalNext')?.addEventListener('click', () => {
        let m = calMonth + 1, y = calYear;
        if (m > 12) { m = 1; y++; }
        fetchAndRenderActivityCalendar(currentCalPkgId, currentCalPkgType, y, m);
    });

    // ── 2-Click Date Range Selection ────────────────────────────────
    // State: null = awaiting click 1 (check-in), non-null = awaiting click 2 (check-out)
    let pendingCheckIn = null; // dateStr of the first click

    /** Helper: formats a YYYY-MM-DD string into a human-friendly label */
    function fmtDate(d) {
        if (!d) return '';
        const [y, m, day] = d.split('-');
        return new Date(+y, +m - 1, +day).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /** Re-applies selected / range highlight classes to all cells */
    function applyRangeHighlight(checkIn, checkOut) {
        container.querySelectorAll('.act-cal-cell[data-date]').forEach(c => {
            const d = c.getAttribute('data-date');
            c.classList.remove('selected', 'in-range', 'range-start', 'range-end');
            if (checkIn && checkOut) {
                if (d === checkIn)  { c.classList.add('selected', 'range-start'); }
                else if (d === checkOut) { c.classList.add('selected', 'range-end'); }
                else if (d > checkIn && d < checkOut) { c.classList.add('in-range'); }
            } else if (checkIn && d === checkIn) {
                c.classList.add('selected', 'range-start');
            }
        });
    }

    container.querySelectorAll('.act-cal-cell[data-date]').forEach(cell => {
        cell.addEventListener('click', () => {
            const pickedDate = cell.getAttribute('data-date');
            const isOvernightAccommodation = currentCalPkgType === 'accommodation' && currentTourType === 'overnight';

            if (!isOvernightAccommodation) {
                // ── Single-date mode (activities, day-tour) ────────────
                const bookingDateInput = document.getElementById('bookingDate');
                if (bookingDateInput) {
                    bookingDateInput.value = pickedDate;
                    bookingDateInput.dispatchEvent(new Event('change'));
                }
                // Update visible display
                const dispText = document.getElementById('checkinDisplayText');
                if (dispText) {
                    dispText.textContent = fmtDate(pickedDate);
                    dispText.style.color = 'var(--text-primary)';
                }
                container.querySelectorAll('.act-cal-cell').forEach(c => c.classList.remove('selected', 'in-range', 'range-start', 'range-end'));
                cell.classList.add('selected');
                pendingCheckIn = null;

            } else if (pendingCheckIn === null) {
                // ── Click 1: set Check-in ──────────────────────────────
                pendingCheckIn = pickedDate;

                const bookingDateInput = document.getElementById('bookingDate');
                if (bookingDateInput) {
                    bookingDateInput.value = pickedDate;
                    bookingDateInput.dispatchEvent(new Event('change'));
                }
                // Clear checkout so a stale value doesn't linger
                const checkoutInput = document.getElementById('checkoutDate');
                if (checkoutInput) { checkoutInput.value = ''; checkoutInput.dispatchEvent(new Event('change')); }

                // Update visible displays
                const ciText = document.getElementById('checkinDisplayText');
                if (ciText) { ciText.textContent = fmtDate(pickedDate); ciText.style.color = 'var(--text-primary)'; }
                const coText = document.getElementById('checkoutDisplayText');
                if (coText) { coText.textContent = 'Now click your check-out date ↑'; coText.style.color = 'var(--accent-warning)'; }

                applyRangeHighlight(pickedDate, null);

            } else if (pickedDate > pendingCheckIn) {
                // ── Click 2 (valid): set Check-out ────────────────────
                const checkIn  = pendingCheckIn;
                const checkOut = pickedDate;
                pendingCheckIn = null; // reset state

                const bookingDateInput = document.getElementById('bookingDate');
                if (bookingDateInput) { bookingDateInput.value = checkIn; bookingDateInput.dispatchEvent(new Event('change')); }

                const checkoutInput = document.getElementById('checkoutDate');
                if (checkoutInput) { checkoutInput.value = checkOut; checkoutInput.dispatchEvent(new Event('change')); }

                // Update visible displays
                const ciText = document.getElementById('checkinDisplayText');
                if (ciText) { ciText.textContent = fmtDate(checkIn); ciText.style.color = 'var(--text-primary)'; }
                const coText = document.getElementById('checkoutDisplayText');
                if (coText) { coText.textContent = fmtDate(checkOut); coText.style.color = 'var(--text-primary)'; }

                applyRangeHighlight(checkIn, checkOut);

            } else {
                // ── Click 2 (invalid — earlier/same date): treat as new Click 1 ──
                pendingCheckIn = pickedDate;

                const bookingDateInput = document.getElementById('bookingDate');
                if (bookingDateInput) { bookingDateInput.value = pickedDate; bookingDateInput.dispatchEvent(new Event('change')); }
                const checkoutInput = document.getElementById('checkoutDate');
                if (checkoutInput) { checkoutInput.value = ''; checkoutInput.dispatchEvent(new Event('change')); }

                const ciText = document.getElementById('checkinDisplayText');
                if (ciText) { ciText.textContent = fmtDate(pickedDate); ciText.style.color = 'var(--text-primary)'; }
                const coText = document.getElementById('checkoutDisplayText');
                if (coText) { coText.textContent = 'Now click your check-out date ↑'; coText.style.color = 'var(--accent-warning)'; }

                applyRangeHighlight(pickedDate, null);
            }

            if (typeof recalcPrice === 'function') recalcPrice();
        });
    });
}

window.fetchAndRenderActivityCalendar = fetchAndRenderActivityCalendar;
</script>
<style>
#activity-calendar {
    margin-bottom: 1.25rem;
    width: 100%;
}
.act-cal-box {
    background: var(--bg-card, rgba(30, 41, 59, 0.85));
    border: 1px solid var(--border-color, rgba(51, 65, 85, 0.6));
    border-radius: var(--radius-card, 0.75rem);
    padding: 0.75rem;
    color: var(--text-primary, #f1f5f9);
    width: 100%;
    box-sizing: border-box;
}
.act-cal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border-color, rgba(51, 65, 85, 0.6));
}
.act-cal-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-primary, #f1f5f9);
    text-align: center;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.act-cal-nav-btn {
    background: var(--bg-tertiary, #334155);
    border: 1px solid var(--border-color, rgba(51, 65, 85, 0.6));
    color: var(--text-primary, #f1f5f9);
    border-radius: 0.375rem;
    padding: 0.25rem 0.55rem;
    cursor: pointer;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    min-height: 32px;
    touch-action: manipulation;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.act-cal-nav-btn:hover, .act-cal-nav-btn:active {
    background: var(--accent-primary, #3b82f6);
    color: #fff;
}
.act-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
    text-align: center;
    width: 100%;
}
.act-cal-day-head {
    font-size: 0.62rem;
    font-weight: 700;
    color: var(--text-muted, #94a3b8);
    text-transform: uppercase;
    padding-bottom: 0.2rem;
    user-select: none;
}
.act-cal-cell {
    min-height: 56px;
    border-radius: 0.375rem;
    padding: 0.25rem 0.1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.4);
    border: 1px solid rgba(51, 65, 85, 0.5);
    touch-action: manipulation;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
    overflow: hidden;
}
.act-cal-cell.empty {
    background: transparent;
    border-color: transparent;
    pointer-events: none;
}
.act-cal-cell.available {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.6);
    color: #fff;
    cursor: pointer;
}
.act-cal-cell.available:hover {
    background: rgba(16, 185, 129, 0.25);
    border-color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
}
.act-cal-cell.available:active {
    transform: scale(0.96);
}
.act-cal-cell.limited {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.6);
    color: #fff;
    cursor: pointer;
}
.act-cal-cell.limited:hover {
    background: rgba(245, 158, 11, 0.25);
    border-color: #f59e0b;
    transform: translateY(-2px);
}
.act-cal-cell.limited:active {
    transform: scale(0.96);
}
.act-cal-cell.full {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.6);
    color: #f87171;
    opacity: 0.65;
    cursor: not-allowed;
}
.act-cal-cell.blocked {
    background: rgba(51, 65, 85, 0.25);
    border-color: rgba(51, 65, 85, 0.35);
    color: #64748b;
    opacity: 0.5;
    cursor: not-allowed;
}
.act-cal-cell.selected {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    background: rgba(59, 130, 246, 0.25) !important;
    transform: scale(1.02);
}
/* Range-start and range-end endpoints get a stronger blue accent */
.act-cal-cell.range-start,
.act-cal-cell.range-end {
    background: rgba(59, 130, 246, 0.45) !important;
    border-color: #60a5fa !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.55);
    transform: scale(1.04);
    z-index: 1;
}
/* Cells inside the selected range get a subtle mid-tone highlight */
.act-cal-cell.in-range {
    background: rgba(59, 130, 246, 0.13) !important;
    border-color: rgba(59, 130, 246, 0.35) !important;
    color: #bfdbfe;
}
.act-cal-day-num {
    font-size: 0.85rem;
    font-weight: 700;
    line-height: 1;
}
.act-cal-meta {
    font-size: 0.6rem;
    color: var(--text-secondary, #cbd5e1);
    line-height: 1.1;
    margin-top: 0.12rem;
    text-align: center;
    width: 100%;
}
.act-cal-meta .meta-short {
    display: none;
}
.act-cal-meta .meta-full {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 0 1px;
}

.act-cal-legend {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.65rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(51, 65, 85, 0.4);
    font-size: 0.68rem;
    color: var(--text-muted, #94a3b8);
}
.act-cal-legend-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.act-cal-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.act-cal-legend-dot.available { background: #10b981; }
.act-cal-legend-dot.limited   { background: #f59e0b; }
.act-cal-legend-dot.full      { background: #ef4444; }
.act-cal-legend-dot.blocked   { background: #64748b; }

/* ── Tablet & Small Laptops (max-width: 992px) ── */
@media (max-width: 992px) {
    .act-cal-cell {
        min-height: 60px;
    }
}

/* ── Mobile Devices & Compact Screens (max-width: 640px) ── */
@media (max-width: 640px) {
    .act-cal-box {
        padding: 0.6rem;
    }
    .act-cal-title {
        font-size: 0.78rem;
    }
    .act-cal-grid {
        gap: 0.2rem;
    }
    .act-cal-cell {
        min-height: 48px;
        padding: 0.15rem 0.05rem;
    }
    .act-cal-day-num {
        font-size: 0.78rem;
    }
    /* Switch to compact X/Y count so text never clips or overflows on phones */
    .act-cal-meta .meta-full {
        display: none;
    }
    .act-cal-meta .meta-short {
        display: block;
        font-size: 0.52rem;
        font-weight: 600;
        letter-spacing: -0.2px;
    }
    .act-cal-legend {
        gap: 0.5rem;
        font-size: 0.62rem;
    }
}

/* ── Ultra-narrow devices (max-width: 360px) ── */
@media (max-width: 360px) {
    .act-cal-grid {
        gap: 0.15rem;
    }
    .act-cal-cell {
        min-height: 44px;
    }
    .act-cal-day-head {
        font-size: 0.65rem;
    }
    .act-cal-nav-btn {
        min-width: 28px;
        min-height: 28px;
        padding: 0.15rem 0.35rem;
    }
}
</style>
</body>
</html>
