<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * Login Page
 * 
 * NOTE: This login page uses prepared statements and is NOT vulnerable to SQL injection.
 */
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Secure: using prepared statements
    $stmt = $conn->prepare("SELECT id, username, full_name, role FROM users WHERE username = ? AND password = ?");
    $hashed = md5($password);
    $stmt->bind_param("ss", $username, $hashed);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saint Mary's Clinic - Patient Portal</title>
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
        .login-container {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 40px;
            width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #0c2340;
            font-size: 22px;
            margin-bottom: 5px;
        }
        .logo p {
            color: #666;
            font-size: 13px;
        }
        .logo .cross {
            font-size: 48px;
            color: #c0392b;
            display: block;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2980b9;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #0c2340;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover { background: #1a5276; }
        .error {
            background: #fce4e4;
            color: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .footer-links {
            margin-top: 25px;
            text-align: center;
        }
        .footer-links a {
            color: #2980b9;
            text-decoration: none;
            font-size: 14px;
        }
        .footer-links a:hover { text-decoration: underline; }
        .version {
            text-align: center;
            color: #999;
            font-size: 11px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="cross">&#9769;</span>
            <h1>Saint Mary's Clinic</h1>
            <p>Patient Portal &mdash; Staff Login</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="footer-links">
            <a href="appointment.php">Check Appointment Status</a>
        </div>

        <div class="version">Patient Portal v2.1.3 &copy; 2015 Saint Mary's Clinic IT Dept.</div>
    </div>
</body>
</html>
