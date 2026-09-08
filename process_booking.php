<?php
session_start();

// 1. කස්ටමර් ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['customer_id'])) {
    echo "<script>
        window.location.href = 'login.php';
    </script>";
    header("Location: login.php");
    exit();
}

// 2. ඩේටාබේස් සම්බන්ධතාවය (Port 3307 සමඟ)
$host = 'localhost';
$port = '3307';
$db   = 'veloura_nail_studio';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Booking</title>
    <!-- SweetAlert2 CDN එකතු කිරීම -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1604902396888-10a80f0c727e?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
            background-color: rgba(0, 0, 0, 0.5); 
            background-blend-mode: overlay;
        }
    </style>
</head>
<body>
<?php
// 3. ෆෝම් එක SUBMIT වී ඇද්දැයි පරීක්ෂා කිරීම
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ෆෝම් එකෙන් එවන දත්ත ලබා ගැනීම
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? 'Luxury Nail Service');
    
    // Dropdown එකෙන් එවන worker ID එක ලබා ගැනීම
    $worker_input = trim($_POST['worker'] ?? 'any');
    
    $appointment_date = trim($_POST['date'] ?? '');
    $appointment_time = trim($_POST['time'] ?? '');

    // 4. අතීත දින (Past Dates) පරීක්ෂා කිරීම
    $today = date('Y-m-d');
    if ($appointment_date < $today) {
        echo "<script>
            Swal.fire({
                title: 'Veloura Nail Studio',
                text: 'Error: You cannot select a past date for booking!',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }

    // worker_id සහ worker (නම) තීරණය කිරීම
    $db_worker_id = NULL;
    $worker_name = 'Any Available Worker';

    if ($worker_input !== 'any' && $worker_input !== '') {
        $db_worker_id = (int)$worker_input;
        
        // අදාළ ID එකට අනුව workers වගුවෙන් සේවිකාවගේ නම ලබා ගැනීම
        $stmt = $pdo->prepare("SELECT name FROM workers WHERE worker_id = ?");
        $stmt->execute([$db_worker_id]);
        $w_row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($w_row) {
            $worker_name = $w_row['name'];
        }
    }

    // 5. මෙම දිනය සහ වේලාව සඳහා දැනටමත් බුකින් එකක් පවතීදැයි පරීක්ෂා කිරීම (Date & Time Availability Check)
    if ($db_worker_id !== NULL) {
        // නිශ්චිත සේවිකාවක් තෝරා ඇත්නම්, එම සේවිකාවට එම වෙලාවට වෙනත් බුකින් එකක් තිබේදැයි බලයි
        $checkStmt = $pdo->prepare("SELECT * FROM appointments WHERE worker_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'");
        $checkStmt->execute([$db_worker_id, $appointment_date, $appointment_time]);
    } else {
        // Any Available Worker තෝරා ඇත්නම්, එම වේලාවට වෙනත් බුකින්ස් තිබේදැයි බලයි (අවශ්‍ය නම් මෙය වෙනස් කළ හැක)
        $checkStmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'");
        $checkStmt->execute([$appointment_date, $appointment_time]);
    }

    if ($checkStmt->rowCount() > 0) {
        echo "<script>
            Swal.fire({
                title: 'Veloura Nail Studio',
                text: 'Sorry! This date and time slot is already booked. Please choose another time.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }

    // ලොග් වී සිටින කස්ටමර්ගේ සැබෑ ID එක ලබා ගැනීම
    $customer_id = $_SESSION['customer_id'];

    // 6. ඩේටාබේස් එකට ඇතුළත් කිරීම
    try {
        $insertStmt = $pdo->prepare("INSERT INTO appointments (customer_id, service, worker, worker_id, customer_name, email, phone, appointment_date, appointment_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        
        $insertStmt->execute([
            $customer_id, 
            $service, 
            $worker_name,  
            $db_worker_id, 
            $fullName, 
            $email, 
            $phone, 
            $appointment_date, 
            $appointment_time
        ]);

        echo "<script>
            Swal.fire({
                title: 'Veloura Nail Studio',
                text: 'Appointment booked successfully! Waiting for owner approval.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href='index.php';
            });
        </script>";

    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                title: 'Veloura Nail Studio',
                text: 'Database Error: " . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }

} else {
    header("Location: booking.php");
    exit();
}
?>
</body>
</html>