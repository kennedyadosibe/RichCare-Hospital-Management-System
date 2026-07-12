<?php include __DIR__ . '/includes/header.php'; ?>

<header class="hero-section">
    <div class="container">
        <div class="row hero-row">
            <div class="col-md-8">
                <p class="eyebrow">Digital hospital care</p>
                <h1>RichCare</h1>
                <p class="lead">
                    A modern hospital management website where patients can learn about RichCare,
                    book appointments, and hospital staff can coordinate care from one secure portal.
                </p>
                <p>
                    <a class="btn btn-light btn-lg" href="book.php">Book appointment</a>
                    <a class="btn btn-outline btn-lg" href="#about">Read about RichCare</a>
                </p>
            </div>
            <div class="col-md-4">
                <div class="hero-card">
                    <h3>Open for appointments</h3>
                    <p>Outpatient, emergency, laboratory, pharmacy, maternity, and general consultation services.</p>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section about-section" id="about">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="eyebrow dark">About RichCare</p>
                <h2>Healthcare that feels organized before you even arrive.</h2>
                <p>
                    RichCare is built for patients who want clarity, speed, and confidence from the first
                    click. Instead of waiting in uncertainty, patients can book ahead, share their visit
                    reason, and arrive knowing the hospital team already has the right information.
                </p>
                <p>
                    For staff, RichCare connects appointments, patient profiles, medical records,
                    lab results, prescriptions, billing, and administration into one coordinated workflow.
                    That means fewer misplaced records, better department communication, and a smoother
                    care experience for everyone.
                </p>
                <div class="about-metrics row">
                    <div class="col-xs-4">
                        <strong>Fast</strong>
                        <span>Booking</span>
                    </div>
                    <div class="col-xs-4">
                        <strong>Secure</strong>
                        <span>Records</span>
                    </div>
                    <div class="col-xs-4">
                        <strong>Smart</strong>
                        <span>Care flow</span>
                    </div>
                </div>
                <div class="about-cta-row">
                    <a class="btn btn-primary" href="book.php">Choose RichCare</a>
                    <a class="btn btn-default" href="#services">View services</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-image-wrap">
                    <img class="img-responsive" src="assets/img/richcare-about.png" alt="RichCare care team using digital records">
                </div>
                <div class="feature-list">
                    <p><span class="glyphicon glyphicon-ok"></span> Book visits without unnecessary queues</p>
                    <p><span class="glyphicon glyphicon-ok"></span> Staff can find previous patient records quickly</p>
                    <p><span class="glyphicon glyphicon-ok"></span> Lab, pharmacy, billing, and doctors work from one system</p>
                    <p><span class="glyphicon glyphicon-ok"></span> Patients receive a more organized hospital experience</p>
                </div>
            </div>
        </div>
        <div class="about-trust-grid">
            <div class="trust-item">
                <span class="glyphicon glyphicon-time"></span>
                <strong>Less waiting</strong>
                <p>Online booking and clear appointment queues help the hospital prepare before patients arrive.</p>
            </div>
            <div class="trust-item">
                <span class="glyphicon glyphicon-folder-open"></span>
                <strong>Better continuity</strong>
                <p>Previous visits, lab results, prescriptions, and invoices stay connected to the patient profile.</p>
            </div>
            <div class="trust-item">
                <span class="glyphicon glyphicon-lock"></span>
                <strong>Controlled access</strong>
                <p>Role-based dashboards help each staff member work with the right tools and information.</p>
            </div>
        </div>
    </div>
</section>

<section class="section services-section" id="services">
    <div class="container">
        <p class="eyebrow dark">Services</p>
        <h2>Care areas supported by RichCare</h2>
        <div class="row service-grid">
            <div class="col-sm-6 col-md-3">
                <div class="service-card">
                    <span class="glyphicon glyphicon-user"></span>
                    <h3>Patient Management</h3>
                    <p>Registration, admissions, ward assignment, and care ownership.</p>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="service-card">
                    <span class="glyphicon glyphicon-calendar"></span>
                    <h3>Appointments</h3>
                    <p>Online booking, scheduling, queue visibility, and doctor assignment.</p>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="service-card">
                    <span class="glyphicon glyphicon-file"></span>
                    <h3>Medical Records</h3>
                    <p>Clinical notes, lab updates, prescriptions, and discharge summaries.</p>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="service-card">
                    <span class="glyphicon glyphicon-briefcase"></span>
                    <h3>Administration</h3>
                    <p>Department workload, billing status, staff roles, and secure access.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section contact-section" id="contact">
    <div class="container">
        <div class="contact-card">
            <div class="row">
                <div class="col-md-5">
                    <p class="eyebrow">Contact</p>
                    <h2>Visit RichCare at Accra Technical University</h2>
                    <p>
                        RichCare is positioned as a campus hospital service within Accra Technical University,
                        supporting students, staff, and visitors with appointment booking and coordinated care.
                    </p>
                    <div class="campus-note">
                        <strong>Campus location</strong>
                        <span>Accra Technical University, Barnes Road, Accra</span>
                    </div>
                    <div class="contact-actions">
                        <a class="btn btn-primary" href="book.php">Book appointment</a>
                        <a class="btn btn-default" href="https://www.google.com/maps/search/?api=1&query=Accra%20Technical%20University%20Barnes%20Road%20Accra%20Ghana" target="_blank" rel="noopener">Open map</a>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="map-panel">
                        <iframe
                            title="RichCare Hospital at Accra Technical University"
                            src="https://www.google.com/maps?q=Accra%20Technical%20University%2C%20Barnes%20Road%2C%20Accra%2C%20Ghana&output=embed"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <div class="contact-list">
                        <p><span class="glyphicon glyphicon-earphone"></span> +233 20 000 0000</p>
                        <p><span class="glyphicon glyphicon-envelope"></span> care@richcare.example</p>
                        <p><span class="glyphicon glyphicon-map-marker"></span> RichCare Campus Hospital, Accra Technical University</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
