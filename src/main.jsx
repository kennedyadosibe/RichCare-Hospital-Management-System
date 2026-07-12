import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  Activity,
  Bell,
  Building2,
  CalendarDays,
  CheckCircle2,
  ClipboardList,
  Clock3,
  CreditCard,
  Download,
  Filter,
  FileText,
  HeartPulse,
  LayoutDashboard,
  LockKeyhole,
  LogIn,
  Mail,
  MapPin,
  Menu,
  MessageSquareText,
  Phone,
  Plus,
  Save,
  Search,
  Settings,
  ShieldCheck,
  UserPlus,
  UserRoundCheck,
  Stethoscope,
  Users,
  X,
} from 'lucide-react';
import './styles.css';

const navItems = [
  { label: 'Dashboard', icon: LayoutDashboard },
  { label: 'Patients', icon: Users },
  { label: 'Appointments', icon: CalendarDays },
  { label: 'Medical Records', icon: FileText },
  { label: 'Billing', icon: CreditCard },
  { label: 'Settings', icon: Settings },
];

const API_URL = 'http://127.0.0.1:4000/api';

const stats = [
  { label: 'Patients Today', value: '128', change: '+18%', icon: Users },
  { label: 'Active Appointments', value: '46', change: '12 urgent', icon: CalendarDays },
  { label: 'Open Records', value: '312', change: '24 updated', icon: ClipboardList },
  { label: 'Care Teams Online', value: '19', change: '7 departments', icon: Stethoscope },
];

const initialAppointments = [
  { time: '08:30', patient: 'Amara Okafor', reason: 'Cardiology review', status: 'Checked in', doctor: 'Dr. K. Mensah' },
  { time: '09:10', patient: 'Daniel Mensah', reason: 'Lab follow-up', status: 'Waiting', doctor: 'Dr. R. Cole' },
  { time: '10:00', patient: 'Grace Bello', reason: 'General consultation', status: 'Confirmed', doctor: 'Dr. A. Silva' },
  { time: '11:20', patient: 'Kwame Essien', reason: 'Pharmacy counselling', status: 'Pending', doctor: 'Dr. N. Addo' },
];

const initialPatients = [
  { name: 'Sarah Johnson', id: 'RC-20491', ward: 'Maternity', risk: 'Stable', doctor: 'Dr. N. Addo' },
  { name: 'Peter Coleman', id: 'RC-20485', ward: 'Emergency', risk: 'Critical', doctor: 'Dr. R. Cole' },
  { name: 'Mina Arthur', id: 'RC-20470', ward: 'Pediatrics', risk: 'Moderate', doctor: 'Dr. A. Silva' },
  { name: 'Felix Brown', id: 'RC-20451', ward: 'Outpatient', risk: 'Stable', doctor: 'Dr. K. Mensah' },
];

const departments = [
  { name: 'Emergency', load: 86 },
  { name: 'Outpatient', load: 64 },
  { name: 'Laboratory', load: 53 },
  { name: 'Pharmacy', load: 42 },
];

const recordItems = [
  { patient: 'Sarah Johnson', type: 'Antenatal review', updated: '08:42', owner: 'Dr. N. Addo' },
  { patient: 'Peter Coleman', type: 'Emergency triage note', updated: '09:18', owner: 'Dr. R. Cole' },
  { patient: 'Mina Arthur', type: 'Lab result attached', updated: '10:05', owner: 'Lab Unit' },
  { patient: 'Felix Brown', type: 'Prescription issued', updated: '11:16', owner: 'Pharmacy' },
];

const invoices = [
  { id: 'INV-7401', patient: 'Amara Okafor', item: 'Cardiology consultation', amount: 'GHS 420', status: 'Paid' },
  { id: 'INV-7398', patient: 'Daniel Mensah', item: 'Laboratory panel', amount: 'GHS 260', status: 'Pending' },
  { id: 'INV-7394', patient: 'Grace Bello', item: 'General consultation', amount: 'GHS 180', status: 'Paid' },
  { id: 'INV-7389', patient: 'Kwame Essien', item: 'Medication pack', amount: 'GHS 145', status: 'Pending' },
];

const emptyPatient = {
  name: '',
  ward: 'Outpatient',
  risk: 'Stable',
  doctor: 'Dr. K. Mensah',
};

const emptyAppointment = {
  date: new Date().toISOString().slice(0, 10),
  time: '12:00',
  patient: '',
  phone: '',
  email: '',
  gender: 'Prefer not to say',
  reason: '',
  symptoms: '',
  emergency: false,
  status: 'Confirmed',
  doctor: 'Dr. K. Mensah',
};

const emptyRecord = {
  patient: '',
  type: '',
  owner: 'Dr. K. Mensah',
  notes: '',
};

function App() {
  const [currentUser, setCurrentUser] = useState(null);
  const [siteMode, setSiteMode] = useState('public');
  const [activeSection, setActiveSection] = useState('Dashboard');
  const [searchTerm, setSearchTerm] = useState('');
  const [patients, setPatients] = useState(initialPatients);
  const [appointments, setAppointments] = useState(initialAppointments);
  const [records, setRecords] = useState(recordItems);
  const [billingItems, setBillingItems] = useState(invoices);
  const [departmentLoads, setDepartmentLoads] = useState(departments);
  const [apiStatus, setApiStatus] = useState('Connecting to API');
  const [isPatientModalOpen, setIsPatientModalOpen] = useState(false);
  const [isAppointmentModalOpen, setIsAppointmentModalOpen] = useState(false);
  const [isRecordModalOpen, setIsRecordModalOpen] = useState(false);
  const [selectedPatient, setSelectedPatient] = useState(null);
  const [newPatient, setNewPatient] = useState(emptyPatient);
  const [newAppointment, setNewAppointment] = useState(emptyAppointment);
  const [newRecord, setNewRecord] = useState(emptyRecord);

  useEffect(() => {
    async function loadDashboard() {
      try {
        const response = await fetch(`${API_URL}/dashboard`);
        if (!response.ok) throw new Error('Unable to load RichCare API data.');

        const data = await response.json();
        setPatients(data.patients ?? initialPatients);
        setAppointments(data.appointments ?? initialAppointments);
        setRecords(data.records ?? recordItems);
        setBillingItems(data.invoices ?? invoices);
        setDepartmentLoads(data.departments ?? departments);
        setApiStatus('Connected to persistent database');
      } catch (error) {
        setApiStatus('Using demo data until API starts');
      }
    }

    loadDashboard();
  }, []);

  const filteredPatients = useMemo(() => {
    const term = searchTerm.trim().toLowerCase();
    if (!term) return patients;
    return patients.filter((patient) =>
      [patient.name, patient.id, patient.ward, patient.risk, patient.doctor].some((value) =>
        value.toLowerCase().includes(term),
      ),
    );
  }, [patients, searchTerm]);

  const filteredAppointments = useMemo(() => {
    const term = searchTerm.trim().toLowerCase();
    if (!term) return appointments;
    return appointments.filter((item) =>
      [item.patient, item.reason, item.status, item.doctor].some((value) => value.toLowerCase().includes(term)),
    );
  }, [appointments, searchTerm]);

  function openPatientModal() {
    setNewPatient(emptyPatient);
    setIsPatientModalOpen(true);
  }

  function openAppointmentModal() {
    setNewAppointment({ ...emptyAppointment, patient: patients[0]?.name ?? '' });
    setIsAppointmentModalOpen(true);
  }

  function openRecordModal() {
    setNewRecord({ ...emptyRecord, patient: patients[0]?.name ?? '' });
    setIsRecordModalOpen(true);
  }

  function openRecordForPatient(patient) {
    setNewRecord({ ...emptyRecord, patient: patient.name, owner: patient.doctor });
    setSelectedPatient(null);
    setIsRecordModalOpen(true);
  }

  function openAppointmentForPatient(patient) {
    setNewAppointment({ ...emptyAppointment, patient: patient.name, doctor: patient.doctor });
    setSelectedPatient(null);
    setIsAppointmentModalOpen(true);
  }

  async function bookPublicAppointment(appointment) {
    const publicAppointment = {
      date: appointment.date,
      email: appointment.email,
      emergency: appointment.emergency,
      gender: appointment.gender,
      phone: appointment.phone,
      time: appointment.time,
      patient: appointment.patient,
      reason: appointment.reason,
      symptoms: appointment.symptoms,
      status: 'Pending',
      doctor: appointment.doctor,
    };

    try {
      const response = await fetch(`${API_URL}/appointments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(publicAppointment),
      });

      if (!response.ok) throw new Error('Appointment booking failed.');

      const savedAppointment = await response.json();
      setAppointments([...appointments, savedAppointment]);
      setApiStatus('Website appointment request saved');
      return savedAppointment;
    } catch (error) {
      const fallbackAppointment = { ...publicAppointment, id: `APT-${1000 + appointments.length + 1}` };
      setAppointments([...appointments, fallbackAppointment]);
      setApiStatus('Website appointment request added locally');
      return fallbackAppointment;
    }
  }

  async function addPatient(event) {
    event.preventDefault();
    const name = newPatient.name.trim();
    if (!name) return;

    try {
      const response = await fetch(`${API_URL}/patients`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...newPatient, name }),
      });

      if (!response.ok) throw new Error('Patient registration failed.');

      const savedPatient = await response.json();
      setPatients([savedPatient, ...patients]);
      setApiStatus('Patient saved to database');
    } catch (error) {
      const fallbackPatient = { ...newPatient, name, id: `RC-${20500 + patients.length}` };
      setPatients([fallbackPatient, ...patients]);
      setApiStatus('Patient added locally; start API to persist changes');
    } finally {
      setIsPatientModalOpen(false);
      setActiveSection('Patients');
    }
  }

  async function addAppointment(event) {
    event.preventDefault();
    if (!newAppointment.patient.trim() || !newAppointment.reason.trim()) return;

    try {
      const response = await fetch(`${API_URL}/appointments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newAppointment),
      });

      if (!response.ok) throw new Error('Appointment booking failed.');

      const savedAppointment = await response.json();
      setAppointments([...appointments, savedAppointment]);
      setApiStatus('Appointment saved to database');
    } catch (error) {
      const fallbackAppointment = { ...newAppointment, id: `APT-${1000 + appointments.length + 1}` };
      setAppointments([...appointments, fallbackAppointment]);
      setApiStatus('Appointment added locally; start API to persist changes');
    } finally {
      setIsAppointmentModalOpen(false);
      setActiveSection('Appointments');
    }
  }

  function addRecord(event) {
    event.preventDefault();
    if (!newRecord.patient.trim() || !newRecord.type.trim()) return;

    const savedRecord = {
      ...newRecord,
      id: `REC-${3000 + records.length + 1}`,
      updated: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };

    setRecords([savedRecord, ...records]);
    setIsRecordModalOpen(false);
    setActiveSection('Medical Records');
    setApiStatus('Record added to current session');
  }

  async function updateAppointmentStatus(id, status) {
    const previousAppointments = appointments;
    setAppointments(appointments.map((item) => (item.id === id ? { ...item, status } : item)));

    try {
      const response = await fetch(`${API_URL}/appointments/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      });

      if (!response.ok) throw new Error('Status update failed.');

      const savedAppointment = await response.json();
      setAppointments((current) => current.map((item) => (item.id === id ? savedAppointment : item)));
      setApiStatus(`Appointment marked ${status.toLowerCase()}`);
    } catch (error) {
      setAppointments(previousAppointments);
      setApiStatus('Could not update appointment status');
    }
  }

  if (!currentUser && siteMode === 'staff') {
    return <LoginScreen onLogin={setCurrentUser} onBack={() => setSiteMode('public')} />;
  }

  if (!currentUser) {
    return <PublicSite onBookAppointment={bookPublicAppointment} onStaffPortal={() => setSiteMode('staff')} />;
  }

  return (
    <main className="app-shell">
      <aside className="sidebar" aria-label="Primary navigation">
        <div className="brand">
          <span className="brand-mark">
            <HeartPulse size={24} aria-hidden="true" />
          </span>
          <div>
            <strong>RichCare</strong>
            <span>Hospital System</span>
          </div>
        </div>

        <nav className="nav-list">
          {navItems.map((item) => {
            const Icon = item.icon;
            return (
              <button
                className={activeSection === item.label ? 'nav-item active' : 'nav-item'}
                key={item.label}
                type="button"
                onClick={() => setActiveSection(item.label)}
              >
                <Icon size={18} aria-hidden="true" />
                <span>{item.label}</span>
              </button>
            );
          })}
        </nav>

        <div className="sidebar-panel">
          <ShieldCheck size={22} aria-hidden="true" />
          <strong>Secure records</strong>
          <span>Role-based access keeps patient information coordinated and protected.</span>
          <small>{apiStatus}</small>
        </div>
      </aside>

      <section className="workspace">
        <header className="topbar">
          <button className="icon-button mobile-menu" type="button" aria-label="Open menu">
            <Menu size={20} />
          </button>
          <div className="search-field">
            <Search size={18} aria-hidden="true" />
            <input
              aria-label="Search patients, records, appointments"
              placeholder="Search patients, records, appointments"
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
            />
          </div>
          <div className="topbar-actions">
            <div className="user-pill" aria-label="Signed in user">
              <UserRoundCheck size={18} aria-hidden="true" />
              <span>{currentUser.role}</span>
            </div>
            <button className="icon-button" type="button" aria-label="Messages">
              <MessageSquareText size={20} />
            </button>
            <button className="icon-button alert" type="button" aria-label="Notifications">
              <Bell size={20} />
            </button>
            <button className="primary-action" type="button" onClick={openPatientModal}>
              <Plus size={18} aria-hidden="true" />
              New patient
            </button>
          </div>
        </header>

        <div className="mobile-tabs" aria-label="Section navigation">
          {navItems.map((item) => (
            <button
              className={activeSection === item.label ? 'mobile-tab active' : 'mobile-tab'}
              key={item.label}
              type="button"
              onClick={() => setActiveSection(item.label)}
            >
              {item.label}
            </button>
          ))}
        </div>

        {activeSection === 'Dashboard' && (
          <DashboardView
            filteredPatients={filteredPatients}
            filteredAppointments={filteredAppointments}
            records={records}
            departments={departmentLoads}
            openAppointmentModal={openAppointmentModal}
            openRecordModal={openRecordModal}
            openPatientProfile={setSelectedPatient}
            setActiveSection={setActiveSection}
          />
        )}

        {activeSection === 'Patients' && (
          <PatientsView
            filteredPatients={filteredPatients}
            openPatientModal={openPatientModal}
            openPatientProfile={setSelectedPatient}
          />
        )}

        {activeSection === 'Appointments' && (
          <AppointmentsView
            filteredAppointments={filteredAppointments}
            openAppointmentModal={openAppointmentModal}
            updateAppointmentStatus={updateAppointmentStatus}
          />
        )}

        {activeSection === 'Medical Records' && <RecordsView records={records} openRecordModal={openRecordModal} />}

        {activeSection === 'Billing' && <BillingView invoices={billingItems} />}

        {activeSection === 'Settings' && <SettingsView />}
      </section>

      {isPatientModalOpen && (
        <div className="modal-backdrop" role="presentation">
          <form className="modal" onSubmit={addPatient} aria-label="Register new patient">
            <div className="modal-header">
              <div>
                <h2>Register Patient</h2>
                <p>Create a patient profile for admission, triage, or outpatient care.</p>
              </div>
              <button className="icon-button" type="button" aria-label="Close" onClick={() => setIsPatientModalOpen(false)}>
                <X size={20} />
              </button>
            </div>

            <label>
              Full name
              <input
                required
                value={newPatient.name}
                onChange={(event) => setNewPatient({ ...newPatient, name: event.target.value })}
                placeholder="Enter patient name"
              />
            </label>

            <div className="form-grid">
              <label>
                Ward
                <select value={newPatient.ward} onChange={(event) => setNewPatient({ ...newPatient, ward: event.target.value })}>
                  <option>Outpatient</option>
                  <option>Emergency</option>
                  <option>Maternity</option>
                  <option>Pediatrics</option>
                  <option>Laboratory</option>
                </select>
              </label>
              <label>
                Status
                <select value={newPatient.risk} onChange={(event) => setNewPatient({ ...newPatient, risk: event.target.value })}>
                  <option>Stable</option>
                  <option>Moderate</option>
                  <option>Critical</option>
                </select>
              </label>
            </div>

            <label>
              Assigned doctor
              <select value={newPatient.doctor} onChange={(event) => setNewPatient({ ...newPatient, doctor: event.target.value })}>
                <option>Dr. K. Mensah</option>
                <option>Dr. N. Addo</option>
                <option>Dr. R. Cole</option>
                <option>Dr. A. Silva</option>
              </select>
            </label>

            <div className="modal-actions">
              <button className="ghost-action" type="button" onClick={() => setIsPatientModalOpen(false)}>
                Cancel
              </button>
              <button className="primary-action" type="submit">
                <UserPlus size={18} aria-hidden="true" />
                Register
              </button>
            </div>
          </form>
        </div>
      )}

      {isAppointmentModalOpen && (
        <div className="modal-backdrop" role="presentation">
          <form className="modal" onSubmit={addAppointment} aria-label="Book appointment">
            <div className="modal-header">
              <div>
                <h2>Book Appointment</h2>
                <p>Schedule a consultation, review, lab follow-up, or pharmacy counselling session.</p>
              </div>
              <button className="icon-button" type="button" aria-label="Close" onClick={() => setIsAppointmentModalOpen(false)}>
                <X size={20} />
              </button>
            </div>

            <div className="form-grid">
              <label>
                Time
                <input
                  required
                  type="time"
                  value={newAppointment.time}
                  onChange={(event) => setNewAppointment({ ...newAppointment, time: event.target.value })}
                />
              </label>
              <label>
                Status
                <select
                  value={newAppointment.status}
                  onChange={(event) => setNewAppointment({ ...newAppointment, status: event.target.value })}
                >
                  <option>Confirmed</option>
                  <option>Waiting</option>
                  <option>Checked in</option>
                  <option>Pending</option>
                </select>
              </label>
            </div>

            <label>
              Patient
              <input
                required
                value={newAppointment.patient}
                onChange={(event) => setNewAppointment({ ...newAppointment, patient: event.target.value })}
                placeholder="Enter patient name"
              />
            </label>

            <label>
              Reason
              <input
                required
                value={newAppointment.reason}
                onChange={(event) => setNewAppointment({ ...newAppointment, reason: event.target.value })}
                placeholder="Consultation reason"
              />
            </label>

            <label>
              Assigned doctor
              <select
                value={newAppointment.doctor}
                onChange={(event) => setNewAppointment({ ...newAppointment, doctor: event.target.value })}
              >
                <option>Dr. K. Mensah</option>
                <option>Dr. N. Addo</option>
                <option>Dr. R. Cole</option>
                <option>Dr. A. Silva</option>
              </select>
            </label>

            <div className="modal-actions">
              <button className="ghost-action" type="button" onClick={() => setIsAppointmentModalOpen(false)}>
                Cancel
              </button>
              <button className="primary-action" type="submit">
                <CalendarDays size={18} aria-hidden="true" />
                Book visit
              </button>
            </div>
          </form>
        </div>
      )}

      {isRecordModalOpen && (
        <div className="modal-backdrop" role="presentation">
          <form className="modal" onSubmit={addRecord} aria-label="Create medical record">
            <div className="modal-header">
              <div>
                <h2>New Medical Record</h2>
                <p>Capture clinical notes, lab updates, prescriptions, or discharge summaries.</p>
              </div>
              <button className="icon-button" type="button" aria-label="Close" onClick={() => setIsRecordModalOpen(false)}>
                <X size={20} />
              </button>
            </div>

            <label>
              Patient
              <input
                required
                value={newRecord.patient}
                onChange={(event) => setNewRecord({ ...newRecord, patient: event.target.value })}
                placeholder="Enter patient name"
              />
            </label>

            <label>
              Record type
              <input
                required
                value={newRecord.type}
                onChange={(event) => setNewRecord({ ...newRecord, type: event.target.value })}
                placeholder="Diagnosis, lab result, prescription, discharge note"
              />
            </label>

            <label>
              Owner
              <select value={newRecord.owner} onChange={(event) => setNewRecord({ ...newRecord, owner: event.target.value })}>
                <option>Dr. K. Mensah</option>
                <option>Dr. N. Addo</option>
                <option>Dr. R. Cole</option>
                <option>Dr. A. Silva</option>
                <option>Lab Unit</option>
                <option>Pharmacy</option>
              </select>
            </label>

            <label>
              Notes
              <textarea
                value={newRecord.notes}
                onChange={(event) => setNewRecord({ ...newRecord, notes: event.target.value })}
                placeholder="Clinical notes"
                rows="4"
              />
            </label>

            <div className="modal-actions">
              <button className="ghost-action" type="button" onClick={() => setIsRecordModalOpen(false)}>
                Cancel
              </button>
              <button className="primary-action" type="submit">
                <Save size={18} aria-hidden="true" />
                Save record
              </button>
            </div>
          </form>
        </div>
      )}

      {selectedPatient && (
        <PatientProfileModal
          appointments={appointments}
          invoices={billingItems}
          openAppointmentForPatient={openAppointmentForPatient}
          openRecordForPatient={openRecordForPatient}
          patient={selectedPatient}
          records={records}
          onClose={() => setSelectedPatient(null)}
        />
      )}
    </main>
  );
}

function PublicSite({ onBookAppointment, onStaffPortal }) {
  const publicNavItems = [
    { label: 'About', id: 'about' },
    { label: 'Services', id: 'services' },
    { label: 'Book', id: 'book' },
    { label: 'Contact', id: 'contact' },
  ];
  const [booking, setBooking] = useState({
    date: new Date().toISOString().slice(0, 10),
    patient: '',
    phone: '',
    email: '',
    gender: 'Prefer not to say',
    time: '09:00',
    reason: 'General consultation',
    symptoms: '',
    emergency: false,
    doctor: 'Dr. K. Mensah',
  });
  const [bookingConfirmation, setBookingConfirmation] = useState(null);
  const [activePage, setActivePage] = useState('about');

  useEffect(() => {
    function updateActivePage() {
      const current = publicNavItems.findLast((item) => {
        const section = document.getElementById(item.id);
        return section && section.getBoundingClientRect().top <= 150;
      });

      if (current) setActivePage(current.id);
    }

    updateActivePage();
    window.addEventListener('scroll', updateActivePage, { passive: true });
    return () => window.removeEventListener('scroll', updateActivePage);
  }, []);

  async function submitBooking(event) {
    event.preventDefault();
    if (!booking.patient.trim() || !booking.phone.trim() || !booking.email.trim()) return;

    const savedAppointment = await onBookAppointment(booking);
    setBookingConfirmation(savedAppointment);
    setBooking({
      ...booking,
      date: new Date().toISOString().slice(0, 10),
      emergency: false,
      email: '',
      patient: '',
      phone: '',
      symptoms: '',
    });
  }

  return (
    <main className="public-site">
      <header className="public-nav">
        <div className="brand">
          <span className="brand-mark">
            <HeartPulse size={24} aria-hidden="true" />
          </span>
          <div>
            <strong>RichCare</strong>
            <span>Hospital</span>
          </div>
        </div>
        <nav aria-label="Website navigation">
          {publicNavItems.map((item) => (
            <a
              aria-current={activePage === item.id ? 'page' : undefined}
              className={activePage === item.id ? 'active' : ''}
              href={`#${item.id}`}
              key={item.id}
              onClick={() => setActivePage(item.id)}
            >
              {item.label}
            </a>
          ))}
        </nav>
        <button className="ghost-action" type="button" onClick={onStaffPortal}>
          <LockKeyhole size={17} aria-hidden="true" />
          Staff portal
        </button>
      </header>

      <section className="public-hero">
        <div>
          <p className="eyebrow">Digital hospital care</p>
          <h1>RichCare</h1>
          <p>
            A modern hospital management platform that helps patients book care quickly while helping staff coordinate
            appointments, records, departments, billing, and service delivery.
          </p>
          <div className="quick-actions">
            <a className="public-primary" href="#book">
              <CalendarDays size={18} aria-hidden="true" />
              Book appointment
            </a>
            <a className="public-secondary" href="#about">
              <FileText size={18} aria-hidden="true" />
              Read about RichCare
            </a>
          </div>
        </div>
        <div className="hero-status public-status">
          <Activity size={26} aria-hidden="true" />
          <div>
            <strong>Open for appointments</strong>
            <span>Outpatient, emergency, lab, pharmacy, and maternity services</span>
          </div>
        </div>
      </section>

      <section className="public-section public-split" id="about">
        <div>
          <p className="eyebrow dark">About the system</p>
          <h2>One connected site for patients and hospital teams.</h2>
          <p>
            RichCare reduces delays caused by manual record keeping and fragmented departmental systems. Patients can
            request appointments online, while hospital staff use the secure portal to manage admissions, scheduling,
            clinical records, billing, and administrative coordination.
          </p>
        </div>
        <div className="about-list">
          <span><CheckCircle2 size={18} /> Faster patient registration</span>
          <span><CheckCircle2 size={18} /> Coordinated appointments</span>
          <span><CheckCircle2 size={18} /> Organized medical records</span>
          <span><CheckCircle2 size={18} /> Better department visibility</span>
        </div>
      </section>

      <section className="public-section" id="services">
        <div className="section-heading">
          <p className="eyebrow dark">Services</p>
          <h2>Care areas supported by RichCare</h2>
        </div>
        <div className="service-grid">
          {[
            { title: 'Patient Management', text: 'Registration, admissions, ward assignment, and care ownership.', icon: Users },
            { title: 'Appointments', text: 'Online requests, scheduling, queue visibility, and doctor assignment.', icon: CalendarDays },
            { title: 'Medical Records', text: 'Clinical notes, lab updates, prescriptions, and discharge summaries.', icon: FileText },
            { title: 'Administration', text: 'Department workload, billing status, staff roles, and secure access.', icon: Building2 },
          ].map((service) => {
            const Icon = service.icon;
            return (
              <article className="service-card" key={service.title}>
                <Icon size={24} aria-hidden="true" />
                <strong>{service.title}</strong>
                <span>{service.text}</span>
              </article>
            );
          })}
        </div>
      </section>

      <section className="public-section booking-section" id="book">
        <div>
          <p className="eyebrow dark">Book online</p>
          <h2>Request an appointment</h2>
          <p>
            Submit your visit request and RichCare will place it into the hospital appointment workflow for confirmation.
          </p>
        </div>
        <form className="booking-form" onSubmit={submitBooking}>
          <label>
            Full name
            <input
              required
              value={booking.patient}
              onChange={(event) => setBooking({ ...booking, patient: event.target.value })}
              placeholder="Enter your full name"
            />
          </label>
          <label>
            Phone number
            <input
              required
              value={booking.phone}
              onChange={(event) => setBooking({ ...booking, phone: event.target.value })}
              placeholder="Enter phone number"
            />
          </label>
          <label>
            Email address
            <input
              required
              type="email"
              value={booking.email}
              onChange={(event) => setBooking({ ...booking, email: event.target.value })}
              placeholder="Enter email address"
            />
          </label>
          <div className="form-grid">
            <label>
              Preferred date
              <input
                type="date"
                value={booking.date}
                onChange={(event) => setBooking({ ...booking, date: event.target.value })}
              />
            </label>
            <label>
              Preferred time
              <input
                type="time"
                value={booking.time}
                onChange={(event) => setBooking({ ...booking, time: event.target.value })}
              />
            </label>
          </div>
          <div className="form-grid">
            <label>
              Department
              <select value={booking.reason} onChange={(event) => setBooking({ ...booking, reason: event.target.value })}>
                <option>General consultation</option>
                <option>Cardiology review</option>
                <option>Laboratory follow-up</option>
                <option>Maternity care</option>
                <option>Pharmacy counselling</option>
              </select>
            </label>
            <label>
              Gender
              <select value={booking.gender} onChange={(event) => setBooking({ ...booking, gender: event.target.value })}>
                <option>Prefer not to say</option>
                <option>Female</option>
                <option>Male</option>
                <option>Other</option>
              </select>
            </label>
          </div>
          <label>
            Preferred doctor
            <select value={booking.doctor} onChange={(event) => setBooking({ ...booking, doctor: event.target.value })}>
              <option>Dr. K. Mensah</option>
              <option>Dr. N. Addo</option>
              <option>Dr. R. Cole</option>
              <option>Dr. A. Silva</option>
            </select>
          </label>
          <label>
            Symptoms or reason for visit
            <textarea
              value={booking.symptoms}
              onChange={(event) => setBooking({ ...booking, symptoms: event.target.value })}
              placeholder="Briefly describe symptoms or purpose of visit"
              rows="4"
            />
          </label>
          <label className="check-row">
            <input
              checked={booking.emergency}
              type="checkbox"
              onChange={(event) => setBooking({ ...booking, emergency: event.target.checked })}
            />
            This is urgent and should be reviewed quickly
          </label>
          <button className="primary-action booking-action" type="submit">
            <CalendarDays size={18} aria-hidden="true" />
            Submit booking
          </button>
          {bookingConfirmation && (
            <div className="booking-confirmation" role="status">
              <CheckCircle2 size={22} aria-hidden="true" />
              <div>
                <strong>Appointment request received</strong>
                <span>Reference: {bookingConfirmation.id}</span>
                <span>
                  {bookingConfirmation.patient} - {bookingConfirmation.reason} on {bookingConfirmation.date || 'today'} at{' '}
                  {bookingConfirmation.time}
                </span>
              </div>
            </div>
          )}
        </form>
      </section>

      <section className="public-section contact-band" id="contact">
        <div>
          <p className="eyebrow">Contact</p>
          <h2>Reach RichCare Hospital</h2>
        </div>
        <div className="contact-list">
          <span><Phone size={18} /> +233 20 000 0000</span>
          <span><Mail size={18} /> care@richcare.example</span>
          <span><MapPin size={18} /> RichCare Hospital, Main Road</span>
        </div>
      </section>
    </main>
  );
}

function LoginScreen({ onLogin, onBack }) {
  const roles = ['Administrator', 'Doctor', 'Nurse', 'Receptionist'];
  const [selectedRole, setSelectedRole] = useState(roles[0]);

  return (
    <main className="login-screen">
      <section className="login-hero">
        <div className="brand login-brand">
          <span className="brand-mark">
            <HeartPulse size={24} aria-hidden="true" />
          </span>
          <div>
            <strong>RichCare</strong>
            <span>Hospital Management</span>
          </div>
        </div>
        <h1>Manage care from admission to billing.</h1>
        <p>
          A web-based hospital system for patient registration, appointment scheduling, clinical records,
          and administrative coordination.
        </p>
      </section>

      <section className="login-panel" aria-label="Sign in">
        <button className="ghost-action back-action" type="button" onClick={onBack}>
          Back to website
        </button>
        <div>
          <p className="eyebrow dark">Secure access</p>
          <h2>Sign in</h2>
        </div>
        <label>
          Staff role
          <select value={selectedRole} onChange={(event) => setSelectedRole(event.target.value)}>
            {roles.map((role) => (
              <option key={role}>{role}</option>
            ))}
          </select>
        </label>
        <label>
          Staff ID
          <input defaultValue="RC-STAFF-001" />
        </label>
        <label>
          Password
          <input defaultValue="richcare" type="password" />
        </label>
        <button className="primary-action login-action" type="button" onClick={() => onLogin({ role: selectedRole })}>
          <LogIn size={18} aria-hidden="true" />
          Enter dashboard
        </button>
      </section>
    </main>
  );
}

function DashboardView({
  filteredPatients,
  filteredAppointments,
  records,
  departments,
  openAppointmentModal,
  openRecordModal,
  openPatientProfile,
  setActiveSection,
}) {
  return (
    <>
        <section className="hero-band">
          <div>
            <p className="eyebrow">Hospital operations command center</p>
            <h1>RichCare</h1>
            <p className="hero-copy">
              One web-based system for patient management, appointment scheduling, medical records, and administrative coordination.
            </p>
            <div className="quick-actions">
              <button className="light-action" type="button" onClick={openAppointmentModal}>
                <CalendarDays size={18} aria-hidden="true" />
                Book appointment
              </button>
              <button className="light-action" type="button" onClick={openRecordModal}>
                <FileText size={18} aria-hidden="true" />
                New record
              </button>
            </div>
          </div>
          <div className="hero-status" aria-label="Current hospital status">
            <Activity size={26} aria-hidden="true" />
            <div>
              <strong>Service flow healthy</strong>
              <span>Average wait time: 18 minutes</span>
            </div>
          </div>
        </section>

        <section className="stats-grid" aria-label="Hospital summary">
          {stats.map((stat, index) => {
            const Icon = stat.icon;
            const value = index === 0 ? String(filteredPatients.length + 124) : stat.value;
            return (
              <article className="stat-card" key={stat.label}>
                <span className="stat-icon">
                  <Icon size={20} aria-hidden="true" />
                </span>
                <span className="stat-label">{stat.label}</span>
                <strong>{value}</strong>
                <small>{stat.change}</small>
              </article>
            );
          })}
        </section>

        <section className="content-grid">
          <article className="panel schedule-panel">
            <div className="panel-header">
              <div>
                <h2>Today's Appointments</h2>
                <p>Live queue across outpatient, laboratory, and clinical teams.</p>
              </div>
              <button className="ghost-action" type="button" onClick={() => setActiveSection('Appointments')}>
                <CalendarDays size={17} aria-hidden="true" />
                View calendar
              </button>
            </div>
            <div className="appointment-list">
              {filteredAppointments.map((item) => (
                <div className="appointment-row" key={`${item.time}-${item.patient}`}>
                  <span className="time">{item.time}</span>
                  <div>
                    <strong>{item.patient}</strong>
                    <span>{item.reason}</span>
                  </div>
                  <span className={`badge ${item.status.toLowerCase().replaceAll(' ', '-')}`}>{item.status}</span>
                </div>
              ))}
            </div>
          </article>

          <article className="panel records-panel">
            <div className="panel-header compact">
              <div>
                <h2>Medical Records</h2>
                <p>Recent clinical updates.</p>
              </div>
            </div>
            <div className="record-summary">
              <FileText size={30} aria-hidden="true" />
              <strong>{records.length} records updated</strong>
              <span>Vitals, prescriptions, lab results, and discharge notes synchronized today.</span>
            </div>
            <button className="wide-action" type="button">
              <ClipboardList size={18} aria-hidden="true" />
              Open records
            </button>
          </article>

          <article className="panel patients-panel">
            <div className="panel-header">
              <div>
                <h2>Patient Management</h2>
                <p>Admissions and care ownership by department.</p>
              </div>
              <button className="ghost-action" type="button" onClick={() => setActiveSection('Patients')}>
                <Users size={17} aria-hidden="true" />
                All patients
              </button>
            </div>
            <PatientTable patients={filteredPatients.slice(0, 4)} openPatientProfile={openPatientProfile} />
          </article>

          <article className="panel admin-panel">
            <div className="panel-header compact">
              <div>
                <h2>Administration</h2>
                <p>Department workload.</p>
              </div>
            </div>
            <div className="department-list">
              {departments.map((department) => (
                <div className="department-row" key={department.name}>
                  <div>
                    <strong>{department.name}</strong>
                    <span>{department.load}% capacity</span>
                  </div>
                  <div className="meter" aria-hidden="true">
                    <span style={{ width: `${department.load}%` }} />
                  </div>
                </div>
              ))}
            </div>
            <div className="admin-note">
              <Clock3 size={18} aria-hidden="true" />
              <span>Shift handover due in 42 minutes</span>
              <CheckCircle2 size={18} aria-hidden="true" />
            </div>
          </article>
        </section>
      </>
  );
}

function PatientsView({ filteredPatients, openPatientModal, openPatientProfile }) {
  return (
    <section className="single-view">
      <div className="view-header">
        <div>
          <p className="eyebrow dark">Patient registry</p>
          <h1>Patients</h1>
          <p>Manage admissions, outpatient visits, care ownership, and patient status from one list.</p>
        </div>
        <button className="primary-action" type="button" onClick={openPatientModal}>
          <UserPlus size={18} aria-hidden="true" />
          Add patient
        </button>
      </div>
      <article className="panel">
        <div className="panel-header">
          <div>
            <h2>Patient Management</h2>
            <p>{filteredPatients.length} matching patient records.</p>
          </div>
          <button className="ghost-action" type="button">
            <Filter size={17} aria-hidden="true" />
            Filter
          </button>
        </div>
        <PatientTable patients={filteredPatients} openPatientProfile={openPatientProfile} />
      </article>
    </section>
  );
}

function AppointmentsView({ filteredAppointments, openAppointmentModal, updateAppointmentStatus }) {
  const statusActions = ['Confirmed', 'Checked in', 'Completed', 'Cancelled'];

  return (
    <section className="single-view">
      <div className="view-header">
        <div>
          <p className="eyebrow dark">Scheduling desk</p>
          <h1>Appointments</h1>
          <p>Coordinate consultations, reviews, lab follow-ups, and pharmacy counselling.</p>
        </div>
        <button className="primary-action" type="button" onClick={openAppointmentModal}>
          <Plus size={18} aria-hidden="true" />
          Book visit
        </button>
      </div>
      <article className="panel">
        <div className="appointment-list expanded">
          {filteredAppointments.map((item) => (
            <div className="appointment-row appointment-row-detailed" key={item.id ?? `${item.time}-${item.patient}`}>
              <span className="time">
                {item.date && <small>{item.date}</small>}
                {item.time}
              </span>
              <div className="appointment-detail">
                <strong>{item.patient}</strong>
                <span>{item.reason} with {item.doctor}</span>
                {(item.phone || item.email || item.symptoms) && (
                  <small>
                    {[item.phone, item.email, item.symptoms].filter(Boolean).join(' - ')}
                  </small>
                )}
              </div>
              <div className="appointment-controls">
                {item.emergency && <span className="badge urgent">Urgent</span>}
                <span className={`badge ${item.status.toLowerCase().replaceAll(' ', '-')}`}>{item.status}</span>
                <div className="status-actions" aria-label={`Actions for ${item.patient}`}>
                  {statusActions.map((status) => (
                    <button
                      disabled={item.status === status}
                      key={status}
                      type="button"
                      onClick={() => updateAppointmentStatus(item.id, status)}
                    >
                      {status}
                    </button>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      </article>
    </section>
  );
}

function RecordsView({ records, openRecordModal }) {
  return (
    <section className="single-view">
      <div className="view-header">
        <div>
          <p className="eyebrow dark">Clinical documentation</p>
          <h1>Medical Records</h1>
          <p>Review patient notes, lab results, prescriptions, and discharge documents.</p>
        </div>
        <button className="primary-action" type="button" onClick={openRecordModal}>
          <FileText size={18} aria-hidden="true" />
          New record
        </button>
      </div>
      <article className="panel">
        <div className="record-list">
          {records.map((record) => (
            <div className="record-row" key={`${record.patient}-${record.type}`}>
              <FileText size={20} aria-hidden="true" />
              <div>
                <strong>{record.patient}</strong>
                <span>{record.type}</span>
              </div>
              <span>{record.owner}</span>
              <small>{record.updated}</small>
            </div>
          ))}
        </div>
      </article>
    </section>
  );
}

function BillingView({ invoices }) {
  return (
    <section className="single-view">
      <div className="view-header">
        <div>
          <p className="eyebrow dark">Finance office</p>
          <h1>Billing</h1>
          <p>Track invoices, payments, pending balances, and service charges.</p>
        </div>
        <button className="primary-action" type="button">
          <Download size={18} aria-hidden="true" />
          Export
        </button>
      </div>
      <article className="panel">
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Patient</th>
                <th>Service</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {invoices.map((invoice) => (
                <tr key={invoice.id}>
                  <td>{invoice.id}</td>
                  <td>{invoice.patient}</td>
                  <td>{invoice.item}</td>
                  <td>{invoice.amount}</td>
                  <td>
                    <span className={`risk ${invoice.status === 'Paid' ? 'stable' : 'moderate'}`}>{invoice.status}</span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </article>
    </section>
  );
}

function PatientProfileModal({
  appointments,
  invoices,
  onClose,
  openAppointmentForPatient,
  openRecordForPatient,
  patient,
  records,
}) {
  const patientAppointments = appointments.filter((item) => item.patient === patient.name);
  const patientRecords = records.filter((item) => item.patient === patient.name);
  const patientInvoices = invoices.filter((item) => item.patient === patient.name);
  const openBalance = patientInvoices.filter((item) => item.status !== 'Paid').length;

  return (
    <div className="modal-backdrop" role="presentation">
      <section className="profile-modal" aria-label={`${patient.name} profile`}>
        <div className="profile-header">
          <div>
            <p className="eyebrow dark">Patient profile</p>
            <h2>{patient.name}</h2>
            <span>{patient.id}</span>
          </div>
          <button className="icon-button" type="button" aria-label="Close" onClick={onClose}>
            <X size={20} />
          </button>
        </div>

        <div className="profile-summary">
          <div>
            <span>Ward</span>
            <strong>{patient.ward}</strong>
          </div>
          <div>
            <span>Status</span>
            <strong>{patient.risk}</strong>
          </div>
          <div>
            <span>Doctor</span>
            <strong>{patient.doctor}</strong>
          </div>
          <div>
            <span>Open bills</span>
            <strong>{openBalance}</strong>
          </div>
        </div>

        <div className="profile-actions">
          <button className="primary-action" type="button" onClick={() => openAppointmentForPatient(patient)}>
            <CalendarDays size={18} aria-hidden="true" />
            Book visit
          </button>
          <button className="ghost-action" type="button" onClick={() => openRecordForPatient(patient)}>
            <FileText size={18} aria-hidden="true" />
            Add record
          </button>
        </div>

        <div className="profile-grid">
          <article>
            <h3>Appointments</h3>
            <div className="mini-list">
              {patientAppointments.map((item) => (
                <div key={item.id ?? `${item.time}-${item.reason}`}>
                  <strong>{item.reason}</strong>
                  <span>{item.date || 'Today'} at {item.time} - {item.status}</span>
                </div>
              ))}
              {patientAppointments.length === 0 && <span className="muted-line">No appointments yet.</span>}
            </div>
          </article>

          <article>
            <h3>Medical Records</h3>
            <div className="mini-list">
              {patientRecords.map((item) => (
                <div key={item.id ?? `${item.updated}-${item.type}`}>
                  <strong>{item.type}</strong>
                  <span>{item.owner} - {item.updated}</span>
                </div>
              ))}
              {patientRecords.length === 0 && <span className="muted-line">No records yet.</span>}
            </div>
          </article>

          <article>
            <h3>Billing</h3>
            <div className="mini-list">
              {patientInvoices.map((item) => (
                <div key={item.id}>
                  <strong>{item.item}</strong>
                  <span>{item.amount} - {item.status}</span>
                </div>
              ))}
              {patientInvoices.length === 0 && <span className="muted-line">No invoices yet.</span>}
            </div>
          </article>
        </div>
      </section>
    </div>
  );
}

function SettingsView() {
  return (
    <section className="single-view">
      <div className="view-header">
        <div>
          <p className="eyebrow dark">System controls</p>
          <h1>Settings</h1>
          <p>Configure departments, staff roles, notification rules, and record access.</p>
        </div>
      </div>
      <section className="settings-grid">
        <article className="panel setting-item">
          <LockKeyhole size={24} aria-hidden="true" />
          <strong>Role permissions</strong>
          <span>Doctors, nurses, lab staff, pharmacists, and administrators use role-based access.</span>
        </article>
        <article className="panel setting-item">
          <Bell size={24} aria-hidden="true" />
          <strong>Alerts</strong>
          <span>Queue changes, critical patients, unpaid invoices, and shift handovers can trigger alerts.</span>
        </article>
        <article className="panel setting-item">
          <ShieldCheck size={24} aria-hidden="true" />
          <strong>Audit trail</strong>
          <span>Every sensitive patient record action is logged for accountability.</span>
        </article>
      </section>
    </section>
  );
}

function PatientTable({ patients, openPatientProfile }) {
  return (
    <div className="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Patient</th>
            <th>Ward</th>
            <th>Status</th>
            <th>Doctor</th>
          </tr>
        </thead>
        <tbody>
          {patients.map((patient) => (
            <tr
              className={openPatientProfile ? 'clickable-row' : ''}
              key={patient.id}
              onClick={() => openPatientProfile?.(patient)}
            >
              <td>
                <strong>{patient.name}</strong>
                <span>{patient.id}</span>
              </td>
              <td>{patient.ward}</td>
              <td>
                <span className={`risk ${patient.risk.toLowerCase()}`}>{patient.risk}</span>
              </td>
              <td>{patient.doctor}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {patients.length === 0 && <p className="empty-state">No matching patient records found.</p>}
    </div>
  );
}

createRoot(document.getElementById('root')).render(<App />);
