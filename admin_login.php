<?php
session_start();
include 'db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // owners table එකෙන් තොරතුරු පරීක්ෂා කිරීම
    $stmt = $conn->prepare("SELECT owner_id, name, email, password FROM owners WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // පාස්වර්ඩ් එක සත්‍යාපනය කිරීම (Hashed password)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['owner_id'] = $row['owner_id'];
            $_SESSION['owner_name'] = $row['name'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'Owner email not found!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login | Veloura Nails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background-color: #1e1e1e; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .gold-text { color: #d4af37; }
        .form-control { background-color: #121212; border: 1px solid rgba(212, 175, 55, 0.4); color: #fff; }
        .form-control:focus { background-color: #121212; border-color: #d4af37; color: #fff; box-shadow: none; }
        .btn-gold { background-color: #d4af37; color: #121212; font-weight: 600; width: 100%; }
        .btn-gold:hover { background-color: #f3e5ab; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="font-luxury gold-text"><i class="fa-solid fa-gem me-2"></i>Owner Portal</h3>
            <p class="text-secondary small">Veloura Nails Management</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-secondary">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="owner@veloura.com">
            </div>
            <div class="mb-3">
                <label class="form-label small text-secondary">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-gold py-2">Sign In</button>
        </form>
    </div>
</body>
</html>