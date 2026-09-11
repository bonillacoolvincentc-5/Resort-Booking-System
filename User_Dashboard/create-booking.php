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
    $userPhone = $rowP['phone_number'] ?? 'N/A';
}
if (empty($userPhone)) {
    $userPhone = 'N/A';
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
    <div id="step1Section">

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

                        <form id="bookingForm" novalidate>
                            <!-- Check-in date -->
                            <div class="mb-4">
                                <label class="form-label-modern"><i class="bi bi-calendar3 me-1"></i>Booking Date</label>
                                <input type="date" id="bookingDate" name="booking_date"
                                       class="form-control-modern" required>
                                <div class="mt-1" style="font-size:.75rem;color:var(--text-muted);">
                                    Select your preferred check-in date
                                </div>
                            </div>

                            <!-- Time range — shown only for activities -->
                            <div id="timeRangeSection" class="mb-4 d-none">
                                <label class="form-label-modern"><i class="bi bi-clock me-1"></i>Time Range</label>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label-modern" style="font-size:.8rem;color:var(--text-muted);">Time-In (Start)</label>
                                        <input type="time" id="timeIn" name="time_in"
                                               class="form-control-modern" min="07:00" max="22:00">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-modern" style="font-size:.8rem;color:var(--text-muted);">Time-Out (End)</label>
                                        <input type="time" id="timeOut" name="time_out"
                                               class="form-control-modern" min="07:00" max="22:00">
                                    </div>
                                </div>
                                <div class="mt-2 p-2 rounded" id="durationDisplay"
                                     style="background:var(--bg-tertiary);border:1px solid var(--border-color);font-size:.8rem;color:var(--text-muted);text-align:center;">
                                    Duration: —
                                </div>
                                <div class="alert-modern alert-danger-modern mt-2 d-none" id="timeValidation"
                                     style="padding:.5rem 1rem;font-size:.8rem;"></div>
                            </div>

                            <!-- Check-out date — shown only for accommodations -->
                            <div id="checkoutSection" class="mb-4 d-none">
                                <label class="form-label-modern"><i class="bi bi-calendar3 me-1"></i>Check-out Date</label>
                                <input type="date" id="checkoutDate" name="check_out_date"
                                       class="form-control-modern">
                                <div class="mt-1" style="font-size:.75rem;color:var(--text-muted);">
                                    Select your check-out date (minimum 1 night)
                                </div>
                            </div>

                            <!-- Number of guests -->
                            <div class="mb-4">
                                <label class="form-label-modern"><i class="bi bi-people me-1"></i>Number of Guests</label>
                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="adjustGuests(-1)" id="guestMinus">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" id="numberOfGuests" name="number_of_guests"
                                           class="form-control-modern text-center" value="1" min="1" max="100"
                                           style="width:80px;" readonly>
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="adjustGuests(1)" id="guestPlus">
                                        <i class="bi bi-plus"></i>
                                    </button>
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

                            <!-- Hidden fields -->
                            <input type="hidden" id="selectedPackageId"   name="package_id">
                            <input type="hidden" id="selectedPackageType" name="package_type">

                            <!-- Action buttons -->
                            <div class="d-flex gap-3 flex-wrap">
                                <button type="button" class="btn-modern btn-secondary-modern" onclick="goToStep1()">
                                    <i class="bi bi-arrow-left"></i> Back to Packages
                                </button>
                                <button type="submit" class="btn-modern btn-primary-modern" id="submitBtn">
                                    <i class="bi bi-check-circle"></i> Confirm Booking
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar — price summary -->
            <div class="col-lg-4">
                <div class="card-modern">
                    <div class="card-header-modern">
                        <i class="bi bi-receipt"></i> Price Summary
                    </div>
                    <div class="card-body-modern">
                        <div class="price-summary">
                            <div class="price-row">
                                <span class="price-label" id="basePriceLabel">Base price</span>
                                <span class="price-val" id="basePriceVal">—</span>
                            </div>
                            <div class="price-row">
                                <span class="price-label">Add-ons</span>
                                <span class="price-val" id="addonsTotal">₱0.00</span>
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

                <!-- What's included mini-card -->
                <div class="card-modern mt-3">
                    <div class="card-header-modern">
                        <i class="bi bi-gift"></i> What's Included
                    </div>
                    <div class="card-body-modern">
                        <ul class="detail-list" id="inclusionsList">
                            <li><i class="bi bi-dash"></i> Select a package to see inclusions</li>
                        </ul>
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

// ── State ──────────────────────────────────────────────────────────
let allPackages   = [];
let selectedPkg   = null;
let addonsMap     = {};      // {id: {name, price, selected}}
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
    document.getElementById('timeIn').addEventListener('change',  () => { validateTime(); recalcPrice(); });
    document.getElementById('timeOut').addEventListener('change', () => { validateTime(); recalcPrice(); });
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

        // Exclude bundles from this page since they have a dedicated premium page
        allPackages = (data.packages || []).filter(p => p.package_type !== 'bundle');

        // Load activities as potential add-ons
        (data.activities || []).forEach(a => {
            addonsMap[a.id] = {
                id: a.id,
                name: a.name,
                price: a.price,
                price_type: a.price_type || 'flat_rate',
                selected: false
            };
        });

        if (!allPackages.length) { renderEmptyState('No packages available yet. Check back soon!'); return; }

        renderPackages(allPackages);
        document.getElementById('packageCount').textContent = allPackages.length + ' packages';
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

    if (!pkgs.length) { grid.innerHTML = emptyStateHTML('No packages match the current filter.'); return; }

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

        card.innerHTML = `
            <div class="card-img-wrap">
                ${imgHtml}
                <span class="card-type-badge badge-${escHtml(pkg.package_type)}">
                    ${pkg.package_type === 'activity' ? 'Activity' : (pkg.package_type === 'bundle' ? 'Bundle' : ucfirst(pkg.type || 'Accommodation'))}
                </span>
            </div>
            <div class="card-content">
                <div class="card-pkg-name">${escHtml(pkg.name)}</div>
                <div class="card-meta">
                    <div class="card-meta-item">
                        <i class="bi bi-tag"></i>
                        <span>${escHtml(pkg.price_label)}</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <button class="btn-modern btn-secondary-modern" style="flex:1;"
                        onclick="openDetailModal(${pkg.id},'${escHtml(pkg.package_type)}')">
                    <i class="bi bi-eye"></i> View Details
                </button>
                <button class="btn-modern btn-primary-modern" style="flex:1;"
                        onclick="selectPackage(${pkg.id},'${escHtml(pkg.package_type)}')">
                    <i class="bi bi-calendar-plus"></i> Book Now
                </button>
            </div>
        `;
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
    btn.classList.add('active');
    const filtered = type === 'all' ? allPackages : allPackages.filter(p => p.package_type === type);
    renderPackages(filtered);
    document.getElementById('packageCount').textContent = filtered.length + ' packages';
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
                <div class="detail-stat-val">${escHtml(ucfirst(pkg.package_type === 'activity' ? 'Activity' : (pkg.type||'Room')))}</div>
                <div class="detail-stat-lbl">Type</div>
            </div>
        </div>
        <div class="detail-price-tag mb-4">
            <div class="price-main">${escHtml(pkg.starting_price)}</div>
            <div class="price-sub">${escHtml(pkg.price_label)}</div>
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

    selectedPkg = pkg;
    document.getElementById('selectedPackageId').value   = pkg.id;
    document.getElementById('selectedPackageType').value = pkg.package_type;

    // Update selected pkg header
    const imgEl = pkg.image_url
        ? `<img src="${escHtml(pkg.image_url)}" alt="${escHtml(pkg.name)}" class="selected-pkg-img"
                onerror="this.style.display='none';">`
        : `<div class="selected-pkg-img-placeholder"><i class="bi ${escHtml(pkg.icon||'bi-star')}"></i></div>`;

    document.getElementById('selectedPkgPreview').innerHTML = `
        ${imgEl}
        <div>
            <div class="selected-pkg-name">${escHtml(pkg.name)}</div>
            <div class="selected-pkg-price">${escHtml(pkg.price_label)}</div>
        </div>
    `;

    // Show/hide time vs checkout date
    if (pkg.package_type === 'activity') {
        document.getElementById('timeRangeSection').classList.remove('d-none');
        document.getElementById('checkoutSection').classList.add('d-none');
    } else {
        document.getElementById('timeRangeSection').classList.add('d-none');
        document.getElementById('checkoutSection').classList.remove('d-none');
    }

    // Capacity hint
    document.getElementById('capacityHint').textContent = `(max ${pkg.capacity})`;
    document.getElementById('numberOfGuests').max = pkg.capacity;

    // Inclusions
    const inclHtml = (pkg.inclusions||[]).map(i => `<li><i class="bi bi-check-circle-fill"></i>${escHtml(i)}</li>`).join('');
    document.getElementById('inclusionsList').innerHTML = inclHtml || '<li><i class="bi bi-dash"></i>No inclusions listed</li>';

    // Add-on activities (only show OTHER activities if current pkg is accommodation or different activity)
    renderAddons(pkg);

    recalcPrice();
    goToStep2();
}

// ── Add-ons ────────────────────────────────────────────────────────
function renderAddons(currentPkg) {
    const listEl = document.getElementById('addonsList');
    // Activities that are not the currently selected package
    const available = allPackages.filter(p => p.package_type === 'activity' && !(p.package_type === currentPkg.package_type && p.id == currentPkg.id));

    if (!available.length) {
        listEl.innerHTML = '<p style="font-size:.8rem;color:var(--text-muted);">No add-on activities available.</p>';
        return;
    }

    listEl.innerHTML = available.map(a => {
        const priceType = a.price_type || 'flat_rate';
        const guestsEl = document.getElementById('numberOfGuests');
        const maxQty = Math.max(1, parseInt(guestsEl ? guestsEl.value : 1, 10) || 1);
        return `
        <div class="addon-item" id="addon-${a.id}" data-type="${escHtml(priceType)}">
            <div class="addon-check">
                <input type="checkbox" id="addonChk-${a.id}" value="${a.id}"
                       onchange="toggleAddon(${a.id},${a.price},this)">
            </div>
            <div class="addon-name">${escHtml(a.name)}</div>
            <input type="number" class="addon-qty" id="addonQty-${a.id}" min="1" max="${maxQty}" value="1" step="1"
                   style="display:none;width:4.5rem;text-align:center;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);border-radius:.5rem;padding:.25rem .35rem;"
                   title="Quantity for this add-on"
                   onclick="event.stopPropagation()"
                   oninput="recalcPrice()">
            <div class="addon-price">+${escHtml(a.starting_price)}</div>
        </div>`;
    }).join('');
}

function toggleAddon(id, price, chk) {
    const item = document.getElementById('addon-' + id);
    const qtyEl = document.getElementById('addonQty-' + id);
    const priceType = (item && item.dataset.type) || (addonsMap[id] && addonsMap[id].price_type) || 'flat_rate';
    if (chk.checked) {
        item.classList.add('selected');
        addonsMap[id] = { ...addonsMap[id], selected: true, price, price_type: priceType };
        if (qtyEl) qtyEl.style.display = (priceType === 'flat_rate') ? 'none' : 'inline-block';
    } else {
        item.classList.remove('selected');
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
        // Calculate from time range
        const tIn  = document.getElementById('timeIn').value;
        const tOut = document.getElementById('timeOut').value;
        if (tIn && tOut) {
            const mins = toMinutes(tOut) - toMinutes(tIn);
            if (mins > 0) {
                const hrs = mins / 60;
                base = selectedPkg.price * hrs * guests;
                baseLabel = `${selectedPkg.price_label} × ${hrs.toFixed(2)}h × ${guests} guest${guests > 1 ? 's' : ''}`;
            }
        } else {
            base = selectedPkg.price * guests; // show per-hour rate with guests
            baseLabel = `${selectedPkg.price_label} × ${guests} guest${guests > 1 ? 's' : ''} (per hour)`;
        }
    } else if (selectedPkg.package_type === 'bundle') {
        base = selectedPkg.price * guests;
        baseLabel = `${selectedPkg.price_label} × ${guests} guest${guests > 1 ? 's' : ''}`;
    } else {
        // Accommodation — nights
        const ci = document.getElementById('bookingDate').value;
        const co = document.getElementById('checkoutDate').value;
        if (ci && co) {
            const nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
            if (nights > 0) {
                base = selectedPkg.price * nights * guests;
                baseLabel = `${selectedPkg.price_label} × ${nights} night${nights>1?'s':''} × ${guests} guest${guests > 1 ? 's' : ''}`;
            }
        } else {
            base = selectedPkg.price * guests;
            baseLabel = `${selectedPkg.price_label} × ${guests} guest${guests > 1 ? 's' : ''}`;
        }
    }

    const addonsTotal = Object.values(addonsMap).filter(a => a.selected).reduce((sum, a) => {
        const type = a.price_type || 'flat_rate';
        if (type === 'flat_rate') return sum + Number(a.price);
        const qtyEl = document.getElementById('addonQty-' + a.id);
        let qty = Math.max(1, parseInt(qtyEl ? qtyEl.value : 1, 10) || 1);
        if (qtyEl) {
            qtyEl.max = guests;
            if (qty > guests) {
                qty = guests;
                qtyEl.value = guests;
            }
        }
        return sum + (Number(a.price) * qty);
    }, 0);
    const grand = base + addonsTotal;

    document.getElementById('basePriceLabel').textContent = baseLabel;
    document.getElementById('basePriceVal').textContent   = '₱' + base.toFixed(2);
    document.getElementById('addonsTotal').textContent    = '₱' + addonsTotal.toFixed(2);
    document.getElementById('grandTotal').textContent     = '₱' + grand.toFixed(2);
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
    if ((selectedPkg.package_type === 'accommodation' || selectedPkg.package_type === 'bundle') && !checkOut) {
        showError('Please select a check-out date.'); return;
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
    if (!file.type.startsWith('image/')) {
        showError('Please upload an image file (JPG, PNG, GIF).');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showError('File size exceeds 5MB limit.');
        return;
    }
    proofFile = file;

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

        const name = file.name.length > 18 ? file.name.substring(0, 15) + '...' : file.name;
        filenameEl.textContent = name;
        submitBtn.disabled = false;
    };
    reader.readAsDataURL(file);
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
    fd.append('full_name',        '<?php echo addslashes($userName); ?>');
    fd.append('email',            '<?php echo addslashes($userEmail); ?>');
    fd.append('phone_number',     '<?php echo addslashes($userPhone); ?>' || 'N/A');
    fd.append('check_in_date',    date);
    fd.append('check_out_date',   checkOut || '');
    fd.append('check_in_time',    tIn || '');
    fd.append('check_out_time',   tOut || '');
    fd.append('number_of_guests', guests);
    fd.append('special_requests', document.getElementById('specialRequests').value || '');
    fd.append('payment_proof',    proofFile, proofFile.name);

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
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('bookingDate').min = today;
    document.getElementById('bookingDate').value = today;
    document.getElementById('checkoutDate').min  = today;

    document.getElementById('bookingDate').addEventListener('change', function() {
        document.getElementById('checkoutDate').min = this.value;
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
</script>
</body>
</html>
