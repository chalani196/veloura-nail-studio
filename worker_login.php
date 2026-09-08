<?php
session_start();
include 'db_connection.php';

// If already logged in, go straight to the dashboard
if (isset($_SESSION['worker_id'])) {
    header("Location: worker_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT worker_id, name, email, password, profile_picture, experience, availability_status, last_login FROM workers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['worker_id'] = $row['worker_id'];
                $_SESSION['worker_name'] = $row['name'];
                $_SESSION['worker_email'] = $row['email'];
                $_SESSION['worker_profile_picture'] = $row['profile_picture'];
                $_SESSION['worker_experience'] = $row['experience'];
                $_SESSION['worker_availability'] = $row['availability_status'];
                // Remember the PREVIOUS last_login (before we overwrite it) so the
                // dashboard can flag anything assigned after that moment as "new"
                $_SESSION['worker_last_login'] = $row['last_login'];

                // Now stamp this login as the new last_login
                $update = $conn->prepare("UPDATE workers SET last_login = NOW() WHERE worker_id = ?");
                $update->bind_param("i", $row['worker_id']);
                $update->execute();
                $update->close();

                header("Location: worker_dashboard.php");
                exit();
            } else {
                $error = 'Incorrect email or password.';
            }
        } else {
            $error = 'Incorrect email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Login | Veloura Nails</title>
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

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1, h2, h3, h4, h5, .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .gold-text {
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            max-width: 420px;
            width: 100%;
            padding: 40px;
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
            padding: 10px 22px;
            border-radius: 2px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s ease;
        }

        .btn-gold:hover {
            box-shadow: 0 0 15px rgba(197, 160, 89, 0.5);
            transform: translateY(-2px);
            color: #000;
        }

        a.back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
        }

        a.back-link:hover {
            color: var(--gold-primary);
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <i class="fa-solid fa-user-tie fs-1 gold-text mb-3"></i>
        <h3 class="font-luxury gold-text mb-1">Worker Portal</h3>
        <p class="text-muted small mb-4">Sign in to manage your appointments</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="worker_login.php">
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@veloura.com" required>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label small text-muted">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-gold w-100">Sign In</button>
        </form>

        <a href="index.php" class="back-link d-inline-block mt-4"><i class="fa-solid fa-arrow-left me-1"></i> Back to Home</a>
    </div>

</body>
</html>