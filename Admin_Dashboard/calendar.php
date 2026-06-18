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
    <title>Calendar - YP4K Resort</title>
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

        .calendar-container {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .calendar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .calendar-nav {
            display: flex;
            gap: 1rem;
        }

        .calendar-nav button {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-nav button:hover {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--border-color);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .calendar-day-header {
            background: var(--bg-tertiary);
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .calendar-day {
            background: var(--bg-secondary);
            padding: 1rem;
            min-height: 80px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-day:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .calendar-day.other-month {
            background: var(--bg-primary);
            color: var(--text-muted);
        }

        .calendar-day.today {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid var(--accent-primary);
        }

        .calendar-day-number {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .calendar-events {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .event-item {
            background: var(--accent-primary);
            color: white;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            margin-bottom: 0.125rem;
            font-size: 0.6rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .event-item:hover {
            background: var(--accent-secondary);
            transform: scale(1.05);
        }

        .event-item.basketball {
            background: var(--accent-warning);
        }

        .event-item.swimming {
            background: var(--accent-success);
        }

        .event-item.gym {
            background: var(--accent-danger);
        }

        .event-item.adventure {
            background: var(--accent-secondary);
        }

        .event-item.playground {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .calendar-day.has-bookings {
            background: rgba(59, 130, 246, 0.05);
        }

        .calendar-day.has-bookings:hover {
            background: rgba(59, 130, 246, 0.15);
        }

        .calendar-day.blocked {
            background: rgba(100, 116, 139, 0.35);
            border: 1px solid rgba(100, 116, 139, 0.6);
            cursor: pointer;
        }

        .calendar-day.blocked:hover {
            background: rgba(100, 116, 139, 0.45);
        }

        .booking-count {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
            margin-top: 0.25rem;
            display: inline-block;
        }

        .booking-count.low { background: rgba(16, 185, 129, 0.25); color: #10b981; }
        .booking-count.moderate { background: rgba(245, 158, 11, 0.25); color: #f59e0b; }
        .booking-count.high { background: rgba(239, 68, 68, 0.25); color: #ef4444; }
        .booking-count.blocked { background: rgba(100, 116, 139, 0.35); color: #94a3b8; }

        .modal-content-admin {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .modal-header-admin {
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .modal-footer-admin {
            background: var(--bg-tertiary);
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--text-secondary); }
        .detail-value { color: var(--text-primary); font-weight: 500; text-align: right; }

        .booking-list-item {
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid var(--accent-primary);
        }

        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .service-check-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .service-check-scroll {
            max-height: 220px;
            overflow-y: auto;
            padding: 0.5rem;
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .form-ctrl {
            width: 100%;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }

        .form-ctrl:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .btn-modal {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
        }

        .btn-modal-secondary {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-modal-danger {
            background: var(--accent-danger);
            color: white;
        }

        /* Booking Details Modal */
        .booking-details-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .booking-details-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .booking-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .booking-details-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .booking-details-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .booking-details-close:hover {
            color: var(--text-primary);
        }

        .booking-item {
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-primary);
        }

        .booking-item h4 {
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .booking-item p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }

        .booking-time {
            color: var(--accent-primary);
            font-weight: 500;
        }

        .legend {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .legend-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div class="main-content">
        <div class="calendar-container">
            <div class="calendar-header">
                <h2 class="calendar-title">March 2026</h2>
                <div class="calendar-nav">
                    <button><i class="bi bi-chevron-left"></i> Previous</button>
                    <button>Today</button>
                    <button>Next <i class="bi bi-chevron-right"></i></button>
                </div>
            </div>

            <div class="calendar-grid" id="calendarGrid">
                <!-- Calendar will be generated dynamically -->
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(59, 130, 246, 0.2); border: 1px solid var(--accent-primary);"></div>
                    <span class="legend-text">Today</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(16, 185, 129, 0.35);"></div>
                    <span class="legend-text">Low Occupancy</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(245, 158, 11, 0.35);"></div>
                    <span class="legend-text">Moderate Occupancy</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(239, 68, 68, 0.35);"></div>
                    <span class="legend-text">High / Fully Booked</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(100, 116, 139, 0.45);"></div>
                    <span class="legend-text">Blocked Date</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Details Modal -->
    <div class="modal fade" id="dateDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>Date Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="detail-row"><span class="detail-label">Selected Date</span><span class="detail-value" id="detailDate">—</span></div>
                    <div class="detail-row"><span class="detail-label">Total Bookings</span><span class="detail-value" id="detailTotal">0</span></div>
                    <div class="detail-row"><span class="detail-label">Rooms Occupied</span><span class="detail-value" id="detailRooms">0</span></div>
                    <div class="detail-row"><span class="detail-label">Cottages Occupied</span><span class="detail-value" id="detailCottages">0</span></div>
                    <div class="detail-row"><span class="detail-label">Activities Reserved</span><span class="detail-value" id="detailActivities">0</span></div>
                    <div id="blockedReasonRow" class="detail-row d-none"><span class="detail-label">Block Reason</span><span class="detail-value" id="detailBlockReason">—</span></div>
                </div>
                <div class="modal-footer modal-footer-admin d-flex flex-wrap gap-2">
                    <button type="button" class="btn-modal btn-modal-primary" onclick="openBookingsListModal()"><i class="bi bi-list-ul me-1"></i>View Bookings</button>
                    <button type="button" class="btn-modal btn-modal-secondary" id="blockDateBtn" onclick="openBlockDateModal()"><i class="bi bi-slash-circle me-1"></i>Block Date</button>
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="openCreateBundleModal()"><i class="bi bi-box-seam me-1"></i>Create Bundle</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings List Modal -->
    <div class="modal fade" id="bookingsListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content modal-content-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Bookings — <span id="bookingsListDate"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingsListBody">
                    <p class="text-muted">Loading bookings...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Booking Details Modal -->
    <div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content modal-content-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Booking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetailBody">
                    <p class="text-muted">Loading...</p>
                </div>
                <div class="modal-footer modal-footer-admin">
                    <button type="button" class="btn-modal btn-modal-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Block Date Modal -->
    <div class="modal fade" id="blockDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title"><i class="bi bi-slash-circle me-2"></i>Block Date</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Blocking: <strong id="blockDateLabel" style="color: var(--text-primary);"></strong></p>
                    <label class="form-label mt-3" style="color: var(--text-primary);">Reason <span style="color: var(--accent-danger);">*</span></label>
                    <textarea class="form-ctrl" id="blockReasonInput" rows="3" placeholder="e.g. Maintenance, private event, holiday closure"></textarea>
                </div>
                <div class="modal-footer modal-footer-admin">
                    <button type="button" class="btn-modal btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modal btn-modal-danger" id="unblockDateBtn" onclick="unblockSelectedDate()" style="display:none;">Unblock Date</button>
                    <button type="button" class="btn-modal btn-modal-primary" onclick="saveBlockedDate()">Save Block</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Bundle Modal -->
    <div class="modal fade" id="createBundleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content modal-content-admin">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Create Bundle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-primary);">Bundle Name <span style="color: var(--accent-danger);">*</span></label>
                            <input type="text" class="form-ctrl" id="bundleName" placeholder="e.g. Weekend Getaway">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: var(--text-primary);">Calendar Date</label>
                            <input type="text" class="form-ctrl" id="bundleCalendarDate" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: var(--text-primary);">Description</label>
                            <textarea class="form-ctrl" id="bundleDescription" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: var(--text-primary);">Original Price (₱)</label>
                            <input type="number" class="form-ctrl" id="bundleOriginalPrice" min="0" step="0.01" value="0" oninput="calculateBundleFinalPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: var(--text-primary);">Discount %</label>
                            <input type="number" class="form-ctrl" id="bundleDiscount" min="0" max="100" step="0.01" value="0" oninput="calculateBundleFinalPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: var(--text-primary);">Final Price (₱) <span style="color: var(--accent-danger);">*</span></label>
                            <input type="number" class="form-ctrl" id="bundleFinalPrice" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: var(--text-primary);">Included Services</label>
                            <div class="service-check-scroll" id="bundleServicesList"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-admin">
                    <button type="button" class="btn-modal btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modal btn-modal-primary" onclick="saveCalendarBundle()"><i class="bi bi-check-circle me-1"></i>Save Bundle</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load components
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

        // Calendar state
        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();
        let calendarDays = {};
        let calendarServices = { activities: [], accommodations: [] };
        let selectedDate = null;

        let dateDetailsModal, bookingsListModal, bookingDetailModal, blockDateModal, createBundleModal;

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        document.addEventListener('DOMContentLoaded', function() {
            dateDetailsModal = new bootstrap.Modal(document.getElementById('dateDetailsModal'));
            bookingsListModal = new bootstrap.Modal(document.getElementById('bookingsListModal'));
            bookingDetailModal = new bootstrap.Modal(document.getElementById('bookingDetailModal'));
            blockDateModal = new bootstrap.Modal(document.getElementById('blockDateModal'));
            createBundleModal = new bootstrap.Modal(document.getElementById('createBundleModal'));

            initCalendar();
            setupEventListeners();
        });

        function initCalendar() {
            loadCalendarData().then(() => renderCalendar());
        }

        async function loadCalendarData() {
            try {
                const res = await fetch(`../php/get_calendar_data.php?year=${currentYear}&month=${currentMonth + 1}`);
                const data = await res.json();
                if (data.success) {
                    calendarDays = data.days || {};
                    calendarServices = data.services || { activities: [], accommodations: [] };
                } else {
                    calendarDays = {};
                    console.error(data.message);
                }
            } catch (err) {
                calendarDays = {};
                console.error('Failed to load calendar data:', err);
            }
        }

        function renderCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();

            document.querySelector('.calendar-title').textContent = `${monthNames[currentMonth]} ${currentYear}`;

            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
                const headerElement = document.createElement('div');
                headerElement.className = 'calendar-day-header';
                headerElement.textContent = day;
                calendarGrid.appendChild(headerElement);
            });

            for (let i = firstDay - 1; i >= 0; i--) {
                calendarGrid.appendChild(createDayElement(daysInPrevMonth - i, true, false));
            }

            const today = new Date();
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = currentYear === today.getFullYear() &&
                               currentMonth === today.getMonth() &&
                               day === today.getDate();
                calendarGrid.appendChild(createDayElement(day, false, isToday));
            }

            const totalCells = calendarGrid.children.length - 7;
            const remainingCells = Math.max(0, 42 - totalCells);
            for (let day = 1; day <= remainingCells; day++) {
                calendarGrid.appendChild(createDayElement(day, true, false));
            }
        }

        function createDayElement(day, isOtherMonth, isToday) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';

            if (isOtherMonth) dayElement.classList.add('other-month');
            if (isToday) dayElement.classList.add('today');

            const dayNumber = document.createElement('div');
            dayNumber.className = 'calendar-day-number';
            dayNumber.textContent = day;
            dayElement.appendChild(dayNumber);

            if (!isOtherMonth) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayData = calendarDays[dateStr] || {
                    total_bookings: 0,
                    occupancy_level: 'none',
                    is_blocked: false
                };

                if (dayData.is_blocked) {
                    dayElement.classList.add('blocked');
                } else if (dayData.total_bookings > 0) {
                    dayElement.classList.add('has-bookings');
                }

                const countEl = document.createElement('div');
                const level = dayData.is_blocked ? 'blocked' : (dayData.occupancy_level === 'none' ? 'low' : dayData.occupancy_level);
                countEl.className = `booking-count ${level}`;
                countEl.textContent = dayData.is_blocked ? 'Blocked' : `Booked: ${dayData.total_bookings}`;
                dayElement.appendChild(countEl);

                dayElement.onclick = () => openDateDetailsModal(dateStr);
            }

            return dayElement;
        }

        function openDateDetailsModal(dateStr) {
            selectedDate = dateStr;
            const dayData = calendarDays[dateStr] || {
                total_bookings: 0,
                rooms: 0,
                cottages: 0,
                activities: 0,
                is_blocked: false,
                block_reason: null
            };

            document.getElementById('detailDate').textContent = formatDate(dateStr);
            document.getElementById('detailTotal').textContent = dayData.total_bookings;
            document.getElementById('detailRooms').textContent = dayData.rooms || 0;
            document.getElementById('detailCottages').textContent = dayData.cottages || 0;
            document.getElementById('detailActivities').textContent = dayData.activities || 0;

            const blockedRow = document.getElementById('blockedReasonRow');
            if (dayData.is_blocked) {
                blockedRow.classList.remove('d-none');
                document.getElementById('detailBlockReason').textContent = dayData.block_reason || 'No reason provided';
                document.getElementById('blockDateBtn').innerHTML = '<i class="bi bi-unlock me-1"></i>Unblock Date';
            } else {
                blockedRow.classList.add('d-none');
                document.getElementById('blockDateBtn').innerHTML = '<i class="bi bi-slash-circle me-1"></i>Block Date';
            }

            dateDetailsModal.show();
        }

        function openBookingsListModal() {
            if (!selectedDate) return;
            dateDetailsModal.hide();
            document.getElementById('bookingsListDate').textContent = formatDate(selectedDate);
            document.getElementById('bookingsListBody').innerHTML = '<p class="text-muted">Loading bookings...</p>';
            bookingsListModal.show();

            fetch(`../php/get_calendar_bookings.php?date=${selectedDate}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        document.getElementById('bookingsListBody').innerHTML = `<p style="color: var(--accent-danger);">${escapeHtml(data.message)}</p>`;
                        return;
                    }
                    if (!data.bookings.length) {
                        document.getElementById('bookingsListBody').innerHTML = '<p class="text-muted">No bookings for this date.</p>';
                        return;
                    }
                    document.getElementById('bookingsListBody').innerHTML = data.bookings.map(b => `
                        <div class="booking-list-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <strong>${escapeHtml(b.full_name)}</strong>
                                    <div style="color: var(--text-secondary); font-size: 0.85rem;">${escapeHtml(b.item_name || b.booking_type)}</div>
                                </div>
                                <div class="text-end">
                                    <span class="status-badge" style="background:${b.status_color || '#64748b'}33;color:${b.status_color || '#94a3b8'}">${escapeHtml(formatStatus(b.status_name))}</span>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Payment: ${escapeHtml(capitalize(b.payment_status))}</div>
                                </div>
                            </div>
                            <button class="btn-modal btn-modal-primary btn-sm mt-2" onclick="viewBookingDetails(${b.id})">View Details</button>
                        </div>
                    `).join('');
                })
                .catch(() => {
                    document.getElementById('bookingsListBody').innerHTML = '<p style="color: var(--accent-danger);">Failed to load bookings.</p>';
                });
        }

        function viewBookingDetails(bookingId) {
            document.getElementById('bookingDetailBody').innerHTML = '<p class="text-muted">Loading...</p>';
            bookingDetailModal.show();

            fetch(`../php/get_booking_details.php?booking_id=${bookingId}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        document.getElementById('bookingDetailBody').innerHTML = `<p style="color: var(--accent-danger);">${escapeHtml(data.message)}</p>`;
                        return;
                    }
                    const b = data.booking;
                    document.getElementById('bookingDetailBody').innerHTML = `
                        <div class="detail-row"><span class="detail-label">Reference</span><span class="detail-value">${escapeHtml(b.booking_reference)}</span></div>
                        <div class="detail-row"><span class="detail-label">Customer</span><span class="detail-value">${escapeHtml(b.full_name)}</span></div>
                        <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">${escapeHtml(b.email)}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">${escapeHtml(b.phone_number)}</span></div>
                        <div class="detail-row"><span class="detail-label">Package / Activity</span><span class="detail-value">${escapeHtml(b.item_name || b.booking_type)}</span></div>
                        <div class="detail-row"><span class="detail-label">Booking Status</span><span class="detail-value">${escapeHtml(formatStatus(b.status_name))}</span></div>
                        <div class="detail-row"><span class="detail-label">Payment Status</span><span class="detail-value">${escapeHtml(capitalize(b.payment_status))}</span></div>
                        <div class="detail-row"><span class="detail-label">Check-in</span><span class="detail-value">${escapeHtml(b.check_in_date)} ${b.check_in_time ? escapeHtml(b.check_in_time) : ''}</span></div>
                        <div class="detail-row"><span class="detail-label">Check-out</span><span class="detail-value">${escapeHtml(b.check_out_date || '—')} ${b.check_out_time ? escapeHtml(b.check_out_time) : ''}</span></div>
                        <div class="detail-row"><span class="detail-label">Guests</span><span class="detail-value">${b.number_of_guests}</span></div>
                        <div class="detail-row"><span class="detail-label">Total Amount</span><span class="detail-value">₱${parseFloat(b.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span></div>
                        <div class="detail-row"><span class="detail-label">Payment Method</span><span class="detail-value">${escapeHtml(capitalize(b.payment_method || '—'))}</span></div>
                        ${b.special_requests ? `<div class="detail-row"><span class="detail-label">Special Requests</span><span class="detail-value">${escapeHtml(b.special_requests)}</span></div>` : ''}
                    `;
                })
                .catch(() => {
                    document.getElementById('bookingDetailBody').innerHTML = '<p style="color: var(--accent-danger);">Failed to load booking details.</p>';
                });
        }

        function openBlockDateModal() {
            if (!selectedDate) return;
            const dayData = calendarDays[selectedDate] || {};
            dateDetailsModal.hide();
            document.getElementById('blockDateLabel').textContent = formatDate(selectedDate);
            document.getElementById('blockReasonInput').value = dayData.block_reason || '';

            const isBlocked = !!dayData.is_blocked;
            document.getElementById('unblockDateBtn').style.display = isBlocked ? 'inline-block' : 'none';
            document.querySelector('#blockDateModal .btn-modal-primary').style.display = isBlocked ? 'none' : 'inline-block';

            blockDateModal.show();
        }

        function saveBlockedDate() {
            const reason = document.getElementById('blockReasonInput').value.trim();
            if (!reason) {
                alert('Please enter a reason for blocking this date.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'block');
            formData.append('date', selectedDate);
            formData.append('reason', reason);

            fetch('../php/block_date.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        blockDateModal.hide();
                        loadCalendarData().then(() => renderCalendar());
                    } else {
                        alert(data.message || 'Failed to block date.');
                    }
                })
                .catch(() => alert('Failed to block date.'));
        }

        function unblockSelectedDate() {
            const formData = new FormData();
            formData.append('action', 'unblock');
            formData.append('date', selectedDate);

            fetch('../php/block_date.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        blockDateModal.hide();
                        loadCalendarData().then(() => renderCalendar());
                    } else {
                        alert(data.message || 'Failed to unblock date.');
                    }
                })
                .catch(() => alert('Failed to unblock date.'));
        }

        function openCreateBundleModal() {
            if (!selectedDate) return;
            dateDetailsModal.hide();
            document.getElementById('bundleCalendarDate').value = selectedDate;
            document.getElementById('bundleName').value = '';
            document.getElementById('bundleDescription').value = '';
            document.getElementById('bundleOriginalPrice').value = '0';
            document.getElementById('bundleDiscount').value = '0';
            document.getElementById('bundleFinalPrice').value = '0';
            renderBundleServices();
            createBundleModal.show();
        }

        function renderBundleServices() {
            const container = document.getElementById('bundleServicesList');
            let html = '';

            if (calendarServices.activities.length) {
                html += '<div style="font-weight:600;margin-bottom:0.25rem;color:var(--text-secondary);">Activities</div>';
                calendarServices.activities.forEach(a => {
                    html += `<label class="service-check-item"><input type="checkbox" class="bundle-act-chk" value="${a.id}" data-price="${a.price_per_hour}"> ${escapeHtml(a.name)} <span style="color:var(--text-muted);margin-left:auto;">₱${parseFloat(a.price_per_hour).toFixed(2)}/hr</span></label>`;
                });
            }

            if (calendarServices.accommodations.length) {
                html += '<div style="font-weight:600;margin:0.75rem 0 0.25rem;color:var(--text-secondary);">Rooms & Cottages</div>';
                calendarServices.accommodations.forEach(acc => {
                    html += `<label class="service-check-item"><input type="checkbox" class="bundle-acc-chk" value="${acc.id}" data-price="${acc.price_per_night}"> ${escapeHtml(acc.name)} <small style="color:var(--text-muted);">(${escapeHtml(acc.type)})</small> <span style="color:var(--text-muted);margin-left:auto;">₱${parseFloat(acc.price_per_night).toFixed(2)}/night</span></label>`;
                });
            }

            if (!html) html = '<p class="text-muted">No services available. Add activities or accommodations first.</p>';
            container.innerHTML = html;
        }

        function calculateBundleFinalPrice() {
            const original = parseFloat(document.getElementById('bundleOriginalPrice').value) || 0;
            const discount = parseFloat(document.getElementById('bundleDiscount').value) || 0;
            const finalPrice = original - (original * discount / 100);
            document.getElementById('bundleFinalPrice').value = Math.max(0, finalPrice).toFixed(2);
        }

        function saveCalendarBundle() {
            const name = document.getElementById('bundleName').value.trim();
            const finalPrice = parseFloat(document.getElementById('bundleFinalPrice').value) || 0;
            if (!name) { alert('Bundle name is required.'); return; }
            if (finalPrice <= 0) { alert('Final price must be greater than zero.'); return; }

            const activityIds = [...document.querySelectorAll('.bundle-act-chk:checked')].map(el => el.value);
            const accommodationIds = [...document.querySelectorAll('.bundle-acc-chk:checked')].map(el => el.value);
            const originalPrice = document.getElementById('bundleOriginalPrice').value;
            const discount = document.getElementById('bundleDiscount').value;
            const description = document.getElementById('bundleDescription').value.trim();
            const amenities = JSON.stringify({
                original_price: originalPrice,
                discount_percent: discount,
                calendar_date: selectedDate
            });

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('name', name);
            formData.append('description', description);
            formData.append('price', finalPrice);
            formData.append('capacity', '1');
            formData.append('amenities', amenities);
            formData.append('is_active', '1');
            formData.append('activity_ids', activityIds.join(','));
            formData.append('accommodation_ids', accommodationIds.join(','));

            fetch('../php/add_edit_bundle.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        createBundleModal.hide();
                        alert('Bundle created successfully!');
                    } else {
                        alert(data.message || 'Failed to create bundle.');
                    }
                })
                .catch(() => alert('Failed to create bundle.'));
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }

        function formatStatus(status) {
            if (!status) return '—';
            return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        function capitalize(str) {
            if (!str) return '—';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            const div = document.createElement('div');
            div.textContent = String(text);
            return div.innerHTML;
        }

        function setupEventListeners() {
            document.querySelector('.calendar-nav button:nth-child(1)').onclick = previousMonth;
            document.querySelector('.calendar-nav button:nth-child(2)').onclick = goToToday;
            document.querySelector('.calendar-nav button:nth-child(3)').onclick = nextMonth;
        }

        async function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            await loadCalendarData();
            renderCalendar();
        }

        async function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            await loadCalendarData();
            renderCalendar();
        }

        async function goToToday() {
            const today = new Date();
            currentMonth = today.getMonth();
            currentYear = today.getFullYear();
            await loadCalendarData();
            renderCalendar();
        }
    </script>
</body>
</html>
