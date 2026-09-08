<?php
session_start();

// Clear only worker-related session data (keeps things safe if a customer session ever overlaps)
unset($_SESSION['worker_id']);
unset($_SESSION['worker_name']);
unset($_SESSION['worker_email']);
unset($_SESSION['worker_profile_picture']);
unset($_SESSION['worker_experience']);
unset($_SESSION['worker_availability']);

session_unset();
session_destroy();

header("Location: index.php");
exit();