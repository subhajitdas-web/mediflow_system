mediflow_system/
├── config/
│   └── db.php
├── css/
│   └── style.css
├── js/
│   └── script.js
│   └── called_patient.js
├── index.php
├── login.php
├── logout.php
├── register.php
├── book_appointment.php
├── call_patient.php
├── called_patient.php
├── called_patient_data.php
├── delete_user.php
├── complete_appointment.php
├── refer_patient.php
├── export_data.php
├── admin_dashboard.php
├── doctor_dashboard.php
├── patient_dashboard.php

****The Mediflow System is a web-based hospital management application designed to streamline hospital operations by managing user registrations, appointments, patient queues, and administrative tasks. 
	Automate appointment booking and management.
	Provide role-based access control for secure operations.
	Enable real-time updates for called patients via polling.
    Generate reports for admins on users and appointments



Test the Application

Access http://localhost/mediflow_system/.
Register:

Admin: username: admin1, role: admin, full_name: Admin User, email: admin@example.com
Doctor: username: doc1, role: doctor, full_name: Dr. John, specialty: Cardiology, experience: 5
Patient: username: patient1, role: patient, full_name: Jane Doe, age: 30, gender: female, phone: 1234567890


Login:

Use username: patient1, password: your_password to test login.
On success, you should redirect to index.php with a welcome message.


Dashboards:

Admin: View users, appointments, and reports (total appointments, user counts).
Doctor: See pending/called appointments, call patients, or mark as completed.
Patient: Book appointments (generates a token), view appointments with token and status.


Patient Calling: Doctors can click "Call Patient" to set status to 'called', then "Complete".
Logout: Clears session and redirects to the homepage.
