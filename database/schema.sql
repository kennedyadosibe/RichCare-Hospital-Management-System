CREATE DATABASE IF NOT EXISTS richcare_hospital;
USE richcare_hospital;

CREATE TABLE IF NOT EXISTS patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_code VARCHAR(20) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(40),
  email VARCHAR(120),
  gender VARCHAR(30),
  ward VARCHAR(80) DEFAULT 'Outpatient',
  risk_status VARCHAR(30) DEFAULT 'Stable',
  doctor VARCHAR(120),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_code VARCHAR(20) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(120),
  gender VARCHAR(30),
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  department VARCHAR(120) NOT NULL,
  doctor VARCHAR(120) NOT NULL,
  symptoms TEXT,
  is_emergency TINYINT(1) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medical_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_name VARCHAR(120) NOT NULL,
  record_type VARCHAR(120) NOT NULL,
  owner VARCHAR(120) NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NULL,
  invoice_code VARCHAR(20) NOT NULL UNIQUE,
  patient_name VARCHAR(120) NOT NULL,
  service_name VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) DEFAULT 'Pending',
  INDEX (appointment_id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lab_results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  result_code VARCHAR(20) NOT NULL UNIQUE,
  patient_name VARCHAR(120) NOT NULL,
  test_name VARCHAR(120) NOT NULL,
  result_value TEXT NOT NULL,
  normal_range VARCHAR(120),
  status VARCHAR(30) DEFAULT 'Pending',
  technician VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prescriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prescription_code VARCHAR(20) NOT NULL UNIQUE,
  patient_name VARCHAR(120) NOT NULL,
  medicine_name VARCHAR(120) NOT NULL,
  dosage VARCHAR(120) NOT NULL,
  frequency VARCHAR(120) NOT NULL,
  duration VARCHAR(120) NOT NULL,
  instructions TEXT,
  prescriber VARCHAR(120) NOT NULL,
  status VARCHAR(30) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS staff_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_code VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  role VARCHAR(60) NOT NULL,
  department VARCHAR(120) DEFAULT 'Administration',
  phone VARCHAR(40),
  email VARCHAR(120),
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (staff_id),
  INDEX (token_hash)
);

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NULL,
  staff_code VARCHAR(30),
  staff_name VARCHAR(120),
  staff_role VARCHAR(60),
  action VARCHAR(80) NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  target_type VARCHAR(80),
  target_id INT NULL,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (staff_id),
  INDEX (action),
  INDEX (category),
  INDEX (created_at)
);

INSERT INTO patients (patient_code, full_name, phone, email, gender, ward, risk_status, doctor)
VALUES
('RC-20491', 'Sarah Johnson', '+233 20 100 0001', 'sarah@example.com', 'Female', 'Maternity', 'Stable', 'Dr. N. Addo'),
('RC-20485', 'Peter Coleman', '+233 20 100 0002', 'peter@example.com', 'Male', 'Emergency', 'Critical', 'Dr. R. Cole'),
('RC-20470', 'Mina Arthur', '+233 20 100 0003', 'mina@example.com', 'Female', 'Pediatrics', 'Moderate', 'Dr. A. Silva'),
('RC-20451', 'Felix Brown', '+233 20 100 0004', 'felix@example.com', 'Male', 'Outpatient', 'Stable', 'Dr. K. Mensah')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

INSERT INTO appointments
(appointment_code, full_name, phone, email, gender, appointment_date, appointment_time, department, doctor, symptoms, is_emergency, status)
VALUES
('APT-1001', 'Amara Okafor', '+233 20 200 0001', 'amara@example.com', 'Female', CURDATE(), '08:30:00', 'Cardiology review', 'Dr. K. Mensah', 'Routine review', 0, 'Checked in'),
('APT-1002', 'Daniel Mensah', '+233 20 200 0002', 'daniel@example.com', 'Male', CURDATE(), '09:10:00', 'Laboratory follow-up', 'Dr. R. Cole', 'Lab result review', 0, 'Waiting'),
('APT-1003', 'Grace Bello', '+233 20 200 0003', 'grace@example.com', 'Female', CURDATE(), '10:00:00', 'General consultation', 'Dr. A. Silva', 'General consultation', 0, 'Confirmed')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

INSERT INTO staff_users (staff_code, full_name, role, department, phone, email, password_hash, is_active)
VALUES
('RC-STAFF-001', 'RichCare Administrator', 'Administrator', 'Administration', '+233 20 300 0001', 'admin@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-DOC-002', 'Dr. K. Mensah', 'Doctor', 'General Medicine', '+233 20 300 0002', 'mensah@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-NUR-003', 'Nurse Ama Boateng', 'Nurse', 'Emergency', '+233 20 300 0003', 'ama@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-BIL-004', 'Kojo Billing', 'Billing', 'Accounts', '+233 20 300 0004', 'billing@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-REC-005', 'Esi Reception', 'Receptionist', 'Front Desk', '+233 20 300 0005', 'reception@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-LAB-006', 'Lab Unit Officer', 'Laboratory', 'Laboratory', '+233 20 300 0006', 'lab@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1),
('RC-PHA-007', 'Pharmacy Officer', 'Pharmacy', 'Pharmacy', '+233 20 300 0007', 'pharmacy@richcare.local', '$2y$10$fRuTdMV6tKJoa4gpMtgc4O20zwynG/pI00Cplx9mHpQYrsgzttSiC', 1)
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), role = VALUES(role), department = VALUES(department), is_active = VALUES(is_active);
