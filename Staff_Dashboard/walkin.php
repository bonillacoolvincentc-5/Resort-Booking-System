<?php
session_start();
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] !== true) {
    header('Location: ../Authentication/staff-login.php');
    exit();
}
require_once '../phpAuthentication/db_config.php';
global $conn;

// ── Fetch catalog data ────────────────────────────────────────────────────────
$activities = [];
$res = $conn->query("SELECT id, name, slug, description, price_per_hour, min_duration_hours, max_duration_hours, capacity, image_url FROM activities WHERE is_active = 1 ORDER BY name ASC");
while ($r = $res->fetch_assoc()) $activities[] = $r;

$accommodations = [];
$res = $conn->query("SELECT id, name, type, description, capacity, price_per_night, price_per_day, amenities, image_url FROM accommodations WHERE is_active = 1 ORDER BY type ASC, name ASC");
while ($r = $res->fetch_assoc()) $accommodations[] = $r;

$bundles = [];
$res = $conn->query("SELECT id, name, slug, description, price, capacity, amenities, image_url FROM bundles WHERE is_active = 1 ORDER BY name ASC");
while ($r = $res->fetch_assoc()) $bundles[] = $r;
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
    <script src="components/staff-components.js" defer></script>

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
        .badge-room     { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .badge-cottage  { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-activity { background: rgba(16,185,129,0.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
        .badge-bundle   { background: rgba(236,72,153,0.15); color: #f9a8d4; border: 1px solid rgba(236,72,153,0.3); }
        .badge-stay-in  { background: rgba(139,92,246,0.15); color: #c4b5fd; border: 1px solid rgba(139,92,246,0.3); }

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
                        <button class="cat-tab" data-cat="room"
                                onclick="filterCat('room',this)">
                            <i class="bi bi-house-door me-1"></i>Rooms
                        </button>
                        <button class="cat-tab" data-cat="cottage"
                                onclick="filterCat('cottage',this)">
                            <i class="bi bi-tree me-1"></i>Cottages
                        </button>
                        <button class="cat-tab" data-cat="activity"
                                onclick="filterCat('activity',this)">
                            <i class="bi bi-lightning-charge me-1"></i>Activities
                        </button>
                        <button class="cat-tab" data-cat="bundle"
                                onclick="filterCat('bundle',this)">
                            <i class="bi bi-box-seam me-1"></i>Bundles
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
                            <input type="date" id="bi_checkin_date" class="pos-input" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="recalcTotal()">
                        </div>
                        <div>
                            <label class="pos-label" for="bi_checkin_time">Start Time</label>
                            <input type="time" id="bi_checkin_time" class="pos-input" onchange="recalcTotal()">
                        </div>
                    </div>

                    <div class="pos-input-row pos-field-group">
                        <div>
                            <label class="pos-label" for="bi_checkout_date">Check-Out Date</label>
                            <input type="date" id="bi_checkout_date" class="pos-input" onchange="recalcTotal()">
                        </div>
                        <div>
                            <label class="pos-label" for="bi_checkout_time">End Time</label>
                            <input type="time" id="bi_checkout_time" class="pos-input" onchange="recalcTotal()">
                        </div>
                    </div>

                    <div class="pos-field-group">
                        <label class="pos-label" for="bi_num_guests">Number of Guests / Pax</label>
                        <input type="number" id="bi_num_guests" class="pos-input"
                               value="1" min="1" max="200" onchange="recalcTotal()">
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

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── PHP → JS Data ─────────────────────────────────────────────────────────
    const PHP_ACTIVITIES     = <?= json_encode($activities,     JSON_HEX_TAG) ?>;
    const PHP_ACCOMMODATIONS = <?= json_encode($accommodations, JSON_HEX_TAG) ?>;
    const PHP_BUNDLES        = <?= json_encode($bundles,        JSON_HEX_TAG) ?>;

    // ── Build unified catalog ─────────────────────────────────────────────────
    const CATALOG = [];

    PHP_ACCOMMODATIONS.forEach(a => {
        CATALOG.push({
            id:          parseInt(a.id, 10),
            type:        a.type,       // 'room' | 'cottage' | 'stay-in'
            booking_type:'accommodation',
            name:        a.name,
            description: a.description || '',
            capacity:    parseInt(a.capacity, 10) || 1,
            price:       parseFloat(a.price_per_night) || parseFloat(a.price_per_day) || 0,
            price_label: '/ night',
            amenities:   a.amenities   || '',
            image:       a.image_url   || '',
        });
    });

    PHP_ACTIVITIES.forEach(a => {
        CATALOG.push({
            id:          parseInt(a.id, 10),
            type:        'activity',
            booking_type:'activity',
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
            name:        b.name,
            description: b.description || '',
            capacity:    parseInt(b.capacity, 10) || 1,
            price:       parseFloat(b.price) || 0,
            price_label: 'flat rate',
            amenities:   b.amenities   || '',
            image:       b.image_url   || '',
        });
    });

    // ── State ─────────────────────────────────────────────────────────────────
    let currentCat    = 'all';
    let currentSearch = '';
    let selectedItem  = null;

    // ── Type badges / icons ───────────────────────────────────────────────────
    const TYPE_META = {
        room:          { badge:'badge-room',     label:'Room',      icon:'bi-house-door' },
        cottage:       { badge:'badge-cottage',  label:'Cottage',   icon:'bi-tree' },
        'stay-in':     { badge:'badge-stay-in',  label:'Stay-In',   icon:'bi-moon-stars' },
        activity:      { badge:'badge-activity', label:'Activity',  icon:'bi-lightning-charge' },
        bundle:        { badge:'badge-bundle',   label:'Bundle',    icon:'bi-box-seam' },
    };

    // ── Render catalog grid ───────────────────────────────────────────────────
    function renderCatalog() {
        const grid = document.getElementById('posGrid');
        const term = currentSearch.toLowerCase();

        const filtered = CATALOG.filter(item => {
            const catMatch = currentCat === 'all'
                || item.type === currentCat
                || (currentCat === 'room' && item.type === 'stay-in'); // treat stay-in as room tab
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
            const meta  = TYPE_META[item.type] || { badge:'badge-activity', label:item.type, icon:'bi-grid' };
            const isSelected = selectedItem && Number(selectedItem.id) === Number(item.id) && selectedItem.booking_type === item.booking_type;
            const priceStr   = '₱' + item.price.toLocaleString('en-PH', {minimumFractionDigits:2}) + ' <span>' + item.price_label + '</span>';
            const imgHtml    = item.image
                ? `<img class="pos-card-img" src="../${escH(item.image)}" alt="${escH(item.name)}" loading="lazy" onerror="this.parentElement.innerHTML='<div class=pos-card-img-placeholder><i class=bi-${escH(meta.icon)}></i></div>'">`
                : `<div class="pos-card-img-placeholder"><i class="bi ${escH(meta.icon)}"></i></div>`;

            return `
            <div class="pos-card${isSelected ? ' selected' : ''} h-100 d-flex flex-column"
                 id="card_${item.booking_type}_${item.id}"
                 onclick="selectItem(${item.id},'${item.booking_type}')">
                ${imgHtml}
                <div class="pos-card-body d-flex flex-column flex-grow-1">
                    <div class="pos-card-name">${escH(item.name)}</div>
                    <span class="pos-card-type-badge ${meta.badge}">${meta.label}</span>
                    <div class="pos-card-price">${priceStr}</div>
                    <div class="pos-card-cap">
                        <i class="bi bi-people-fill"></i>
                        ${item.capacity} Pax${item.capacity >= 50 ? '+' : ''}
                    </div>
                    <div class="pos-card-desc">${escH(item.description || 'No description available.')}</div>
                </div>
                <div class="pos-card-footer mt-auto">
                    <button type="button" class="pos-select-btn" onclick="selectItem(${item.id},'${item.booking_type}');">
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
        selectedItem = CATALOG.find(i => Number(i.id) === Number(id) && i.booking_type === btype) || null;
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
        }
        
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
            total = days * selectedItem.price;
        } else {
            total = selectedItem.price;  // bundle flat rate
        }

        document.getElementById('posTotal').textContent =
            '₱' + Math.max(0, total).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    // ── Submit walk-in via fetch ──────────────────────────────────────────────
    async function submitWalkIn() {
        clearAlert();

        if (!selectedItem)  { showAlert('Please select an item from the catalog.', 'danger'); return; }

        const name    = document.getElementById('bi_guest_name').value.trim();
        const contact = document.getElementById('bi_guest_contact').value.trim();
        const ciDate  = document.getElementById('bi_checkin_date').value;
        const coDate  = document.getElementById('bi_checkout_date').value || null;
        const ciTime  = document.getElementById('bi_checkin_time').value  || null;
        const coTime  = document.getElementById('bi_checkout_time').value  || null;
        const guests  = parseInt(document.getElementById('bi_num_guests').value) || 1;
        const pm      = 'cash';
        const notes   = document.getElementById('bi_special_requests').value.trim() || null;

        if (!name)              { showAlert('Guest full name is required.', 'danger'); return; }
        if (!/^09\d{9}$/.test(contact)) { showAlert('Contact must be a valid PH mobile number (09XXXXXXXXX).', 'danger'); return; }
        if (!ciDate)            { showAlert('Check-in date is required.', 'danger'); return; }

        const btn = document.getElementById('posConfirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing…';

        try {
            const res    = await fetch('../php/process_walkin_booking.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    guest_name:      name,
                    guest_contact:   contact,
                    booking_type:    selectedItem.booking_type,
                    item_id:         selectedItem.id,
                    check_in_date:   ciDate,
                    check_in_time:   ciTime,
                    check_out_date:  coDate,
                    check_out_time:  coTime,
                    num_guests:      guests,
                    payment_method:  pm,
                    special_requests:notes,
                }),
            });
            const result = await res.json();

            if (result.success) {
                // Show success overlay
                document.getElementById('successRef').textContent    = result.reference;
                document.getElementById('successName').textContent   = name;
                document.getElementById('successDetail').innerHTML =
                    `${escH(result.item_name || selectedItem.name)} &nbsp;|&nbsp; `
                  + `₱${parseFloat(result.total || 0).toLocaleString('en-PH',{minimumFractionDigits:2})} &nbsp;|&nbsp; `
                  + `${pm.toUpperCase()}`;
                document.getElementById('successOverlay').classList.add('show');
            } else {
                showAlert(result.message || 'Something went wrong. Please try again.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm &amp; Check-In';
            }
        } catch (err) {
            console.error(err);
            showAlert('Connection error. Please check your network and retry.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirm &amp; Check-In';
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

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        // Min date = today
        document.getElementById('bi_checkin_date').min = new Date().toISOString().split('T')[0];
        renderCatalog();
    });
    </script>
</body>
</html>

