<?php
require_once __DIR__ . '/auth.php';
require_staff_login();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$canManageStaff = staff_has_role('Administrator');
$canManageAppointments = staff_has_any_role(['Administrator', 'Receptionist', 'Nurse']);
$canManagePatients = staff_has_any_role(['Administrator', 'Receptionist', 'Doctor', 'Nurse', 'Laboratory', 'Pharmacy']);
$canManageRecords = staff_has_any_role(['Administrator', 'Doctor', 'Nurse', 'Laboratory', 'Pharmacy']);
$canManageBilling = staff_has_any_role(['Administrator', 'Billing']);
$canManageLabResults = staff_has_any_role(['Administrator', 'Laboratory']);
$canManagePrescriptions = staff_has_any_role(['Administrator', 'Doctor', 'Pharmacy']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageAppointments && ($_POST['form_type'] ?? '') === '' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointmentId = (int) $_POST['appointment_id'];
    $appointmentLookup = $pdo->prepare('SELECT appointment_code, full_name, status FROM appointments WHERE id = :id');
    $appointmentLookup->execute([':id' => $appointmentId]);
    $appointmentLog = $appointmentLookup->fetch();
    $statement = $pdo->prepare('UPDATE appointments SET status = :status WHERE id = :id');
    $statement->execute([
        ':status' => $_POST['status'],
        ':id' => $appointmentId,
    ]);
    if ($appointmentLog && $appointmentLog['status'] !== $_POST['status']) {
        log_activity(
            $pdo,
            'Updated appointment status',
            'Appointments',
            $appointmentLog['appointment_code'] . ' for ' . $appointmentLog['full_name'] . ' changed from ' . $appointmentLog['status'] . ' to ' . $_POST['status'] . '.',
            'appointment',
            $appointmentId
        );
    }
    $returnStatus = $_POST['return_status'] ?? 'All';
    if (!in_array($returnStatus, ['All', 'Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'], true)) {
        $returnStatus = 'All';
    }
    header('Location: staff.php?view=appointments&status=' . urlencode($returnStatus));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageBilling && isset($_POST['invoice_id'], $_POST['invoice_status'])) {
    $invoiceId = (int) $_POST['invoice_id'];
    $invoiceLookup = $pdo->prepare('SELECT invoice_code, patient_name, status FROM invoices WHERE id = :id');
    $invoiceLookup->execute([':id' => $invoiceId]);
    $invoiceLog = $invoiceLookup->fetch();
    $statement = $pdo->prepare('UPDATE invoices SET status = :status WHERE id = :id');
    $statement->execute([
        ':status' => $_POST['invoice_status'],
        ':id' => $invoiceId,
    ]);
    if ($invoiceLog && $invoiceLog['status'] !== $_POST['invoice_status']) {
        log_activity(
            $pdo,
            'Updated invoice status',
            'Billing',
            $invoiceLog['invoice_code'] . ' for ' . $invoiceLog['patient_name'] . ' changed from ' . $invoiceLog['status'] . ' to ' . $_POST['invoice_status'] . '.',
            'invoice',
            $invoiceId
        );
    }
    header('Location: staff.php?view=billing');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManagePrescriptions && isset($_POST['prescription_id'], $_POST['prescription_status'])) {
    $prescriptionId = (int) $_POST['prescription_id'];
    $prescriptionLookup = $pdo->prepare('SELECT prescription_code, patient_name, status FROM prescriptions WHERE id = :id');
    $prescriptionLookup->execute([':id' => $prescriptionId]);
    $prescriptionLog = $prescriptionLookup->fetch();
    $statement = $pdo->prepare('UPDATE prescriptions SET status = :status WHERE id = :id');
    $statement->execute([
        ':status' => $_POST['prescription_status'],
        ':id' => $prescriptionId,
    ]);
    if ($prescriptionLog && $prescriptionLog['status'] !== $_POST['prescription_status']) {
        log_activity(
            $pdo,
            'Updated prescription status',
            'Prescriptions',
            $prescriptionLog['prescription_code'] . ' for ' . $prescriptionLog['patient_name'] . ' changed from ' . $prescriptionLog['status'] . ' to ' . $_POST['prescription_status'] . '.',
            'prescription',
            $prescriptionId
        );
    }
    header('Location: staff.php?view=prescriptions');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageStaff && isset($_POST['staff_user_id'], $_POST['staff_status'])) {
    $staffTargetId = (int) $_POST['staff_user_id'];
    $staffLookup = $pdo->prepare('SELECT staff_code, full_name, is_active FROM staff_users WHERE id = :id');
    $staffLookup->execute([':id' => $staffTargetId]);
    $staffLog = $staffLookup->fetch();
    $statement = $pdo->prepare('UPDATE staff_users SET is_active = :is_active WHERE id = :id');
    $statement->execute([
        ':is_active' => (int) $_POST['staff_status'],
        ':id' => $staffTargetId,
    ]);
    if ($staffLog && (int) $staffLog['is_active'] !== (int) $_POST['staff_status']) {
        $newStatus = (int) $_POST['staff_status'] === 1 ? 'reactivated' : 'disabled';
        log_activity(
            $pdo,
            ucfirst($newStatus) . ' staff account',
            'Staff Accounts',
            $staffLog['staff_code'] . ' - ' . $staffLog['full_name'] . ' was ' . $newStatus . '.',
            'staff_user',
            $staffTargetId
        );
    }
    header('Location: staff.php?view=staff-users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageStaff && ($_POST['form_type'] ?? '') === 'delete_staff_user') {
    $staffUserId = (int) ($_POST['staff_user_id'] ?? 0);
    $staffLookup = $pdo->prepare('SELECT staff_code, full_name FROM staff_users WHERE id = :id AND is_active = 0 AND id <> :current_id');
    $staffLookup->execute([
        ':id' => $staffUserId,
        ':current_id' => (int) ($_SESSION['staff_user']['id'] ?? 0),
    ]);
    $staffLog = $staffLookup->fetch();
    $statement = $pdo->prepare('DELETE FROM staff_users WHERE id = :id AND is_active = 0 AND id <> :current_id');
    $statement->execute([
        ':id' => $staffUserId,
        ':current_id' => (int) ($_SESSION['staff_user']['id'] ?? 0),
    ]);
    if ($staffLog && $statement->rowCount() > 0) {
        log_activity(
            $pdo,
            'Deleted staff account',
            'Staff Accounts',
            $staffLog['staff_code'] . ' - ' . $staffLog['full_name'] . ' was permanently deleted.',
            'staff_user',
            $staffUserId
        );
    }
    header('Location: staff.php?view=staff-users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManagePatients && ($_POST['form_type'] ?? '') === 'patient') {
    $patientCode = make_code('RC');
    $patientName = trim($_POST['full_name'] ?? '');
    $statement = $pdo->prepare(
        'INSERT INTO patients (patient_code, full_name, phone, email, gender, ward, risk_status, doctor)
         VALUES (:patient_code, :full_name, :phone, :email, :gender, :ward, :risk_status, :doctor)'
    );
    $statement->execute([
        ':patient_code' => $patientCode,
        ':full_name' => $patientName,
        ':phone' => trim($_POST['phone'] ?? ''),
        ':email' => trim($_POST['email'] ?? ''),
        ':gender' => $_POST['gender'] ?? 'Prefer not to say',
        ':ward' => $_POST['ward'] ?? 'Outpatient',
        ':risk_status' => $_POST['risk_status'] ?? 'Stable',
        ':doctor' => $_POST['doctor'] ?? 'Dr. K. Mensah',
    ]);
    log_activity(
        $pdo,
        'Registered patient',
        'Patients',
        $patientCode . ' - ' . $patientName . ' was registered.',
        'patient',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=patients');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageRecords && ($_POST['form_type'] ?? '') === 'record') {
    $recordPatient = trim($_POST['patient_name'] ?? '');
    $recordType = trim($_POST['record_type'] ?? '');
    $statement = $pdo->prepare(
        'INSERT INTO medical_records (patient_name, record_type, owner, notes)
         VALUES (:patient_name, :record_type, :owner, :notes)'
    );
    $statement->execute([
        ':patient_name' => $recordPatient,
        ':record_type' => $recordType,
        ':owner' => $_POST['owner'] ?? 'Dr. K. Mensah',
        ':notes' => trim($_POST['notes'] ?? ''),
    ]);
    log_activity(
        $pdo,
        'Added medical record',
        'Medical Records',
        $recordType . ' record was added for ' . $recordPatient . '.',
        'medical_record',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=records');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageLabResults && ($_POST['form_type'] ?? '') === 'lab_result') {
    $resultCode = make_code('LAB');
    $labPatient = trim($_POST['patient_name'] ?? '');
    $testName = trim($_POST['test_name'] ?? '');
    $statement = $pdo->prepare(
        'INSERT INTO lab_results (result_code, patient_name, test_name, result_value, normal_range, status, technician)
         VALUES (:result_code, :patient_name, :test_name, :result_value, :normal_range, :status, :technician)'
    );
    $statement->execute([
        ':result_code' => $resultCode,
        ':patient_name' => $labPatient,
        ':test_name' => $testName,
        ':result_value' => trim($_POST['result_value'] ?? ''),
        ':normal_range' => trim($_POST['normal_range'] ?? ''),
        ':status' => $_POST['status'] ?? 'Pending',
        ':technician' => trim($_POST['technician'] ?? ($_SESSION['staff_user']['name'] ?? 'Lab Unit')),
    ]);
    log_activity(
        $pdo,
        'Added lab result',
        'Lab Results',
        $resultCode . ' - ' . $testName . ' was recorded for ' . $labPatient . '.',
        'lab_result',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=lab-results');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManagePrescriptions && ($_POST['form_type'] ?? '') === 'prescription') {
    $prescriptionCode = make_code('RX');
    $prescriptionPatient = trim($_POST['patient_name'] ?? '');
    $medicineName = trim($_POST['medicine_name'] ?? '');
    $statement = $pdo->prepare(
        'INSERT INTO prescriptions (prescription_code, patient_name, medicine_name, dosage, frequency, duration, instructions, prescriber, status)
         VALUES (:prescription_code, :patient_name, :medicine_name, :dosage, :frequency, :duration, :instructions, :prescriber, :status)'
    );
    $statement->execute([
        ':prescription_code' => $prescriptionCode,
        ':patient_name' => $prescriptionPatient,
        ':medicine_name' => $medicineName,
        ':dosage' => trim($_POST['dosage'] ?? ''),
        ':frequency' => trim($_POST['frequency'] ?? ''),
        ':duration' => trim($_POST['duration'] ?? ''),
        ':instructions' => trim($_POST['instructions'] ?? ''),
        ':prescriber' => trim($_POST['prescriber'] ?? ($_SESSION['staff_user']['name'] ?? 'RichCare Staff')),
        ':status' => $_POST['status'] ?? 'Pending',
    ]);
    log_activity(
        $pdo,
        'Created prescription',
        'Prescriptions',
        $prescriptionCode . ' - ' . $medicineName . ' was prescribed for ' . $prescriptionPatient . '.',
        'prescription',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=prescriptions');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageBilling && ($_POST['form_type'] ?? '') === 'invoice') {
    $invoiceCode = make_code('INV');
    $invoicePatient = trim($_POST['patient_name'] ?? '');
    $invoiceService = trim($_POST['service_name'] ?? '');
    $statement = $pdo->prepare(
        'INSERT INTO invoices (appointment_id, invoice_code, patient_name, service_name, amount, status)
         VALUES (:appointment_id, :invoice_code, :patient_name, :service_name, :amount, :status)'
    );
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $statement->execute([
        ':appointment_id' => $appointmentId > 0 ? $appointmentId : null,
        ':invoice_code' => $invoiceCode,
        ':patient_name' => $invoicePatient,
        ':service_name' => $invoiceService,
        ':amount' => (float) ($_POST['amount'] ?? 0),
        ':status' => $_POST['status'] ?? 'Pending',
    ]);
    log_activity(
        $pdo,
        'Created invoice',
        'Billing',
        $invoiceCode . ' for ' . $invoicePatient . ' was created for ' . $invoiceService . '.',
        'invoice',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=billing');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageStaff && ($_POST['form_type'] ?? '') === 'staff_user') {
    $newStaffCode = trim($_POST['staff_code'] ?? '');
    $newStaffName = trim($_POST['full_name'] ?? '');
    $newStaffRole = $_POST['role'] ?? 'Receptionist';
    $statement = $pdo->prepare(
        'INSERT INTO staff_users (staff_code, full_name, role, department, phone, email, password_hash, is_active)
         VALUES (:staff_code, :full_name, :role, :department, :phone, :email, :password_hash, 1)'
    );
    $statement->execute([
        ':staff_code' => $newStaffCode,
        ':full_name' => $newStaffName,
        ':role' => $newStaffRole,
        ':department' => trim($_POST['department'] ?? 'Administration'),
        ':phone' => trim($_POST['phone'] ?? ''),
        ':email' => trim($_POST['email'] ?? ''),
        ':password_hash' => password_hash($_POST['password'] ?? 'richcare', PASSWORD_DEFAULT),
    ]);
    log_activity(
        $pdo,
        'Created staff account',
        'Staff Accounts',
        $newStaffCode . ' - ' . $newStaffName . ' was created as ' . $newStaffRole . '.',
        'staff_user',
        (int) $pdo->lastInsertId()
    );
    header('Location: staff.php?view=staff-users');
    exit;
}

$statusFilter = $_GET['status'] ?? 'All';
$patientSearch = trim($_GET['patient_search'] ?? '');
$billingSearch = trim($_GET['billing_search'] ?? '');
$activitySearch = trim($_GET['activity_search'] ?? '');
$activityCategory = trim($_GET['activity_category'] ?? 'All');
$activityAction = trim($_GET['activity_action'] ?? 'All');
$activityDate = trim($_GET['activity_date'] ?? '');
$reportStart = trim($_GET['report_start'] ?? date('Y-m-01'));
$reportEnd = trim($_GET['report_end'] ?? date('Y-m-d'));
$allowedStatuses = ['All', 'Pending', 'Confirmed', 'Checked in', 'Completed', 'Cancelled'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'All';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportStart)) {
    $reportStart = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportEnd)) {
    $reportEnd = date('Y-m-d');
}
if ($reportStart > $reportEnd) {
    [$reportStart, $reportEnd] = [$reportEnd, $reportStart];
}

if ($patientSearch !== '') {
    $patientStatement = $pdo->prepare(
        'SELECT * FROM patients
         WHERE full_name LIKE :search OR patient_code LIKE :search OR phone LIKE :search OR doctor LIKE :search
         ORDER BY created_at DESC'
    );
    $patientStatement->execute([':search' => '%' . $patientSearch . '%']);
    $patients = $patientStatement->fetchAll();
} else {
    $patients = $pdo->query('SELECT * FROM patients ORDER BY created_at DESC')->fetchAll();
}
$records = $pdo->query('SELECT * FROM medical_records ORDER BY created_at DESC')->fetchAll();
if ($billingSearch !== '') {
    $invoiceStatement = $pdo->prepare(
        'SELECT i.*, a.appointment_code
         FROM invoices i
         LEFT JOIN appointments a ON a.id = i.appointment_id
         WHERE i.invoice_code LIKE :search
            OR i.patient_name LIKE :search
            OR i.service_name LIKE :search
            OR i.status LIKE :search
            OR a.appointment_code LIKE :search
         ORDER BY i.created_at DESC'
    );
    $invoiceStatement->execute([':search' => '%' . $billingSearch . '%']);
    $invoices = $invoiceStatement->fetchAll();
} else {
    $invoices = $pdo->query(
        'SELECT i.*, a.appointment_code
         FROM invoices i
         LEFT JOIN appointments a ON a.id = i.appointment_id
         ORDER BY i.created_at DESC'
    )->fetchAll();
}
$readyForBilling = $pdo->query(
    "SELECT a.*
     FROM appointments a
     LEFT JOIN invoices i ON i.appointment_id = a.id
     WHERE a.status IN ('Checked in', 'Completed')
       AND i.id IS NULL
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
)->fetchAll();
$labResults = $pdo->query('SELECT * FROM lab_results ORDER BY created_at DESC')->fetchAll();
$prescriptions = $pdo->query('SELECT * FROM prescriptions ORDER BY created_at DESC')->fetchAll();
$staffUsers = $pdo->query('SELECT * FROM staff_users ORDER BY created_at DESC')->fetchAll();
$activityCategories = $canManageStaff ? $pdo->query('SELECT DISTINCT category FROM activity_logs ORDER BY category')->fetchAll(PDO::FETCH_COLUMN) : [];
$activityActions = $canManageStaff ? $pdo->query('SELECT DISTINCT action FROM activity_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN) : [];
$activityLogs = [];
if ($canManageStaff) {
    $activityConditions = [];
    $activityParams = [];

    if ($activitySearch !== '') {
        $activityConditions[] = '(staff_name LIKE :activity_search OR staff_code LIKE :activity_search OR description LIKE :activity_search OR action LIKE :activity_search)';
        $activityParams[':activity_search'] = '%' . $activitySearch . '%';
    }

    if ($activityCategory !== '' && $activityCategory !== 'All') {
        $activityConditions[] = 'category = :activity_category';
        $activityParams[':activity_category'] = $activityCategory;
    }

    if ($activityAction !== '' && $activityAction !== 'All') {
        $activityConditions[] = 'action = :activity_action';
        $activityParams[':activity_action'] = $activityAction;
    }

    if ($activityDate !== '') {
        $activityConditions[] = 'DATE(created_at) = :activity_date';
        $activityParams[':activity_date'] = $activityDate;
    }

    $activitySql = 'SELECT * FROM activity_logs';
    if ($activityConditions) {
        $activitySql .= ' WHERE ' . implode(' AND ', $activityConditions);
    }
    $activitySql .= ' ORDER BY created_at DESC LIMIT 200';
    $activityStatement = $pdo->prepare($activitySql);
    $activityStatement->execute($activityParams);
    $activityLogs = $activityStatement->fetchAll();
}
$reportStats = [];
$reportRecentActivity = [];
$reportTopServices = [];
if ($canManageStaff) {
    $reportParams = [
        ':start' => $reportStart,
        ':end' => $reportEnd,
    ];

    $reportAppointmentStatement = $pdo->prepare(
        "SELECT
            COUNT(*) AS total_appointments,
            SUM(status = 'Completed') AS completed_visits,
            SUM(status = 'Checked in') AS checked_in_visits,
            SUM(status = 'Cancelled') AS cancelled_visits
         FROM appointments
         WHERE appointment_date BETWEEN :start AND :end"
    );
    $reportAppointmentStatement->execute($reportParams);
    $appointmentStats = $reportAppointmentStatement->fetch() ?: [];

    $reportPatientStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM patients WHERE DATE(created_at) BETWEEN :start AND :end'
    );
    $reportPatientStatement->execute($reportParams);
    $newPatientCount = (int) $reportPatientStatement->fetchColumn();

    $reportInvoiceStatement = $pdo->prepare(
        "SELECT
            COUNT(*) AS invoice_count,
            COALESCE(SUM(amount), 0) AS total_billed,
            COALESCE(SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END), 0) AS paid_revenue,
            COALESCE(SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END), 0) AS pending_debt,
            COALESCE(SUM(CASE WHEN status = 'Cancelled' THEN amount ELSE 0 END), 0) AS cancelled_billing
         FROM invoices
         WHERE DATE(created_at) BETWEEN :start AND :end"
    );
    $reportInvoiceStatement->execute($reportParams);
    $invoiceStats = $reportInvoiceStatement->fetch() ?: [];

    $reportActivityStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) BETWEEN :start AND :end'
    );
    $reportActivityStatement->execute($reportParams);
    $activityCount = (int) $reportActivityStatement->fetchColumn();

    $reportTopServicesStatement = $pdo->prepare(
        'SELECT service_name, COUNT(*) AS invoice_count, COALESCE(SUM(amount), 0) AS service_total
         FROM invoices
         WHERE DATE(created_at) BETWEEN :start AND :end
         GROUP BY service_name
         ORDER BY service_total DESC, invoice_count DESC
         LIMIT 5'
    );
    $reportTopServicesStatement->execute($reportParams);
    $reportTopServices = $reportTopServicesStatement->fetchAll();

    $reportRecentActivityStatement = $pdo->prepare(
        'SELECT * FROM activity_logs
         WHERE DATE(created_at) BETWEEN :start AND :end
         ORDER BY created_at DESC
         LIMIT 6'
    );
    $reportRecentActivityStatement->execute($reportParams);
    $reportRecentActivity = $reportRecentActivityStatement->fetchAll();

    $reportStats = [
        'appointments' => (int) ($appointmentStats['total_appointments'] ?? 0),
        'completed' => (int) ($appointmentStats['completed_visits'] ?? 0),
        'checked_in' => (int) ($appointmentStats['checked_in_visits'] ?? 0),
        'cancelled' => (int) ($appointmentStats['cancelled_visits'] ?? 0),
        'new_patients' => $newPatientCount,
        'invoice_count' => (int) ($invoiceStats['invoice_count'] ?? 0),
        'total_billed' => (float) ($invoiceStats['total_billed'] ?? 0),
        'paid_revenue' => (float) ($invoiceStats['paid_revenue'] ?? 0),
        'pending_debt' => (float) ($invoiceStats['pending_debt'] ?? 0),
        'cancelled_billing' => (float) ($invoiceStats['cancelled_billing'] ?? 0),
        'activity_count' => $activityCount,
    ];
}
$staffRoles = ['Administrator', 'Doctor', 'Nurse', 'Receptionist', 'Laboratory', 'Pharmacy', 'Billing'];
$staffUsersByRole = array_fill_keys($staffRoles, []);
foreach ($staffUsers as $account) {
    $role = in_array($account['role'], $staffRoles, true) ? $account['role'] : 'Other';
    if (!isset($staffUsersByRole[$role])) {
        $staffUsersByRole[$role] = [];
    }
    $staffUsersByRole[$role][] = $account;
}

if ($statusFilter === 'All') {
    $appointments = $pdo->query('SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC')->fetchAll();
} else {
    $statement = $pdo->prepare('SELECT * FROM appointments WHERE status = :status ORDER BY appointment_date DESC, appointment_time DESC');
    $statement->execute([':status' => $statusFilter]);
    $appointments = $statement->fetchAll();
}

$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$checkedInCount = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Checked in'")->fetchColumn();
$pendingInvoiceCount = (int) $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'Pending'")->fetchColumn();
$pendingPrescriptionCount = (int) $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'Pending'")->fetchColumn();
$pendingLabCount = (int) $pdo->query("SELECT COUNT(*) FROM lab_results WHERE status = 'Pending'")->fetchColumn();
$recentResetCount = $canManageStaff ? (int) $pdo->query("SELECT COUNT(*) FROM password_resets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn() : 0;
$staffUser = $_SESSION['staff_user'];
$staffRole = $staffUser['role'] ?? '';
$notifications = [];
if (($canManageAppointments || $canManageStaff) && $pendingCount > 0) {
    $notifications[] = [
        'title' => $pendingCount . ' pending appointment' . ($pendingCount === 1 ? '' : 's'),
        'message' => 'Reception or nursing should confirm the booking queue.',
        'href' => 'staff.php?view=appointments&status=Pending',
        'type' => 'Appointments',
    ];
}
if (($canManageBilling || $canManageStaff) && count($readyForBilling) > 0) {
    $notifications[] = [
        'title' => count($readyForBilling) . ' visit' . (count($readyForBilling) === 1 ? '' : 's') . ' ready for billing',
        'message' => 'Checked-in or completed visits are waiting for invoices.',
        'href' => 'staff.php?view=billing',
        'type' => 'Billing',
    ];
}
if (($staffRole === 'Billing' || $canManageStaff) && $pendingInvoiceCount > 0) {
    $notifications[] = [
        'title' => $pendingInvoiceCount . ' unpaid invoice' . ($pendingInvoiceCount === 1 ? '' : 's'),
        'message' => 'Follow up on pending balances.',
        'href' => 'staff.php?view=billing&billing_search=Pending',
        'type' => 'Billing',
    ];
}
if (($canManageLabResults || $canManageStaff) && $pendingLabCount > 0) {
    $notifications[] = [
        'title' => $pendingLabCount . ' pending lab result' . ($pendingLabCount === 1 ? '' : 's'),
        'message' => 'Laboratory work still needs completion or review.',
        'href' => 'staff.php?view=lab-results',
        'type' => 'Laboratory',
    ];
}
if (($staffRole === 'Pharmacy' || $canManageStaff) && $pendingPrescriptionCount > 0) {
    $notifications[] = [
        'title' => $pendingPrescriptionCount . ' prescription' . ($pendingPrescriptionCount === 1 ? '' : 's') . ' waiting',
        'message' => 'Pharmacy should review pending medication orders.',
        'href' => 'staff.php?view=prescriptions',
        'type' => 'Pharmacy',
    ];
}
if ($canManageStaff && $recentResetCount > 0) {
    $notifications[] = [
        'title' => $recentResetCount . ' password reset request' . ($recentResetCount === 1 ? '' : 's'),
        'message' => 'Security activity recorded in the last 24 hours.',
        'href' => 'staff.php?view=activity-logs&activity_category=Security',
        'type' => 'Security',
    ];
}
$dashboardViews = ['overview' => 'Dashboard'];
if ($canManageAppointments) { $dashboardViews['appointments'] = 'Appointments'; }
if ($canManagePatients) { $dashboardViews['patients'] = 'Patients'; }
if ($canManageRecords) { $dashboardViews['records'] = 'Medical Records'; }
if ($canManageLabResults) { $dashboardViews['lab-results'] = 'Lab Results'; }
if ($canManagePrescriptions) { $dashboardViews['prescriptions'] = 'Prescriptions'; }
if ($canManageBilling) { $dashboardViews['billing'] = 'Billing'; }
if ($canManageStaff) { $dashboardViews['staff-users'] = 'Staff Accounts'; }
if ($canManageStaff) { $dashboardViews['activity-logs'] = 'Activity Logs'; }
if ($canManageStaff) { $dashboardViews['reports'] = 'Reports'; }
$activeView = $_GET['view'] ?? 'overview';
if (!isset($dashboardViews[$activeView])) {
    $activeView = 'overview';
}

include __DIR__ . '/includes/header.php';
?>

<section class="staff-app">
    <aside class="staff-sidebar">
        <div class="staff-brand">
            <span class="staff-brand-logo">
                <img src="assets/img/richcare-logo.svg" alt="RichCare Hospital">
            </span>
            <div>
                <strong>RichCare</strong>
                <span><?php echo e($staffUser['role']); ?></span>
            </div>
        </div>
        <a class="<?php echo $activeView === 'overview' ? 'active' : ''; ?>" href="staff.php">Dashboard</a>
        <?php if ($canManageAppointments): ?>
            <a class="<?php echo $activeView === 'appointments' ? 'active' : ''; ?>" href="staff.php?view=appointments">Appointments</a>
        <?php endif; ?>
        <?php if ($canManagePatients): ?>
            <a class="<?php echo $activeView === 'patients' ? 'active' : ''; ?>" href="staff.php?view=patients">Patients</a>
        <?php endif; ?>
        <?php if ($canManageRecords): ?>
            <a class="<?php echo $activeView === 'records' ? 'active' : ''; ?>" href="staff.php?view=records">Medical Records</a>
        <?php endif; ?>
        <?php if ($canManageLabResults): ?>
            <a class="<?php echo $activeView === 'lab-results' ? 'active' : ''; ?>" href="staff.php?view=lab-results">Lab Results</a>
        <?php endif; ?>
        <?php if ($canManagePrescriptions): ?>
            <a class="<?php echo $activeView === 'prescriptions' ? 'active' : ''; ?>" href="staff.php?view=prescriptions">Prescriptions</a>
        <?php endif; ?>
        <?php if ($canManageBilling): ?>
            <a class="<?php echo $activeView === 'billing' ? 'active' : ''; ?>" href="staff.php?view=billing">Billing</a>
        <?php endif; ?>
        <?php if ($canManageStaff): ?>
            <a class="<?php echo $activeView === 'staff-users' ? 'active' : ''; ?>" href="staff.php?view=staff-users">Staff Accounts</a>
            <a class="<?php echo $activeView === 'activity-logs' ? 'active' : ''; ?>" href="staff.php?view=activity-logs">Activity Logs</a>
            <a class="<?php echo $activeView === 'reports' ? 'active' : ''; ?>" href="staff.php?view=reports">Reports</a>
        <?php endif; ?>
        <a href="book.php">Public booking</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="staff-main">
        <div class="staff-header">
            <div>
                <p class="eyebrow dark">Staff portal</p>
                <h1>RichCare Dashboard</h1>
                <p>Welcome, <?php echo e($staffUser['name']); ?>. Manage today’s hospital workflow.</p>
            </div>
            <div class="staff-header-actions no-print">
                <div class="dropdown notification-dropdown">
                    <button class="btn btn-default notification-toggle dropdown-toggle" type="button" id="notificationMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
                        <span class="notification-count"><?php echo count($notifications); ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right notification-menu" aria-labelledby="notificationMenu">
                        <div class="notification-menu-heading">
                            <strong>Notifications</strong>
                            <span><?php echo count($notifications); ?> active</span>
                        </div>
                        <?php foreach ($notifications as $notice): ?>
                            <a class="notification-item" href="<?php echo e($notice['href']); ?>">
                                <span class="notification-type"><?php echo e($notice['type']); ?></span>
                                <strong><?php echo e($notice['title']); ?></strong>
                                <small><?php echo e($notice['message']); ?></small>
                            </a>
                        <?php endforeach; ?>
                        <?php if (count($notifications) === 0): ?>
                            <div class="notification-empty">
                                <strong>All clear</strong>
                                <small>No urgent alerts for your role right now.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($canManageAppointments): ?>
                    <a class="btn btn-primary" href="book.php">New booking</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($activeView === 'overview'): ?>
        <div class="row stat-row">
            <?php if ($canManagePatients): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Patients</span><strong><?php echo count($patients); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManageAppointments): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Appointments</span><strong><?php echo count($appointments); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManageBilling): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Invoices</span><strong><?php echo count($invoices); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManageRecords): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Records</span><strong><?php echo count($records); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManageLabResults): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Lab Results</span><strong><?php echo count($labResults); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManagePrescriptions): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Prescriptions</span><strong><?php echo count($prescriptions); ?></strong></div></div>
            <?php endif; ?>
            <?php if ($canManageStaff): ?>
            <div class="col-sm-3"><div class="stat-box"><span>Staff</span><strong><?php echo count($staffUsers); ?></strong></div></div>
            <?php endif; ?>
        </div>
        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Choose a workspace</h2>
                    <p>Select a category from the sidebar to open only that area of the hospital system.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($canManagePatients && $activeView === 'patients') || ($canManageRecords && $activeView === 'records')): ?>
        <div class="row action-form-row">
            <?php if ($canManagePatients && $activeView === 'patients'): ?>
            <div class="col-md-6">
                <div class="dashboard-panel compact-form-panel">
                    <div class="panel-title-row">
                        <div>
                            <h2>Register Patient</h2>
                            <p>Create a new patient profile.</p>
                        </div>
                    </div>
                    <form method="post" action="staff.php?view=patients">
                        <input type="hidden" name="form_type" value="patient">
                        <div class="row">
                            <div class="col-sm-6"><div class="form-group"><label>Full name</label><input class="form-control" name="full_name" required></div></div>
                            <div class="col-sm-6"><div class="form-group"><label>Phone</label><input class="form-control" name="phone"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6"><div class="form-group"><label>Email</label><input class="form-control" type="email" name="email"></div></div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select class="form-control" name="gender">
                                        <option>Prefer not to say</option>
                                        <option>Female</option>
                                        <option>Male</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4"><div class="form-group"><label>Ward</label><select class="form-control" name="ward"><option>Outpatient</option><option>Emergency</option><option>Maternity</option><option>Pediatrics</option><option>Laboratory</option></select></div></div>
                            <div class="col-sm-4"><div class="form-group"><label>Status</label><select class="form-control" name="risk_status"><option>Stable</option><option>Moderate</option><option>Critical</option></select></div></div>
                            <div class="col-sm-4"><div class="form-group"><label>Doctor</label><select class="form-control" name="doctor"><option>Dr. K. Mensah</option><option>Dr. N. Addo</option><option>Dr. R. Cole</option><option>Dr. A. Silva</option></select></div></div>
                        </div>
                        <button class="btn btn-primary" type="submit">Save patient</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($canManageRecords && $activeView === 'records'): ?>
            <div class="col-md-6">
                <div class="dashboard-panel compact-form-panel" id="records">
                    <div class="panel-title-row">
                        <div>
                            <h2>Add Medical Record</h2>
                            <p>Capture clinical notes for a patient.</p>
                        </div>
                    </div>
                    <form method="post" action="staff.php?view=records">
                        <input type="hidden" name="form_type" value="record">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Patient</label>
                                    <select class="form-control" name="patient_name">
                                        <?php foreach ($patients as $patient): ?>
                                            <option><?php echo e($patient['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6"><div class="form-group"><label>Record type</label><input class="form-control" name="record_type" placeholder="Diagnosis, prescription, lab note" required></div></div>
                        </div>
                        <div class="form-group">
                            <label>Owner</label>
                            <select class="form-control" name="owner"><option>Dr. K. Mensah</option><option>Dr. N. Addo</option><option>Dr. R. Cole</option><option>Dr. A. Silva</option><option>Lab Unit</option><option>Pharmacy</option></select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="4"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Save record</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($canManageStaff && $activeView === 'staff-users'): ?>
            <div class="dashboard-panel" id="staff-users">
                <div class="panel-title-row">
                    <div>
                        <h2>Staff Accounts</h2>
                        <p>Add staff members, disable accounts when workers leave, then delete disabled accounts permanently.</p>
                    </div>
                </div>
                <form class="staff-create-form" method="post" action="staff.php?view=staff-users">
                    <input type="hidden" name="form_type" value="staff_user">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Staff ID</label>
                                <input class="form-control" name="staff_code" placeholder="RC-STAFF-004" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Full name</label>
                                <input class="form-control" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>Role</label>
                                <select class="form-control" name="role">
                                    <option>Administrator</option>
                                    <option>Doctor</option>
                                    <option>Nurse</option>
                                    <option>Receptionist</option>
                                    <option>Laboratory</option>
                                    <option>Billing</option>
                                    <option>Pharmacy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>Department</label>
                                <input class="form-control" name="department" placeholder="Outpatient">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="password-toggle-field">
                                    <input class="form-control" id="new-staff-password" type="password" name="password" value="richcare" required>
                                    <button class="btn btn-default password-eye-button" type="button" data-toggle-password="#new-staff-password" onclick="toggleStaffPassword(event, '#new-staff-password')" aria-label="Show password">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Phone</label>
                                <input class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" type="email" name="email">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-primary staff-submit" type="submit">Create staff account</button>
                        </div>
                    </div>
                </form>

                <div class="staff-account-directory">
                    <?php foreach ($staffUsersByRole as $roleName => $roleAccounts): ?>
                        <?php if (count($roleAccounts) === 0) { continue; } ?>
                        <section class="staff-role-section">
                            <div class="staff-role-heading">
                                <div>
                                    <h3><?php echo e($roleName); ?></h3>
                                    <p><?php echo count($roleAccounts); ?> account<?php echo count($roleAccounts) === 1 ? '' : 's'; ?></p>
                                </div>
                                <span class="role-count"><?php echo count($roleAccounts); ?></span>
                            </div>
                            <div class="staff-card-grid">
                                <?php foreach ($roleAccounts as $staffAccount): ?>
                                    <article class="staff-card <?php echo (int) $staffAccount['is_active'] === 1 ? 'is-active' : 'is-disabled'; ?>">
                                        <div class="staff-card-top">
                                            <div>
                                                <strong><?php echo e($staffAccount['full_name']); ?></strong>
                                                <span><?php echo e($staffAccount['staff_code']); ?></span>
                                            </div>
                                            <span class="account-status <?php echo (int) $staffAccount['is_active'] === 1 ? 'active' : 'disabled'; ?>">
                                                <?php echo (int) $staffAccount['is_active'] === 1 ? 'Active' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                        <small><?php echo e($staffAccount['department']); ?></small>
                                        <p><?php echo e($staffAccount['phone'] ?: 'No phone'); ?> <?php echo e($staffAccount['email'] ?: 'No email'); ?></p>
                                        <div class="staff-account-actions">
                                            <?php if ((int) $staffAccount['is_active'] === 1): ?>
                                                <?php if ((int) $staffAccount['id'] !== (int) $staffUser['id']): ?>
                                                    <form method="post" action="staff.php?view=staff-users" onsubmit="return confirm('Disable this staff account?');">
                                                        <input type="hidden" name="staff_user_id" value="<?php echo e($staffAccount['id']); ?>">
                                                        <input type="hidden" name="staff_status" value="0">
                                                        <button class="btn btn-default btn-sm" type="submit">Disable account</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-default btn-sm" type="button" disabled>Current account</button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <form method="post" action="staff.php?view=staff-users">
                                                    <input type="hidden" name="staff_user_id" value="<?php echo e($staffAccount['id']); ?>">
                                                    <input type="hidden" name="staff_status" value="1">
                                                    <button class="btn btn-default btn-sm" type="submit">Reactivate</button>
                                                </form>
                                                <form method="post" action="staff.php?view=staff-users" onsubmit="return confirm('Delete this disabled staff account permanently?');">
                                                    <input type="hidden" name="form_type" value="delete_staff_user">
                                                    <input type="hidden" name="staff_user_id" value="<?php echo e($staffAccount['id']); ?>">
                                                    <button class="btn btn-danger btn-sm" type="submit">Delete account</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canManageStaff && $activeView === 'activity-logs'): ?>
            <div class="dashboard-panel" id="activity-logs">
                <div class="panel-title-row">
                    <div>
                        <h2>Activity Logs</h2>
                        <p>Track important staff actions, security events, patient updates, and billing changes.</p>
                    </div>
                </div>

                <form class="activity-search" method="get" action="staff.php">
                    <input type="hidden" name="view" value="activity-logs">
                    <input class="form-control" name="activity_search" value="<?php echo e($activitySearch); ?>" placeholder="Search staff, action, or description">
                    <select class="form-control" name="activity_category">
                        <option>All</option>
                        <?php foreach ($activityCategories as $category): ?>
                            <option <?php echo $activityCategory === $category ? 'selected' : ''; ?>><?php echo e($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-control" name="activity_action">
                        <option>All</option>
                        <?php foreach ($activityActions as $action): ?>
                            <option <?php echo $activityAction === $action ? 'selected' : ''; ?>><?php echo e($action); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control" type="date" name="activity_date" value="<?php echo e($activityDate); ?>">
                    <button class="btn btn-default" type="submit">Filter</button>
                    <?php if ($activitySearch !== '' || $activityCategory !== 'All' || $activityAction !== 'All' || $activityDate !== ''): ?>
                        <a class="btn btn-link" href="staff.php?view=activity-logs">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover activity-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Staff</th>
                                <th>Category</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td>
                                        <?php echo e(date('M j, Y', strtotime($log['created_at']))); ?><br>
                                        <small><?php echo e(date('g:i A', strtotime($log['created_at']))); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($log['staff_name'] ?: 'System'); ?></strong><br>
                                        <small><?php echo e($log['staff_code'] ?: 'No staff ID'); ?> <?php echo e($log['staff_role'] ?: ''); ?></small>
                                    </td>
                                    <td><span class="activity-pill"><?php echo e($log['category']); ?></span></td>
                                    <td><?php echo e($log['action']); ?></td>
                                    <td><?php echo e($log['description']); ?></td>
                                    <td><?php echo e($log['ip_address'] ?: 'Local'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($activityLogs) === 0): ?>
                                <tr><td colspan="6" class="text-muted">No activity logs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canManageStaff && $activeView === 'reports'): ?>
            <div class="dashboard-panel report-dashboard" id="reports">
                <div class="panel-title-row">
                    <div>
                        <h2>Reports Dashboard</h2>
                        <p>Review hospital performance from <?php echo e(date('M j, Y', strtotime($reportStart))); ?> to <?php echo e(date('M j, Y', strtotime($reportEnd))); ?>.</p>
                    </div>
                    <button class="btn btn-primary no-print" type="button" onclick="window.print()">Print report</button>
                </div>

                <form class="report-filter no-print" method="get" action="staff.php">
                    <input type="hidden" name="view" value="reports">
                    <div class="form-group">
                        <label>Start date</label>
                        <input class="form-control" type="date" name="report_start" value="<?php echo e($reportStart); ?>">
                    </div>
                    <div class="form-group">
                        <label>End date</label>
                        <input class="form-control" type="date" name="report_end" value="<?php echo e($reportEnd); ?>">
                    </div>
                    <button class="btn btn-default" type="submit">Apply</button>
                </form>

                <div class="report-summary-grid">
                    <div class="stat-box"><span>Total appointments</span><strong><?php echo e($reportStats['appointments'] ?? 0); ?></strong></div>
                    <div class="stat-box"><span>Completed visits</span><strong><?php echo e($reportStats['completed'] ?? 0); ?></strong></div>
                    <div class="stat-box"><span>New patients</span><strong><?php echo e($reportStats['new_patients'] ?? 0); ?></strong></div>
                    <div class="stat-box"><span>Invoices</span><strong><?php echo e($reportStats['invoice_count'] ?? 0); ?></strong></div>
                    <div class="stat-box money"><span>Total billed</span><strong>GHS <?php echo e(number_format((float) ($reportStats['total_billed'] ?? 0), 2)); ?></strong></div>
                    <div class="stat-box money"><span>Paid revenue</span><strong>GHS <?php echo e(number_format((float) ($reportStats['paid_revenue'] ?? 0), 2)); ?></strong></div>
                    <div class="stat-box money"><span>Pending debt</span><strong>GHS <?php echo e(number_format((float) ($reportStats['pending_debt'] ?? 0), 2)); ?></strong></div>
                    <div class="stat-box"><span>Staff actions</span><strong><?php echo e($reportStats['activity_count'] ?? 0); ?></strong></div>
                </div>

                <div class="report-grid">
                    <section class="report-card">
                        <div class="panel-title-row">
                            <div>
                                <h3>Visit Flow</h3>
                                <p>Appointment movement for the selected dates.</p>
                            </div>
                        </div>
                        <div class="profile-detail-grid report-mini-grid">
                            <div><span>Checked in</span><strong><?php echo e($reportStats['checked_in'] ?? 0); ?></strong></div>
                            <div><span>Completed</span><strong><?php echo e($reportStats['completed'] ?? 0); ?></strong></div>
                            <div><span>Cancelled</span><strong><?php echo e($reportStats['cancelled'] ?? 0); ?></strong></div>
                            <div><span>Total</span><strong><?php echo e($reportStats['appointments'] ?? 0); ?></strong></div>
                        </div>
                    </section>

                    <section class="report-card">
                        <div class="panel-title-row">
                            <div>
                                <h3>Billing Health</h3>
                                <p>Revenue and outstanding balances.</p>
                            </div>
                        </div>
                        <div class="profile-detail-grid report-mini-grid">
                            <div><span>Paid</span><strong>GHS <?php echo e(number_format((float) ($reportStats['paid_revenue'] ?? 0), 2)); ?></strong></div>
                            <div><span>Pending</span><strong>GHS <?php echo e(number_format((float) ($reportStats['pending_debt'] ?? 0), 2)); ?></strong></div>
                            <div><span>Cancelled</span><strong>GHS <?php echo e(number_format((float) ($reportStats['cancelled_billing'] ?? 0), 2)); ?></strong></div>
                            <div><span>Billed</span><strong>GHS <?php echo e(number_format((float) ($reportStats['total_billed'] ?? 0), 2)); ?></strong></div>
                        </div>
                    </section>
                </div>

                <div class="report-grid">
                    <section class="report-card">
                        <div class="panel-title-row">
                            <div>
                                <h3>Top Services</h3>
                                <p>Highest billing services in this period.</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Service</th><th>Invoices</th><th>Total</th></tr></thead>
                                <tbody>
                                    <?php foreach ($reportTopServices as $service): ?>
                                        <tr>
                                            <td><?php echo e($service['service_name']); ?></td>
                                            <td><?php echo e($service['invoice_count']); ?></td>
                                            <td>GHS <?php echo e(number_format((float) $service['service_total'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($reportTopServices) === 0): ?>
                                        <tr><td colspan="3" class="text-muted">No billed services in this period.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="report-card">
                        <div class="panel-title-row">
                            <div>
                                <h3>Recent Activity</h3>
                                <p>Latest staff actions in this period.</p>
                            </div>
                        </div>
                        <div class="record-card-list report-activity-list">
                            <?php foreach ($reportRecentActivity as $activity): ?>
                                <article class="record-card">
                                    <strong><?php echo e($activity['action']); ?></strong>
                                    <span><?php echo e($activity['category']); ?></span>
                                    <small><?php echo e($activity['staff_name'] ?: 'System'); ?> - <?php echo e(date('M j, g:i A', strtotime($activity['created_at']))); ?></small>
                                    <p><?php echo e($activity['description']); ?></p>
                                </article>
                            <?php endforeach; ?>
                            <?php if (count($reportRecentActivity) === 0): ?>
                                <p class="text-muted">No activity logged in this period.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canManageLabResults && $activeView === 'lab-results'): ?>
        <div class="dashboard-panel" id="lab-results">
            <div class="panel-title-row">
                <div>
                    <h2>Lab Results</h2>
                    <p>Record patient test results and lab status.</p>
                </div>
            </div>
            <form class="lab-create-form" method="post" action="staff.php?view=lab-results">
                <input type="hidden" name="form_type" value="lab_result">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Patient</label>
                            <select class="form-control" name="patient_name" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option><?php echo e($patient['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Test name</label>
                            <input class="form-control" name="test_name" placeholder="Malaria test, FBC, X-ray" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Normal range</label>
                            <input class="form-control" name="normal_range" placeholder="Optional">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option>Pending</option>
                                <option>Completed</option>
                                <option>Reviewed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Technician</label>
                            <input class="form-control" name="technician" value="<?php echo e($staffUser['name']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Result</label>
                    <textarea class="form-control" name="result_value" rows="3" placeholder="Enter result findings" required></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Save lab result</button>
            </form>

            <div class="record-card-list lab-result-list">
                <?php foreach (array_slice($labResults, 0, 6) as $result): ?>
                    <article class="record-card lab-result-card">
                        <strong><?php echo e($result['patient_name']); ?></strong>
                        <span><?php echo e($result['test_name']); ?></span>
                        <small><?php echo e($result['result_code']); ?> - <?php echo e($result['status']); ?></small>
                        <p><?php echo e($result['result_value']); ?></p>
                        <?php if ($result['normal_range']): ?>
                            <small>Normal range: <?php echo e($result['normal_range']); ?></small>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
                <?php if (count($labResults) === 0): ?>
                    <p class="text-muted">No lab results yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManagePrescriptions && $activeView === 'prescriptions'): ?>
        <div class="dashboard-panel" id="prescriptions">
            <div class="panel-title-row">
                <div>
                    <h2>Prescriptions</h2>
                    <p>Create medication orders and track dispensing status.</p>
                </div>
            </div>
            <form class="prescription-create-form" method="post" action="staff.php?view=prescriptions">
                <input type="hidden" name="form_type" value="prescription">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Patient</label>
                            <select class="form-control" name="patient_name" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option><?php echo e($patient['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Medicine</label>
                            <input class="form-control" name="medicine_name" placeholder="Paracetamol, Amoxicillin" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Dosage</label>
                            <input class="form-control" name="dosage" placeholder="500mg" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Frequency</label>
                            <input class="form-control" name="frequency" placeholder="Twice daily" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Duration</label>
                            <input class="form-control" name="duration" placeholder="5 days" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Prescriber</label>
                            <input class="form-control" name="prescriber" value="<?php echo e($staffUser['name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option>Pending</option>
                                <option>Dispensed</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label>Instructions</label>
                            <input class="form-control" name="instructions" placeholder="Take after meals">
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Save prescription</button>
            </form>

            <div class="table-responsive">
                <table class="table table-hover prescription-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Patient</th>
                            <th>Medicine</th>
                            <th>Dosage</th>
                            <th>Prescriber</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $prescription): ?>
                            <tr>
                                <td><?php echo e($prescription['prescription_code']); ?></td>
                                <td><?php echo e($prescription['patient_name']); ?></td>
                                <td>
                                    <strong><?php echo e($prescription['medicine_name']); ?></strong><br>
                                    <small><?php echo e($prescription['frequency']); ?> for <?php echo e($prescription['duration']); ?></small>
                                </td>
                                <td><?php echo e($prescription['dosage']); ?></td>
                                <td><?php echo e($prescription['prescriber']); ?></td>
                                <td><span class="label label-info"><?php echo e($prescription['status']); ?></span></td>
                                <td>
                                    <form class="inline-form" method="post" action="staff.php?view=prescriptions">
                                        <input type="hidden" name="prescription_id" value="<?php echo e($prescription['id']); ?>">
                                        <select class="form-control input-sm" name="prescription_status">
                                            <?php foreach (['Pending', 'Dispensed', 'Cancelled'] as $prescriptionStatus): ?>
                                                <option <?php echo $prescription['status'] === $prescriptionStatus ? 'selected' : ''; ?>><?php echo e($prescriptionStatus); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-default btn-sm" type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($prescriptions) === 0): ?>
                            <tr><td colspan="7" class="text-muted">No prescriptions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManageBilling && $activeView === 'billing'): ?>
        <div class="dashboard-panel" id="billing">
            <div class="panel-title-row">
                <div>
                    <h2>Billing</h2>
                    <p>Create invoices from checked-in or completed visits, then update payment status.</p>
                </div>
            </div>
            <div class="dashboard-panel billing-ready-panel">
                <div class="panel-title-row">
                    <div>
                        <h3>Patients Ready for Billing</h3>
                        <p>These visits have been checked in or completed and do not yet have an invoice.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover billing-table">
                        <thead>
                            <tr>
                                <th>Visit</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Doctor</th>
                                <th>Create invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($readyForBilling as $visit): ?>
                                <tr>
                                    <td>
                                        <?php echo e($visit['appointment_code']); ?><br>
                                        <small><?php echo e($visit['appointment_date']); ?> <?php echo e(substr($visit['appointment_time'], 0, 5)); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($visit['full_name']); ?></strong><br>
                                        <small><?php echo e($visit['phone']); ?></small>
                                    </td>
                                    <td><?php echo e($visit['department']); ?></td>
                                    <td><?php echo e($visit['doctor']); ?></td>
                                    <td>
                                        <form class="inline-form billing-visit-form" method="post" action="staff.php?view=billing">
                                            <input type="hidden" name="form_type" value="invoice">
                                            <input type="hidden" name="appointment_id" value="<?php echo e($visit['id']); ?>">
                                            <input type="hidden" name="patient_name" value="<?php echo e($visit['full_name']); ?>">
                                            <input type="hidden" name="service_name" value="<?php echo e($visit['department']); ?>">
                                            <input class="form-control input-sm" type="number" name="amount" min="0" step="0.01" placeholder="Amount" required>
                                            <select class="form-control input-sm" name="status">
                                                <option>Pending</option>
                                                <option>Paid</option>
                                            </select>
                                            <button class="btn btn-primary btn-sm" type="submit">Create</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($readyForBilling) === 0): ?>
                                <tr><td colspan="5" class="text-muted">No checked-in or completed visits are waiting for billing.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <form class="billing-create-form" method="post" action="staff.php?view=billing">
                <input type="hidden" name="form_type" value="invoice">
                <input type="hidden" name="appointment_id" value="">
                <h3>Manual invoice</h3>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Patient</label>
                            <select class="form-control" name="patient_name" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option><?php echo e($patient['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Service</label>
                            <input class="form-control" name="service_name" placeholder="Consultation, lab test, scan" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Amount</label>
                            <input class="form-control" type="number" name="amount" min="0" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option>Pending</option>
                                <option>Paid</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-primary billing-submit" type="submit">Create invoice</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <div class="panel-title-row billing-records-heading">
                    <div>
                        <h3>Invoice Records</h3>
                        <p><?php echo $billingSearch !== '' ? 'Search results for "' . e($billingSearch) . '"' : 'All billing records'; ?></p>
                    </div>
                    <form class="billing-search" method="get" action="staff.php">
                        <input type="hidden" name="view" value="billing">
                        <input class="form-control" name="billing_search" value="<?php echo e($billingSearch); ?>" placeholder="Search invoice, patient, service, status, visit">
                        <button class="btn btn-default" type="submit">Search</button>
                        <?php if ($billingSearch !== ''): ?>
                            <a class="btn btn-link" href="staff.php?view=billing">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <table class="table table-hover billing-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Visit</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo e($invoice['invoice_code']); ?></td>
                                <td><?php echo e($invoice['patient_name']); ?></td>
                                <td><?php echo e($invoice['service_name']); ?></td>
                                <td><?php echo $invoice['appointment_code'] ? e($invoice['appointment_code']) : 'Manual'; ?></td>
                                <td class="amount">GHS <?php echo number_format((float) $invoice['amount'], 2); ?></td>
                                <td><span class="label label-info"><?php echo e($invoice['status']); ?></span></td>
                                <td>
                                    <form class="inline-form" method="post" action="staff.php?view=billing">
                                        <input type="hidden" name="invoice_id" value="<?php echo e($invoice['id']); ?>">
                                        <select class="form-control input-sm" name="invoice_status">
                                            <?php foreach (['Pending', 'Paid', 'Cancelled'] as $invoiceStatus): ?>
                                                <option <?php echo $invoice['status'] === $invoiceStatus ? 'selected' : ''; ?>><?php echo e($invoiceStatus); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-default btn-sm" type="submit">Update</button>
                                        <a class="btn btn-primary btn-sm" href="invoice.php?id=<?php echo e($invoice['id']); ?>">Print</a>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($invoices) === 0): ?>
                            <tr><td colspan="7" class="text-muted">No invoices yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManageAppointments && $activeView === 'appointments'): ?>
        <div class="dashboard-panel" id="appointments">
            <div class="panel-title-row">
                <div>
                    <h2>Appointments</h2>
                    <p><?php echo e($statusFilter); ?> appointment queue</p>
                </div>
                <div class="filter-pills">
                    <?php foreach ($allowedStatuses as $status): ?>
                        <a class="<?php echo $statusFilter === $status ? 'active' : ''; ?>" href="staff.php?view=appointments&status=<?php echo urlencode($status); ?>">
                            <?php echo e($status); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Doctor</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo e($appointment['appointment_code']); ?></td>
                                <td>
                                    <strong><?php echo e($appointment['full_name']); ?></strong><br>
                                    <small><?php echo e($appointment['phone']); ?> <?php echo e($appointment['email']); ?></small>
                                </td>
                                <td><?php echo e($appointment['appointment_date']); ?> <?php echo e(substr($appointment['appointment_time'], 0, 5)); ?></td>
                                <td>
                                    <?php echo e($appointment['department']); ?>
                                    <?php if ((int) $appointment['is_emergency'] === 1): ?>
                                        <span class="label label-danger">Urgent</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($appointment['doctor']); ?></td>
                                <td><span class="label label-info"><?php echo e($appointment['status']); ?></span></td>
                                <td>
                                    <form class="inline-form" method="post" action="staff.php?view=appointments">
                                        <input type="hidden" name="appointment_id" value="<?php echo e($appointment['id']); ?>">
                                        <input type="hidden" name="return_status" value="<?php echo e($statusFilter); ?>">
                                        <select class="form-control input-sm" name="status">
                                            <?php foreach (array_slice($allowedStatuses, 1) as $status): ?>
                                                <option <?php echo $appointment['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-default btn-sm" type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManagePatients && $activeView === 'patients'): ?>
        <div class="dashboard-panel" id="patients">
            <div class="panel-title-row">
                <div>
                    <h2>Patients</h2>
                    <p>Registered patient records</p>
                </div>
                <form class="patient-search" id="patient-search" method="get" action="staff.php">
                    <input type="hidden" name="view" value="patients">
                    <input class="form-control" name="patient_search" value="<?php echo e($patientSearch); ?>" placeholder="Search name, code, phone, doctor">
                    <button class="btn btn-default" type="submit">Search</button>
                    <?php if ($patientSearch !== ''): ?>
                        <a class="btn btn-link" href="staff.php?view=patients">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="patient-card-grid">
                <?php foreach ($patients as $patient): ?>
                    <a class="patient-card patient-card-link" href="patient.php?id=<?php echo e($patient['id']); ?>">
                        <strong><?php echo e($patient['full_name']); ?></strong>
                        <span><?php echo e($patient['patient_code']); ?></span>
                        <div>
                            <small><?php echo e($patient['ward']); ?></small>
                            <small><?php echo e($patient['risk_status']); ?></small>
                        </div>
                        <p><?php echo e($patient['doctor']); ?></p>
                    </a>
                <?php endforeach; ?>
                <?php if (count($patients) === 0): ?>
                    <p class="text-muted">No matching patients found.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManageRecords && $activeView === 'records'): ?>
        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Recent Medical Records</h2>
                    <p>Latest clinical updates.</p>
                </div>
            </div>
            <div class="record-card-list">
                <?php foreach (array_slice($records, 0, 6) as $record): ?>
                    <article class="record-card">
                        <strong><?php echo e($record['patient_name']); ?></strong>
                        <span><?php echo e($record['record_type']); ?></span>
                        <small><?php echo e($record['owner']); ?> - <?php echo e($record['created_at']); ?></small>
                    </article>
                <?php endforeach; ?>
                <?php if (count($records) === 0): ?>
                    <p class="text-muted">No medical records yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
