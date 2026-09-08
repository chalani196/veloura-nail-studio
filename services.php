<?php
session_start();

// 1. Include Database Connection (MySQLi)
require_once 'db_connection.php';

/**
 * XSS Helper Function for Output Sanitization
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Smart Category Resolver: Inspects service name & category
 * to fix database misclassifications automatically!
 */
function getRealCategory(string $name, ?string $rawCategory): string {
    $n = strtolower(trim($name));
    $c = strtolower(trim($rawCategory ?? ''));

    // Check service NAME first to fix database misclassifications
    if (strpos($n, 'pedicure') !== false) return 'Pedicure';
    if (strpos($n, 'extension') !== false) return 'Nail Extensions';
    if (strpos($n, 'art') !== false) return 'Nail Art';
    if (strpos($n, 'care') !== false) return 'Nail Care';
    if (strpos($n, 'removal') !== false) return 'Removal Services';
    if (strpos($n, 'addon') !== false) return 'Addons & Extras';
    if (strpos($n, 'bridal') !== false || strpos($n, 'package') !== false) return 'Bridal & Special Packages';
    if (strpos($n, 'manicure') !== false) return 'Manicure';

    // Fallback to Category column if Name doesn't match above keywords
    if (strpos($c, 'pedicure') !== false) return 'Pedicure';
    if (strpos($c, 'extension') !== false) return 'Nail Extensions';
    if (strpos($c, 'art') !== false) return 'Nail Art';
    if (strpos($c, 'care') !== false) return 'Nail Care';
    if (strpos($c, 'removal') !== false) return 'Removal Services';
    if (strpos($c, 'bridal') !== false || strpos($c, 'package') !== false) return 'Bridal & Special Packages';
    if (strpos($c, 'manicure') !== false) return 'Manicure';

    return !empty($rawCategory) ? ucwords(trim($rawCategory)) : 'Other Services';
}

/**
 * Get FontAwesome Icon for Category
 */
function getCategoryIcon(string $categoryName): string {
    switch ($categoryName) {
        case 'Manicure': return 'fa-hand-sparkles';
        case 'Pedicure': return 'fa-spa';
        case 'Nail Extensions': return 'fa-hand-holding-medical';
        case 'Nail Art': return 'fa-paint-brush';
        case 'Nail Care': return 'fa-shield-heart';
        case 'Removal Services': return 'fa-hand-dots';
        case 'Addons & Extras': return 'fa-plus-circle';
        case 'Bridal & Special Packages': return 'fa-crown';
        default: return 'fa-gem';
    }
}

/**
 * 2. Fetch and Smartly Categorize Services from MySQLi
 */
$services_by_category = [];

if (isset($conn) && $conn instanceof mysqli) {

    $categoryOrder = [
        'Manicure' => 1,
        'Pedicure' => 2,
        'Nail Extensions' => 3,
        'Nail Art' => 4,
        'Nail Care' => 5,
        'Removal Services' => 6,
        'Addons & Extras' => 7,
        'Bridal & Special Packages' => 8,
        'Other Services' => 9
    ];

    $query = "SELECT service_id, name, category, price, duration_minutes, description, image, created_at 
              FROM services 
              ORDER BY service_id ASC";
    
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Automatically resolve real category using service name + category
            $resolvedCat = getRealCategory($row['name'], $row['category']);
            $services_by_category[$resolvedCat][] = $row;
        }
    }

    // Sort categories in logical menu order
    uksort($services_by_category, function($a, $b) use ($categoryOrder) {
        $orderA = $categoryOrder[$a] ?? 99;
        $orderB = $categoryOrder[$b] ?? 99;
        return $orderA <=> $orderB;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services & Pricing | Veloura Nails</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
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
        }

        body.light-mode {
            --bg-color: #f4f1ea;
            --card-bg: #ffffff;
            --text-color: #1c1c1c;
            --text-muted: #555555;
            --border-color: rgba(197, 160, 89, 0.4);
            --nav-bg: rgba(244, 241, 234, 0.95);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
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
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 400;
            letter-spacing: 1px;
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

        .price-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.4s ease;
            height: 100%;
        }

        .price-card:hover {
            border-color: var(--gold-primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .service-img-top {
            height: 200px;
            object-fit: cover;
            width: 100%;
            background-color: #1a1a1e;
        }

        .category-title {
            border-bottom: 2px solid var(--gold-primary);
            padding-bottom: 10px;
            margin-bottom: 30px;
            margin-top: 50px;
            text-transform: capitalize;
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
                <button class="theme-toggle-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                    <i class="fa-solid fa-sun" id="themeIcon"></i>
                </button>
                <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center text-uppercase">
                    <li class="nav-item me-2"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item me-2"><a class="nav-link active" href="services.php">Servies</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="index.php#gallery">Gallery</a></li>
                   
                    <li class="nav-item me-2"><a class="nav-link" href="index.php#about">About Us</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="index.php#contact">Contact Us</a></li>
                    <li class="nav-item me-2"><a class="nav-link" href="booking.php">Reviews</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <div class="container my-5 text-center">
        <p class="gold-text text-uppercase mb-1 fs-6">Investment in Beauty</p>
        <h1 class="display-4 font-luxury fw-bold">Our Services & Pricing Menu</h1>
        <div style="width: 60px; height: 2px; background: var(--gold-primary); margin: 15px auto;"></div>
        <p class="text-secondary" style="max-width: 600px; margin: 0 auto;">
            Explore our curated selection of elite nail care treatments, luxury spa rituals, and custom artistry categorized for your perfection.
        </p>
    </div>

    <!-- Main Dynamic Services Container -->
    <div class="container mb-5">

        <?php if (!empty($services_by_category)): ?>
            <?php foreach ($services_by_category as $category_name => $services): ?>
                
                <!-- DYNAMIC CATEGORY HEADER -->
                <h3 class="font-luxury gold-text category-title">
                    <i class="fa-solid <?= getCategoryIcon($category_name) ?> me-2"></i><?= e($category_name) ?>
                </h3>

                <!-- DYNAMIC SERVICES GRID -->
                <div class="row g-4 mb-5">
                    <?php foreach ($services as $service): ?>
                        <?php 
                            // Fallback image handling
                            $img_field = trim($service['image'] ?? '');
                            $default_fallback = 'https://images.unsplash.com/photo-1632345031435-8727f6897d53?q=80&w=600';

                            if (!empty($img_field)) {
                                if (strpos($img_field, 'http://') === 0 || strpos($img_field, 'https://') === 0) {
                                    $img_src = $img_field;
                                } elseif (file_exists('uploads/' . $img_field)) {
                                    $img_src = 'uploads/' . $img_field;
                                } else {
                                    $img_src = $img_field;
                                }
                            } else {
                                $img_src = $default_fallback;
                            }
                        ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="price-card overflow-hidden d-flex flex-column h-100">
                                <img src="<?= e($img_src) ?>" 
                                     class="service-img-top" 
                                     alt="<?= e($service['name']) ?>"
                                     onerror="this.src='<?= $default_fallback ?>';">
                                
                                <div class="p-3 d-flex flex-column justify-content-between flex-grow-1" style="min-height: 200px;">
                                    <div>
                                        <h5 class="font-luxury gold-text mb-1"><?= e($service['name']) ?></h5>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fs-6 fw-bold gold-text">LKR <?= number_format((float)$service['price'], 2) ?></span>
                                            <?php if (!empty($service['duration_minutes'])): ?>
                                                <small class="text-secondary">
                                                    <i class="fa-regular fa-clock me-1"></i><?= (int)$service['duration_minutes'] ?>m
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-secondary small mt-2 mb-3">
                                            <?= e($service['description'] ?? 'Exclusive luxury nail service.') ?>
                                        </p>
                                    </div>

                                    <a href="booking.php?service=<?= urlencode($service['name']) ?>&service_id=<?= (int)$service['service_id'] ?>" 
                                       class="btn btn-outline-warning btn-sm w-100 mt-auto">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-gem fa-3x gold-text mb-3"></i>
                <p class="text-secondary fs-5">No services currently available in the database.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5">
        <div class="container">
            <p class="font-luxury gold-text fs-5 mb-1">VELOURA NAILS</p>
            
            <p class="text-secondary small mb-0">&copy; 2026 Veloura Nails. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
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