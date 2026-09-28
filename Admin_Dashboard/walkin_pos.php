<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

admin_include('db_config.php');
global $conn;

// ── Fetch catalog data ────────────────────────────────────────────────────────
$activities = [];
$res = $conn->query("SELECT id, name, slug, description, price_per_hour, min_duration_hours, max_duration_hours, capacity, image_url FROM activities WHERE is_active = 1 ORDER BY name ASC");
while ($r = $res->fetch_assoc()) $activities[] = $r;

$accommodations = [];
$res = $conn->query("SELECT id, name, type, tour_type, description, capacity, price_per_night, price_per_day, check_in_time, check_out_time, amenities, image_url FROM accommodations WHERE is_active = 1 ORDER BY type ASC, name ASC");
while ($r = $res->fetch_assoc()) $accommodations[] = $r;

$bundles = [];
$res = $conn->query("SELECT id, name, slug, description, price, capacity, check_in_time, check_out_time, amenities, image_url FROM bundles WHERE is_active = 1 ORDER BY name ASC");
while ($r = $res->fetch_assoc()) $bundles[] = $r;

$timeSlots = [];
$stmtSlots = $conn->query("SELECT id, activity_id, accommodation_id, label, start_time, end_time FROM time_slots WHERE is_active = 1 ORDER BY start_time ASC");
if ($stmtSlots) {
    while ($r = $stmtSlots->fetch_assoc()) $timeSlots[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-In — YP4K Resort</title>

    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="../css/admin-theme.css">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    <script src="components/admin-components.js" defer></script>

    <style>
        /* ── POS Layout ──────────────────────────────────────────────────────── */
        .pos-wrapper {
            display: flex;
            gap: 0;
            height: calc(100vh - var(--navbar-height, 68px));
            overflow: hidden;
        }

        /* ── Left Pane: Catalog ──────────────────────────────────────────────── */
        .pos-catalog {
            flex: 0 0 60%;
            max-width: 60%;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.07);
            overflow: hidden;
            background: rgba(8,18,14,0.6);
        }

        .pos-catalog-header {
            padding: 1.25rem 1.5rem 0;
            flex-shrink: 0;
        }

        .pos-catalog-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Category tabs */
        .cat-tabs {
            display: flex;
            gap: 0.4rem;
            margin-bottom: 0.9rem;
            flex-wrap: wrap;
        }

        .cat-tab {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            color: #8c9992;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.45rem 1rem;
            border-radius: 100px;
            cursor: pointer;
            transition: all 0.18s ease;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .cat-tab:hover {
            background: rgba(157,255,0,0.07);
            border-color: rgba(157,255,0,0.25);
            color: #9dff00;
        }
        .cat-tab.active {
            background: rgba(157,255,0,0.14);
            border-color: rgba(157,255,0,0.45);
            color: #9dff00;
            box-shadow: 0 0 14px rgba(157,255,0,0.12);
        }

        /* Search bar */
        .pos-search-wrap {
            position: relative;
            margin-bottom: 1rem;
        }
        .pos-search-wrap i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #5c6b63;
            font-size: 0.9rem;
            pointer-events: none;
        }
        .pos-search {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 10px;
            color: #fff;
            font-size: 0.875rem;
            padding: 0.6rem 1rem 0.6rem 2.3rem;
            outline: none;
            transition: border-color 0.18s;
            font-family: inherit;
        }
        .pos-search::placeholder { color: #5c6b63; }
        .pos-search:focus { border-color: rgba(157,255,0,0.35); }

        /* Card grid */
        .pos-cards-area {
            flex: 1;
            overflow-y: auto;
            padding: 0 1.5rem 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(157,255,0,0.2) transparent;
        }

        .pos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 1rem;
            align-items: stretch;
        }

        /* Item card */
        .pos-card {
            background: rgba(21,34,29,0.8);
            border: 1.5px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            user-select: none;
        }
        .pos-card:hover {
            transform: translateY(-3px);
            border-color: rgba(157,255,0,0.3);
            box-shadow: 0 8px 28px rgba(0,0,0,0.45), 0 0 20px rgba(157,255,0,0.06);
        }
        .pos-card.selected {
            border-color: #9dff00;
            box-shadow: 0 0 0 2px rgba(157,255,0,0.25), 0 8px 30px rgba(0,0,0,0.5);
        }
        .pos-card.selected::after {
            content: '\F26E'; /* bi-check-circle-fill */
            font-family: 'Bootstrap-icons';
            position: absolute;
            top: 0.5rem;
            right: 0.6rem;
            color: #9dff00;
            font-size: 1.1rem;
            text-shadow: 0 0 8px rgba(157,255,0,0.6);
        }

        .pos-card-img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            display: block;
            background: rgba(0,0,0,0.3);
        }
        .pos-card-img-placeholder {
            width: 100%;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(157,255,0,0.05);
            font-size: 2rem;
            color: rgba(157,255,0,0.3);
        }

        .pos-card-body {
            padding: 0.8rem 0.9rem;
        }
        .pos-card-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.2rem;
            line-height: 1.3;
        }
        .pos-card-type-badge {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 100px;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-room        { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .badge-cottage     { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-day-tour    { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-overnight   { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .badge-activity    { background: rgba(16,185,129,0.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
        .badge-bundle      { background: rgba(236,72,153,0.15); color: #f9a8d4; border: 1px solid rgba(236,72,153,0.3); }
        .badge-stay-in     { background: rgba(139,92,246,0.15); color: #c4b5fd; border: 1px solid rgba(139,92,246,0.3); }

        .pos-card-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: #9dff00;
            margin-bottom: 0.2rem;
        }
        .pos-card-price span {
            font-size: 0.72rem;
            font-weight: 500;
            color: #5c6b63;
        }
        .pos-card-cap {
            font-size: 0.75rem;
            color: #8c9992;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .pos-card-desc {
            font-size: 0.73rem;
            color: #6b7c74;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pos-card-footer {
            padding: 0.55rem 0.9rem 0.8rem;
        }
        .pos-select-btn {
            width: 100%;
            background: rgba(157,255,0,0.09);
            border: 1px solid rgba(157,255,0,0.25);
            color: #9dff00;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.45rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.18s;
            font-family: inherit;
            letter-spacing: 0.02em;
        }
        .pos-select-btn:hover,
        .pos-card.selected .pos-select-btn {
            background: rgba(157,255,0,0.18);
            border-color: rgba(157,255,0,0.55);
        }

        .pos-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
            color: #5c6b63;
        }
        .pos-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.6rem; }

        /* ── Right Pane: Billing ─────────────────────────────────────────────── */
        .pos-billing {
            flex: 0 0 40%;
            max-width: 40%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: rgba(10,20,15,0.7);
        }

        .pos-billing-header {
            padding: 1.25rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .pos-billing-header h2 {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pos-billing-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(157,255,0,0.2) transparent;
        }

        /* Selected item preview */
        .selected-preview {
            background: rgba(157,255,0,0.06);
            border: 1px solid rgba(157,255,0,0.2);
            border-radius: 12px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.25rem;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all 0.25s;
        }
        .selected-preview.empty {
            align-items: center;
            border-style: dashed;
            background: rgba(255,255,255,0.02);
            border-color: rgba(255,255,255,0.1);
        }
        .selected-preview.empty p {
            color: #5c6b63;
            font-size: 0.82rem;
            margin: 0;
            text-align: center;
        }
        .preview-name {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.25rem;
        }
        .preview-meta {
            font-size: 0.8rem;
            color: #8c9992;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .preview-meta span { display: flex; align-items: center; gap: 0.25rem; }
        .preview-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #9dff00;
            margin-top: 0.4rem;
        }

        /* Form fields */
        .pos-field-group {
            margin-bottom: 1rem;
        }
        .pos-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #8c9992;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }
        .pos-input,
        .pos-select,
        .pos-textarea {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 9px;
            color: #fff;
            font-size: 0.875rem;
            padding: 0.6rem 0.9rem;
            font-family: inherit;
            transition: border-color 0.18s;
            outline: none;
        }
        .pos-select { cursor: pointer; }
        .pos-select option {
            background-color: #08120e;
            color: #fff;
        }
        .pos-textarea { resize: vertical; min-height: 72px; }
        .pos-input::placeholder, .pos-textarea::placeholder { color: #5c6b63; }
        .pos-input:focus, .pos-select:focus, .pos-textarea:focus {
            border-color: rgba(157,255,0,0.4);
            background: rgba(157,255,0,0.03);
        }
        .pos-input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
        }

        .pos-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #5c6b63;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 1.1rem 0 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .pos-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.06);
        }

        /* Billing total strip */
        .pos-total-strip {
            background: rgba(157,255,0,0.07);
            border: 1px solid rgba(157,255,0,0.18);
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pos-total-label {
            font-size: 0.8rem;
            color: #8c9992;
            font-weight: 600;
        }
        .pos-total-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #9dff00;
            letter-spacing: -0.02em;
        }

        /* Alert strip */
        .pos-alert {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            display: none;
        }
        .pos-alert.show { display: flex; }
        .pos-alert.danger {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
        }
        .pos-alert.success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.3);
            color: #34d399;
        }

        /* Confirm button */
        .pos-billing-footer {
            padding: 1rem 1.5rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .pos-confirm-btn {
            width: 100%;
            background: linear-gradient(135deg, #9dff00, #6edc45);
            border: none;
            border-radius: 12px;
            color: #08120e;
            font-size: 1rem;
            font-weight: 800;
            padding: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .pos-confirm-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(157,255,0,0.35);
        }
        .pos-confirm-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pos-back-link {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #6b7c74;
            font-size: 0.8rem;
            text-decoration: none;
            margin-top: 0.6rem;
            justify-content: center;
            transition: color 0.18s;
        }
        .pos-back-link:hover { color: #9dff00; }

        /* Confirmation overlay */
        .pos-success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(8,18,14,0.92);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            padding: 2rem;
        }
        .pos-success-overlay.show { display: flex; }
        .pos-success-icon {
            font-size: 4.5rem;
            color: #9dff00;
            margin-bottom: 1rem;
            animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes popIn {
            from { transform: scale(0.3); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .pos-success-ref {
            font-size: 2rem;
            font-weight: 900;
            color: #9dff00;
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }
        .pos-success-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.3rem;
        }
        .pos-success-detail {
            font-size: 0.9rem;
            color: #8c9992;
            margin-bottom: 2rem;
        }
        .pos-success-actions { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; }
        .pos-success-actions a,
        .pos-success-actions button {
            padding: 0.7rem 1.75rem;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            border: none;
            transition: all 0.18s;
        }
        .btn-new-walkin {
            background: linear-gradient(135deg,#9dff00,#6edc45);
            color: #08120e;
        }
        .btn-go-bookings {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12) !important;
            color: #fff;
        }
        .btn-go-bookings:hover { background: rgba(255,255,255,0.12); }

        /* Scrollbar polish */
        .pos-cards-area::-webkit-scrollbar,
        .pos-billing-body::-webkit-scrollbar { width: 5px; }
        .pos-cards-area::-webkit-scrollbar-track,
        .pos-billing-body::-webkit-scrollbar-track { background: transparent; }
        .pos-cards-area::-webkit-scrollbar-thumb,
        .pos-billing-body::-webkit-scrollbar-thumb {
            background: rgba(157,255,0,0.2);
            border-radius: 4px;
        }

        /* Override main-content padding for POS */
        #mainContent { padding: 0 !important; }

        @media (max-width: 900px) {
            .pos-wrapper { flex-direction: column; height: auto; overflow: visible; }
            .pos-catalog, .pos-billing { flex: none; max-width: 100%; width: 100%; }
            .pos-catalog { height: 55vh; }
            .pos-billing { height: auto; }
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
        <div class="pos-wrapper">

            <!-- ══════════ LEFT PANE — Catalog ══════════ -->
            <div class="pos-catalog">
                <div class="pos-catalog-header">
                    <h2>
                        <i class="bi bi-grid-3x3-gap-fill" style="color:#9dff00;"></i>
                        Resort Catalog
                    </h2>

                    <!-- Category tabs -->
                    <div class="cat-tabs" id="catTabs">
                        <button class="cat-tab active" data-cat="all"
                                onclick="filterCat('all',this)">
                            <i class="bi bi-layout-three-columns me-1"></i>All
                        </button>
                        <button class="cat-tab" data-cat="bundle"
                                onclick="filterCat('bundle',this)">
                            <i class="bi bi-box-seam me-1"></i>Bundles
                        </button>
                        <button class="cat-tab" data-cat="day_tour"
                                onclick="filterCat('day_tour',this)">
                            <i class="bi bi-sun me-1"></i>Day Tour Packages
                        </button>
                        <button class="cat-tab" data-cat="overnight"
                                onclick="filterCat('overnight',this)">
                            <i class="bi bi-moon-stars me-1"></i>Overnight Packages
                        </button>
                        <button class="cat-tab" data-cat="activity"
                                onclick="filterCat('activity',this)">
                            <i class="bi bi-lightning-charge me-1"></i>Activities
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="pos-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="pos-search" id="catalogSearch"
                               placeholder="Search rooms, activities, bundles…"
                               oninput="searchCatalog(this.value)">
                    </div>
                </div>

                <div class="pos-cards-area" id="posCardsArea">
                    <div class="pos-grid" id="posGrid">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div><!-- /pos-catalog -->

            <!-- ══════════ RIGHT PANE — Billing ══════════ -->
            <div class="pos-billing">
                <div class="pos-billing-header">
                    <h2>
                        <i class="bi bi-person-walking" style="color:#9dff00;"></i>
                        Walk-In
                    </h2>
                </div>

                <div class="pos-billing-body">

                    <!-- Selected item preview -->
                    <div class="selected-preview empty" id="selectedPreview">
                        <p><i class="bi bi-hand-index-thumb me-1"></i>
                            Tap a card on the left to select an item
                        </p>
                    </div>

                    <!-- Alert area -->
                    <div class="pos-alert" id="posAlert"></div>

                    <!-- ── Guest Details ── -->
                    <div class="pos-section-label">
                        <i class="bi bi-person-fill"></i> Guest Details
                    </div>

                    <div class="pos-field-group">
                        <label class="pos-label" for="bi_guest_name">
                            Full Name <span style="color:#ff5a5f;">*</span>
                        </label>
                        <input type="text" id="bi_guest_name" class="pos-input"
                               placeholder="e.g. Juan dela Cruz" maxlength="255">
                    </div>

                    <div class="pos-field-group">
                        <label class="pos-label" for="bi_guest_contact">
                            Contact Number <span style="color:#ff5a5f;">*</span>
                        </label>
                        <input type="tel" id="bi_guest_contact" class="pos-input"
                               placeholder="09XXXXXXXXX" maxlength="11"
                               oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>

                    <!-- ── Scheduling ── -->
                    <div class="pos-section-label">
                        <i class="bi bi-calendar3"></i> Scheduling
                    </div>

                    <div class="pos-input-row pos-field-group">
                        <div>
                            <label class="pos-label" for="bi_checkin_date">Check-In Date <span class="text-danger">*</span></label>
                            <input type="date" id="bi_checkin_date" class="pos-input" required min="<?php echo date('Y-m-d'); ?>" onchange="handleDateChange()">
                        </div>
                        <div>
                            <label class="pos-label" for="bi_checkout_date">Check-Out Date</label>
                            <input type="date" id="bi_checkout_date" class="pos-input" onchange="recalcTotal()">
                        </div>
                    </div>
                    
                    <div class="pos-input-row pos-field-group" id="overnightTimeSection" style="display:none;">
                        <div>
                            <label class="pos-label" for="bi_overnight_checkin"><i class="bi bi-clock me-1"></i>Check-In Time</label>
                            <input type="time" id="bi_overnight_checkin" class="pos-input" readonly style="background: rgba(255,255,255,0.05); color: #8c9992; cursor: not-allowed;">
                        </div>
                        <div>
                            <label class="pos-label" for="bi_overnight_checkout"><i class="bi bi-clock-history me-1"></i>Check-Out Time</label>
                            <input type="time" id="bi_overnight_checkout" class="pos-input" readonly style="background: rgba(255,255,255,0.05); color: #8c9992; cursor: not-allowed;">
                        </div>
                    </div>
                    
                    <div class="pos-field-group" id="timeSlotContainer" style="display:none;">
                        <label class="pos-label" for="bi_time_slot">Time Slot</label>
                        <select id="bi_time_slot" class="pos-select" onchange="handleTimeSlotChange()">
                            <option value="">-- Select Time Slot --</option>
                        </select>
                    </div>

                    <div class="pos-input-row pos-field-group" id="customTimeContainer" style="display:none;">
                        <div>
                            <label class="pos-label" for="bi_checkin_time">Start Time</label>
                            <input type="time" id="bi_checkin_time" class="pos-input" onchange="recalcTotal()">
                        </div>
                        <div>
                            <label class="pos-label" for="bi_checkout_time">End Time</label>
                            <input type="time" id="bi_checkout_time" class="pos-input" onchange="recalcTotal()">
                        </div>
                    </div>

                    <div class="pos-input-row pos-field-group" style="align-items: flex-end;">
                        <div>
                            <label class="pos-label" for="bi_num_guests">Guests / Pax</label>
                            <input type="number" id="bi_num_guests" class="pos-input"
                                   value="1" min="1" max="200" oninput="if(this.value && parseInt(this.value) > parseInt(this.max)) this.value = this.max; recalcTotal();" onchange="recalcTotal()">
                        </div>
                        <div>
                            <button type="button" class="btn w-100" id="btnAddMoreRoom" onclick="startAddingRoom()" style="display:none; padding: 0.6rem 0.9rem; background:rgba(157, 255, 0, 0.15); border:1px solid #9dff00; color:#9dff00; border-radius: 9px; font-weight: 600;"><i class="bi bi-plus-circle me-1"></i>Add More Room</button>
                        </div>
                    </div>
                    <div id="extraRoomsContainer" style="margin-bottom: 1rem;"></div>
                    
                    <div id="addonsContainer" style="display:none; margin-bottom: 1rem;">
                        <div class="pos-section-label" style="font-size: 0.8rem;">
                            <i class="bi bi-plus-circle"></i> Add-on Activities (Optional)
                        </div>
                        <div id="addonsList" style="display: flex; flex-direction: column; gap: 0.4rem;"></div>
                    </div>

                    <!-- ── Billing ── -->
                    <div class="pos-section-label">
                        <i class="bi bi-cash-stack"></i> Billing & Payment
                    </div>

                    <!-- Payment method removed -->

                    <div class="pos-total-strip">
                        <span class="pos-total-label">
                            <i class="bi bi-receipt me-1"></i>Estimated Total
                        </span>
                        <span class="pos-total-amount" id="posTotal">₱0.00</span>
                    </div>

                    <div class="pos-field-group">
                        <label class="pos-label" for="bi_special_requests">Special Requests / Notes</label>
                        <textarea id="bi_special_requests" class="pos-textarea"
                                  placeholder="Wheelchair access, extra bedding, etc."></textarea>
                    </div>

                </div><!-- /pos-billing-body -->

                <div class="pos-billing-footer">
                    <button class="pos-confirm-btn" id="posConfirmBtn" onclick="submitWalkIn()">
                        <i class="bi bi-check-circle-fill"></i>
                        Confirm &amp; Check-In
                    </button>
                    <a href="bookings.php" class="pos-back-link">
                        <i class="bi bi-arrow-left"></i> Back to Bookings
                    </a>
                </div>
            </div><!-- /pos-billing -->

        </div><!-- /pos-wrapper -->
    </div><!-- /mainContent -->

    <!-- ══════════ Walk-In Confirmation Modal ══════════ -->
    <div class="modal fade" id="walkinConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--pos-bg-dark); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(255,255,255,0.02);">
                    <h5 class="modal-title" style="color: var(--pos-text-light); font-weight: 600;"><i class="bi bi-receipt me-2"></i>Booking Summary</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="walkinConfirmBody" style="color: var(--pos-text-light); padding: 1.5rem;">
                    <!-- Content populated by JS -->
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.2);">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <button type="button" class="btn" id="posFinalConfirmBtn" onclick="executeSubmitWalkIn()" style="background: var(--accent-success); color: #000; font-weight: 600; border-radius: 6px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ Success Overlay ══════════ -->
    <div class="pos-success-overlay" id="successOverlay">
        <div class="pos-success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="pos-success-ref" id="successRef">WI-2026-XXXXXX</div>
        <div class="pos-success-name" id="successName"></div>
        <div class="pos-success-detail" id="successDetail"></div>
        <div class="pos-success-actions">
            <button class="btn-new-walkin" onclick="resetPOS()">
                <i class="bi bi-plus-circle me-1"></i>New Walk-In
            </button>
            <a href="bookings.php" class="btn-go-bookings">
                <i class="bi bi-list-check me-1"></i>View Bookings
            </a>
        </div>
    </div>

    <!-- ══════════ Details Modal ══════════ -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content" style="background: rgba(18, 30, 24, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(157,255,0,0.2); border-top: 4px solid #9dff00; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.5), 0 0 20px rgba(157,255,0,0.05);">
                <div class="modal-header border-0 pb-1 px-4 pt-4">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2" style="font-weight: 700; letter-spacing: 0.02em; font-size: 1.15rem;">
                        <i class="bi bi-stars" style="color: #9dff00; font-size: 1.1rem;"></i>
                        <span id="itemDetailsTitle"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <p id="itemDetailsDesc" style="color:#a8b8b0; font-size: 0.92rem; line-height: 1.6; margin: 0;"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── PHP → JS Data ─────────────────────────────────────────────────────────
    const PHP_ACTIVITIES     = <?= json_encode($activities,     JSON_HEX_TAG) ?>;
    const PHP_ACCOMMODATIONS = <?= json_encode($accommodations, JSON_HEX_TAG) ?>;
    const PHP_BUNDLES        = <?= json_encode($bundles,        JSON_HEX_TAG) ?>;
    const PHP_TIME_SLOTS     = <?= json_encode($timeSlots,      JSON_HEX_TAG) ?>;

    // ── Build unified catalog ─────────────────────────────────────────────────
    const CATALOG = [];

    PHP_ACCOMMODATIONS.forEach(a => {
        const tourType = (a.tour_type || '').toLowerCase();
        const accType  = (a.type || '').toLowerCase();
        const isDayTour   = tourType.includes('day_tour') || accType === 'cottage';
        const isOvernight = tourType.includes('overnight') || accType === 'room' || accType === 'stay-in';

        const pricePerNight = parseFloat(a.price_per_night) || 0;
        const pricePerDay   = parseFloat(a.price_per_day) || 0;

        let primaryPrice = 0;
        let priceLabel = '';
        let badgeClass = 'badge-room';
        let badgeLabel = 'Accommodation';

        if (isDayTour && !isOvernight) {
            primaryPrice = pricePerDay > 0 ? pricePerDay : pricePerNight;
            priceLabel   = '/ day';
            badgeClass   = 'badge-day-tour';
            badgeLabel   = 'Day Tour';
        } else if (isOvernight && !isDayTour) {
            primaryPrice = pricePerNight > 0 ? pricePerNight : pricePerDay;
            priceLabel   = '/ night';
            badgeClass   = 'badge-overnight';
            badgeLabel   = 'Overnight';
        } else {
            // Supports both Day Tour and Overnight
            primaryPrice = pricePerNight > 0 ? pricePerNight : pricePerDay;
            priceLabel   = pricePerDay > 0 ? `/ night · ₱${pricePerDay.toLocaleString('en-PH')}/day` : '/ night';
            badgeClass   = 'badge-overnight';
            badgeLabel   = 'Day & Overnight';
        }

        CATALOG.push({
            id:              parseInt(a.id, 10),
            type:            a.type,       // 'room' | 'cottage' | 'stay-in'
            tour_type:       a.tour_type || '',
            is_day_tour:     isDayTour,
            is_overnight:    isOvernight,
            price_per_day:   pricePerDay,
            price_per_night: pricePerNight,
            booking_type:    'accommodation',
            badge_class:     badgeClass,
            badge_label:     badgeLabel,
            name:            a.name,
            description:     a.description || '',
            capacity:        parseInt(a.capacity, 10) || 1,
            price:           primaryPrice,
            price_label:     priceLabel,
            check_in_time:   a.check_in_time || null,
            check_out_time:  a.check_out_time || null,
            amenities:       a.amenities   || '',
            image:           a.image_url   || '',
        });
    });

    PHP_ACTIVITIES.forEach(a => {
        CATALOG.push({
            id:          parseInt(a.id, 10),
            type:        'activity',
            booking_type:'activity',
            is_day_tour: false,
            is_overnight:false,
            badge_class: 'badge-activity',
            badge_label: 'Activity',
            name:        a.name,
            description: a.description || '',
            capacity:    parseInt(a.capacity, 10) || 1,
            price:       parseFloat(a.price_per_hour) || 0,
            price_label: '/ hr',
            min_hrs:     parseFloat(a.min_duration_hours) || 1,
            max_hrs:     parseFloat(a.max_duration_hours) || 4,
            amenities:   '',
            image:       a.image_url   || '',
        });
    });

    PHP_BUNDLES.forEach(b => {
        CATALOG.push({
            id:          parseInt(b.id, 10),
            type:        'bundle',
            booking_type:'bundle',
            is_day_tour: false,
            is_overnight:false,
            badge_class: 'badge-bundle',
            badge_label: 'Bundle',
            name:        b.name,
            description: b.description || '',
            capacity:    parseInt(b.capacity, 10) || 1,
            price:       parseFloat(b.price) || 0,
            price_label: 'flat rate',
            check_in_time: b.check_in_time || null,
            check_out_time: b.check_out_time || null,
            amenities:   b.amenities   || '',
            image:       b.image_url   || '',
        });
    });

    // ── State ─────────────────────────────────────────────────────────────────
    let currentCat    = 'all';
    let currentSearch = '';
    let selectedItem  = null;
    let extraRooms = [];
    let isAddingExtraRoom = false;
    let AVAILABILITY_MAP = null;
    let walkinAddonsMap = {};

    async function loadAllAvailability(date) {
        if (!date) return;
        try {
            const res = await fetch(`../php/get_all_availability.php?date=${date}`);
            const result = await res.json();
            if (result.success) {
                AVAILABILITY_MAP = result.data;
                renderCatalog();
            }
        } catch (e) {
            console.error('Failed to load availability map', e);
        }
    }

    // ── Type badges / icons ───────────────────────────────────────────────────
    const TYPE_META = {
        room:            { badge:'badge-overnight', label:'Room',           icon:'bi-house-door' },
        cottage:         { badge:'badge-day-tour',  label:'Cottage',        icon:'bi-tree' },
        'room,cottage':  { badge:'badge-overnight', label:'Room & Cottage', icon:'bi-buildings' },
        'cottage,room':  { badge:'badge-overnight', label:'Room & Cottage', icon:'bi-buildings' },
        'stay-in':       { badge:'badge-stay-in',   label:'Stay-In',        icon:'bi-moon-stars' },
        activity:        { badge:'badge-activity',  label:'Activity',       icon:'bi-lightning-charge' },
        bundle:          { badge:'badge-bundle',    label:'Bundle',         icon:'bi-box-seam' },
    };

    // ── Render catalog grid ───────────────────────────────────────────────────
    function renderCatalog() {
        const grid = document.getElementById('posGrid');
        const term = currentSearch.toLowerCase();
        
        const tabs = document.querySelectorAll('.cat-tab');
        if (selectedItem) {
            const isDayTour = selectedItem.is_day_tour && !selectedItem.is_overnight;
            const isOvernight = selectedItem.is_overnight;
            const isBundle = selectedItem.booking_type === 'bundle';
            
            tabs.forEach(t => {
                const cat = t.getAttribute('data-cat');
                if (cat === 'all') return;
                
                if (isBundle) {
                    if (cat === 'day_tour' || cat === 'overnight' || cat === 'activity') t.style.display = 'none';
                    else t.style.display = 'inline-block';
                } else if (isOvernight) {
                    if (cat === 'day_tour' || cat === 'bundle') t.style.display = 'none';
                    else t.style.display = 'inline-block';
                } else if (isDayTour) {
                    if (cat === 'overnight' || cat === 'bundle') t.style.display = 'none';
                    else t.style.display = 'inline-block';
                }
            });
            
            const activeTab = document.querySelector('.cat-tab[data-cat="' + currentCat + '"]');
            if (activeTab && activeTab.style.display === 'none') {
                filterCat('all', document.querySelector('.cat-tab[data-cat="all"]'));
                return;
            }
        } else {
            tabs.forEach(t => t.style.display = 'inline-block');
        }

        const filtered = CATALOG.filter(item => {
            if (selectedItem) {
                const isDayTour = selectedItem.is_day_tour && !selectedItem.is_overnight;
                const isOvernight = selectedItem.is_overnight;
                const isBundle = selectedItem.booking_type === 'bundle';
                
                if (isBundle) {
                    if (item.booking_type !== 'bundle') return false;
                } else if (isOvernight) {
                    if (item.booking_type === 'bundle') return false;
                    if (item.booking_type === 'accommodation' && !item.is_overnight) return false;
                } else if (isDayTour) {
                    if (item.booking_type === 'bundle') return false;
                    if (item.booking_type === 'accommodation' && !item.is_day_tour) return false;
                }
            }
            
            let catMatch = false;
            if (currentCat === 'all') {
                catMatch = true;
            } else if (currentCat === 'bundle') {
                catMatch = (item.booking_type === 'bundle');
            } else if (currentCat === 'day_tour') {
                catMatch = (item.is_day_tour === true);
            } else if (currentCat === 'overnight') {
                catMatch = (item.is_overnight === true);
            } else if (currentCat === 'activity') {
                catMatch = (item.booking_type === 'activity');
            }

            const nameMatch = item.name.toLowerCase().includes(term)
                || (item.description || '').toLowerCase().includes(term);
            return catMatch && nameMatch;
        });

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="pos-empty">
                    <i class="bi bi-search"></i>
                    <p>No items found</p>
                </div>`;
            return;
        }

        grid.innerHTML = filtered.map(item => {
            const meta  = TYPE_META[item.type] || { badge:item.badge_class || 'badge-activity', label:item.badge_label || item.type, icon:'bi-grid' };
            const cardBadgeClass = item.badge_class || meta.badge;
            const cardBadgeLabel = item.badge_label || meta.label;
            const isSelected = selectedItem && Number(selectedItem.id) === Number(item.id) && selectedItem.booking_type === item.booking_type;
            const priceStr   = '₱' + item.price.toLocaleString('en-PH', {minimumFractionDigits:2}) + ' <span>' + item.price_label + '</span>';
            const imgHtml    = item.image
                ? `<img class="pos-card-img" src="../${escH(item.image)}" alt="${escH(item.name)}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=pos-card-img-placeholder><i class=bi-${escH(meta.icon)}></i></div>'">`
                : `<div class="pos-card-img-placeholder"><i class="bi ${escH(meta.icon)}"></i></div>`;
                
            let availText = '';
            if (AVAILABILITY_MAP && AVAILABILITY_MAP[item.booking_type] && AVAILABILITY_MAP[item.booking_type][item.id]) {
                const info = AVAILABILITY_MAP[item.booking_type][item.id];
                const available = Math.max(0, info.capacity - info.booked);
                
                let availColor = '#9dff00';
                if (available === 0) availColor = '#f87171'; // Red
                else if (available <= 2) availColor = '#fbbf24'; // Yellow
                
                availText = `<div class="pos-card-avail" style="font-size:0.8rem; font-weight:600; color:${availColor}; margin-top:0.4rem; background:rgba(0,0,0,0.2); padding:0.2rem 0.5rem; border-radius:4px; display:inline-block;"><i class="bi bi-clock-history me-1"></i>Available: ${available}/${info.capacity}</div>`;
            }

            return `
            <div class="pos-card${isSelected ? ' selected' : ''} h-100 d-flex flex-column"
                 id="card_${item.booking_type}_${item.id}"
                 onclick="selectItem(${item.id},'${item.booking_type}')">
                ${imgHtml}
                <div class="pos-card-body d-flex flex-column flex-grow-1">
                    <div class="pos-card-name">${escH(item.name)}</div>
                    <span class="pos-card-type-badge ${cardBadgeClass}">${cardBadgeLabel}</span>
                    <div class="pos-card-price">${priceStr}</div>
                    <div class="pos-card-cap">
                        <i class="bi bi-people-fill"></i>
                        ${item.capacity} Pax${item.capacity >= 50 ? '+' : ''}
                    </div>
                    ${availText}
                </div>
                <div class="pos-card-footer mt-auto">
                    <button type="button" class="btn btn-sm w-100 mb-2" style="background: rgba(255,255,255,0.05); color: #8c9992; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; font-size: 0.75rem;" onclick="event.stopPropagation(); showItemDetails(${item.id},'${item.booking_type}')">
                        <i class="bi bi-info-circle me-1"></i>View Details
                    </button>
                    <button type="button" class="pos-select-btn" onclick="event.stopPropagation(); selectItem(${item.id},'${item.booking_type}');">
                        <i class="bi bi-${isSelected ? 'check-circle-fill' : 'plus-circle'} me-1"></i>
                        ${isSelected ? 'Selected' : 'Select Item'}
                    </button>
                </div>
            </div>`;
        }).join('');
    }

    // ── Filter by category tab ────────────────────────────────────────────────
    function filterCat(cat, btn) {
        currentCat = cat;
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        renderCatalog();
    }

    // ── Search ────────────────────────────────────────────────────────────────
    function searchCatalog(val) {
        currentSearch = val;
        renderCatalog();
    }

    // ── Select item ───────────────────────────────────────────────────────────
    function selectItem(id, btype) {
        if (isAddingExtraRoom) {
            const pkg = CATALOG.find(i => Number(i.id) === Number(id) && i.booking_type === btype);
            if (!pkg) return;
            if (pkg.booking_type !== 'accommodation') {
                showAlert('You can only add accommodations as extra rooms.', 'danger');
                isAddingExtraRoom = false;
                renderCatalog();
                return;
            }
            const primaryGuests = parseInt(document.getElementById('bi_num_guests').value) || 1;
            const roomEntry = {
                index: extraRooms.length + 2,
                pkg: pkg,
                guests: primaryGuests
            };
            extraRooms.push(roomEntry);
            isAddingExtraRoom = false;
            renderExtraRooms();
            recalcTotal();
            renderCatalog();
            return;
        }

        const newlySelected = CATALOG.find(i => Number(i.id) === Number(id) && i.booking_type === btype) || null;
        
        if (selectedItem && newlySelected && selectedItem.id === newlySelected.id && selectedItem.booking_type === newlySelected.booking_type) {
            selectedItem = null; // Toggle off
        } else {
            selectedItem = newlySelected;
        }
        
        if (!selectedItem || selectedItem.booking_type !== 'accommodation') {
             extraRooms = [];
             renderExtraRooms();
        }
        
        renderWalkinAddons();
        renderCatalog();          // re-render to update selected state
        updatePreview();
        recalcTotal();
        
        // Update guest capacity max
        if (selectedItem) {
            const guestInput = document.getElementById('bi_num_guests');
            if (guestInput) {
                guestInput.max = selectedItem.capacity;
                if (parseInt(guestInput.value) > selectedItem.capacity) {
                    guestInput.value = selectedItem.capacity;
                }
            }
            
            // Show or hide Add More Room button
            const addRoomBtn = document.getElementById('btnAddMoreRoom');
            if (addRoomBtn) {
                if (selectedItem.booking_type === 'accommodation') {
                    addRoomBtn.style.display = 'block';
                } else {
                    addRoomBtn.style.display = 'none';
                    extraRooms = [];
                    renderExtraRooms();
                }
            }
            
            // Time Slots handling
            const tsSelect = document.getElementById('bi_time_slot');
            const tsContainer = document.getElementById('timeSlotContainer');
            const customTimeContainer = document.getElementById('customTimeContainer');
            
            tsSelect.innerHTML = '<option value="">-- Select Time Slot --</option>';
            let slots = [];
            
            if (selectedItem.booking_type === 'activity') {
                slots = PHP_TIME_SLOTS.filter(s => parseInt(s.activity_id) === selectedItem.id);
            } else if (selectedItem.booking_type === 'accommodation') {
                slots = PHP_TIME_SLOTS.filter(s => parseInt(s.accommodation_id) === selectedItem.id);
            }
            
            if (slots.length > 0) {
                slots.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.dataset.in = s.start_time;
                    opt.dataset.out = s.end_time;
                    
                    const formatTime = (t) => {
                        if(!t) return '';
                        let [h,m] = t.split(':');
                        h = parseInt(h);
                        let ampm = h >= 12 ? 'PM' : 'AM';
                        h = h % 12 || 12;
                        return `${h}:${m} ${ampm}`;
                    };
                    
                    opt.textContent = `${s.label} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})`;
                    tsSelect.appendChild(opt);
                });
                tsContainer.style.display = 'block';
                customTimeContainer.style.display = 'none';
                document.getElementById('overnightTimeSection').style.display = 'none';
            } else {
                tsContainer.style.display = 'none';
                if (selectedItem.is_overnight) {
                    customTimeContainer.style.display = 'none';
                    document.getElementById('overnightTimeSection').style.display = 'flex';
                    const cIn = document.getElementById('bi_overnight_checkin');
                    const cOut = document.getElementById('bi_overnight_checkout');
                    if(selectedItem.check_in_time) cIn.value = selectedItem.check_in_time.substring(0,5);
                    if(selectedItem.check_out_time) cOut.value = selectedItem.check_out_time.substring(0,5);
                    document.getElementById('bi_checkin_time').value = selectedItem.check_in_time || '14:00';
                    document.getElementById('bi_checkout_time').value = selectedItem.check_out_time || '12:00';
                } else {
                    document.getElementById('overnightTimeSection').style.display = 'none';
                    customTimeContainer.style.display = 'flex';
                }
            }
        }
        
        handleDateChange();
        // Scroll billing pane to top
        document.getElementById('posAlert').className = 'pos-alert';
    }

    // ── Update right-pane preview ─────────────────────────────────────────────
    function updatePreview() {
        const el = document.getElementById('selectedPreview');
        if (!selectedItem) {
            el.className = 'selected-preview empty';
            el.innerHTML = `<p><i class="bi bi-hand-index-thumb me-1"></i>Tap a card on the left to select an item</p>`;
            return;
        }
        const meta = TYPE_META[selectedItem.type] || {};
        el.className = 'selected-preview';
        el.innerHTML = `
            <div class="preview-name">${escH(selectedItem.name)}</div>
            <div class="preview-meta">
                <span><i class="bi bi-people-fill"></i>${selectedItem.capacity} Pax</span>
                ${selectedItem.amenities ? `<span><i class="bi bi-card-checklist"></i>${escH(selectedItem.amenities.slice(0,40))}…</span>` : ''}
            </div>
            <div class="preview-price">₱${selectedItem.price.toLocaleString('en-PH',{minimumFractionDigits:2})} <small style="font-size:.7em;color:#5c6b63;font-weight:500;">${selectedItem.price_label}</small></div>
            ${selectedItem.description ? `<p style="font-size:.78rem;color:#6b7c74;margin:.35rem 0 0;line-height:1.4;">${escH(selectedItem.description.slice(0,120))}${selectedItem.description.length > 120 ? '…' : ''}</p>` : ''}
        `;
    }

    // ── Handle Date Change ────────────────────────────────────────────────────
    function handleDateChange() {
        if (!selectedItem) { recalcTotal(); return; }
        
        const ciEl = document.getElementById('bi_checkin_date');
        const coEl = document.getElementById('bi_checkout_date');
        
        if (selectedItem.is_day_tour && !selectedItem.is_overnight) {
            coEl.value = ciEl.value;
            coEl.setAttribute('readonly', true);
            coEl.style.backgroundColor = 'rgba(255,255,255,0.02)';
            coEl.style.color = '#5c6b63';
        } else {
            coEl.removeAttribute('readonly');
            coEl.style.backgroundColor = '';
            coEl.style.color = '';
            if (coEl.value && ciEl.value && new Date(coEl.value) < new Date(ciEl.value)) {
                coEl.value = ciEl.value;
            }
        }
        
        recalcTotal();
    }
    
    function handleTimeSlotChange() {
        const sel = document.getElementById('bi_time_slot');
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('bi_checkin_time').value = opt.dataset.in || '';
            document.getElementById('bi_checkout_time').value = opt.dataset.out || '';
        }
        recalcTotal();
    }

    // ── Dynamic total calculation ─────────────────────────────────────────────
    function recalcTotal() {
        if (!selectedItem) { document.getElementById('posTotal').textContent = '₱0.00'; return; }

        const ciDate = document.getElementById('bi_checkin_date').value;
        const coDate = document.getElementById('bi_checkout_date').value;
        const ciTime = document.getElementById('bi_checkin_time').value;
        const coTime = document.getElementById('bi_checkout_time').value;

        let total = 0;
        const btype = selectedItem.booking_type;

        if (btype === 'activity') {
            // Hours between start and end time (same day assumed)
            if (ciTime && coTime) {
                const start = ciTime.split(':').map(Number);
                const end   = coTime.split(':').map(Number);
                const hrs   = ((end[0]*60+end[1]) - (start[0]*60+start[1])) / 60;
                total = Math.max(selectedItem.min_hrs || 1, hrs) * selectedItem.price;
            } else {
                total = (selectedItem.min_hrs || 1) * selectedItem.price;
            }
        } else if (btype === 'accommodation') {
            const days = (ciDate && coDate)
                ? Math.max(1, (new Date(coDate) - new Date(ciDate)) / 86400000)
                : 1;
            let dailyRate = selectedItem.price;
            let is_day = false;
            if (selectedItem.is_day_tour && !selectedItem.is_overnight && selectedItem.price_per_day > 0) {
                dailyRate = selectedItem.price_per_day;
                is_day = true;
            } else if (!selectedItem.is_overnight && selectedItem.price_per_day > 0) {
                 // Might be day tour if no overnight selected
                 if(selectedItem.price_per_night <= 0) {
                     dailyRate = selectedItem.price_per_day;
                     is_day = true;
                 }
            }
            total = days * dailyRate;
            
            const guests = parseInt(document.getElementById('bi_num_guests').value) || 1;
            const bCap = selectedItem.base_capacity || 1;
            const extFee = selectedItem.extra_person_fee || 0;
            if (guests > bCap && extFee > 0) {
                 total += (guests - bCap) * extFee * (is_day ? 1 : days);
            }
            
            // Add extra rooms
            extraRooms.forEach(er => {
                let erDaily = er.pkg.price;
                let er_is_day = false;
                if (er.pkg.is_day_tour && !er.pkg.is_overnight && er.pkg.price_per_day > 0) {
                    erDaily = er.pkg.price_per_day;
                    er_is_day = true;
                } else if (!er.pkg.is_overnight && er.pkg.price_per_day > 0 && er.pkg.price_per_night <= 0) {
                    erDaily = er.pkg.price_per_day;
                    er_is_day = true;
                }
                
                let erTotal = days * erDaily;
                const erGuests = parseInt(document.getElementById('extraRoomGuests-' + er.index)?.value) || er.guests;
                const erBCap = er.pkg.base_capacity || 1;
                const erExtFee = er.pkg.extra_person_fee || 0;
                
                if (erGuests > erBCap && erExtFee > 0) {
                     erTotal += (erGuests - erBCap) * erExtFee * (er_is_day ? 1 : days);
                }
                total += erTotal;
            });
            
            // Add addons
            let atvShort = false;
            let atvLong = false;
            Object.keys(walkinAddonsMap).forEach(key => {
                if (walkinAddonsMap[key] && walkinAddonsMap[key].selected) {
                    total += walkinAddonsMap[key].price * walkinAddonsMap[key].qty;
                    const lowerName = walkinAddonsMap[key].name.toLowerCase();
                    if (lowerName.includes('atv short ride')) atvShort = true;
                    if (lowerName.includes('atv long ride')) atvLong = true;
                }
            });
            
            if (atvShort) total += 1500;
            if (atvLong) total += 30000;
            
        } else {
            total = selectedItem.price;  // bundle flat rate
        }

        document.getElementById('posTotal').textContent =
            '₱' + Math.max(0, total).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
            
        checkLiveAvailability();
    }

    // ── Check Availability on Date/Item Change ────────────────────────────────
    async function checkLiveAvailability() {
        const ciDate = document.getElementById('bi_checkin_date').value;
        const btn = document.getElementById('posConfirmBtn');
        const previewEl = document.getElementById('selectedPreview');
        
        // Remove existing badge
        const existingBadge = previewEl.querySelector('.availability-badge');
        if (existingBadge) existingBadge.remove();
        
        if (!selectedItem || !ciDate) return;

        try {
            const url = `../php/check_availability.php?type=${selectedItem.booking_type}&item_id=${selectedItem.id}&date=${ciDate}`;
            const res = await fetch(url);
            const result = await res.json();
            
            let statusBadge = '';
            let disableBtn = false;
            
            if (result.success) {
                if (result.is_blocked) {
                    statusBadge = `<div class="availability-badge" style="margin-top:0.5rem;color:#f87171;font-size:0.8rem;font-weight:600;"><i class="bi bi-x-circle-fill me-1"></i>Date Blocked: ${escH(result.block_reason)}</div>`;
                    disableBtn = true;
                    showAlert(`This date is blocked: ${result.block_reason}`, 'danger');
                } else if (!result.available) {
                    statusBadge = `<div class="availability-badge" style="margin-top:0.5rem;color:#f87171;font-size:0.8rem;font-weight:600;"><i class="bi bi-x-circle-fill me-1"></i>Fully Booked (0 slots left)</div>`;
                    disableBtn = true;
                    showAlert('This item is fully booked for this date.', 'danger');
                } else {
                    statusBadge = `<div class="availability-badge" style="margin-top:0.5rem;color:#34d399;font-size:0.8rem;font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Available: ${result.available_slots} slot(s) left</div>`;
                    clearAlert();
                }
                
                if (!previewEl.classList.contains('empty')) {
                    previewEl.innerHTML += statusBadge;
                }
                btn.disabled = disableBtn;
            }
        } catch (err) {
            console.error('Failed to check availability:', err);
        }
    }

    // ── Submit walk-in via fetch ──────────────────────────────────────────────
    async function submitWalkIn() {
        clearAlert();

        if (!selectedItem)  { showAlert('Please select an item from the catalog.', 'danger'); return; }

        const name    = document.getElementById('bi_guest_name').value.trim();
        const contact = document.getElementById('bi_guest_contact').value.trim();
        const ciDate  = document.getElementById('bi_checkin_date').value;
        const guests  = parseInt(document.getElementById('bi_num_guests').value) || 1;

        if (!name)              { showAlert('Guest full name is required.', 'danger'); return; }
        if (!/^09\d{9}$/.test(contact)) { showAlert('Contact must be a valid PH mobile number (09XXXXXXXXX).', 'danger'); return; }
        if (!ciDate)            { showAlert('Check-in date is required.', 'danger'); return; }
        
        let summaryHtml = `
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 1.1rem; font-weight: 600; color: #9dff00;">${escH(name)}</div>
                <div style="font-size: 0.85rem; color: #aaa;">${escH(contact)}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #777; margin-bottom: 0.5rem;">Primary Package</div>
                <div style="display: flex; justify-content: space-between;">
                    <span>${escH(selectedItem.name)}</span>
                    <span style="font-weight: 600;">₱${selectedItem.price.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                </div>
                <div style="font-size: 0.8rem; color: #aaa; margin-top: 0.2rem;"><i class="bi bi-calendar-event me-1"></i>${ciDate} &bull; ${guests} Guests</div>
            </div>
        `;
        
        if (extraRooms.length > 0) {
            summaryHtml += `<div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #777; margin-bottom: 0.5rem;">Extra Rooms</div>
            `;
            extraRooms.forEach(r => {
                const g = parseInt(document.getElementById('extraRoomGuests-' + r.index)?.value) || r.guests;
                const rPrice = (r.pkg.is_day_tour && !r.pkg.is_overnight) ? (parseFloat(r.pkg.price_per_day) || parseFloat(r.pkg.price)) : (parseFloat(r.pkg.price_per_night) || parseFloat(r.pkg.price));
                summaryHtml += `
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.3rem;">
                        <span>${escH(r.pkg.name)} <small style="color:#aaa;">(x${g} guests)</small></span>
                        <span>₱${Number(rPrice).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                    </div>
                `;
            });
            summaryHtml += `</div>`;
        }
        
        let hasAddons = false;
        let addonsHtml = `<div style="margin-bottom: 1rem;">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #777; margin-bottom: 0.5rem;">Add-on Activities</div>`;
        
        let atvShort = false, atvLong = false;
        Object.keys(walkinAddonsMap).forEach(key => {
            if (walkinAddonsMap[key] && walkinAddonsMap[key].selected) {
                hasAddons = true;
                const cost = walkinAddonsMap[key].price * walkinAddonsMap[key].qty;
                addonsHtml += `
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.3rem;">
                        <span>${escH(walkinAddonsMap[key].name)} <small style="color:#aaa;">(x${walkinAddonsMap[key].qty})</small></span>
                        <span>₱${cost.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                    </div>
                `;
                const lowerName = walkinAddonsMap[key].name.toLowerCase();
                if (lowerName.includes('atv short ride')) atvShort = true;
                if (lowerName.includes('atv long ride')) atvLong = true;
            }
        });
        
        if (atvShort) {
            hasAddons = true;
            addonsHtml += `
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.3rem;">
                    <span>Required Tour Guide <small style="color:#aaa;">(ATV Short)</small></span>
                    <span>₱1,500.00</span>
                </div>
            `;
        }
        if (atvLong) {
            hasAddons = true;
            addonsHtml += `
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.3rem;">
                    <span>Required Tour Guide <small style="color:#aaa;">(ATV Long)</small></span>
                    <span>₱30,000.00</span>
                </div>
            `;
        }
        addonsHtml += `</div>`;
        if (hasAddons) summaryHtml += addonsHtml;
        
        const currentTotal = document.getElementById('posTotal').innerText;
        summaryHtml += `
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.1rem; font-weight: 600;">Total Amount</span>
                <span style="font-size: 1.5rem; font-weight: 700; color: #9dff00;">${currentTotal}</span>
            </div>
        `;
        
        document.getElementById('walkinConfirmBody').innerHTML = summaryHtml;
        const confirmModal = new bootstrap.Modal(document.getElementById('walkinConfirmModal'));
        confirmModal.show();
    }
    
    async function executeSubmitWalkIn() {
        const modalEl = document.getElementById('walkinConfirmModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        
        const btn = document.getElementById('posFinalConfirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing…';

        const name    = document.getElementById('bi_guest_name').value.trim();
        const contact = document.getElementById('bi_guest_contact').value.trim();
        const ciDate  = document.getElementById('bi_checkin_date').value;
        const coDate  = document.getElementById('bi_checkout_date').value || null;
        const ciTime  = document.getElementById('bi_checkin_time').value  || null;
        const coTime  = document.getElementById('bi_checkout_time').value  || null;
        const guests  = parseInt(document.getElementById('bi_num_guests').value) || 1;
        const pm      = 'cash';
        const notes   = document.getElementById('bi_special_requests').value.trim() || null;

        const extraRoomsPayload = extraRooms.map(r => {
             const g = parseInt(document.getElementById('extraRoomGuests-' + r.index)?.value) || r.guests;
             return {
                 item_id: r.pkg.id,
                 num_guests: g,
                 stay_type: (r.pkg.is_day_tour && !r.pkg.is_overnight) ? 'day_tour' : (r.pkg.is_overnight ? 'overnight' : null)
             };
        });
        
        const addonsPayload = [];
        Object.keys(walkinAddonsMap).forEach(key => {
            if (walkinAddonsMap[key] && walkinAddonsMap[key].selected) {
                addonsPayload.push({
                    id: key,
                    name: walkinAddonsMap[key].name,
                    price: walkinAddonsMap[key].price,
                    qty: walkinAddonsMap[key].qty
                });
            }
        });

        try {
            const res    = await fetch('../php/process_walkin_booking.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    guest_name:      name,
                    guest_contact:   contact,
                    booking_type:    selectedItem.booking_type,
                    stay_type:       (selectedItem.is_day_tour && !selectedItem.is_overnight) ? 'day_tour' : (selectedItem.is_overnight ? 'overnight' : null),
                    item_id:         selectedItem.id,
                    check_in_date:   ciDate,
                    check_in_time:   ciTime,
                    check_out_date:  coDate,
                    check_out_time:  coTime,
                    num_guests:      guests,
                    payment_method:  pm,
                    special_requests:notes,
                    extra_rooms:     extraRoomsPayload,
                    addons:          addonsPayload
                }),
            });
            const result = await res.json();

            if (result.success) {
                if (modalInstance) modalInstance.hide();
                // Show success overlay
                document.getElementById('successRef').textContent    = result.reference;
                document.getElementById('successName').textContent   = name;
                document.getElementById('successDetail').innerHTML =
                    `${escH(result.item_name || selectedItem.name)} &nbsp;|&nbsp; `
                  + `₱${parseFloat(result.total || 0).toLocaleString('en-PH',{minimumFractionDigits:2})} &nbsp;|&nbsp; `
                  + `${pm.toUpperCase()}`;
                document.getElementById('successOverlay').classList.add('show');
            } else {
                if (modalInstance) modalInstance.hide();
                showAlert(result.message || 'Something went wrong. Please try again.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm Booking';
            }
        } catch (err) {
            console.error(err);
            if (modalInstance) modalInstance.hide();
            showAlert('Connection error. Please check your network and retry.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm Booking';
        }
    }

    // ── Reset POS for next walk-in ────────────────────────────────────────────
    function resetPOS() {
        selectedItem = null;
        document.getElementById('successOverlay').classList.remove('show');
        document.getElementById('bi_guest_name').value    = '';
        document.getElementById('bi_guest_contact').value = '';
        document.getElementById('bi_checkin_date').value  = '';
        document.getElementById('bi_checkin_time').value  = '';
        document.getElementById('bi_checkout_date').value = '';
        document.getElementById('bi_checkout_time').value = '';
        document.getElementById('bi_num_guests').value    = 1;
        document.getElementById('bi_special_requests').value = '';
        document.getElementById('posTotal').textContent   = '₱0.00';
        document.getElementById('posConfirmBtn').disabled = false;
        document.getElementById('posConfirmBtn').innerHTML =
            '<i class="bi bi-check-circle-fill"></i> Confirm &amp; Check-In';
        clearAlert();
        walkinAddonsMap = {};
        renderWalkinAddons();
        updatePreview();
        renderCatalog();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showAlert(msg, type) {
        const el = document.getElementById('posAlert');
        el.className = 'pos-alert show ' + type;
        el.innerHTML = `<i class="bi bi-${type==='success'?'check-circle-fill':'exclamation-triangle-fill'}"></i><span>${escH(msg)}</span>`;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function clearAlert() {
        document.getElementById('posAlert').className = 'pos-alert';
    }
    function escH(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
    
    function showItemDetails(id, btype) {
        const item = CATALOG.find(i => Number(i.id) === Number(id) && i.booking_type === btype);
        if (item) {
            document.getElementById('itemDetailsTitle').textContent = item.name;
            document.getElementById('itemDetailsDesc').textContent = item.description || 'No description available.';
            new bootstrap.Modal(document.getElementById('itemDetailsModal')).show();
        }
    }

    // ── Extra Rooms JS Helpers ────────────────────────────────────────────────
    window.startAddingRoom = function() {
        if (!selectedItem || selectedItem.booking_type !== 'accommodation') return;
        isAddingExtraRoom = true;
        renderCatalog(); // re-render to update the catalog visual cue (maybe change button text)
        showAlert('Select an accommodation from the catalog to add as an extra room.', 'info');
    };

    window.removeExtraRoom = function(index) {
        extraRooms = extraRooms.filter(r => r.index !== index);
        renderExtraRooms();
        recalcTotal();
    };

    function renderExtraRooms() {
        const container = document.getElementById('extraRoomsContainer');
        if (!container) return;
        if (extraRooms.length === 0) {
             container.innerHTML = '';
             return;
        }
        
        let html = '';
        extraRooms.forEach(room => {
             const r = room.pkg;
             html += `
             <div class="pos-field-group" id="extraRoomCard-${room.index}" style="background: rgba(157, 255, 0, 0.03); border: 1px solid rgba(157, 255, 0, 0.2); border-radius: 8px; padding: 0.8rem; margin-top: 0.5rem;">
                 <div style="display:flex; justify-content: space-between; align-items:flex-start;">
                     <div>
                         <div style="color:#9dff00; font-size: 0.8rem; font-weight: 600;"><i class="bi bi-door-open me-1"></i> Room ${room.index}: ${escH(r.name)}</div>
                         <div style="color:var(--pos-text-muted); font-size: 0.75rem;">Capacity: ${r.capacity} pax</div>
                     </div>
                     <button type="button" onclick="removeExtraRoom(${room.index})" class="btn btn-sm btn-outline-danger" style="padding: 0.1rem 0.4rem; font-size: 0.75rem;"><i class="bi bi-x-lg"></i></button>
                 </div>
                 <div style="margin-top: 0.5rem;">
                     <label style="font-size: 0.75rem; color:var(--pos-text-muted);">Guests / Pax</label>
                     <input type="number" id="extraRoomGuests-${room.index}" class="pos-input" style="width: 80px; padding: 0.3rem 0.5rem; font-size: 0.8rem; min-height: 0;" value="${room.guests}" min="1" max="${r.capacity}" oninput="if(this.value && parseInt(this.value) > parseInt(this.max)) this.value = this.max; recalcTotal();" onchange="recalcTotal()">
                 </div>
             </div>`;
        });
        container.innerHTML = html;
    }

    // ── Addons JS Helpers ────────────────────────────────────────────────
    function renderWalkinAddons() {
        const addonsCont = document.getElementById('addonsContainer');
        const addonsList = document.getElementById('addonsList');
        if (!selectedItem || selectedItem.booking_type !== 'accommodation') {
            addonsCont.style.display = 'none';
            walkinAddonsMap = {};
            return;
        }
        
        const isDayTour = selectedItem.is_day_tour && !selectedItem.is_overnight;
        if (!isDayTour) {
            addonsCont.style.display = 'none';
            walkinAddonsMap = {};
            return;
        }
        
        const available = CATALOG.filter(i => {
            if (i.booking_type !== 'activity') return false;
            return true;
        });
        
        if (available.length === 0) {
            addonsCont.style.display = 'none';
            return;
        }
        
        addonsCont.style.display = 'block';
        
        addonsList.innerHTML = available.map(a => {
            const isSelected = !!(walkinAddonsMap[a.id] && walkinAddonsMap[a.id].selected);
            const qtyVal = (walkinAddonsMap[a.id] && walkinAddonsMap[a.id].qty) || 1;
            const isATV = (a.name || '').toLowerCase().includes('atv');
            
            return `
            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); padding:0.5rem; border-radius:6px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <input type="checkbox" id="wAddonChk-${a.id}" ${isSelected?'checked':''} onchange="toggleWalkinAddon(${a.id}, ${a.price}, this, '${escH(a.name)}')">
                    <label for="wAddonChk-${a.id}" style="font-size:0.8rem; color:#fff; cursor:pointer; margin-left:5px;">${escH(a.name)}</label>
                    <div style="font-size:0.7rem; color:var(--pos-text-muted); margin-left: 20px;">+₱${a.price.toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                </div>
                ${isATV ? `<input type="number" id="wAddonQty-${a.id}" min="1" value="${qtyVal}" oninput="updateWalkinAddonQty(${a.id}, this)" style="width: 50px; padding:0.2rem; font-size:0.75rem; border-radius:4px; border:1px solid rgba(157, 255, 0, 0.2); background:rgba(157, 255, 0, 0.05); color:#9dff00; display:${isSelected ? 'block' : 'none'};">` : ''}
            </div>
            `;
        }).join('');
    }
    
    function toggleWalkinAddon(id, price, chk, name) {
        const lowerName = name.toLowerCase();
        if (chk.checked) {
            if (lowerName.includes('atv')) {
                Object.keys(walkinAddonsMap).forEach(key => {
                    const mapItem = walkinAddonsMap[key];
                    if (mapItem && mapItem.selected && mapItem.name.toLowerCase().includes('atv')) {
                        if (key != id) {
                            mapItem.selected = false;
                            const otherChk = document.getElementById('wAddonChk-' + key);
                            if (otherChk) otherChk.checked = false;
                            const otherQty = document.getElementById('wAddonQty-' + key);
                            if (otherQty) otherQty.style.display = 'none';
                        }
                    }
                });
            }
            const qtyEl = document.getElementById('wAddonQty-' + id);
            if (qtyEl) qtyEl.style.display = 'block';
            walkinAddonsMap[id] = { selected: true, price: Number(price), qty: qtyEl ? parseInt(qtyEl.value)||1 : 1, name: name };
        } else {
            const qtyEl = document.getElementById('wAddonQty-' + id);
            if (qtyEl) qtyEl.style.display = 'none';
            if (walkinAddonsMap[id]) walkinAddonsMap[id].selected = false;
        }
        recalcTotal();
    }
    
    window.updateWalkinAddonQty = function(id, input) {
        if (walkinAddonsMap[id]) {
            walkinAddonsMap[id].qty = parseInt(input.value)||1;
        }
        recalcTotal();
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        // Min date = today
        const todayStr = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('bi_checkin_date');
        dateInput.min = todayStr;
        
        dateInput.addEventListener('change', (e) => {
            loadAllAvailability(e.target.value);
        });
        
        loadAllAvailability(dateInput.value || todayStr);
        renderCatalog();
    });
    </script>
</body>
</html>
