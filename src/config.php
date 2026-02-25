<?php
/**
 * Saint Mary's Clinic - Patient Portal
 * Database Configuration
 * 
 * Last updated: 2015-08-12
 * Author: IT Department
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'webuser');
define('DB_PASS', 'w3bUs3r_2015!');
define('DB_NAME', 'hospital_portal');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed. Please contact IT support.");
}
?>
