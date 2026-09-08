<?php
session_start();
require_once 'db_connection.php'; // අනෙක් ෆෝල්ඩර වල පොදු db connection එක

// 1. කස්ටමර් ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
$customer_id = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: login.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// 2. ෆෝම් සබ්මිට් වීම් හැසිරවීම (Profile Update & Password Change)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // කොටස A: ප්‍රොෆයිල් සහ දුරකථන අංකය යාවත්කාලීන කිරීම (ඊමේල් එක හැර)
    if (isset($_POST['update_profile'])) {
        $phone = trim($_POST['phone'] ?? '');
        
        // ප්‍රොෆයිල් පිංතූරය අප්ලෝඩ් කිරීම පරීක්ෂා කිරීම
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name = $_FILES['profile_picture']['name'];
            $file_tmp = $_FILES['profile_picture']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed)) {
                $new_file_name = "profile_" . $customer_id . "_" . time() . "." . $file_ext;
                $upload_dir = 'uploads/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Phone සහ Profile Picture පමණක් Update කිරීම
                    $stmt = $conn->prepare("UPDATE customers SET phone = ?, profile_picture = ? WHERE customer_id = ?");
                    $stmt->bind_param("ssi", $phone, $new_file_name, $customer_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $_SESSION['profile_picture'] = $new_file_name;
                    $success_msg = "Profile updated successfully!";
                } else {
                    $error_msg = "Failed to upload profile image.";
                }
            } else {
                $error_msg = "Invalid image format! Only JPG, JPEG, PNG, WEBP allowed.";
            }
        } else {
            // පිංතූරයක් නැත්නම් Phone පමණක් Update කිරීම
            $stmt = $conn->prepare("UPDATE customers SET phone = ? WHERE customer_id = ?");
            $stmt->bind_param("si", $phone, $customer_id);
            $stmt->execute();
            $stmt->close();
            
            $success_msg = "Phone number updated successfully!";
        }
    }

    // කොටස B: පාස්වර්ඩ් එක වෙනස් කිරීම
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer_pass_check = $result->fetch_assoc();
        $stmt->close();

        if ($customer_pass_check && password_verify($current_password, $customer_pass_check['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE customers SET password = ? WHERE customer_id = ?");
                $update_pass->bind_param("si", $hashed_password, $customer_id);
                $update_pass->execute();
                $update_pass->close();
                
                $success_msg = "Password changed successfully!";
            } else {
                $error_msg = "New passwords do not match!";
            }
        } else {
            $error_msg = "Incorrect current password!";
        }
    }
}

// 3. කස්ටමර්ගේ වර්තමාන දත්ත ඩේටාබේස් එකෙන් ලබා ගැනීම
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings - Veloura Nail Studio</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #121212;
            color: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .settings-container {
            max-width: 600px;
            margin: 50px auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            border: 1px solid #333;
        }
        h2, h3 {
            color: #d4af37;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
        }
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 12px;
            background: #2a2a2a;
            border: 1px solid #444;
            color: #fff;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .profile-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-preview img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d4af37;
        }
        .btn {
            background: #d4af37;
            color: #121212;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }
        .btn:hover {
            background: #c5a028;
        }
        hr {
            border: 0;
            height: 1px;
            background: #333;
            margin: 30px 0;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #d4af37;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="settings-container">
    <h2>Account Settings</h2>

    <!-- SweetAlert Messages -->
    <?php if (!empty($success_msg)): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $success_msg; ?>',
                icon: 'success',
                confirmButtonColor: '#d4af37'
            }).then(() => {
                window.location.href = 'account_settings.php';
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <script>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo $error_msg; ?>',
                icon: 'error',
                confirmButtonColor: '#d4af37'
            });
        </script>
    <?php endif; ?>

    <!-- 1. ප්‍රොෆයිල් පිංතූරය සහ දුරකථන අංකය වෙනස් කිරීමේ ෆෝම් එක -->
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="profile-preview">
            <?php 
            $imgSrc = (!empty($customer['profile_picture']) && file_exists('uploads/' . $customer['profile_picture'])) 
                        ? 'uploads/' . $customer['profile_picture'] 
                        : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png';
            ?>
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Profile Picture">
        </div>

        <div class="form-group">
            <label>Change Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">
        </div>

        <!-- ක්‍රමය 2: ලොග් වී සිටින කස්ටමර්ගේ සැබෑ ඊමේල් එක වෙනස් කළ නොහැකි ලෙස පෙන්වීම -->
        <div class="form-group">
            <label>Email Address:</label>
            <div style="padding: 12px; background: #2a2a2a; border: 1px solid #444; color: #d4af37; border-radius: 6px; font-weight: 500;">
                <?php echo htmlspecialchars($customer['email'] ?? ''); ?>
            </div>
        </div>

        <div class="form-group">
            <label>Mobile Number:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" placeholder="Enter your mobile number">
        </div>

        <button type="submit" name="update_profile" class="btn">Update Profile</button>
    </form>

    <hr>

    <!-- 2. පාස්වර්ඩ් එක වෙනස් කිරීමේ ෆෝම් එක -->
    <h3>Change Password</h3>
    <form action="" method="POST">
        <div class="form-group">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
        </div>

        <div class="form-group">
            <label>New Password:</label>
            <input type="password" name="new_password" required>
        </div>

        <div class="form-group">
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" name="change_password" class="btn">Change Password</button>
    </form>

    <a href="index.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>