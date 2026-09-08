<?php
session_start();
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veloura Nails | Luxury Studio</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gold-primary: #c5a059;
            --gold-gradient: linear-gradient(135deg, #d4af37, #f3e5ab, #aa771c);
            --bg-color: #0d0d0d;
            --card-bg: #161616;
            --text-color: #f8f9fa;
            --text-muted: #b0b0b0;
            --border-color: rgba(197, 160, 89, 0.25);
            --nav-bg: rgba(13, 13, 13, 0.95);
            --input-bg: #0d0d0d;
        }

        body.light-mode {
            --bg-color: #f4f1ea;
            --card-bg: #ffffff;
            --text-color: #1c1c1c;
            --text-muted: #555555;
            --border-color: rgba(197, 160, 89, 0.4);
            --nav-bg: rgba(244, 241, 234, 0.95);
            --input-bg: #eae6df;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            scroll-behavior: smooth;
            transition: background-color 0.3s, color 0.3s;
        }

        h1, h2, h3, h4, h5, .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .gold-text {
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        body.light-mode .gold-text {
            background: none;
            -webkit-text-fill-color: initial;
            color: #997328;
        }

        .navbar {
            background-color: var(--nav-bg);
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            transition: background-color 0.3s;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 400;
            letter-spacing: 1px;
            transition: 0.3s;
            font-size: 0.88rem;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--gold-primary) !important;
        }

        .theme-toggle-btn {
            background: transparent;
            border: 1px solid var(--gold-primary);
            color: var(--gold-primary);
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .theme-toggle-btn:hover {
            background: var(--gold-primary);
            color: #000;
        }

        .btn-gold {
            background: var(--gold-gradient);
            color: #000;
            font-weight: 600;
            border: none;
            padding: 8px 22px;
            border-radius: 2px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s ease;
        }

        body.light-mode .btn-gold {
            background: #997328;
            color: #ffffff;
        }

        .btn-gold:hover {
            box-shadow: 0 0 15px rgba(197, 160, 89, 0.5);
            transform: translateY(-2px);
            color: #000;
        }

        .profile-icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-primary);
        }

        /* ඩ්‍රොප්ඩවුන් බොක්ක නිවැරදිව පෙළගැස්වීම සඳහා සකස් කරන ලදී */
        .dropdown-menu {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            width: 310px; 
            padding: 15px;
            border-radius: 8px;
        }

        .dropdown-item {
            color: var(--text-color);
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 0.85rem;
            white-space: normal; 
        }

        .dropdown-item:hover {
            background-color: rgba(197, 160, 89, 0.1);
            color: var(--gold-primary);
        }

        .hero-section {
            background: linear-gradient(rgba(13, 13, 13, 0.65), rgba(13, 13, 13, 0.75)), 
                        url('https://images.unsplash.com/photo-1604654894610-df63bc536371?q=80&w=2000') center/cover no-repeat;
            height: 75vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 40px;
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
        }

        .service-card, .gallery-card, .review-card, .info-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .service-card:hover, .gallery-card:hover, .info-card:hover {
            border-color: var(--gold-primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .service-img, .gallery-img {
            height: 250px;
            object-fit: cover;
            width: 100%;
            filter: brightness(0.9);
            transition: 0.4s;
        }

        .gallery-card:hover .gallery-img {
            transform: scale(1.05);
            filter: brightness(1);
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 12px;
        }

        .form-control:focus {
            background-color: var(--input-bg);
            border-color: var(--gold-primary);
            color: var(--text-color);
            box-shadow: none;
        }

        footer {
            border-top: 1px solid var(--border-color);
            background-color: var(--card-bg);
        }

        .text-secondary {
            color: var(--text-muted) !important;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand font-luxury fs-3 gold-text fw-bold me-auto" href="index.php">
                <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS
            </a>
            
            <div class="d-flex align-items-center order-lg-3 ms-3">
                <button class="theme-toggle-btn me-3" id="themeToggle" title="Toggle Light/Dark Mode">
                    <i class="fa-solid fa-sun" id="themeIcon"></i>
                </button>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center text-uppercase">
                    <li class="nav-item me-2"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="#contact">Contact Us</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="#reviews">Reviews</a></li>
                    <a href="customer_dashboard.php" class="btn btn-outline-warning btn-sm me-2 position-relative" title="My Appointments">
    <i class="fa-solid fa-calendar-days fs-6"></i>
    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
        <span class="visually-hidden">New alerts</span>
    </span>
</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php 
                            if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_email'])) {
                                $uid = $_SESSION['user_id'];
                                $stmt = $conn->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
                                $stmt->bind_param("i", $uid);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($row = $result->fetch_assoc()) {
                                    $_SESSION['user_name'] = $row['name'];
                                    $_SESSION['user_email'] = $row['email'];
                                    $_SESSION['profile_picture'] = $row['profile_picture'];
                                }
                                $stmt->close();
                            }
                            $profile_pic = !empty($_SESSION['profile_picture']) ? 'uploads/' . $_SESSION['profile_picture'] : 'uploads/default.png';
                        ?>
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="profile-icon-circle">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li class="px-3 py-2 text-center border-bottom border-secondary mb-2">
                                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="rounded-circle mb-2" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid var(--gold-primary);">
                                    <h6 class="font-luxury gold-text mb-1"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h6>
                                    <p class="text-secondary small mb-0 text-lowercase"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'client@veloura.com'); ?></p>
                                </li>
                                <li><a class="dropdown-item small" href="account_settings.php"><i class="fa-solid fa-gear me-2 gold-text"></i>Account Settings</a></li>
                                <li><hr class="dropdown-divider border-secondary my-1"></li>
                                <li><a class="dropdown-item small text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign Out</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- නිවැරදි ලෙස සකස් කළ Sign In ඩ්‍රොප්ඩවුන් මෙනුව -->
                        <li class="nav-item dropdown ms-2">
                            <button class="btn btn-gold dropdown-toggle px-4 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow mt-2 border border-warning">
                                <li><h6 class="dropdown-header text-warning fw-bold text-center tracking-wider">SELECT PORTAL</h6></li>
                                <li><hr class="dropdown-divider border-secondary my-2"></li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="login.php">
                                        <i class="fa-solid fa-user me-2 text-warning fs-6" style="width: 20px;"></i> 
                                        <span>Customer Login / Register</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="worker_login.php">
                                        <i class="fa-solid fa-user-tie me-2 text-warning fs-6" style="width: 20px;"></i> 
                                        <span>Worker Login</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="admin_login.php">
                                        <i class="fa-solid fa-user-shield me-2 text-warning fs-6" style="width: 20px;"></i> 
                                        <span>Owner / Admin Login</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary my-2"></li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center" href="account_settings.php">
                                        <i class="fa-solid fa-gear me-2 text-warning fs-6" style="width: 20px;"></i> 
                                        <span>Account Settings</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center text-danger" href="logout.php">
                                        <i class="fa-solid fa-right-from-bracket me-2 fs-6" style="width: 20px;"></i> 
                                        <span>Sign Out</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Banner Section -->
    <div class="hero-section">
        <div class="container px-4">
            <p class="text-uppercase gold-text mb-2 tracking-widest fw-semibold">Welcome to Premium Care</p>
            <h1 class="display-2 fw-bold mb-4 font-luxury text-white">Elegance at Your Fingertips</h1>
            <p class="fs-5 text-light opacity-75 mx-auto" style="max-width: 650px;">
                Indulge in bespoke nail artistry, restorative manicures, and opulent foot spa treatments crafted for perfection.
            </p>
        </div>
    </div>

    <!-- Featured Services Section -->
    <div id="services" class="container my-5 py-4">
        <div class="text-center mb-5">
            <p class="gold-text text-uppercase mb-1 fs-6">Pure Indulgence</p>
            <h2 class="display-5 font-luxury fw-bold">Our Signature Services</h2>
            <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <img src="https://images.unsplash.com/photo-1519014816548-bf5fe059798b?q=80&w=600" class="service-img" alt="Manicure">
                        <div class="p-4 text-center">
                            <h4 class="font-luxury gold-text mb-2">Veloura Gel Manicure</h4>
                            <p class="text-secondary fs-6 mb-3">Long-lasting LED cured gel polish with anti-chip formula and deep moisture therapy.</p>
                        </div>
                    </div>
                    <div class="pb-4 text-center">
                        <a href="services.php" class="text-decoration-none gold-text fw-semibold">View Pricing &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=600" class="service-img" alt="Pedicure">
                        <div class="p-4 text-center">
                            <h4 class="font-luxury gold-text mb-2">Luxury Foot Spa Pedicure</h4>
                            <p class="text-secondary fs-6 mb-3">Warm essential oil soak, exfoliating scrub, massage, and hydrating foot mask.</p>
                        </div>
                    </div>
                    <div class="pb-4 text-center">
                        <a href="services.php" class="text-decoration-none gold-text fw-semibold">View Pricing &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="service-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <img src="https://images.unsplash.com/photo-1632345031435-8727f6897d53?q=80&w=600" class="service-img" alt="Nail Art">
                        <div class="p-4 text-center">
                            <h4 class="font-luxury gold-text mb-2">Acrylic Full Set Extensions</h4>
                            <p class="text-secondary fs-6 mb-3">Full set nail extensions using premium acrylic powder with durable finish.</p>
                        </div>
                    </div>
                    <div class="pb-4 text-center">
                        <a href="services.php" class="text-decoration-none gold-text fw-semibold">View Pricing &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="services.php" class="btn btn-gold px-5 py-3 fs-6">Explore Full Services Menu</a>
        </div>
    </div>

    <!-- Studio Gallery Section -->
    <div id="gallery" class="container my-5 py-4 border-top border-secondary">
        <div class="text-center mb-5">
            <p class="gold-text text-uppercase mb-1 fs-6">Our Work</p>
            <h2 class="display-5 font-luxury fw-bold">Studio Gallery</h2>
            <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1604654894610-df63bc536371?q=80&w=600" class="gallery-img" alt="Gallery 1">
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1632345031435-8727f6897d53?q=80&w=600" class="gallery-img" alt="Gallery 2">
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1519014816548-bf5fe059798b?q=80&w=600" class="gallery-img" alt="Gallery 3">
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1607779097040-26e80aa78e66?q=80&w=600" class="gallery-img" alt="Gallery 4">
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=600" class="gallery-img" alt="Gallery 5">
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="gallery-card">
                    <img src="https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?q=80&w=600" class="gallery-img" alt="Gallery 6">
                </div>
            </div>
        </div>
    </div>

    <!-- About Us Section -->
    <div id="about" class="container my-5 py-5 border-top border-secondary">
        <div class="text-center mb-5">
            <p class="gold-text text-uppercase mb-1 fs-6">Who We Are</p>
            <h2 class="display-5 font-luxury fw-bold">About Veloura Nails Studio</h2>
            <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        </div>

        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <h3 class="font-luxury gold-text mb-3">Redefining Nail Luxury & Elegance</h3>
                <p class="text-secondary leading-relaxed mb-4">
                    Founded in the heart of Colombo, <strong>Veloura Nails Studio</strong> was created with a single vision: to elevate routine nail care into an unforgettable luxury pampering experience. We combine modern aesthetics, medical-grade hygiene standards, and internationally certified artists to bring your dream nail designs to life.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="info-card p-4 text-center">
                            <i class="fa-solid fa-award fs-1 gold-text mb-3"></i>
                            <h5 class="font-luxury gold-text">100% Certified</h5>
                            <p class="text-secondary small mb-0">Master Artists</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card p-4 text-center">
                            <i class="fa-solid fa-pump-soap fs-1 gold-text mb-3"></i>
                            <h5 class="font-luxury gold-text">Strict Hygiene</h5>
                            <p class="text-secondary small mb-0">Sterilized Tools</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Us Section -->
    <div id="contact" class="container my-5 py-5 border-top border-secondary">
        <div class="text-center mb-5">
            <p class="gold-text text-uppercase mb-1 fs-6">Get In Touch</p>
            <h2 class="display-5 font-luxury fw-bold">Contact & Location</h2>
            <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        </div>

        <div class="row g-5">
            <div class="col-lg-5">
                <div class="info-card p-4 mb-4">
                    <h4 class="font-luxury gold-text mb-4">Studio Information</h4>
                    <p class="text-secondary mb-2"><i class="fa-solid fa-clock gold-text me-2"></i> Mon – Sun: 9:00 AM – 5:00 PM</p>
                    <p class="text-secondary mb-2"><i class="fa-solid fa-phone gold-text me-2"></i> 011 223 6555</p>
                    <p class="text-secondary mb-0"><i class="fa-solid fa-location-dot gold-text me-2"></i> Colombo 03, Sri Lanka</p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="info-card p-4">
                    <h4 class="font-luxury gold-text mb-3">Send Us a Direct Message</h4>
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" placeholder="Email Address" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-gold w-100">Send Inquiry</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div id="reviews" class="container my-5 py-4 border-top border-secondary">
        <div class="text-center mb-5">
            <p class="gold-text text-uppercase mb-1 fs-6">Client Feedback</p>
            <h2 class="display-5 font-luxury fw-bold">Client Reviews</h2>
            <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="review-card p-4 text-center">
                    <p class="text-secondary small italic">"The acrylic set lasted over 4 weeks without a single chip! Truly luxury studio experience."</p>
                    <h6 class="font-luxury gold-text mb-0">- Sarah M.</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card p-4 text-center">
                    <p class="text-secondary small italic">"Loved the foot spa pedicure! Very clean, relaxing environment and professional staff."</p>
                    <h6 class="font-luxury gold-text mb-0">- Amanda P.</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card p-4 text-center">
                    <p class="text-secondary small italic">"Bespoke nail art is top tier. The attention to detail with gold foil accents is unbelievable."</p>
                    <h6 class="font-luxury gold-text mb-0">- Dilini K.</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 text-center text-secondary fs-6">
        <div class="container">
            <p class="mb-1 font-luxury gold-text fs-5">VELOURA NAILS STUDIO</p>
            <p class="mb-0">&copy; 2026 Veloura Nails. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Toggle JavaScript -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'light') {
            document.body.classList.add('light-mode');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }

        themeToggleBtn.addEventListener('click', () => {
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