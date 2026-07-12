import cors from 'cors';
import express from 'express';
import { access, mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dbDir = path.join(__dirname, '..', 'data');
const dbPath = path.join(dbDir, 'db.json');
const port = Number(process.env.PORT || 4000);

const seedData = {
  patients: [
    { id: 'RC-20491', name: 'Sarah Johnson', ward: 'Maternity', risk: 'Stable', doctor: 'Dr. N. Addo' },
    { id: 'RC-20485', name: 'Peter Coleman', ward: 'Emergency', risk: 'Critical', doctor: 'Dr. R. Cole' },
    { id: 'RC-20470', name: 'Mina Arthur', ward: 'Pediatrics', risk: 'Moderate', doctor: 'Dr. A. Silva' },
    { id: 'RC-20451', name: 'Felix Brown', ward: 'Outpatient', risk: 'Stable', doctor: 'Dr. K. Mensah' }
  ],
  appointments: [
    { id: 'APT-1001', time: '08:30', patient: 'Amara Okafor', reason: 'Cardiology review', status: 'Checked in', doctor: 'Dr. K. Mensah' },
    { id: 'APT-1002', time: '09:10', patient: 'Daniel Mensah', reason: 'Lab follow-up', status: 'Waiting', doctor: 'Dr. R. Cole' },
    { id: 'APT-1003', time: '10:00', patient: 'Grace Bello', reason: 'General consultation', status: 'Confirmed', doctor: 'Dr. A. Silva' },
    { id: 'APT-1004', time: '11:20', patient: 'Kwame Essien', reason: 'Pharmacy counselling', status: 'Pending', doctor: 'Dr. N. Addo' }
  ],
  records: [
    { id: 'REC-3001', patient: 'Sarah Johnson', type: 'Antenatal review', updated: '08:42', owner: 'Dr. N. Addo' },
    { id: 'REC-3002', patient: 'Peter Coleman', type: 'Emergency triage note', updated: '09:18', owner: 'Dr. R. Cole' },
    { id: 'REC-3003', patient: 'Mina Arthur', type: 'Lab result attached', updated: '10:05', owner: 'Lab Unit' },
    { id: 'REC-3004', patient: 'Felix Brown', type: 'Prescription issued', updated: '11:16', owner: 'Pharmacy' }
  ],
  invoices: [
    { id: 'INV-7401', patient: 'Amara Okafor', item: 'Cardiology consultation', amount: 'GHS 420', status: 'Paid' },
    { id: 'INV-7398', patient: 'Daniel Mensah', item: 'Laboratory panel', amount: 'GHS 260', status: 'Pending' },
    { id: 'INV-7394', patient: 'Grace Bello', item: 'General consultation', amount: 'GHS 180', status: 'Paid' },
    { id: 'INV-7389', patient: 'Kwame Essien', item: 'Medication pack', amount: 'GHS 145', status: 'Pending' }
  ],
  departments: [
    { name: 'Emergency', load: 86 },
    { name: 'Outpatient', load: 64 },
    { name: 'Laboratory', load: 53 },
    { name: 'Pharmacy', load: 42 }
  ]
};

async function ensureDatabase() {
  await mkdir(dbDir, { recursive: true });
  try {
    await access(dbPath);
  } catch {
    await writeFile(dbPath, JSON.stringify(seedData, null, 2));
  }
}

async function readDatabase() {
  await ensureDatabase();
  const raw = await readFile(dbPath, 'utf8');
  return JSON.parse(raw);
}

async function writeDatabase(data) {
  await writeFile(dbPath, JSON.stringify(data, null, 2));
}

function createId(prefix, existingIds) {
  const max = existingIds.reduce((highest, id) => {
    const value = Number(String(id).replace(`${prefix}-`, ''));
    return Number.isFinite(value) ? Math.max(highest, value) : highest;
  }, 0);
  return `${prefix}-${max + 1}`;
}

const app = express();

app.use(cors());
app.use(express.json());

app.get('/api/health', (request, response) => {
  response.json({ status: 'ok', service: 'RichCare API' });
});

app.get('/api/dashboard', async (request, response) => {
  const db = await readDatabase();
  response.json(db);
});

app.get('/api/patients', async (request, response) => {
  const db = await readDatabase();
  response.json(db.patients);
});

app.post('/api/patients', async (request, response) => {
  const { name, ward, risk, doctor } = request.body;

  if (!name || !ward || !risk || !doctor) {
    response.status(400).json({ message: 'Name, ward, status, and doctor are required.' });
    return;
  }

  const db = await readDatabase();
  const patient = {
    id: createId('RC', db.patients.map((item) => item.id)),
    name: String(name).trim(),
    ward,
    risk,
    doctor
  };

  db.patients.unshift(patient);
  await writeDatabase(db);
  response.status(201).json(patient);
});

app.get('/api/appointments', async (request, response) => {
  const db = await readDatabase();
  response.json(db.appointments);
});

app.post('/api/appointments', async (request, response) => {
  const { date, email, emergency, gender, phone, symptoms, time, patient, reason, status, doctor } = request.body;

  if (!time || !patient || !reason || !status || !doctor) {
    response.status(400).json({ message: 'Time, patient, reason, status, and doctor are required.' });
    return;
  }

  const db = await readDatabase();
  const appointment = {
    id: createId('APT', db.appointments.map((item) => item.id)),
    date,
    email,
    emergency: Boolean(emergency),
    gender,
    phone,
    time,
    patient,
    reason,
    symptoms,
    status,
    doctor
  };

  db.appointments.push(appointment);
  await writeDatabase(db);
  response.status(201).json(appointment);
});

app.patch('/api/appointments/:id', async (request, response) => {
  const db = await readDatabase();
  const appointment = db.appointments.find((item) => item.id === request.params.id);

  if (!appointment) {
    response.status(404).json({ message: 'Appointment not found.' });
    return;
  }

  Object.assign(appointment, request.body);
  await writeDatabase(db);
  response.json(appointment);
});

app.listen(port, () => {
  console.log(`RichCare API running on http://127.0.0.1:${port}`);
});
