<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * Dashboard (Authenticated)
 */
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

$upload_message = '';
if (isset($_GET['upload'])) {
    if ($_GET['upload'] === 'success') {
        $upload_message = '<div class="alert alert-success">Profile picture uploaded successfully!</div>';
    } elseif ($_GET['upload'] === 'error') {
        $upload_message = '<div class="alert alert-error">Upload failed. Only .jpg files are allowed.</div>';
    }
}

// Get uploaded files for the current user
$uploads = array();
$upload_dir = 'uploads/';
if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
            $uploads[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Saint Mary's Clinic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .navbar {
            background: #0c2340;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .navbar h1 { color: white; font-size: 18px; }
        .navbar .user-info {
            color: #aec6e0;
            font-size: 14px;
        }
        .navbar a {
            color: #e74c3c;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
        }
        .navbar a:hover { color: #ff6b6b; }
        .main-content {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome-card {
            background: linear-gradient(135deg, #0c2340, #1a5276);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .welcome-card h2 { font-size: 24px; margin-bottom: 8px; }
        .welcome-card p { opacity: 0.85; font-size: 14px; }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        .card h3 {
            color: #0c2340;
            margin-bottom: 15px;
            font-size: 16px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .upload-form {
            margin-top: 10px;
        }
        .upload-form input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .upload-form button {
            padding: 10px 25px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .upload-form button:hover { background: #219a52; }
        .file-list { list-style: none; }
        .file-list li {
            padding: 8px 12px;
            background: #f8f9fa;
            margin-bottom: 6px;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
        }
        .file-list li a {
            color: #2980b9;
            text-decoration: none;
        }
        .file-list li a:hover { text-decoration: underline; }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .quick-links { list-style: none; }
        .quick-links li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .quick-links li:last-child { border-bottom: none; }
        .quick-links a {
            color: #2980b9;
            text-decoration: none;
            font-size: 14px;
        }
        .quick-links a:hover { text-decoration: underline; }
        .version {
            text-align: center;
            color: #999;
            font-size: 11px;
            margin-top: 30px;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>&#9769; Saint Mary's Clinic - Portal</h1>
        <div>
            <span class="user-info">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
            <p>Saint Mary's Clinic Patient Portal &mdash; Manage your profile and view clinic information.</p>
        </div>

        <?php echo $upload_message; ?>

        <div class="grid">
            <!-- Upload Profile Picture Card -->
            <div class="card">
                <h3>&#128247; Upload Profile Picture</h3>
                <p style="font-size:13px; color:#666; margin-bottom:15px;">Update your staff profile picture. Accepted format: .jpg</p>
                <form class="upload-form" method="POST" action="upload.php" enctype="multipart/form-data">
                    <input type="file" name="profile_pic" accept=".jpg,.jpeg" required>
                    <button type="submit">Upload Picture</button>
                </form>
            </div>

            <!-- Uploaded Files Card -->
            <div class="card">
                <h3>&#128193; Uploaded Files</h3>
                <?php if (empty($uploads)): ?>
                    <p style="font-size:13px; color:#999;">No files uploaded yet.</p>
                <?php else: ?>
                    <ul class="file-list">
                        <?php foreach ($uploads as $file): ?>
                        <li>
                            <a href="uploads/<?php echo htmlspecialchars($file); ?>" target="_blank"><?php echo htmlspecialchars($file); ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Quick Links Card -->
            <div class="card">
                <h3>&#128279; Quick Links</h3>
                <ul class="quick-links">
                    <li><a href="appointment.php">Check Appointment Status</a></li>
                    <li><a href="#">View Patient Records</a></li>
                    <li><a href="#">Export Patient Data</a></li>
                    <li><a href="health_check.php">System Health Check</a></li>
                </ul>
            </div>

            <!-- System Info Card -->
            <div class="card">
                <h3>&#9881; System Information</h3>
                <ul class="quick-links">
                    <li>Server: Apache/2.4 (Ubuntu)</li>
                    <li>PHP Version: <?php echo phpversion(); ?></li>
                    <li>Portal Version: 2.1.3</li>
                    <li>Last Updated: 2015-08-12</li>
                </ul>
            </div>
        </div>

        <div class="version">Patient Portal v2.1.3 &copy; 2015 Saint Mary's Clinic IT Dept.</div>
    </div>
</body>
</html>
