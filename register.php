<?php
// Session එක Start කිරීම සහ Database සම්බන්ධ කිරීම
session_start();
include 'db_connection.php';

$error_message = "";

// Form එක Submit වී ඇද්දැයි පරීක්ෂා කිරීම
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($name) && !empty($email) && !empty($password)) {
        
        // Email එක දැනටමත් customers ටේබල් එකේ පවතියිදැයි පරීක්ෂා කිරීම
        $check_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "This email address is already registered. Please sign in.";
        } else {
            $profile_picture = "default.png";
            
            // පින්තූරයක් Upload කර ඇද්දැයි පරීක්ෂා කිරීම
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                $file_name = $_FILES['profile_picture']['name'];
                $file_tmp = $_FILES['profile_picture']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if (in_array($file_ext, $allowed_extensions)) {
                    $new_file_name = uniqid("profile_", true) . '.' . $file_ext;
                    $upload_dir = "uploads/";
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                        $profile_picture = $new_file_name;
                    }
                }
            }
            
            // Password එක Hash කිරීම
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $phone = ""; 
            $address = "";
            
            $insert_stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $name, $email, $phone, $address, $hashed_password, $profile_picture);
            
            if ($insert_stmt->execute()) {
                // login.php එකේ පාවිච්චි කරන නියම Session නම් ටිකම මෙහි යොදා ඇත
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['profile_picture'] = $profile_picture;
                
                $insert_stmt->close();
                $check_stmt->close();
                
                // Home page එකට (index.php) යැවීම
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Veloura Nails Studio</title>
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
            --input-bg: #0d0d0d;
        }

        body.light-mode {
            --bg-color: #f4f1ea;
            --card-bg: #ffffff;
            --text-color: #1c1c1c;
            --text-muted: #555555;
            --border-color: rgba(197, 160, 89, 0.4);
            --input-bg: #eae6df;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        .font-luxury { font-family: 'Playfair Display', serif; }

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

        .auth-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
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

        .btn-gold {
            background: var(--gold-gradient);
            color: #000;
            font-weight: 600;
            border: none;
            padding: 12px;
            border-radius: 4px;
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
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="text-center mb-4">
                    <a class="font-luxury fs-2 gold-text fw-bold text-decoration-none" href="index.php">
                        <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS
                    </a>
                </div>

                <div class="auth-card p-4 p-md-5">
                    <h3 class="font-luxury gold-text text-center mb-1">Create Account</h3>
                    <p class="text-secondary text-center small mb-4">Join Veloura Nails for exclusive luxury booking</p>

                    <!-- Alert Messages -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form action="register.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter secure password" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small">Profile Picture (Optional)</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-gold w-100 mb-3">Sign Up</button>

                        <div class="text-center">
                            <p class="text-secondary small mb-0">Already have an account? <a href="login.php" class="gold-text text-decoration-none fw-semibold">Sign In</a></p>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="text-secondary small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Home</a>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>