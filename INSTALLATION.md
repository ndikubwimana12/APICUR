# Installation Guide - APICUR TSS School Management System

## Quick Start Guide

### Step 1: Database Setup

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" for Apache
   - Click "Start" for MySQL

2. **Create Database**
   - Open your browser and go to: `http://localhost/phpmyadmin`
   - Click "New" in the left sidebar
   - Database name: `school_management`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Database Structure**
   - Click on the `school_management` database you just created
   - Click on the "Import" tab at the top
   - Click "Choose File"
   - Navigate to: `C:\xampp\htdocs\SchoolManagementSystem\database\school_management.sql`
   - Click "Go" at the bottom
   - Wait for success message

### Step 2: Verify File Structure

Make sure all files are in the correct location:
```
C:\xampp\htdocs\SchoolManagementSystem\
```

### Step 3: Create Uploads Directory

1. Navigate to: `C:\xampp\htdocs\SchoolManagementSystem\`
2. Create a new folder named: `uploads`
3. Inside `uploads`, create these subfolders:
   - `documents`
   - `students`
   - `profiles`

### Step 4: Access the System

1. **Homepage (Public)**
   ```
   http://localhost/SchoolManagementSystem/public/index.html
   ```

2. **Login Page**
   ```
   http://localhost/SchoolManagementSystem/auth/login.php
   ```

3. **Registration Page**
   ```
   http://localhost/SchoolManagementSystem/auth/register.php
   ```

### Step 5: Login with Default Admin Account

```
Username: admin
Email: admin@apicurtss.edu
Password: admin123
```

### Step 6: Register Your Account

1. Go to the registration page
2. Fill in your details:
   - Full Name
   - Username
   - Email
   - Phone Number
   - Select Your Role (Secretary, Teacher, DOS, etc.)
   - Password (minimum 6 characters)
   - Confirm Password
3. Click "Create Account"
4. After successful registration, login with your credentials

## Testing the System

### Test as Admin
1. Login with admin credentials
2. Navigate to User Management
3. View system statistics

### Test as Secretary
1. Register a new account with role "Secretary"
2. Login with your secretary account
3. Try to register a new student
4. Upload a meeting document

### Test as Teacher
1. Register a new account with role "Teacher"
2. Login with your teacher account
3. View your assigned classes (will be empty initially)
4. Note: DOS needs to assign you to classes first

### Test as DOS
1. Register a new account with role "DOS"
2. Login with your DOS account
3. Create a timetable
4. Assign teachers to classes

## Common Issues and Solutions

### Issue 1: "Connection error: SQLSTATE[HY000] [1045] Access denied"
**Solution:** Check database credentials in `config/database.php`
```php
private $username = "root";
private $password = "";  // Usually empty in XAMPP
```

### Issue 2: "404 Not Found" errors
**Solution:** 
- Make sure Apache is running in XAMPP
- Check that files are in: `C:\xampp\htdocs\SchoolManagementSystem\`
- Verify the URL path is correct

### Issue 3: "Table doesn't exist" errors
**Solution:** 
- Re-import the database SQL file
- Make sure you imported into the correct database

### Issue 4: Upload fails
**Solution:**
- Create the `uploads` folder if it doesn't exist
- Check folder permissions (should be writable)

### Issue 5: Session errors
**Solution:**
- Clear browser cookies and cache
- Check that PHP sessions are enabled in XAMPP

## Next Steps After Installation

### As Admin:
1. Change default admin password
2. Create user accounts for staff
3. Set up classes for current academic year
4. Add/verify subjects
5. Configure system settings

### As Secretary:
1. Start registering students
2. Assign students to classes
3. Upload student photos
4. Enter parent contact information

### As DOS:
1. Assign teachers to subjects and classes
2. Create timetables for each class
3. Set up academic calendar

### As Teacher:
1. View your assigned classes
2. Familiarize yourself with the marks entry system
3. Upload pedagogical documents

### As Accountant:
1. Set up fee structure
2. Begin recording student fees
3. Generate initial financial reports

### As Discipline Officer:
1. Review the discipline tracking system
2. Set up incident categories if needed
3. Begin recording any pending cases

## Configuration Options

### Customize in `config/config.php`:

```php
// Site Settings
define('SITE_NAME', 'APICUR TSS');
define('SITE_TITLE', 'APICUR TSS - School Management System');

// Current Academic Year
define('CURRENT_ACADEMIC_YEAR', '2024');
define('CURRENT_TERM', '1');

// File Upload Limits
define('MAX_FILE_SIZE', 5242880); // 5MB

// Records Per Page
define('RECORDS_PER_PAGE', 20);
```

## Security Recommendations

1. **Change Default Password**
   - Login as admin
   - Go to profile settings
   - Update password immediately

2. **Backup Database Regularly**
   - Use phpMyAdmin to export database
   - Store backups in a secure location
   - Schedule weekly backups

3. **Update PHP Configuration**
   - In production, set `display_errors = 0` in `config/config.php`
   - Enable HTTPS if hosting online

4. **User Management**
   - Only create accounts for verified staff
   - Regularly review user access
   - Disable inactive accounts

## Getting Help

If you encounter issues:
1. Check the error logs in XAMPP: `xampp/apache/logs/error.log`
2. Enable error reporting in `config/config.php`
3. Check browser console for JavaScript errors
4. Review database connection settings

## System Requirements

- **Web Server**: Apache 2.4+
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Browser**: Modern browser (Chrome, Firefox, Edge, Safari)
- **Disk Space**: Minimum 500MB free space
- **RAM**: Minimum 2GB (4GB recommended)

## Congratulations!

Your School Management System is now installed and ready to use! 🎉

Start by logging in and exploring the different dashboards based on your role.

---

**Need Help?** Contact your system administrator or refer to the README.md file for detailed feature documentation.