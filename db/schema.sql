-- =============================================
-- Saint Mary's Clinic - Hospital Portal Database
-- =============================================

CREATE DATABASE IF NOT EXISTS hospital_portal;
USE hospital_portal;

-- ----------------------------
-- Users table
-- ----------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'doctor'
);

-- dr_house password: 'house' (MD5)
-- admin password: random strong hash (not crackable)
INSERT INTO users (username, password, full_name, role) VALUES
('dr_house', '2ca63cddd54f9490efad22421891a9d1', 'Dr. Gregory House', 'doctor'),
('admin', 'e10adc3949ba59abbe56e057f20f883e', 'System Administrator', 'admin');

-- ----------------------------
-- Patients table
-- ----------------------------
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dob DATE,
    blood_type VARCHAR(5),
    phone VARCHAR(20)
);

INSERT INTO patients (first_name, last_name, dob, blood_type, phone) VALUES
('John', 'Smith', '1985-03-15', 'A+', '555-0101'),
('Maria', 'Garcia', '1992-07-22', 'O-', '555-0102'),
('James', 'Wilson', '1978-11-30', 'B+', '555-0103'),
('Lisa', 'Cuddy', '1980-06-18', 'AB+', '555-0104'),
('Robert', 'Chase', '1990-01-25', 'A-', '555-0105'),
('Allison', 'Cameron', '1988-09-12', 'O+', '555-0106'),
('Eric', 'Foreman', '1983-04-08', 'B-', '555-0107'),
('Sarah', 'Walker', '1982-12-05', 'A+', '555-0201'),
('Michael', 'Scofield', '1976-10-08', 'O+', '555-0202'),
('Lincoln', 'Burrows', '1970-03-17', 'B+', '555-0203'),
('Walter', 'White', '1958-09-07', 'A-', '555-0204'),
('Jesse', 'Pinkman', '1984-09-24', 'AB-', '555-0205');

-- ----------------------------
-- Appointments table
-- ----------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    doctor VARCHAR(50) NOT NULL,
    appointment_date DATE,
    department VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Scheduled'
);

INSERT INTO appointments (patient_name, doctor, appointment_date, department, status) VALUES
('John Smith', 'Dr. House', '2024-01-15', 'Diagnostics', 'Completed'),
('Maria Garcia', 'Dr. Wilson', '2024-01-16', 'Oncology', 'Completed'),
('James Wilson', 'Dr. House', '2024-01-17', 'Diagnostics', 'Scheduled'),
('Lisa Cuddy', 'Dr. Cameron', '2024-01-18', 'Immunology', 'Scheduled'),
('Robert Chase', 'Dr. House', '2024-01-19', 'Diagnostics', 'Cancelled'),
('Allison Cameron', 'Dr. Foreman', '2024-01-20', 'Neurology', 'Completed'),
('Eric Foreman', 'Dr. Wilson', '2024-01-21', 'Oncology', 'Scheduled');

-- ----------------------------
-- Medical records (extra realism)
-- ----------------------------
CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    diagnosis VARCHAR(255),
    prescription TEXT,
    record_date DATE,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

INSERT INTO medical_records (patient_id, diagnosis, prescription, record_date) VALUES
(1, 'Hypertension', 'Lisinopril 10mg daily', '2024-01-15'),
(2, 'Type 2 Diabetes', 'Metformin 500mg twice daily', '2024-01-16'),
(3, 'Migraine', 'Sumatriptan 50mg as needed', '2024-01-17'),
(4, 'Acute Bronchitis', 'Amoxicillin 500mg 3x daily for 7 days', '2024-01-18'),
(5, 'Sprained Ankle', 'Ibuprofen 400mg, rest and ice', '2024-01-19');

-- ----------------------------
-- Success verification
-- ----------------------------
CREATE TABLE IF NOT EXISTS secret_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag_name VARCHAR(100),
    message TEXT
);

INSERT INTO secret_flags (flag_name, message) VALUES
('stage1_complete', 'SUCCESS! You have exploited the SQL injection and found the hidden table. Flag: VulnOS{SQLi_D4t4_Exf1l}');

-- ----------------------------
-- Create low-privilege web user
-- ----------------------------
CREATE USER IF NOT EXISTS 'webuser'@'localhost' IDENTIFIED BY 'w3bUs3r_2015!';
CREATE USER IF NOT EXISTS 'webuser'@'127.0.0.1' IDENTIFIED BY 'w3bUs3r_2015!';
GRANT SELECT, INSERT, UPDATE ON hospital_portal.* TO 'webuser'@'localhost';
GRANT SELECT, INSERT, UPDATE ON hospital_portal.* TO 'webuser'@'127.0.0.1';
FLUSH PRIVILEGES;
