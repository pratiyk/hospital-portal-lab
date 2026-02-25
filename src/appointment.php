<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * Appointment Status Checker
 * 
 * PUBLIC PAGE - No authentication required
 * 
 * WARNING: This page contains an intentional SQL Injection vulnerability
 *          for educational/lab purposes. DO NOT use this code in production.
 */
require_once 'config.php';

$result = null;
$error = '';
$id = '';
$blocked = false;

// Basic input sanitization filter - "SecureGuard v1.2"
// Added by IT Dept in 2016 after a security audit flagged the appointment page.
//
// BYPASS: This filter checks for whole keywords case-insensitively, but
//         does NOT handle inline MySQL comments (e.g., UNI/ * * /ON SEL/ * * /ECT)
//         or double URL encoding (%2555NION = %55NION = UNION after double decode).
function sanitize_input($input) {
    // Keywords checked with word boundaries - catches "UNION SELECT" but not "/*!50000UNION*/"
    $keyword_blacklist = array(
        'union', 'select', 'insert', 'update', 'delete', 'drop',
        'truncate', 'alter', 'create', 'exec', 'execute',
        'information_schema',
        'load_file', 'outfile', 'dumpfile', 'benchmark', 'sleep'
    );
    
    // Exact substring patterns (non-word patterns)
    $pattern_blacklist = array(
        'xp_', 'sp_', 'char(', 'concat('
    );
    
    $input_lower = strtolower($input);
    
    // Check keyword boundaries (space-separated or at start/end of input)
    foreach ($keyword_blacklist as $word) {
        if (preg_match('/(^|\s)' . preg_quote($word, '/') . '(\s|$)/i', $input_lower)) {
            return false;
        }
    }
    
    // Check exact patterns
    foreach ($pattern_blacklist as $pattern) {
        if (strpos($input_lower, $pattern) !== false) {
            return false;
        }
    }
    
    return true;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Run input through the "SecureGuard" filter
    if (!sanitize_input($id)) {
        $blocked = true;
        $error = "&#9888; Security Alert: Potentially malicious input detected. This incident has been logged.";
    } else {
        // VULNERABLE: Direct string concatenation - Union-Based SQL Injection
        // The WAF above can be bypassed with inline comments: UNI/**/ON SEL/**/ECT
        // But wait - /* is blocked too! The student must use double URL encoding:
        //   %252f%252a = %2f%2a = /* after Apache double-decodes it
        // OR use /*!50000UNION*/ syntax (MySQL versioned comments) which the
        //   filter doesn't catch because it checks for /* not /*!
        $query = "SELECT id, patient_name, doctor, appointment_date, status FROM appointments WHERE id = $id";
        
        $result = @$conn->query($query);
        
        if (!$result) {
            $error = "No appointment found with that ID. Please verify and try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Appointment - Saint Mary's Clinic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c2340 0%, #1a5276 50%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 40px;
            width: 750px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0c2340;
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p { color: #666; font-size: 13px; }
        .header .cross { font-size: 36px; color: #c0392b; }
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        .search-form input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .search-form input:focus {
            outline: none;
            border-color: #2980b9;
        }
        .search-form button {
            padding: 12px 25px;
            background: #0c2340;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .search-form button:hover { background: #1a5276; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #0c2340;
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        td { font-size: 14px; color: #333; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #eef2f7; }
        .status-completed { color: #27ae60; font-weight: 600; }
        .status-scheduled { color: #2980b9; font-weight: 600; }
        .status-cancelled { color: #c0392b; font-weight: 600; }
        .error-msg {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
        }
        .info-msg {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #2980b9;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover { text-decoration: underline; }
        .version {
            text-align: center;
            color: #999;
            font-size: 11px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="cross">&#9769;</span>
            <h1>Check Appointment Status</h1>
            <p>Enter your appointment ID to view the current status</p>
        </div>

        <form method="GET" action="" class="search-form">
            <input type="text" name="id" placeholder="Enter Appointment ID (e.g., 1)" value="<?php echo htmlspecialchars($id); ?>">
            <button type="submit">Check Status</button>
        </form>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php elseif ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['patient_name']; ?></td>
                        <td><?php echo $row['doctor']; ?></td>
                        <td><?php echo $row['appointment_date']; ?></td>
                        <td>
                            <?php 
                            $statusClass = '';
                            $status = $row['status'] ?? '';
                            if (stripos($status, 'completed') !== false) $statusClass = 'status-completed';
                            elseif (stripos($status, 'scheduled') !== false) $statusClass = 'status-scheduled';
                            elseif (stripos($status, 'cancelled') !== false) $statusClass = 'status-cancelled';
                            ?>
                            <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['id'])): ?>
            <div class="error-msg">No appointment found with ID: <?php echo htmlspecialchars($id); ?></div>
        <?php else: ?>
            <div class="info-msg">Enter your appointment ID above to check the current status.</div>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php">&larr; Back to Login</a>
        </div>
        <div class="version">Patient Portal v2.1.3 &copy; 2015 Saint Mary's Clinic IT Dept.</div>
    </div>
</body>
</html>
