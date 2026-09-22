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
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
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
                    <div class="detail-row"><span class="detail-label">Selected Date</span><span class="detail-value" id="detailDate">�</span></div>
                    <div class="detail-row"><span class="detail-label">Total Bookings</span><span class="detail-value" id="detailTotal">0</span></div>
                    <div class="detail-row"><span class="detail-label">Rooms Occupied</span><span class="detail-value" id="detailRooms">0</span></div>
                    <div class="detail-row"><span class="detail-label">Cottages Occupied</span><span class="detail-value" id="detailCottages">0</span></div>
                    <div class="detail-row"><span class="detail-label">Activities Reserved</span><span class="detail-value" id="detailActivities">0</span></div>
                    <div id="blockedReasonRow" class="detail-row d-none"><span class="detail-label">Block Reason</span><span class="detail-value" id="detailBlockReason">�</span></div>
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
                    <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Bookings � <span id="bookingsListDate"></span></h5>
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
                            <label class="form-label" style="color: var(--text-primary);">Original Price (&#8369;)</label>
                            <input type="number" class="form-ctrl" id="bundleOriginalPrice" min="0" step="0.01" value="0" oninput="calculateBundleFinalPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: var(--text-primary);">Discount %</label>
                            <input type="number" class="form-ctrl" id="bundleDiscount" min="0" max="100" step="0.01" value="0" oninput="calculateBundleFinalPrice()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: var(--text-primary);">Final Price (&#8369;) <span style="color: var(--accent-danger);">*</span></label>
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

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>

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
                    const phoneDisplay = (b.phone_number && b.phone_number !== 'N/A' && b.phone_number !== 'None') ? b.phone_number : 'No phone provided';
                    const checkIn = formatDateTime12(b.check_in_date, b.check_in_time);
                    const checkOut = formatDateTime12(b.check_out_date, b.check_out_time);

                    document.getElementById('bookingDetailBody').innerHTML = `
                        <div class="detail-row"><span class="detail-label">Reference</span><span class="detail-value">${escapeHtml(b.booking_reference)}</span></div>
                        <div class="detail-row"><span class="detail-label">Customer</span><span class="detail-value">${escapeHtml(b.full_name)}</span></div>
                        <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">${escapeHtml(b.email)}</span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">${escapeHtml(phoneDisplay)}</span></div>
                        <div class="detail-row"><span class="detail-label">Package / Activity</span><span class="detail-value">${escapeHtml(b.item_name || b.booking_type)}</span></div>
                        <div class="detail-row"><span class="detail-label">Booking Status</span><span class="detail-value">${escapeHtml(formatStatus(b.status_name))}</span></div>
                        <div class="detail-row"><span class="detail-label">Payment Status</span><span class="detail-value">${escapeHtml(capitalize(b.payment_status))}</span></div>
                        <div class="detail-row"><span class="detail-label">Check-in</span><span class="detail-value">${escapeHtml(checkIn)}</span></div>
                        <div class="detail-row"><span class="detail-label">Check-out</span><span class="detail-value">${escapeHtml(checkOut)}</span></div>
                        <div class="detail-row"><span class="detail-label">Guests</span><span class="detail-value">${b.number_of_guests}</span></div>
                        <div class="detail-row"><span class="detail-label">Total Amount</span><span class="detail-value">&#8369;${parseFloat(b.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span></div>
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
                    html += `<label class="service-check-item"><input type="checkbox" class="bundle-act-chk" value="${a.id}" data-price="${a.price_per_hour}"> ${escapeHtml(a.name)} <span style="color:var(--text-muted);margin-left:auto;">&#8369;${parseFloat(a.price_per_hour).toFixed(2)}/hr</span></label>`;
                });
            }

            if (calendarServices.accommodations.length) {
                html += '<div style="font-weight:600;margin:0.75rem 0 0.25rem;color:var(--text-secondary);">Rooms & Cottages</div>';
                calendarServices.accommodations.forEach(acc => {
                    html += `<label class="service-check-item"><input type="checkbox" class="bundle-acc-chk" value="${acc.id}" data-price="${acc.price_per_night}"> ${escapeHtml(acc.name)} <small style="color:var(--text-muted);">(${escapeHtml(acc.type)})</small> <span style="color:var(--text-muted);margin-left:auto;">&#8369;${parseFloat(acc.price_per_night).toFixed(2)}/night</span></label>`;
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

        function formatDateTime12(dateStr, timeStr) {
            if (!dateStr) return '—';
            if (!timeStr && dateStr.includes(' ')) {
                const parts = dateStr.split(' ');
                dateStr = parts[0];
                timeStr = parts[1];
            }

            let formattedDate = dateStr;
            try {
                const parts = dateStr.split('-');
                if (parts.length === 3) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const year = parts[0];
                    const monthIdx = parseInt(parts[1], 10) - 1;
                    const day = parseInt(parts[2], 10);
                    if (monthIdx >= 0 && monthIdx < 12) {
                        formattedDate = `${months[monthIdx]} ${day}, ${year}`;
                    }
                }
            } catch (e) {}

            if (!timeStr || timeStr === '00:00:00' || timeStr === '00:00') {
                return formattedDate;
            }

            try {
                const tParts = timeStr.split(':');
                let h = parseInt(tParts[0], 10);
                const m = tParts[1] || '00';
                if (isNaN(h)) return formattedDate;

                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                if (h === 0) h = 12;
                return `${formattedDate} ${h}:${m} ${ampm}`;
            } catch (e) {
                return formattedDate;
            }
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



