<?php
session_start();

require_once 'config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

admin_include('db_config.php');
/** @var mysqli $conn */

$msg     = '';
$msgType = 'success';

// ── Handle POST actions ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $label      = trim($_POST['label']      ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time   = trim($_POST['end_time']   ?? '');
        if ($start_time && $end_time) {
            $stmt = $conn->prepare("INSERT INTO time_slots (label, start_time, end_time, is_active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param('sss', $label, $start_time, $end_time);
            if ($stmt->execute()) { $msg = 'Time slot added successfully.'; }
            else { $msg = 'Error: ' . $conn->error; $msgType = 'danger'; }
            $stmt->close();
        } else { $msg = 'Start time and End time are required.'; $msgType = 'danger'; }
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['slot_id'] ?? 0);
        $conn->query("UPDATE time_slots SET is_active = NOT is_active WHERE id = $id");
        $msg = 'Slot visibility updated.';
    }

    if ($action === 'delete') {
        $id = (int)($_POST['slot_id'] ?? 0);
        $conn->query("DELETE FROM time_slots WHERE id = $id");
        $msg = 'Slot deleted.';
    }
}

$slots = $conn->query("SELECT id, label, start_time, end_time, is_active FROM time_slots ORDER BY start_time ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Manager - YP4K Resort Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
    <style>
        :root {
            --bg-primary:     #0f172a;
            --bg-secondary:   #1e293b;
            --bg-tertiary:    #334155;
            --bg-card:        rgba(30,41,59,.88);
            --text-primary:   #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted:     #94a3b8;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-danger:  #ef4444;
            --border-color:   rgba(51,65,85,.6);
            --radius-card:    1rem;
            --radius-btn:     .5rem;
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,var(--bg-primary) 0%,var(--bg-secondary) 50%,var(--bg-tertiary) 100%); color:var(--text-primary); min-height:100vh; }

        .page-wrapper { max-width:820px; margin:2rem auto; padding:0 1rem 4rem; }
        .page-title { font-size:1.75rem; font-weight:800; background:linear-gradient(135deg,#3b82f6,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:.25rem; }
        .page-subtitle { color:var(--text-muted); font-size:.9rem; margin-bottom:2rem; }

        .slot-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-card); backdrop-filter:blur(12px); box-shadow:0 10px 30px rgba(0,0,0,.25); overflow:hidden; margin-bottom:1.5rem; }
        .slot-card-header { padding:1.1rem 1.5rem; border-bottom:1px solid var(--border-color); font-weight:600; font-size:.95rem; display:flex; align-items:center; gap:.5rem; }
        .slot-card-header i { color:var(--accent-primary); font-size:1.1rem; }
        .slot-card-body { padding:1.5rem; }

        .form-label { color:var(--text-secondary); font-size:.8rem; font-weight:500; margin-bottom:.35rem; display:block; }
        .form-control { width:100%; background:var(--bg-tertiary); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-btn); padding:.7rem 1rem; font-size:.875rem; font-family:inherit; transition:border-color .25s,box-shadow .25s; }
        .form-control:focus { outline:none; border-color:var(--accent-primary); box-shadow:0 0 0 3px rgba(59,130,246,.12); }
        .form-control::placeholder { color:var(--text-muted); }
        input[type="time"]::-webkit-calendar-picker-indicator { filter:invert(1); cursor:pointer; }

        .btn { display:inline-flex; align-items:center; gap:.4rem; padding:.65rem 1.25rem; border-radius:var(--radius-btn); border:none; font-size:.875rem; font-weight:600; cursor:pointer; transition:all .2s ease; font-family:inherit; }
        .btn-primary { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
        .btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 15px rgba(59,130,246,.4); }
        .btn-danger  { background:rgba(239,68,68,.15); color:var(--accent-danger); border:1px solid rgba(239,68,68,.25); }
        .btn-danger:hover { background:rgba(239,68,68,.28); }
        .btn-ghost   { background:rgba(255,255,255,.06); color:var(--text-secondary); border:1px solid var(--border-color); }
        .btn-ghost:hover { background:rgba(255,255,255,.1); }
        .btn-sm { padding:.35rem .8rem; font-size:.8rem; }

        .slot-table { width:100%; border-collapse:collapse; }
        .slot-table th { text-align:left; padding:.65rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); border-bottom:1px solid var(--border-color); }
        .slot-table td { padding:.85rem 1rem; font-size:.875rem; border-bottom:1px solid rgba(51,65,85,.35); vertical-align:middle; }
        .slot-table tr:last-child td { border-bottom:none; }
        .slot-table tr:hover td { background:rgba(59,130,246,.04); }

        .badge { display:inline-block; padding:.2rem .6rem; border-radius:999px; font-size:.72rem; font-weight:600; }
        .badge-active   { background:rgba(16,185,129,.15); color:var(--accent-success); }
        .badge-inactive { background:rgba(148,163,184,.12); color:var(--text-muted); }

        .alert { padding:.85rem 1.25rem; border-radius:var(--radius-btn); font-size:.875rem; font-weight:500; margin-bottom:1.5rem; }
        .alert-success { background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.3); color:#6ee7b7; }
        .alert-danger  { background:rgba(239,68,68,.12);  border:1px solid rgba(239,68,68,.3);  color:#fca5a5; }

        .empty-state { text-align:center; padding:3rem 1rem; color:var(--text-muted); }
        .empty-state i { font-size:2.5rem; margin-bottom:.75rem; display:block; }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
    <div class="page-wrapper">

        <h1 class="page-title">Schedule Manager</h1>
        <p class="page-subtitle">Define bookable time slots guests select on the Activity booking form.</p>

        <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msgType) ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Add Slot Form -->
        <div class="slot-card">
            <div class="slot-card-header">
                <i class="bi bi-plus-circle-fill"></i> Add New Time Slot
            </div>
            <div class="slot-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;align-items:end;">
                        <div>
                            <label class="form-label" for="slot_label">Label <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                            <input type="text" id="slot_label" name="label" class="form-control" placeholder="e.g. Morning Session" maxlength="80">
                        </div>
                        <div>
                            <label class="form-label" for="slot_start">Start Time <span style="color:var(--accent-danger)">*</span></label>
                            <input type="time" id="slot_start" name="start_time" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label" for="slot_end">End Time <span style="color:var(--accent-danger)">*</span></label>
                            <input type="time" id="slot_end" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top:1.1rem;text-align:right;">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Slot</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Slots Table -->
        <div class="slot-card">
            <div class="slot-card-header">
                <i class="bi bi-list-check"></i> Configured Time Slots
                <span style="margin-left:auto;font-size:.78rem;color:var(--text-muted);font-weight:400;">
                    <?= count($slots) ?> slot<?= count($slots) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="slot-card-body" style="padding:0;">
                <?php if (empty($slots)): ?>
                <div class="empty-state">
                    <i class="bi bi-clock-history"></i>
                    No time slots yet. Add one above.
                </div>
                <?php else: ?>
                <table class="slot-table">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $s):
                            $startFmt = date('g:i A', strtotime($s['start_time']));
                            $endFmt   = date('g:i A', strtotime($s['end_time']));
                            $diffMins = (strtotime($s['end_time']) - strtotime($s['start_time'])) / 60;
                            $durH     = (int)floor($diffMins / 60);
                            $durM     = (int)($diffMins % 60);
                            $durLabel = $durH > 0 ? "{$durH}h" . ($durM > 0 ? " {$durM}m" : '') : "{$durM}m";
                        ?>
                        <tr>
                            <td style="font-weight:500;color:var(--text-primary);">
                                <?= $s['label'] ? htmlspecialchars($s['label']) : '<em style="color:var(--text-muted);">—</em>' ?>
                            </td>
                            <td><?= $startFmt ?></td>
                            <td><?= $endFmt ?></td>
                            <td style="color:var(--text-muted);"><?= $durLabel ?></td>
                            <td><span class="badge <?= $s['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $s['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                            <td style="text-align:right;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action"  value="toggle">
                                    <input type="hidden" name="slot_id" value="<?= (int)$s['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm" title="<?= $s['is_active'] ? 'Hide' : 'Show' ?> slot">
                                        <i class="bi bi-<?= $s['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this slot?');">
                                    <input type="hidden" name="action"  value="delete">
                                    <input type="hidden" name="slot_id" value="<?= (int)$s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete slot">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
    </div>
</body>
</html>
