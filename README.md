# APICUR TSS - School Management System

A comprehensive web-based School Management System designed for APICUR Technical Secondary School.

## Features

### Role-Based Access Control
The system supports multiple user roles with specific functionalities:

#### 1. **Admin**
- Complete system administration
- User management (create, edit, delete users)
- Manage classes, subjects, and academic structure
- View system-wide reports and analytics
- Activity logs monitoring
- System configuration

#### 2. **Secretary**
- Student registration and management
- Upload and manage meeting documents
- Administrative documentation
- Student records maintenance
- Class assignment

#### 3. **Teacher**
- View assigned classes and students
- Mark student attendance
- Enter marks (Formative, Continuous Assessment, Exam)
- Generate student report forms per term
- Upload pedagogical documents
- View timetable assigned by DOS

#### 4. **DOS (Director of Studies)**
- Create and manage timetables
- Assign teachers to subjects and classes
- Monitor teacher performance
- View all teacher reports and activities
- Academic performance oversight

#### 5. **Head Teacher**
- Overview of entire school operations
- Review teacher and student performance
- Access to all reports
- Discipline case reviews
- Staff management oversight
- Final approval on report cards

#### 6. **Accountant**
- Fee management
- Record payments
- Generate receipts
- Financial reports
- Student account statements
- Track outstanding payments

#### 7. **Discipline Officer**
- Record discipline incidents
- Manage student behavior records
- Track discipline cases
- Generate discipline reports
- Parent notification system

## Technology Stack

- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP)

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

### Setup Instructions

1. **Clone/Copy the project**
   ```
   Copy the SchoolManagementSystem folder to:
   C:\xampp\htdocs\SchoolManagementSystem
   ```

2. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `school_management`
   - Import the SQL file:
     - Click on the `school_management` database
     - Go to "Import" tab
     - Select file: `database/school_management.sql`
     - Click "Go"

3. **Configuration**
   - Database credentials are in: `config/database.php`
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: school_management

4. **Start XAMPP**
   - Start Apache
   - Start MySQL

5. **Access the System**
   - Homepage: `http://localhost/SchoolManagementSystem/public/index.html`
   - Login: `http://localhost/SchoolManagementSystem/auth/login.php`
   - Register: `http://localhost/SchoolManagementSystem/auth/register.php`

## Default Credentials

```
Username: admin
Password: admin123
Role: Admin
```

**⚠️ IMPORTANT:** Change the default admin password immediately after first login!

## Directory Structure

```
SchoolManagementSystem/
├── admin/              # Admin dashboard and functionalities
├── secretary/          # Secretary dashboard and functionalities
├── teacher/            # Teacher dashboard and functionalities
├── dos/                # DOS dashboard and functionalities
├── head_teacher/       # Head Teacher dashboard and functionalities
├── accountant/         # Accountant dashboard and functionalities
├── discipline/         # Discipline Officer dashboard and functionalities
├── auth/               # Authentication (login, register, logout)
├── config/             # Configuration files
│   ├── config.php      # General configuration
│   └── database.php    # Database connection
├── database/           # Database SQL files
├── includes/           # Shared components (header, footer)
├── public/             # Public pages (homepage, about, contact)
└── uploads/            # File uploads directory
```

## Database Schema

### Main Tables
- **users**: System users with role-based access
- **students**: Student information
- **classes**: Class/grade information
- **subjects**: Subject catalog
- **teacher_subjects**: Teacher-subject-class assignments
- **attendance**: Daily attendance records
- **marks**: Student assessment marks
- **report_cards**: Generated report cards
- **timetable**: Class schedules
- **documents**: Uploaded documents
- **discipline_records**: Discipline incidents
- **financial_records**: Fee and payment records
- **announcements**: School announcements
- **activity_logs**: System activity tracking

## Key Features Details

### Student Management
- Complete student registration
- Photo upload
- Parent/guardian information
- Medical information
- Class assignment
- Status tracking (active, graduated, transferred, dropped)

### Attendance System
- Daily attendance marking
- Status types: Present, Absent, Late, Excused
- Attendance reports by class, student, date range
- Teacher accountability tracking

### Marks Entry System
- **Formative Assessment**: Continuous classroom assessments
- **Continuous Assessment**: Regular tests and quizzes
- **Exam Marks**: Term exams and final examinations
- Multiple assessment entries per term
- Automatic grade calculation

### Report Card Generation
- Automated calculation of totals and averages
- Grade assignment based on marks
- Class position calculation
- Teacher comments section
- Head teacher comments section
- Printable PDF format

### Timetable Management
- Day-wise scheduling
- Room allocation
- Teacher availability check
- Conflict detection
- Print-friendly format

### Document Management
- Upload various document types
- Category-based organization
- Role-based access control
- Download and preview functionality

### Financial Management
- Fee structure setup
- Payment recording
- Receipt generation
- Outstanding balance tracking
- Payment history
- Financial reports

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Session management
- Role-based access control
- Activity logging
- CSRF protection (to be implemented)

## Browser Compatibility

- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari
- Opera

## Support and Maintenance

For issues, questions, or feature requests, contact the system administrator.

## Version

Version 1.0 - Initial Release
Date: 2024

## License

Proprietary - APICUR Technical Secondary School

---

**© 2024 APICUR TSS. All rights reserved.**