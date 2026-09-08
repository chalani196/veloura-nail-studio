<?php
session_start();
include 'db_connection.php'; // ඩේටාබේස් සම්බන්ධතාවය ඇතුළත් කිරීම අත්‍යවශ්‍යයි

// කස්ටමර් ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['customer_id'])) {
    echo "<script>
        alert('Please sign in first to book an appointment!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Service name සහ Service ID එක URL එකෙන් හෝ POST එකෙන් ලබා ගැනීම
$display_service_name = isset($_GET['service']) ? trim($_GET['service']) : (isset($_POST['service']) ? trim($_POST['service']) : 'Luxury Nail Service');
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : (isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment | Veloura Nails</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --gold-color: #d4af37;
            --gold-hover: #f3e5ab;
        }

        body.light-mode {
            --bg-color: #f9f9f9;
            --card-bg: #ffffff;
            --text-color: #212529;
            --gold-color: #997a15;
            --gold-hover: #73590f;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .gold-text {
            color: var(--gold-color);
        }

        .booking-card {
            background-color: var(--card-bg);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .form-control, .form-select {
            background-color: var(--bg-color);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: var(--text-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-color);
            border-color: var(--gold-color);
            color: var(--text-color);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        .btn-warning {
            background-color: var(--gold-color);
            border-color: var(--gold-color);
            color: #121212;
            font-weight: 600;
        }

        .btn-warning:hover {
            background-color: var(--gold-hover);
            border-color: var(--gold-hover);
        }

        .theme-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg py-3 border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand font-luxury gold-text fs-4" href="index.php">
                <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS
            </a>
            <div class="d-flex align-items-center">
                <div class="theme-toggle me-3 gold-text" id="themeToggle" title="Toggle Theme">
                    <i class="fa-solid fa-sun fs-5" id="themeIcon"></i>
                </div>
                <a href="services.php" class="btn btn-outline-warning btn-sm px-3">View Services</a>
            </div>
        </div>
    </nav>

    <!-- Booking Section -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="booking-card p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <h2 class="font-luxury gold-text mb-2">Book Your Session</h2>
                        <p class="text-secondary small">Fill in your details below to reserve your luxury nail appointment.</p>
                        <p class="text-warning small mb-0">Selected Service: <strong><?php echo htmlspecialchars($display_service_name); ?></strong></p>
                    </div>

                    <!-- Booking Form -->
                    <form action="process_booking.php" method="POST">
                        
                        <!-- Hidden Fields to send selected service and service id -->
                        <input type="hidden" name="service" value="<?php echo htmlspecialchars($display_service_name); ?>">
                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="fullName" class="form-label small text-secondary">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required placeholder="Enter your name">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label small text-secondary">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="yourname@example.com">
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone" class="form-label small text-secondary">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required placeholder="077 XXX XXXX">
                        </div>
                        
                        <!-- Worker Selection (Dynamic from Database) -->
                        <div class="mb-3">
                            <label for="worker" class="form-label small text-secondary">Select Nail Technician (Worker)</label>
                            <select class="form-select" id="worker" name="worker" required>
                                <option value="any" selected>Any Available Worker</option>
                                <?php
                                $worker_query = "SELECT worker_id, name FROM workers";
                                $worker_result = $conn->query($worker_query);
                                
                                if ($worker_result && $worker_result->num_rows > 0) {
                                    while ($w_row = $worker_result->fetch_assoc()) {
                                        $w_id = $w_row['worker_id'];
                                        $w_name = htmlspecialchars($w_row['name']);
                                        echo '<option value="' . $w_id . '">' . $w_name . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Preferred Date -->
                        <div class="mb-3">
                            <label for="date" class="form-label small text-secondary">Preferred Date</label>
                            <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <!-- Preferred Time Slot -->
                        <div class="mb-4">
                            <label for="time" class="form-label small text-secondary">Preferred Time Slot</label>
                            <select class="form-select" id="time" name="time" required>
                                <option value="" selected disabled>Select time slot...</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="12:00:00">12:00 PM</option>
                                <option value="02:00:00">02:00 PM</option>
                                <option value="04:00:00">04:00 PM</option>
                                <option value="06:00:00">06:00 PM</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-warning w-100 py-2">Confirm Booking</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5 border-top border-secondary">
        <div class="container">
            <p class="font-luxury gold-text fs-5 mb-1"><i class="fa-solid fa-gem me-2"></i>VELOURA NAILS</p>
            <p class="text-secondary small mb-3">Elevating the art of luxury nail care.</p>
            <p class="text-secondary small mb-0">&copy; <?php echo date("Y"); ?> Veloura Nails. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Toggle Script -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'light') {
            document.body.classList.add('light-mode');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }

        themeToggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('light-mode');
            
            if (document.body.classList.contains('light-mode')) {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            } else {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>