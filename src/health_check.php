<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * System Health Check Page
 * 
 * This page executes the internal health-check utility.
 */
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Execute the SUID binary
// The vulnerability lies in the 'health-check' binary itself (PATH hijacking)
$output = shell_exec('/usr/bin/health-check 2>&1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Health Check - Saint Mary's Clinic</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 40px; }
        .container { background: white; border-radius: 12px; padding: 30px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        h2 { color: #0c2340; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; overflow-x: auto; font-family: 'Consolas', monospace; }
        .back { margin-top: 20px; }
        .back a { color: #2980b9; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>System Health Check Output</h2>
        <p>Running diagnostics on clinic infrastructure...</p>
        <pre><?php echo htmlspecialchars($output); ?></pre>
        <div class="back">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
