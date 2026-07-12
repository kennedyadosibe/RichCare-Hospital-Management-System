<?php
require_once __DIR__ . '/auth.php';
require_staff_login();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (!staff_has_any_role(['Administrator', 'Billing'])) {
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-section"><div class="container"><div class="alert alert-danger">You do not have permission to view invoices.</div><a class="btn btn-default" href="staff.php">Back to dashboard</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$invoiceId = (int) ($_GET['id'] ?? 0);
$statement = $pdo->prepare(
    'SELECT i.*, a.appointment_code, a.appointment_date, a.appointment_time, a.department, a.doctor
     FROM invoices i
     LEFT JOIN appointments a ON a.id = i.appointment_id
     WHERE i.id = :id'
);
$statement->execute([':id' => $invoiceId]);
$invoice = $statement->fetch();

if (!$invoice) {
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-section"><div class="container"><div class="alert alert-warning">Invoice not found.</div><a class="btn btn-default" href="staff.php?view=billing">Back to billing</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$patientStatement = $pdo->prepare('SELECT * FROM patients WHERE full_name = :name LIMIT 1');
$patientStatement->execute([':name' => $invoice['patient_name']]);
$patient = $patientStatement->fetch();

include __DIR__ . '/includes/header.php';
?>

<section class="page-section invoice-page">
    <div class="container">
        <div class="invoice-actions no-print">
            <a class="btn btn-default" href="staff.php?view=billing">Back to billing</a>
            <button class="btn btn-primary" type="button" onclick="window.print()">Print invoice</button>
        </div>

        <article class="invoice-sheet">
            <div class="invoice-header">
                <div>
                    <h1>RichCare Hospital</h1>
                    <p>Accra Technical University, Barnes Road, Accra</p>
                    <p>+233 20 000 0000 | care@richcare.example</p>
                </div>
                <div class="invoice-status">
                    <span><?php echo e($invoice['status']); ?></span>
                    <strong><?php echo e($invoice['invoice_code']); ?></strong>
                </div>
            </div>

            <div class="invoice-meta-grid">
                <div>
                    <span>Bill to</span>
                    <strong><?php echo e($invoice['patient_name']); ?></strong>
                    <p><?php echo e($patient['phone'] ?? 'Phone not available'); ?></p>
                    <p><?php echo e($patient['email'] ?? 'Email not available'); ?></p>
                </div>
                <div>
                    <span>Invoice date</span>
                    <strong><?php echo e(date('F j, Y', strtotime($invoice['created_at']))); ?></strong>
                    <p><?php echo $invoice['appointment_code'] ? 'Visit: ' . e($invoice['appointment_code']) : 'Manual invoice'; ?></p>
                    <?php if ($invoice['appointment_code']): ?>
                        <p><?php echo e($invoice['doctor']); ?> - <?php echo e($invoice['appointment_date']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <table class="table invoice-items">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo e($invoice['service_name']); ?></td>
                        <td class="text-right">GHS <?php echo number_format((float) $invoice['amount'], 2); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right">GHS <?php echo number_format((float) $invoice['amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="invoice-footer-note">
                <p>Thank you for choosing RichCare Hospital.</p>
                <p>This invoice was generated from the RichCare hospital management system.</p>
            </div>
        </article>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
