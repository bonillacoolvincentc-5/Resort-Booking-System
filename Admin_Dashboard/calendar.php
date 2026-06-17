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
                    <div class="legend-color" style="background: var(--accent-warning);"></div>
                    <span class="legend-text">Basketball</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: var(--accent-success);"></div>
                    <span class="legend-text">Swimming</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: var(--accent-danger);"></div>
                    <span class="legend-text">Gym</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: var(--accent-secondary);"></div>
                    <span class="legend-text">Adventure</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #ff6b6b, #feca57);"></div>
                    <span class="legend-text">Playground</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="booking-details-modal" id="bookingDetailsModal">
        <div class="booking-details-content">
            <div class="booking-details-header">
                <h3 class="booking-details-title" id="modalTitle">Booking Details</h3>
                <button class="booking-details-close" onclick="closeBookingDetails()">&times;</button>
            </div>
            <div id="modalBody">
                <!-- Booking details will be inserted here -->
            </div>
        </div>
    </div>

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

        // Mock booking data
        const mockBookings = {
            '2026-03-05': [
                { customer: 'John Doe', activity: 'Basketball Court', time: '10:00 AM', type: 'basketball' },
                { customer: 'Sarah Wilson', activity: 'Basketball Court', time: '2:00 PM', type: 'basketball' }
            ],
            '2026-03-08': [
                { customer: 'Mike Johnson', activity: 'Swimming Pool', time: '9:00 AM', type: 'swimming' },
                { customer: 'Emily Davis', activity: 'Swimming Pool', time: '11:00 AM', type: 'swimming' },
                { customer: 'Robert Chen', activity: 'Swimming Pool', time: '3:00 PM', type: 'swimming' }
            ],
            '2026-03-12': [
                { customer: 'Jane Smith', activity: 'Fitness Gym', time: '6:00 AM', type: 'gym' },
                { customer: 'Tom Brown', activity: 'Fitness Gym', time: '7:00 AM', type: 'gym' }
            ],
            '2026-03-15': [
                { customer: 'Lisa Anderson', activity: 'Adventure Rides', time: '1:00 PM', type: 'adventure' },
                { customer: 'David Martinez', activity: 'Adventure Rides', time: '3:00 PM', type: 'adventure' },
                { customer: 'Chris Taylor', activity: 'Adventure Rides', time: '4:00 PM', type: 'adventure' }
            ],
            '2026-03-18': [
                { customer: 'Amanda White', activity: 'Kids Playground', time: '10:00 AM', type: 'playground' }
            ],
            '2026-03-21': [
                { customer: 'Kevin Lee', activity: 'Basketball Court', time: '9:00 AM', type: 'basketball' },
                { customer: 'Michelle Garcia', activity: 'Swimming Pool', time: '11:00 AM', type: 'swimming' },
                { customer: 'James Wilson', activity: 'Fitness Gym', time: '6:00 PM', type: 'gym' }
            ],
            '2026-03-24': [
                { customer: 'Nancy Rodriguez', activity: 'Adventure Rides', time: '2:00 PM', type: 'adventure' },
                { customer: 'Paul Kim', activity: 'Kids Playground', time: '4:00 PM', type: 'playground' }
            ],
            '2026-03-27': [
                { customer: 'Rachel Green', activity: 'Basketball Court', time: '10:00 AM', type: 'basketball' },
                { customer: 'Mark Johnson', activity: 'Fitness Gym', time: '7:00 AM', type: 'gym' },
                { customer: 'Sophie Turner', activity: 'Swimming Pool', time: '1:00 PM', type: 'swimming' },
                { customer: 'Oliver Smith', activity: 'Kids Playground', time: '3:00 PM', type: 'playground' }
            ],
            '2026-03-30': [
                { customer: 'Emma Watson', activity: 'Adventure Rides', time: '12:00 PM', type: 'adventure' }
            ]
        };

        // Calendar state
        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        // Month names
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        // Initialize calendar
        function initCalendar() {
            renderCalendar();
            setupEventListeners();
        }

        // Render calendar
        function renderCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();

            // Update title
            document.querySelector('.calendar-title').textContent = `${monthNames[currentMonth]} ${currentYear}`;

            // Clear calendar grid
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            // Add day headers
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                const headerElement = document.createElement('div');
                headerElement.className = 'calendar-day-header';
                headerElement.textContent = day;
                calendarGrid.appendChild(headerElement);
            });

            // Add previous month days
            for (let i = firstDay - 1; i >= 0; i--) {
                const dayElement = createDayElement(daysInPrevMonth - i, true, false);
                calendarGrid.appendChild(dayElement);
            }

            // Add current month days
            const today = new Date();
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = currentYear === today.getFullYear() && 
                               currentMonth === today.getMonth() && 
                               day === today.getDate();
                const dayElement = createDayElement(day, false, isToday);
                calendarGrid.appendChild(dayElement);
            }

            // Add next month days
            const totalCells = calendarGrid.children.length - 7; // Subtract headers
            const remainingCells = 35 - totalCells;
            for (let day = 1; day <= remainingCells; day++) {
                const dayElement = createDayElement(day, true, false);
                calendarGrid.appendChild(dayElement);
            }
        }

        // Create day element
        function createDayElement(day, isOtherMonth, isToday) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (isOtherMonth) {
                dayElement.classList.add('other-month');
            }
            
            if (isToday) {
                dayElement.classList.add('today');
            }

            const dayNumber = document.createElement('div');
            dayNumber.className = 'calendar-day-number';
            dayNumber.textContent = day;
            dayElement.appendChild(dayNumber);

            // Add bookings if not other month
            if (!isOtherMonth) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const bookings = mockBookings[dateStr];
                
                if (bookings && bookings.length > 0) {
                    dayElement.classList.add('has-bookings');
                    
                    const eventsContainer = document.createElement('div');
                    eventsContainer.className = 'calendar-events';
                    
                    // Show first 2 bookings
                    bookings.slice(0, 2).forEach(booking => {
                        const eventElement = document.createElement('div');
                        eventElement.className = `event-item ${booking.type}`;
                        eventElement.textContent = `${booking.activity}`;
                        eventElement.onclick = (e) => {
                            e.stopPropagation();
                            showBookingDetails(dateStr, booking);
                        };
                        eventsContainer.appendChild(eventElement);
                    });
                    
                    // Show "more" if there are more bookings
                    if (bookings.length > 2) {
                        const moreElement = document.createElement('div');
                        moreElement.className = 'calendar-events';
                        moreElement.textContent = `+${bookings.length - 2} more`;
                        eventsContainer.appendChild(moreElement);
                    }
                    
                    dayElement.appendChild(eventsContainer);
                    
                    // Add click event to show all bookings for the day
                    dayElement.onclick = () => showDayBookings(dateStr);
                }
            }

            return dayElement;
        }

        // Show booking details
        function showBookingDetails(date, booking) {
            const modal = document.getElementById('bookingDetailsModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = `Booking Details - ${booking.activity}`;
            modalBody.innerHTML = `
                <div class="booking-item">
                    <h4>${booking.activity}</h4>
                    <p><strong>Customer:</strong> ${booking.customer}</p>
                    <p><strong>Time:</strong> <span class="booking-time">${booking.time}</span></p>
                    <p><strong>Date:</strong> ${formatDate(date)}</p>
                </div>
            `;
            
            modal.style.display = 'flex';
        }

        // Show all bookings for a day
        function showDayBookings(date) {
            const bookings = mockBookings[date];
            if (!bookings || bookings.length === 0) return;
            
            const modal = document.getElementById('bookingDetailsModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = `Bookings for ${formatDate(date)}`;
            
            let bookingsHtml = '';
            bookings.forEach(booking => {
                bookingsHtml += `
                    <div class="booking-item">
                        <h4>${booking.activity}</h4>
                        <p><strong>Customer:</strong> ${booking.customer}</p>
                        <p><strong>Time:</strong> <span class="booking-time">${booking.time}</span></p>
                    </div>
                `;
            });
            
            modalBody.innerHTML = bookingsHtml;
            modal.style.display = 'flex';
        }

        // Close booking details modal
        function closeBookingDetails() {
            document.getElementById('bookingDetailsModal').style.display = 'none';
        }

        // Format date
        function formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        // Setup event listeners
        function setupEventListeners() {
            // Navigation buttons
            document.querySelector('.calendar-nav button:nth-child(1)').onclick = previousMonth;
            document.querySelector('.calendar-nav button:nth-child(2)').onclick = goToToday;
            document.querySelector('.calendar-nav button:nth-child(3)').onclick = nextMonth;
            
            // Close modal when clicking outside
            document.getElementById('bookingDetailsModal').onclick = function(e) {
                if (e.target === this) {
                    closeBookingDetails();
                }
            };
        }

        // Navigation functions
        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        }

        function goToToday() {
            const today = new Date();
            currentMonth = today.getMonth();
            currentYear = today.getFullYear();
            renderCalendar();
        }

        // Initialize calendar when DOM is loaded
        document.addEventListener('DOMContentLoaded', initCalendar);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
