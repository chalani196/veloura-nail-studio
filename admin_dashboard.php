<?php
session_start();
include 'db_connection.php'; // ඔබේ ඩේටාබේස් සම්බන්ධතා ගොනුව

// Owner ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner_login.php");
    exit();
}

$msg = '';
$error = '';

// Directory for worker profile pictures
$upload_dir = 'uploads/workers/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Directory for service images
$service_upload_dir = 'uploads/';
if (!is_dir($service_upload_dir)) {
    mkdir($service_upload_dir, 0755, true);
}

// Helper: handle a profile picture upload, returns path or null
function handleProfileUpload($file, $worker_id) {
    global $upload_dir;
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return null;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        return null;
    }
    $new_filename = 'worker_' . preg_replace('/[^a-zA-Z0-9_]/', '', $worker_id) . '_' . time() . '.' . $ext;
    $target_path = $upload_dir . $new_filename;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }
    return null;
}

// Helper: handle service image upload
function handleServiceImageUpload($file) {
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return null;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        return null;
    }
    $new_filename = 'service_' . time() . '.' . $ext;
    $target_path = "uploads/" . $new_filename;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $new_filename; // Storing just filename to match DB structure
    }
    return null;
}

// 1. Appointment Status Update කිරීම (Approve / Reject)
if (isset($_POST['update_status'])) {
    $app_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE appointments SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $app_id);
    if ($stmt->execute()) {
        $msg = "Appointment status updated successfully!";
    } else {
        $error = "Failed to update status.";
    }
    $stmt->close();
}

// 2. අලුත් Worker කෙනෙක් එකතු කිරීම
if (isset($_POST['add_worker'])) {
    $worker_name = trim($_POST['worker_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $experience = trim($_POST['experience']);
    $profile_picture = handleProfileUpload($_FILES['profile_picture'] ?? null, uniqid());

    if (!empty($worker_name) && !empty($email)) {
        $w_sql = "INSERT INTO workers (name, email, phone, password, experience, availability_status, profile_picture) VALUES (?, ?, ?, ?, ?, 'Available', ?)";
        $w_stmt = $conn->prepare($w_sql);
        $w_stmt->bind_param("ssssss", $worker_name, $email, $phone, $password, $experience, $profile_picture);
        if ($w_stmt->execute()) {
            $msg = "New worker added successfully!";
        } else {
            $error = "Failed to add worker (Email might already exist).";
        }
        $w_stmt->close();
    } else {
        $error = "Worker name and email are required.";
    }
}

// 3. අලුත් Service එකක් එකතු කිරීම (Fixed bind_param string: "ssdsss")
if (isset($_POST['add_service'])) {
    $name = trim($_POST['service_name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $duration = intval($_POST['duration_minutes']);
    $description = trim($_POST['description']);
    $image_name = handleServiceImageUpload($_FILES['service_image'] ?? null);

    if (!empty($name)) {
        $s_stmt = $conn->prepare("INSERT INTO services (name, category, price, duration_minutes, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $s_stmt->bind_param("ssdsss", $name, $category, $price, $duration, $description, $image_name);
        
        if ($s_stmt->execute()) {
            $msg = "New service added successfully!";
        } else {
            $error = "Failed to add service: " . $conn->error;
        }
        $s_stmt->close();
    } else {
        $error = "Service name cannot be empty.";
    }
}

// 4. Service Edit කිරීම
if (isset($_POST['edit_service'])) {
    $service_id = $_POST['service_id'];
    $name = trim($_POST['service_name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $duration = intval($_POST['duration_minutes']);
    $description = trim($_POST['description']);
    $image_name = handleServiceImageUpload($_FILES['service_image'] ?? null);

    if ($image_name) {
        $up_s_sql = "UPDATE services SET name = ?, category = ?, price = ?, duration_minutes = ?, description = ?, image = ? WHERE service_id = ?";
        $up_s_stmt = $conn->prepare($up_s_sql);
        $up_s_stmt->bind_param("ssdsssi", $name, $category, $price, $duration, $description, $image_name, $service_id);
    } else {
        $up_s_sql = "UPDATE services SET name = ?, category = ?, price = ?, duration_minutes = ?, description = ? WHERE service_id = ?";
        $up_s_stmt = $conn->prepare($up_s_sql);
        $up_s_stmt->bind_param("ssdssi", $name, $category, $price, $duration, $description, $service_id);
    }

    if ($up_s_stmt->execute()) {
        $msg = "Service updated successfully!";
    } else {
        $error = "Failed to update service.";
    }
    $up_s_stmt->close();
}

// 5. Service ඉවත් කිරීම (Delete Service)
if (isset($_POST['delete_service'])) {
    $service_id = $_POST['service_id'];
    $d_s_sql = "DELETE FROM services WHERE service_id = ?";
    $d_s_stmt = $conn->prepare($d_s_sql);
    $d_s_stmt->bind_param("i", $service_id);
    if ($d_s_stmt->execute()) {
        $msg = "Service removed successfully!";
    } else {
        $error = "Failed to remove service.";
    }
    $d_s_stmt->close();
}

// 6. Worker විස්තර Edit කිරීම
if (isset($_POST['edit_worker'])) {
    $worker_id = $_POST['worker_id'];
    $worker_name = trim($_POST['worker_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $experience = trim($_POST['experience']);
    $availability_status = $_POST['availability_status'];
    $profile_picture = handleProfileUpload($_FILES['profile_picture'] ?? null, $worker_id);

    if ($profile_picture) {
        $u_sql = "UPDATE workers SET name = ?, email = ?, phone = ?, experience = ?, availability_status = ?, profile_picture = ? WHERE worker_id = ?";
        $u_stmt = $conn->prepare($u_sql);
        $u_stmt->bind_param("ssssssi", $worker_name, $email, $phone, $experience, $availability_status, $profile_picture, $worker_id);
    } else {
        $u_sql = "UPDATE workers SET name = ?, email = ?, phone = ?, experience = ?, availability_status = ? WHERE worker_id = ?";
        $u_stmt = $conn->prepare($u_sql);
        $u_stmt->bind_param("sssssi", $worker_name, $email, $phone, $experience, $availability_status, $worker_id);
    }

    if ($u_stmt->execute()) {
        $msg = "Worker details updated successfully!";
    } else {
        $error = "Failed to update worker (email may already be in use).";
    }
    $u_stmt->close();
}

// 7. Worker ඉවත් කිරීම
if (isset($_POST['delete_worker'])) {
    $worker_id = $_POST['worker_id'];
    $d_sql = "DELETE FROM workers WHERE worker_id = ?";
    $d_stmt = $conn->prepare($d_sql);
    $d_stmt->bind_param("i", $worker_id);
    if ($d_stmt->execute()) {
        $msg = "Worker removed successfully!";
    } else {
        $error = "Failed to remove worker.";
    }
    $d_stmt->close();
}

// Fetch Data for Display
$query = "SELECT a.*, 
         COALESCE(c.name, 'Client') as client_name, 
         COALESCE(c.email, 'N/A') as client_email, 
         COALESCE(c.phone, 'N/A') as client_phone 
         FROM appointments a 
         LEFT JOIN customers c ON a.customer_id = c.customer_id 
         ORDER BY a.id DESC";
$result = $conn->query($query);

$workers_result = $conn->query("SELECT * FROM workers ORDER BY worker_id ASC");
$services_result = $conn->query("SELECT * FROM services ORDER BY service_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Veloura Nails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .gold-text { color: #d4af37; }
        .card-custom { background-color: #1e1e1e; border: 1px solid rgba(212, 175, 55, 0.2); }
        .form-control, .form-select { background-color: #121212; border-color: rgba(212, 175, 55, 0.3); color: #fff; }
        .form-control:focus, .form-select:focus { background-color: #121212; border-color: #d4af37; color: #fff; box-shadow: none; }
        .worker-avatar, .service-img { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 2px solid #d4af37; }
        .worker-avatar-placeholder {
            width: 44px; height: 44px; border-radius: 50%; border: 2px solid rgba(212,175,55,0.4);
            display: flex; align-items: center; justify-content: center; background-color: #2a2a2a; color: #d4af37;
        }
        .modal-content.card-custom { background-color: #1e1e1e; border: 1px solid #d4af37; }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg py-3 border-bottom border-secondary bg-dark">
        <div class="container">
            <a class="navbar-brand font-luxury gold-text fs-4" href="#">
                <i class="fa-solid fa-gem me-2"></i>VELOURA NAILS - ADMIN DASHBOARD
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['owner_name']); ?></span>
                <a href="owner_logout.php" class="btn btn-outline-warning btn-sm">
                    <i class="fa-solid fa-right-from-bracket me-1"></i>Sign Out
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        
        <!-- Alerts -->
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Top Row: Add Worker & Add Service Forms -->
        <div class="row g-4 mb-4">
            <!-- Add Worker Card -->
            <div class="col-md-6">
                <div class="card card-custom p-4 h-100">
                    <h5 class="font-luxury gold-text mb-3"><i class="fa-solid fa-user-plus me-2"></i>Add New Nail Technician</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Worker Name</label>
                            <input type="text" name="worker_name" class="form-control" required placeholder="Enter name">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="worker@veloura.com">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Password">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Experience</label>
                            <input type="text" name="experience" class="form-control" placeholder="e.g. 3 years experience">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/png, image/jpeg, image/webp">
                        </div>
                        <button type="submit" name="add_worker" class="btn btn-warning btn-sm fw-bold">Add Worker</button>
                    </form>
                </div>
            </div>

            <!-- Add Service Card -->
            <div class="col-md-6">
                <div class="card card-custom p-4 h-100">
                    <h5 class="font-luxury gold-text mb-3"><i class="fa-solid fa-hand-sparkles me-2"></i>Add New Service</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Service Name</label>
                            <input type="text" name="service_name" class="form-control" required placeholder="e.g. Gel Extensions">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Category</label>
                            <select name="category" class="form-select">
                                <option value="Manicure">Manicure</option>
                                <option value="Pedicure">Pedicure</option>
                                <option value="Nail extensions">Nail extensions</option>
                                <option value="Nail art">Nail art</option>
                                <option value="Nail care">Nail care</option>
                                <option value="Bridal and special packages">Bridal and special packages</option>
                                <option value="Addon">Addon</option>
                                <option value="Removal services">Removal services</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="30" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="1" placeholder="Service description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Service Image</label>
                            <input type="file" name="service_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                        </div>
                        <button type="submit" name="add_service" class="btn btn-warning btn-sm fw-bold">Add Service</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Manage Services Section -->
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-header bg-dark text-white border-secondary d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 gold-text"><i class="fa-solid fa-scissors me-2"></i>Manage Services (Edit / Remove)</h5>
            </div>
            <div class="card-body bg-dark">
                <div class="table-responsive">
                    <table class="table table-dark table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price (LKR)</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($services_result && $services_result->num_rows > 0): ?>
                                <?php while ($s = $services_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $s['service_id']; ?></td>
                                        <td>
                                            <?php if (!empty($s['image'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($s['image']); ?>" class="service-img" alt="">
                                            <?php else: ?>
                                                <div class="worker-avatar-placeholder"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['category']); ?></td>
                                        <td>Rs. <?php echo number_format($s['price'], 2); ?></td>
                                        <td><?php echo $s['duration_minutes']; ?> mins</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $s['service_id']; ?>">
                                                    <i class="fa-solid fa-pen"></i> Edit
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Remove this service?');">
                                                    <input type="hidden" name="service_id" value="<?php echo $s['service_id']; ?>">
                                                    <button type="submit" name="delete_service" class="btn btn-outline-danger btn-sm">
                                                        <i class="fa-solid fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Service Modal -->
                                    <div class="modal fade" id="editServiceModal<?php echo $s['service_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content card-custom">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title gold-text font-luxury">Edit Service: <?php echo htmlspecialchars($s['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="service_id" value="<?php echo $s['service_id']; ?>">
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Service Name</label>
                                                            <input type="text" name="service_name" class="form-control" required value="<?php echo htmlspecialchars($s['name']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Category</label>
                                                            <input type="text" name="category" class="form-control" required value="<?php echo htmlspecialchars($s['category']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Price (LKR)</label>
                                                            <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $s['price']; ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Duration (Minutes)</label>
                                                            <input type="number" name="duration_minutes" class="form-control" required value="<?php echo $s['duration_minutes']; ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Description</label>
                                                            <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($s['description']); ?></textarea>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Service Image</label>
                                                            <input type="file" name="service_image" class="form-control" accept="image/png, image/jpeg, image/webp">
                                                            <div class="form-text text-secondary">Leave empty to keep current image.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="edit_service" class="btn btn-warning btn-sm fw-bold">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-secondary">No services found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Manage Workers Section -->
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-header bg-dark text-white border-secondary d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 gold-text"><i class="fa-solid fa-users-gear me-2"></i>Manage Nail Technicians</h5>
            </div>
            <div class="card-body bg-dark">
                <div class="table-responsive">
                    <table class="table table-dark table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Experience</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($workers_result && $workers_result->num_rows > 0): ?>
                                <?php while ($w = $workers_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($w['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($w['profile_picture']); ?>" class="worker-avatar" alt="">
                                            <?php else: ?>
                                                <div class="worker-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($w['name']); ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($w['phone']); ?></small><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($w['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($w['experience']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($w['availability_status'] == 'Available') ? 'success' : 'secondary'; ?>">
                                                <?php echo htmlspecialchars($w['availability_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editWorkerModal<?php echo $w['worker_id']; ?>">
                                                    <i class="fa-solid fa-pen"></i> Edit
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Remove this worker?');">
                                                    <input type="hidden" name="worker_id" value="<?php echo $w['worker_id']; ?>">
                                                    <button type="submit" name="delete_worker" class="btn btn-outline-danger btn-sm">
                                                        <i class="fa-solid fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Worker Modal -->
                                    <div class="modal fade" id="editWorkerModal<?php echo $w['worker_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content card-custom">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title gold-text font-luxury">Edit Worker: <?php echo htmlspecialchars($w['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="worker_id" value="<?php echo $w['worker_id']; ?>">
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Worker Name</label>
                                                            <input type="text" name="worker_name" class="form-control" required value="<?php echo htmlspecialchars($w['name']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Email</label>
                                                            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($w['email']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Phone</label>
                                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($w['phone']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Experience</label>
                                                            <input type="text" name="experience" class="form-control" value="<?php echo htmlspecialchars($w['experience']); ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Availability</label>
                                                            <select name="availability_status" class="form-select">
                                                                <option value="Available" <?php if ($w['availability_status'] == 'Available') echo 'selected'; ?>>Available</option>
                                                                <option value="Unavailable" <?php if ($w['availability_status'] == 'Unavailable') echo 'selected'; ?>>Unavailable</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small text-secondary">Profile Picture</label>
                                                            <input type="file" name="profile_picture" class="form-control" accept="image/png, image/jpeg, image/webp">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="edit_worker" class="btn btn-warning btn-sm fw-bold">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">No workers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Appointments Management Table -->
        <div class="card card-custom shadow-sm">
            <div class="card-header bg-dark text-white border-secondary d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 gold-text"><i class="fa-solid fa-list-check me-2"></i>Client Appointments Management</h5>
            </div>
            <div class="card-body bg-dark">
                <div class="table-responsive">
                    <table class="table table-dark table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Contact</th>
                                <th>Service</th>
                                <th>Worker</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name'] ?? $row['client_name']); ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($row['phone'] ?? $row['client_phone']); ?></small><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['email'] ?? $row['client_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                                        <td><?php echo htmlspecialchars($row['worker'] ?? 'Any Available'); ?></td>
                                        <td>
                                            <?php echo $row['appointment_date']; ?><br>
                                            <small class="text-warning"><?php echo $row['appointment_time']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                if($row['status'] == 'Approved') echo 'success';
                                                elseif($row['status'] == 'Rejected' || $row['status'] == 'Cancelled') echo 'danger';
                                                else echo 'warning text-dark';
                                            ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="admin_dashboard.php" method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <select name="status" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: 110px;">
                                                    <option value="Pending" <?php if($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="Approved" <?php if($row['status'] == 'Approved') echo 'selected'; ?>>Approved</option>
                                                    <option value="Rejected" <?php if($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-warning btn-sm fw-bold">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-secondary">No appointments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>