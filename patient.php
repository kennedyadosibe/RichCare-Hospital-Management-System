<?php
require_once __DIR__ . '/auth.php';
require_staff_login();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (!staff_has_any_role(['Administrator', 'Receptionist', 'Doctor', 'Nurse', 'Laboratory', 'Pharmacy'])) {
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-section"><div class="container"><div class="alert alert-danger">You do not have permission to view patient records.</div><a class="btn btn-default" href="staff.php">Back to dashboard</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$patientId = (int) ($_GET['id'] ?? 0);
$statement = $pdo->prepare('SELECT * FROM patients WHERE id = :id');
$statement->execute([':id' => $patientId]);
$patient = $statement->fetch();

if (!$patient) {
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-section"><div class="container"><div class="alert alert-warning">Patient not found.</div><a class="btn btn-default" href="staff.php">Back to dashboard</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$appointmentStatement = $pdo->prepare('SELECT * FROM appointments WHERE full_name = :name OR phone = :phone OR email = :email ORDER BY appointment_date DESC, appointment_time DESC');
$appointmentStatement->execute([
    ':name' => $patient['full_name'],
    ':phone' => $patient['phone'],
    ':email' => $patient['email'],
]);
$appointments = $appointmentStatement->fetchAll();

$recordStatement = $pdo->prepare('SELECT * FROM medical_records WHERE patient_name = :name ORDER BY created_at DESC');
$recordStatement->execute([':name' => $patient['full_name']]);
$records = $recordStatement->fetchAll();

$labStatement = $pdo->prepare('SELECT * FROM lab_results WHERE patient_name = :name ORDER BY created_at DESC');
$labStatement->execute([':name' => $patient['full_name']]);
$labResults = $labStatement->fetchAll();

$prescriptionStatement = $pdo->prepare('SELECT * FROM prescriptions WHERE patient_name = :name ORDER BY created_at DESC');
$prescriptionStatement->execute([':name' => $patient['full_name']]);
$prescriptions = $prescriptionStatement->fetchAll();

$invoiceStatement = $pdo->prepare('SELECT * FROM invoices WHERE patient_name = :name ORDER BY created_at DESC');
$invoiceStatement->execute([':name' => $patient['full_name']]);
$invoices = $invoiceStatement->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="staff-app patient-report-page">
    <aside class="staff-sidebar no-print">
        <div class="staff-brand">
            <span class="staff-brand-logo">
                <img src="assets/img/richcare-logo.svg" alt="RichCare Hospital">
            </span>
            <div>
                <strong>RichCare</strong>
                <span>Patient file</span>
            </div>
        </div>
        <a href="staff.php">Dashboard</a>
        <a class="active" href="patient.php?id=<?php echo e($patient['id']); ?>">Patient Record</a>
        <a href="staff.php?view=patients">Patients</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="staff-main">
        <div class="staff-header">
            <div>
                <p class="eyebrow dark">Previous patient record</p>
                <h1><?php echo e($patient['full_name']); ?></h1>
                <p><?php echo e($patient['patient_code']); ?> - <?php echo e($patient['ward']); ?> - <?php echo e($patient['doctor']); ?></p>
                <p class="print-only">Generated <?php echo e(date('F j, Y g:i A')); ?> by RichCare Hospital Management System.</p>
            </div>
            <div class="patient-report-actions no-print">
                <button class="btn btn-primary" type="button" onclick="window.print()">Print report</button>
                <a class="btn btn-default" href="staff.php?view=patients">Back to patients</a>
            </div>
        </div>

        <div class="row stat-row">
            <div class="col-sm-3"><div class="stat-box"><span>Status</span><strong><?php echo e($patient['risk_status']); ?></strong></div></div>
            <div class="col-sm-3"><div class="stat-box"><span>Appointments</span><strong><?php echo count($appointments); ?></strong></div></div>
            <div class="col-sm-3"><div class="stat-box"><span>Records</span><strong><?php echo count($records); ?></strong></div></div>
            <div class="col-sm-3"><div class="stat-box"><span>Lab Results</span><strong><?php echo count($labResults); ?></strong></div></div>
            <div class="col-sm-3"><div class="stat-box"><span>Prescriptions</span><strong><?php echo count($prescriptions); ?></strong></div></div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Patient Details</h2>
                    <p>Demographics and assignment</p>
                </div>
            </div>
            <div class="profile-detail-grid">
                <div><span>Phone</span><strong><?php echo e($patient['phone'] ?: 'Not provided'); ?></strong></div>
                <div><span>Email</span><strong><?php echo e($patient['email'] ?: 'Not provided'); ?></strong></div>
                <div><span>Gender</span><strong><?php echo e($patient['gender'] ?: 'Not provided'); ?></strong></div>
                <div><span>Ward</span><strong><?php echo e($patient['ward']); ?></strong></div>
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Appointment History</h2>
                    <p>Previous and upcoming visits</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Reference</th><th>Date</th><th>Department</th><th>Doctor</th><th>Status</th><th>Symptoms</th></tr></thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo e($appointment['appointment_code']); ?></td>
                                <td><?php echo e($appointment['appointment_date']); ?> <?php echo e(substr($appointment['appointment_time'], 0, 5)); ?></td>
                                <td><?php echo e($appointment['department']); ?></td>
                                <td><?php echo e($appointment['doctor']); ?></td>
                                <td><span class="label label-info"><?php echo e($appointment['status']); ?></span></td>
                                <td><?php echo e($appointment['symptoms']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($appointments) === 0): ?><p class="text-muted">No appointments found.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Medical Records</h2>
                    <p>Clinical notes and updates</p>
                </div>
            </div>
            <div class="record-card-list">
                <?php foreach ($records as $record): ?>
                    <article class="record-card">
                        <strong><?php echo e($record['record_type']); ?></strong>
                        <span><?php echo e($record['owner']); ?></span>
                        <small><?php echo e($record['created_at']); ?></small>
                        <p><?php echo e($record['notes']); ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (count($records) === 0): ?><p class="text-muted">No medical records found.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Lab Results</h2>
                    <p>Laboratory tests and findings</p>
                </div>
            </div>
            <div class="record-card-list lab-result-list">
                <?php foreach ($labResults as $result): ?>
                    <article class="record-card lab-result-card">
                        <strong><?php echo e($result['test_name']); ?></strong>
                        <span><?php echo e($result['status']); ?></span>
                        <small><?php echo e($result['result_code']); ?> - <?php echo e($result['technician']); ?> - <?php echo e($result['created_at']); ?></small>
                        <p><?php echo e($result['result_value']); ?></p>
                        <?php if ($result['normal_range']): ?>
                            <small>Normal range: <?php echo e($result['normal_range']); ?></small>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
                <?php if (count($labResults) === 0): ?><p class="text-muted">No lab results found.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Prescriptions</h2>
                    <p>Medication history and dispensing status</p>
                </div>
            </div>
            <div class="record-card-list prescription-list">
                <?php foreach ($prescriptions as $prescription): ?>
                    <article class="record-card prescription-card">
                        <strong><?php echo e($prescription['medicine_name']); ?></strong>
                        <span><?php echo e($prescription['dosage']); ?> - <?php echo e($prescription['frequency']); ?></span>
                        <small><?php echo e($prescription['prescription_code']); ?> - <?php echo e($prescription['status']); ?> - <?php echo e($prescription['prescriber']); ?></small>
                        <p><?php echo e($prescription['duration']); ?><?php echo $prescription['instructions'] ? ' - ' . e($prescription['instructions']) : ''; ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (count($prescriptions) === 0): ?><p class="text-muted">No prescriptions found.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-panel">
            <div class="panel-title-row">
                <div>
                    <h2>Billing</h2>
                    <p>Invoice history</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Invoice</th><th>Service</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo e($invoice['invoice_code']); ?></td>
                                <td><?php echo e($invoice['service_name']); ?></td>
                                <td>GHS <?php echo e(number_format((float) $invoice['amount'], 2)); ?></td>
                                <td><span class="label label-info"><?php echo e($invoice['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($invoices) === 0): ?><p class="text-muted">No invoices found.</p><?php endif; ?>
            </div>
        </div>
    </main>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
