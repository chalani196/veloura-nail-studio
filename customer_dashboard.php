<?php
session_start();
include 'db_connection.php'; // ඔබේ ඩේටාබේස් සම්බන්ධතා ගොනුව

// පාරිභෝගිකයා ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// අදාළ කස්ටමර්ගේ ඇපොයින්ට්මන්ට්ස් පමණක් ලබා ගැනීම
$stmt = $conn->prepare("SELECT * FROM appointments WHERE customer_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | Veloura Nails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .gold-text { color: #d4af37; }
        .card-custom { background-color: #1e1e1e; border: 1px solid rgba(212, 175, 55, 0.2); }
        /* Custom Gold Button Styling */
        .btn-gold {
            background-color: #d4af37;
            color: #121212;
            font-weight: 600;
            border: none;
        }
        .btn-gold:hover {
            background-color: #c19b2e;
            color: #121212;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg py-3 border-bottom border-secondary bg-dark">
        <div class="container">
            <a class="navbar-brand font-luxury gold-text fs-4" href="index.php">
                <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS
            </a>
            <div class="d-flex align-items-center">
                <!-- Book New Appointment button එක දැන් services.php වෙත යොමු කර ඇත -->
                <a href="services.php" class="btn btn-warning btn-sm me-3 fw-bold">Book New Appointment</a>
                <a href="logout.php" class="btn btn-gold btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="font-luxury gold-text mb-4">My Appointments Status</h2>

        <div class="card card-custom shadow-sm">
            <div class="card-body bg-dark">
                <div class="table-responsive">
                    <table class="table table-dark table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Worker</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <!-- Service Name එක පෙන්වීම -->
                                        <td class="fw-semibold text-warning"><?php echo htmlspecialchars($row['service']); ?></td>
                                        
                                        <!-- Worker Name -->
                                        <td><?php echo htmlspecialchars($row['worker'] ?? 'Any Available'); ?></td>
                                        
                                        <!-- Date & Time -->
                                        <td>
                                            <?php echo $row['appointment_date']; ?><br>
                                            <small class="text-warning"><?php echo $row['appointment_time']; ?></small>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td>
                                            <span class="badge px-3 py-2 bg-<?>
                                                <?php 
                                                    if($row['status'] == 'Approved') echo 'success';
                                                    elseif($row['status'] == 'Rejected' || $row['status'] == 'Cancelled') echo 'danger';
                                                    else echo 'warning text-dark';
                                                ?>
                                            ">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">
                                        You have no appointments yet.<br>
                                        <a href="services.php" class="btn btn-warning btn-sm mt-3 fw-bold">Browse Services & Book</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>