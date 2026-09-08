<?php
session_start();
require_once 'db_connection.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // 'users' වෙනුවට නිවැරදි 'customers' ටේබල් එක පාවිච්චි කර ඇත
        $stmt = $conn->prepare("SELECT customer_id, name, password, profile_picture FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $hashed_password, $profile_picture);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Register පේජ් එකේ පාවිච්චි කරන නියම Session නම් ටිකම මෙහිද යොදා ඇත
                $_SESSION['customer_id'] = $id;
                $_SESSION['customer_name'] = $name;
                $_SESSION['profile_picture'] = $profile_picture;

                header("Location: index.php");
                exit();
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "No account found with this email address.";
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Veloura Nails Studio</title>
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
                    <h3 class="font-luxury gold-text text-center mb-1">Welcome Back</h3>
                    <p class="text-secondary text-center small mb-4">Sign in to manage your luxury appointments</p>

                    <!-- Error Messages -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 mb-3">Sign In</button>

                        <div class="text-center">
                            <p class="text-secondary small mb-0">Don't have an account? <a href="register.php" class="gold-text text-decoration-none fw-semibold">Sign Up</a></p>
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