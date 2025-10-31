mediflow_system/
1. config/
   1a. db.php
2. css/
   2a. style.css
3. js/
   3a. script.js
   3b. called_patient.js
4. index.php
5. login.php
6. logout.php
7. register.php
8. book_appointment.php
9. call_patient.php
10. called_patient.php
11. called_patient_data.php
12. delete_user.php
13. complete_appointment.php
14. refer_patient.php
15. export_data.php
16. admin_dashboard.php
17. doctor_dashboard.php
18. patient_dashboard.php

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

