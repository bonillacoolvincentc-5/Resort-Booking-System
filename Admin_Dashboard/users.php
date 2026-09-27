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

// Get database connection
global $conn;

// Fetch real users from database
$query = "
    SELECT 
        u.*,
        COUNT(b.id) as booking_count
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    unset($row['password']);
    unset($row['reset_token']);
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - YP4K Resort</title>
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
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="text-muted">Manage all registered users and their permissions</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-primary" onclick="openAddStaffModal()">
                    <i class="bi bi-person-plus"></i> Add Staff Account
                </button>
            </div>
        </div>

        <div class="users-container">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $index => $user): ?>
                            <?php
                                // Generate initials from name
                                $fullName = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);
                                $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                                
                                // Determine status based on is_verified
                                $statusClass = $user['is_verified'] ? 'status-active' : 'status-inactive';
                                $statusText = $user['is_verified'] ? 'Active' : 'Inactive';
                                
                                // Format date
                                $joinedDate = date('M j, Y', strtotime($user['created_at']));
                                
                                // Avatar colors (cycle through predefined gradients)
                                $gradients = [
                                    'linear-gradient(135deg, var(--accent-primary), var(--accent-secondary))',
                                    'linear-gradient(135deg, var(--accent-success), #059669)',
                                    'linear-gradient(135deg, var(--accent-warning), #d97706)',
                                    'linear-gradient(135deg, var(--accent-danger), #dc2626)',
                                    'linear-gradient(135deg, var(--accent-secondary), #6366f1)'
                                ];
                                $avatarGradient = $gradients[$index % count($gradients)];
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar" style="background: <?php echo $avatarGradient; ?>;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($fullName); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>Customer</td>
                                <td><?php echo $joinedDate; ?></td>
                                <td><?php echo $user['booking_count']; ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
                                <p style="color: var(--text-secondary); margin-top: 0.5rem;">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background: #101B17 !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 1rem !important; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.8) !important;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="viewUserModalLabel" style="color: #fff !important; font-weight: 700;">
                        <i class="bi bi-person-badge text-primary" style="font-size: 1.5rem;"></i> User Account Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="color: #B8C4BE !important; background: #101B17 !important;">
                    <div class="row g-4">
                        <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-center" style="border-right: 1px solid var(--border-color);">
                            <div class="avatar-circle mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h4 id="modalUserName" class="mb-1" style="font-weight: 700; color: #fff;">---</h4>
                            <p id="modalUserEmail" class="text-muted small mb-0">---</p>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3" style="letter-spacing: 1px; color: var(--accent-primary) !important;">Personal Information</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Full Name</label>
                                    <span id="modalFullName" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Phone Number</label>
                                    <span id="modalPhone" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-12">
                                    <label class="text-muted small d-block">Birthdate</label>
                                    <span id="modalBirthdate" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small d-block">Complete Address</label>
                                    <span id="modalAddress" class="fw-medium text-white">---</span>
                                </div>
                            </div>

                            <h6 class="text-uppercase text-muted small fw-bold mb-3" style="letter-spacing: 1px; color: var(--accent-primary) !important;">Account Activity</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Date Joined</label>
                                    <span id="modalJoined" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Total Bookings</label>
                                    <span id="modalBookings" class="fw-medium badge bg-primary px-3 py-1">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #101B17 !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 1rem !important; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.8) !important;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="addStaffModalLabel" style="color: #fff !important; font-weight: 700;">
                        <i class="bi bi-person-plus text-primary" style="font-size: 1.5rem;"></i> Add Staff Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="color: #B8C4BE !important; background: #101B17 !important;">
                    <div id="addStaffAlert" class="alert" style="display:none; border-radius: 0.5rem; font-size: 0.875rem;"></div>
                    <form id="addStaffForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="staffFirstName" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="staffLastName" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="staffEmail" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="staffPhone" required pattern="^09\d{9}$" placeholder="09XXXXXXXXX" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-bold">Temporary Password <span class="text-danger">*</span></label>
                                <div class="auth-input-group" style="position: relative; display: flex; align-items: center;">
                                    <input type="password" class="form-control" id="staffPassword" required minlength="8" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; width: 100%; padding-right: 2.5rem;">
                                    <button type="button" class="auth-eye-btn" onclick="toggleStaffPasswordVisibility()" id="toggleStaffPassword" aria-label="Toggle password visibility" style="position: absolute; right: 0.5rem; background: transparent; border: none; color: #94a3b8; display: flex; align-items: center; justify-content: center; height: 100%; padding: 0 0.5rem; cursor: pointer; z-index: 10;">
                                        <i class="bi bi-eye" id="staffEyeIcon"></i>
                                    </button>
                                </div>
                                <div id="staff-password-checker" style="font-size: 0.72rem; margin-top: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                    <div id="staff-req-length" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 8+ characters</div>
                                    <div id="staff-req-capital" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 capital letter</div>
                                    <div id="staff-req-number" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 number</div>
                                    <div id="staff-req-special" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 special char</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 0.5rem; background: rgba(255,255,255,0.1); border: none; color: #fff;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" id="addStaffSubmitBtn" onclick="submitAddStaff()" style="border-radius: 0.5rem; border: none;">Add Staff Account</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleStaffPasswordVisibility() {
            const passwordField = document.getElementById('staffPassword');
            const eyeIcon = document.getElementById('staffEyeIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function openAddStaffModal() {
            const modal = new bootstrap.Modal(document.getElementById('addStaffModal'));
            document.getElementById('addStaffForm').reset();
            resetStaffPasswordChecker();
            document.getElementById('addStaffAlert').style.display = 'none';
            modal.show();
        }

        function resetStaffPasswordChecker() {
            const reqs = [
                { id: 'staff-req-length', text: '8+ characters' },
                { id: 'staff-req-capital', text: '1 capital letter' },
                { id: 'staff-req-number', text: '1 number' },
                { id: 'staff-req-special', text: '1 special char' }
            ];
            reqs.forEach(r => {
                const el = document.getElementById(r.id);
                if (el) {
                    el.style.color = 'rgba(255,255,255,0.5)';
                    el.style.background = 'rgba(255,255,255,0.05)';
                    el.style.borderColor = 'rgba(255,255,255,0.05)';
                    el.innerHTML = '<i class="bi bi-circle"></i> ' + r.text;
                }
            });
        }

        function setupStaffPasswordChecker() {
            const passInput = document.getElementById('staffPassword');
            if (!passInput) return;

            passInput.addEventListener('input', function() {
                const p = this.value;
                
                function updateReq(elementId, isValid, text) {
                    const el = document.getElementById(elementId);
                    if (!el) return;
                    if (isValid) {
                        el.style.color = '#84ea3b';
                        el.style.background = 'rgba(132, 234, 59, 0.1)';
                        el.style.borderColor = 'rgba(132, 234, 59, 0.3)';
                        el.innerHTML = '<i class="bi bi-check2-circle"></i> ' + text;
                    } else {
                        el.style.color = 'rgba(255,255,255,0.5)';
                        el.style.background = 'rgba(255,255,255,0.05)';
                        el.style.borderColor = 'rgba(255,255,255,0.05)';
                        el.innerHTML = '<i class="bi bi-circle"></i> ' + text;
                    }
                }

                updateReq('staff-req-length', p.length >= 8, '8+ characters');
                updateReq('staff-req-capital', /[A-Z]/.test(p), '1 capital letter');
                updateReq('staff-req-number', /\d/.test(p), '1 number');
                updateReq('staff-req-special', /[!@#$%^&*(),.?":{}|<>]/.test(p), '1 special char');
            });
        }
        document.addEventListener('DOMContentLoaded', setupStaffPasswordChecker);

        async function submitAddStaff() {
            const btn = document.getElementById('addStaffSubmitBtn');
            const alertEl = document.getElementById('addStaffAlert');
            const form = document.getElementById('addStaffForm');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const passwordVal = document.getElementById('staffPassword').value;
            if (passwordVal.length < 8 || !/[A-Z]/.test(passwordVal) || !/\d/.test(passwordVal) || !/[!@#$%^&*(),.?":{}|<>]/.test(passwordVal)) {
                alertEl.className = 'alert alert-danger';
                alertEl.textContent = 'Password does not meet all requirements (min 8 characters, 1 capital letter, 1 number, and 1 special character).';
                alertEl.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
            alertEl.style.display = 'none';

            const payload = {
                first_name: document.getElementById('staffFirstName').value.trim(),
                last_name: document.getElementById('staffLastName').value.trim(),
                email: document.getElementById('staffEmail').value.trim(),
                password: document.getElementById('staffPassword').value,
                phone: document.getElementById('staffPhone').value.trim()
            };

            try {
                const res = await fetch('../php/add_staff_account.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();
                if (data.success) {
                    alertEl.className = 'alert alert-success';
                    alertEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>Staff account added successfully!';
                    alertEl.style.display = 'block';
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Done';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alertEl.className = 'alert alert-danger';
                    alertEl.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + (data.message || 'Error adding staff account.');
                    alertEl.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = 'Add Staff Account';
                }
            } catch (err) {
                console.error(err);
                alertEl.className = 'alert alert-danger';
                alertEl.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Connection error. Please try again.';
                alertEl.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = 'Add Staff Account';
            }
        }

        const usersData = <?php echo json_encode($users); ?>;

        function viewUser(userId) {
            const user = usersData.find(u => u.id == userId);
            if (!user) return;
            
            // Construct full name
            const fname = user.first_name || '';
            const mname = user.middle_name ? ' ' + user.middle_name + ' ' : ' ';
            const lname = user.last_name || '';
            const sfx = user.suffix ? ' ' + user.suffix : '';
            const fullName = (fname + mname + lname + sfx).trim().replace(/\s+/g, ' ') || 'No Name';

            // Construct address
            let address = user.address || '';
            if (!address || /\d{8,}/.test(address)) {
                const parts = [user.barangay, user.city_municipality, user.province, user.region].filter(Boolean);
                address = parts.join(', ') || 'No Address Provided';
            }

            document.getElementById('modalUserName').textContent = fullName;
            document.getElementById('modalFullName').textContent = fullName;
            document.getElementById('modalUserEmail').textContent = user.email || 'N/A';
            document.getElementById('modalPhone').textContent = user.phone_number || 'N/A';
            document.getElementById('modalBirthdate').textContent = user.birthdate || 'Not specified';
            document.getElementById('modalAddress').textContent = address;
            document.getElementById('modalBookings').textContent = user.booking_count || '0';

            // Date joined format
            if (user.created_at) {
                const d = new Date(user.created_at);
                document.getElementById('modalJoined').textContent = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            } else {
                document.getElementById('modalJoined').textContent = 'N/A';
            }

            const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
            modal.show();
        }

        // Table interactions
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                if (confirm('Are you sure you want to delete this user?')) {
                    alert('User deleted');
                }
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>



