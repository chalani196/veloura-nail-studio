<?php
session_start();
include 'db_connection.php';

// Guard: must be logged in as a worker
if (!isset($_SESSION['worker_id'])) {
    header("Location: worker_login.php");
    exit();
}

$worker_id = $_SESSION['worker_id'];

// Always pull fresh profile data
$stmt = $conn->prepare("SELECT worker_id, name, email, phone, profile_picture, experience, availability_status FROM workers WHERE worker_id = ?");
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$worker = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$worker) {
    header("Location: worker_logout.php");
    exit();
}

$worker_name = $worker['name'];

// --- BUG FIX FOR IMAGE PATH ---
// If admin dashboard already saves path starting with 'uploads/', do not prefix 'uploads/' again.
$raw_pic = $worker['profile_picture'] ?? '';
if (!empty($raw_pic)) {
    if (strpos($raw_pic, 'uploads/') === 0) {
        $profile_pic = $raw_pic; // Already contains uploads/
    } else {
        $profile_pic = 'uploads/' . $raw_pic;
    }
} else {
    $profile_pic = 'uploads/default.png';
}

// Appointments assigned to this worker
$stmt = $conn->prepare("SELECT id, customer_id, customer_name, email, phone, service, appointment_date, appointment_time, status, created_at FROM appointments WHERE worker = ? ORDER BY appointment_date DESC, appointment_time DESC");
$stmt->bind_param("s", $worker_name);
$stmt->execute();
$appointments_result = $stmt->get_result();
$appointments = [];
while ($row = $appointments_result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

// Distinct customers derived from this worker's appointments
$customers = [];
foreach ($appointments as $a) {
    $key = $a['customer_id'] . '|' . $a['email'];
    if (!isset($customers[$key])) {
        $customers[$key] = [
            'customer_id' => $a['customer_id'],
            'customer_name' => $a['customer_name'],
            'email' => $a['email'],
            'phone' => $a['phone'],
            'appointment_count' => 0
        ];
    }
    $customers[$key]['appointment_count']++;
}

// Notifications
$previous_login = $_SESSION['worker_last_login'] ?? null;
$notifications = [];

if ($previous_login !== null) {
    $previous_login_ts = strtotime($previous_login);
    foreach ($appointments as $a) {
        if (strtotime($a['created_at']) > $previous_login_ts) {
            $notifications[] = $a;
        }
    }
    usort($notifications, function ($x, $y) {
        return strtotime($y['created_at']) <=> strtotime($x['created_at']);
    });
}

$notification_ids = array_column($notifications, 'id');

function status_badge($status) {
    $status = strtolower($status);
    if ($status === 'approved') return 'bg-success text-white';
    if ($status === 'pending') return 'bg-warning text-dark';
    if ($status === 'rejected') return 'bg-danger text-white';
    return 'bg-secondary text-white';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard | Veloura Nails</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --gold-primary: #d4af37;
            --gold-gradient: linear-gradient(135deg, #f3e5ab, #d4af37, #aa771c);
            --bg-color: #0d0d0d;
            --card-bg: #181818;
            --text-color: #ffffff; /* Brightened text color for full visibility */
            --text-muted: #d0d0d0; /* Brighter muted text to fix dull color */
            --border-color: rgba(212, 175, 55, 0.35);
            --nav-bg: rgba(13, 13, 13, 0.98);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        h1, h2, h3, h4, h5, .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        /* Gold Text with Solid Fallback Color to prevent transparency issues on non-webkit renderers */
        .gold-text {
            color: var(--gold-primary);
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar {
            background-color: var(--nav-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .profile-icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-primary);
        }

        .info-card, .table-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        .table {
            color: var(--text-color);
            margin-bottom: 0;
        }

        .table thead th {
            color: var(--gold-primary);
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td, .table th {
            border-color: var(--border-color);
            vertical-align: middle;
            color: var(--text-color);
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            color: var(--text-muted);
            border: none;
            font-weight: 600;
        }

        .nav-tabs .nav-link:hover {
            color: var(--gold-primary);
        }

        .nav-tabs .nav-link.active {
            background: transparent;
            color: var(--gold-primary);
            border-bottom: 2px solid var(--gold-primary);
        }

        .btn-outline-gold {
            border: 1px solid var(--gold-primary);
            color: var(--gold-primary);
            background: transparent;
            font-weight: 500;
        }

        .btn-outline-gold:hover {
            background: var(--gold-primary);
            color: #000;
        }

        .notif-btn {
            position: relative;
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--gold-primary);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .notif-btn:hover {
            border-color: var(--gold-primary);
            background: rgba(212, 175, 55, 0.1);
        }

        .notif-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc3545;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        .notif-dropdown {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            width: 340px;
            max-height: 380px;
            overflow-y: auto;
            padding: 10px;
            border-radius: 8px;
        }

        .notif-item {
            display: block;
            padding: 10px 12px;
            border-radius: 6px;
            color: var(--text-color);
            text-decoration: none;
            border-left: 3px solid var(--gold-primary);
            margin-bottom: 6px;
            background: rgba(212, 175, 55, 0.08);
        }

        .notif-item:hover {
            background: rgba(212, 175, 55, 0.2);
            color: var(--text-color);
        }

        .new-tag {
            font-size: 0.65rem;
            background: var(--gold-gradient);
            color: #000;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand font-luxury fs-4 gold-text fw-bold" href="index.php">
                <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS
            </a>
            <div class="d-flex align-items-center ms-auto">

                <!-- Notification Bell -->
                <div class="dropdown me-3">
                    <button class="notif-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <?php if (count($notifications) > 0): ?>
                            <span class="notif-count"><?php echo count($notifications); ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow p-2">
                        <li class="px-2 py-1 mb-1">
                            <h6 class="font-luxury gold-text mb-0">Notifications</h6>
                        </li>
                        <?php if (empty($notifications)): ?>
                            <li class="text-center text-muted small py-3">
                                <i class="fa-solid fa-bell-slash mb-2 d-block fs-5"></i>
                                Nothing new since your last visit
                            </li>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <li>
                                    <a href="#appointments-pane" data-bs-toggle="tab" data-bs-target="#appointments-pane" class="notif-item" onclick="document.getElementById('appointments-tab').click();">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong class="small text-white"><?php echo htmlspecialchars($n['customer_name']); ?></strong>
                                            <span class="new-tag">New</span>
                                        </div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($n['service']); ?></div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($n['appointment_date'] . ' ' . $n['appointment_time']))); ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="profile-icon-circle me-2">
                <span class="me-3 small fw-semibold text-white d-none d-sm-inline"><?php echo htmlspecialchars($worker_name); ?></span>
                <a href="worker_logout.php" class="btn btn-outline-gold btn-sm">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Sign Out
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <!-- Profile Summary -->
        <div class="info-card p-4 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-auto">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid var(--gold-primary);">
                </div>
                <div class="col">
                    <h3 class="font-luxury gold-text mb-1"><?php echo htmlspecialchars($worker['name']); ?></h3>
                    <p class="text-muted small mb-1"><i class="fa-solid fa-envelope me-2 gold-text"></i><?php echo htmlspecialchars($worker['email']); ?></p>
                    <?php if (!empty($worker['phone'])): ?>
                        <p class="text-muted small mb-1"><i class="fa-solid fa-phone me-2 gold-text"></i><?php echo htmlspecialchars($worker['phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($worker['experience'])): ?>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-star me-2 gold-text"></i><?php echo htmlspecialchars($worker['experience']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-auto text-end">
                    <?php
                        $avail = strtolower($worker['availability_status'] ?? '');
                        $badge_class = $avail === 'available' ? 'bg-success text-white' : 'bg-secondary text-white';
                    ?>
                    <span class="badge <?php echo $badge_class; ?> px-3 py-2"><?php echo htmlspecialchars($worker['availability_status'] ?? 'Unknown'); ?></span>
                </div>
            </div>
        </div>

        <!-- View Details Tabs -->
        <h5 class="font-luxury gold-text mb-3"><i class="fa-solid fa-list-check me-2"></i>View Details</h5>

        <ul class="nav nav-tabs mb-4" id="detailsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments-pane" type="button" role="tab">
                    <i class="fa-solid fa-calendar-check me-1"></i> Appointments (<?php echo count($appointments); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers-pane" type="button" role="tab">
                    <i class="fa-solid fa-users me-1"></i> Customers (<?php echo count($customers); ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Appointments Pane -->
            <div class="tab-pane fade show active" id="appointments-pane" role="tabpanel">
                <div class="table-card p-3">
                    <?php if (empty($appointments)): ?>
                        <p class="text-muted text-center my-4 mb-0">No appointments assigned to you yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $a): ?>
                                        <tr>
                                            <td>
                                                <span class="text-white fw-medium"><?php echo htmlspecialchars($a['customer_name']); ?></span>
                                                <?php if (in_array($a['id'], $notification_ids)): ?>
                                                    <span class="new-tag ms-1">New</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($a['service']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($a['appointment_date']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($a['appointment_time']))); ?></td>
                                            <td><span class="badge <?php echo status_badge($a['status']); ?>"><?php echo htmlspecialchars($a['status']); ?></span></td>
                                            <td class="small text-muted">
                                                <?php echo htmlspecialchars($a['email']); ?><br>
                                                <?php echo htmlspecialchars($a['phone']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customers Pane -->
            <div class="tab-pane fade" id="customers-pane" role="tabpanel">
                <div class="table-card p-3">
                    <?php if (empty($customers)): ?>
                        <p class="text-muted text-center my-4 mb-0">No customer records yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Total Appointments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $c): ?>
                                        <tr>
                                            <td class="text-white fw-medium"><?php echo htmlspecialchars($c['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($c['email']); ?></td>
                                            <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                            <td><span class="badge bg-secondary text-white"><?php echo $c['appointment_count']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>