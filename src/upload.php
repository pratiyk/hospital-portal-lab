<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * Profile Picture Upload Handler
 * 
 * WARNING: This file contains intentional security vulnerabilities
 *          for educational/lab purposes. (File Upload Bypass)
 */
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $target_dir = "uploads/";
    $file_name = basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Basic extension check - "SecureGuard v1.2"
    // BYPASS: The Dockerfile configuration allows .php.jpg to be executed as PHP.
    //         This filter only checks the FINAL extension.
    if($imageFileType != "jpg" && $imageFileType != "jpeg") {
        header("Location: dashboard.php?upload=error");
        exit();
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        header("Location: dashboard.php?upload=success");
    } else {
        header("Location: dashboard.php?upload=error");
    }
} else {
    header("Location: dashboard.php");
}
?>
