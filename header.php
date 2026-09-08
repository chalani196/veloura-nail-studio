<?php
// Session එක ආරම්භ කිරීම (මෙය ෆයිල් එකේ මුලටම තිබිය යුතුය)
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Veloura Nails - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary px-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-warning" href="index.php">VELOURA NAILS</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="booking.php">Book Appointment</a></li>
                </ul>

                <!-- Dynamic Auth Section in Navbar -->
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <!-- පාරිභෝගිකයා Login වී ඇත්නම් පෙනෙන කොටස -->
                        <div class="d-flex align-items-center">
                            <?php if (!empty($_SESSION['customer_pic'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['customer_pic']); ?>" alt="Profile" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #ffc107;">
                            <?php endif; ?>
                            <span class="me-3 text-light">Hi, <?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                        </div>
                    <?php else: ?>
                        <!-- පාරිභෝගිකයා Login වී නැත්නම් පෙනෙන බටන් -->
                        <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
                        <a href="register.php" class="btn btn-warning btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5 text-center">
        <h1>Welcome to Veloura Nails</h1>
        <p class="text-secondary">Experience the luxury nail care and styling.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>