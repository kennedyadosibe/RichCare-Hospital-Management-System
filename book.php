<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$confirmation = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = make_code('APT');
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? 'Prefer not to say';
    $date = $_POST['appointment_date'] ?? date('Y-m-d');
    $time = $_POST['appointment_time'] ?? '09:00';
    $department = $_POST['department'] ?? 'General consultation';
    $doctor = $_POST['doctor'] ?? 'Dr. K. Mensah';
    $symptoms = trim($_POST['symptoms'] ?? '');
    $emergency = isset($_POST['is_emergency']) ? 1 : 0;

    if ($fullName === '' || $phone === '' || $email === '') {
        $error = 'Please enter your name, phone number, and email address.';
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO appointments
            (appointment_code, full_name, phone, email, gender, appointment_date, appointment_time, department, doctor, symptoms, is_emergency, status)
            VALUES
            (:code, :full_name, :phone, :email, :gender, :appointment_date, :appointment_time, :department, :doctor, :symptoms, :is_emergency, "Pending")'
        );

        $statement->execute([
            ':code' => $code,
            ':full_name' => $fullName,
            ':phone' => $phone,
            ':email' => $email,
            ':gender' => $gender,
            ':appointment_date' => $date,
            ':appointment_time' => $time,
            ':department' => $department,
            ':doctor' => $doctor,
            ':symptoms' => $symptoms,
            ':is_emergency' => $emergency,
        ]);

        $confirmation = [
            'code' => $code,
            'name' => $fullName,
            'date' => $date,
            'time' => $time,
            'department' => $department,
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section page-section">
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <p class="eyebrow dark">Book online</p>
                <h1>Request an appointment</h1>
                <p>
                    Submit your visit request and RichCare will place it into the hospital appointment workflow
                    for front desk confirmation.
                </p>

                <?php if ($confirmation): ?>
                    <div class="confirmation-card">
                        <span class="glyphicon glyphicon-ok-circle"></span>
                        <h3>Appointment request received</h3>
                        <p><strong>Reference:</strong> <?php echo e($confirmation['code']); ?></p>
                        <p>
                            <?php echo e($confirmation['name']); ?> -
                            <?php echo e($confirmation['department']); ?> on
                            <?php echo e($confirmation['date']); ?> at
                            <?php echo e($confirmation['time']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-7">
                <form class="booking-form" method="post" action="book.php">
                    <div class="form-group">
                        <label>Full name</label>
                        <input class="form-control" type="text" name="full_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Phone number</label>
                                <input class="form-control" type="text" name="phone" placeholder="Enter phone number" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email address</label>
                                <input class="form-control" type="email" name="email" placeholder="Enter email address" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Preferred date</label>
                                <input class="form-control" type="date" name="appointment_date" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Preferred time</label>
                                <input class="form-control" type="time" name="appointment_time" value="09:00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="form-control" name="department">
                                    <option>General consultation</option>
                                    <option>Cardiology review</option>
                                    <option>Laboratory follow-up</option>
                                    <option>Maternity care</option>
                                    <option>Pharmacy counselling</option>
                                </select>
                            </div>
                        </div>
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
                    <div class="form-group">
                        <label>Preferred doctor</label>
                        <select class="form-control" name="doctor">
                            <option>Dr. K. Mensah</option>
                            <option>Dr. N. Addo</option>
                            <option>Dr. R. Cole</option>
                            <option>Dr. A. Silva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Symptoms or reason for visit</label>
                        <textarea class="form-control" name="symptoms" rows="4" placeholder="Briefly describe symptoms or purpose of visit"></textarea>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_emergency"> This is urgent and should be reviewed quickly
                        </label>
                    </div>
                    <button class="btn btn-primary btn-lg btn-block" type="submit">Submit booking</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
