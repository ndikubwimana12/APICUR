# 🚀 School Management System - Setup & User Guide

## 📋 Table of Contents
1. [Initial Setup](#initial-setup)
2. [Navigation Guide](#navigation-guide)
3. [User Workflows](#user-workflows)
4. [Troubleshooting](#troubleshooting)
5. [Quick Links](#quick-links)

---

## 🔧 Initial Setup

### Step 1: Import Database Extensions
Visit this URL in your browser:
```
http://localhost/SchoolManagementSystem/database/import_extensions.php
```

✅ You should see a green success message confirming:
- 5 new tables created
- 10 sample modules loaded
- Upload directory created

### Step 2: Verify Database Tables
In phpMyAdmin, check that these tables exist:
- `applications` - Student applications
- `modules` - Training modules
- `module_teachers` - Teacher assignments
- `timetable_slots` - Generated schedules
- `module_marks` - Module grades

---

## 🗺️ Navigation Guide

### System Structure

```
┌─ Public Portal
│  ├─ /public/index.html                    (Homepage)
│  ├─ /public/apply.php                     (NEW: Student Applications)
│  └─ /public/about.html, programs.html, etc
│
├─ Authentication
│  ├─ /auth/login.php                       (Login page)
│  ├─ /auth/register.php                    (Registration)
│  └─ /auth/logout.php                      (Logout)
│
├─ Admin Dashboard (/admin/)
│  ├─ /admin/dashboard.php                  (Overview + stats)
│  ├─ /admin/users.php                      (Manage users)
│  ├─ /admin/students.php                   (Manage students)
│  ├─ /admin/classes.php                    (Manage classes)
│  ├─ /admin/subjects.php                   (Manage subjects)
│  ├─ /dos/modules.php                      (NEW: Training modules)
│  ├─ /admin/reports.php
│  ├─ /admin/settings.php
│  └─ /admin/activity_logs.php
│
├─ Secretary Dashboard (/secretary/)
│  ├─ /secretary/dashboard.php              (Overview + pending apps)
│  ├─ /secretary/applications.php           (NEW: Review & admit applications)
│  ├─ /secretary/students.php               (Manage enrolled students)
│  ├─ /secretary/register_student.php       (Manual registration)
│  ├─ /secretary/student_details.php        (View student info)
│  ├─ /secretary/edit_student.php           (Edit student)
│  ├─ /secretary/documents.php              (Manage documents)
│  ├─ /secretary/meetings.php               (Manage meetings)
│  └─ /secretary/reports.php
│
├─ Teacher Dashboard (/teacher/)
│  ├─ /teacher/dashboard.php                (Overview + modules)
│  ├─ /teacher/my_classes.php               (View assigned modules)
│  ├─ /teacher/students.php                 (View students in module)
│  ├─ /teacher/attendance.php               (Mark attendance)
│  ├─ /teacher/marks.php                    (Enter module marks)
│  ├─ /teacher/reports.php                  (Generate reports)
│  ├─ /teacher/timetable.php                (View schedule)
│  └─ /teacher/documents.php                (Upload documents)
│
├─ DOS Dashboard (/dos/)
│  ├─ /dos/dashboard.php                    (Overview)
│  ├─ /dos/modules.php                      (NEW: Manage modules & assignments)
│  ├─ /dos/generate_timetable.php           (NEW: Auto-generate timetables)
│  ├─ /dos/classes.php                      (Manage classes)
│  ├─ /dos/teachers.php                     (Manage teachers)
│  ├─ /dos/assign_teacher.php               (Teacher assignments)
│  ├─ /dos/timetable.php                    (View timetables)
│  ├─ /dos/teacher_assignments.php
│  ├─ /dos/teacher_details.php
│  └─ /dos/performance.php
│
├─ Head Teacher Dashboard (/head_teacher/)
│  └─ /head_teacher/dashboard.php
│
├─ Discipline Officer Dashboard (/discipline/)
│  └─ /discipline/dashboard.php
│
└─ Accountant Dashboard (/accountant/)
   └─ /accountant/dashboard.php
```

---

## 👥 User Workflows

### 🎓 STUDENT WORKFLOW

#### 1. **Apply Online** (No account needed)
```
Visit: http://localhost/SchoolManagementSystem/public/apply.php

Steps:
1. Click "Apply Now" button
2. Fill in personal information:
   - Full name, DOB, gender, contact
   - Email, phone number
   - ID number or passport
3. Add parent/guardian details
4. Select training level (3, 4, or 5)
5. Select desired module (auto-filtered by level)
6. Upload documents:
   - Result slip
   - Qualification certificate
7. Submit application
8. Receive Application Number (e.g., APP-2024-00015)

Application Status: PENDING
```

---

### 📋 SECRETARY WORKFLOW

#### 1. **Review Applications** ✨ NEW
```
Login → /secretary/applications.php

Features:
- View pending applications
- Search by name, email, phone, application number
- Filter by status (pending, under_review, accepted, rejected, admitted)
- Pagination (20 per page)
- Click to review full application details
- Add reviewer comments
- Decision actions: Accept / Reject / Admit
```

#### 2. **Admit Student** - AUTO ENROLLMENT
```
In Applications page:
1. Click "Review" on a pending/accepted application
2. Click "Admit Student" button
3. System AUTOMATICALLY:
   ✓ Creates entry in students table
   ✓ Generates Student ID (STU-YYYY-XXXXX)
   ✓ Assigns appropriate class based on level
   ✓ Sets admission date to today
   ✓ Updates application status to "admitted"
   ✓ Records admission date

Result: Student can now login and access teacher's classes
```

#### 3. **Manage Students**
```
/secretary/students.php     → View all students
/secretary/register_student.php → Manual registration (if needed)
/secretary/student_details.php  → View student info
/secretary/edit_student.php     → Update student data
```

---

### 👨‍🏫 TEACHER WORKFLOW

#### 1. **Dashboard Overview**
```
Login → /teacher/dashboard.php

Shows:
- My Classes (now shows MODULES)
- Total Students
- Today's Schedule/Timetable
- Attendance marked today
- Quick action buttons
```

#### 2. **View Assigned Modules** ✨ UPDATED
```
/teacher/my_classes.php

Shows:
- All modules assigned to me
- Class information
- Number of students per module
- Module details (code, credits, hours)
- Click to view module details
```

#### 3. **Mark Attendance**
```
/teacher/attendance.php

Steps:
1. Select module
2. Select class
3. Select date
4. Check attendance for each student
5. Save attendance record
```

#### 4. **Enter Module Marks** ✨ UPDATED
```
/teacher/marks.php

Features:
- Select module (from my assignments)
- Select class
- View students
- Enter marks for:
  ✓ Practical assessments
  ✓ Theory assessments
  ✓ Project work
  ✓ Quizzes
- Max marks vary per assessment type
- Save marks to database
```

#### 5. **View Schedule** ✨ UPDATED
```
/teacher/timetable.php

Shows:
- Auto-generated timetable
- Module-based schedule
- Time slots (2-hour blocks)
- Room assignments
- This week view
- Weekly overview
```

#### 6. **Generate Reports**
```
/teacher/reports.php

Reports:
- Student performance per module
- Attendance summary
- Assessment breakdown
- Export to PDF (if configured)
```

---

### 🎛️ DOS (DIRECTOR OF STUDIES) WORKFLOW

#### 1. **Dashboard Overview**
```
/dos/dashboard.php

Shows system status and statistics
```

#### 2. **Manage Training Modules** ✨ NEW
```
/dos/modules.php

Features:
A) View All Modules
   - 10 sample modules pre-loaded
   - Module code, name, level, credits, hours, fee
   - Module status

B) Assign Teachers to Modules
   - Select Teacher
   - Select Module
   - Select Class
   - Set hours per week
   - Prevents duplicate assignments
   - System validates assignments

C) Manage Assignments
   - View all current assignments
   - Teacher name, module, class details
   - One-click removal
```

#### 3. **Generate Timetables** ✨ NEW & AUTOMATED
```
/dos/generate_timetable.php

Steps:
1. Select Target Class
2. Select Term/Period
3. Click "Generate Timetable"

System AUTOMATICALLY:
✓ Reads all module-teacher assignments
✓ Creates 2-hour time blocks
✓ Distributes across Mon-Fri (5 days)
✓ 4 slots per day (08:00-17:15)
✓ Assigns rooms (Room 1, 2, 3)
✓ Stores in timetable_slots table
✓ Prevents schedule conflicts

Result: Complete class schedule ready for teachers
```

#### 4. **View Timetables**
```
/dos/timetable.php

Shows:
- Generated timetables by class
- Module assignments
- Room allocations
- Time slots
- Teachers assigned
```

---

### 👨‍💼 ADMIN WORKFLOW

#### 1. **Dashboard Overview** ✨ UPDATED
```
/admin/dashboard.php

New Stats Cards:
- Pending Applications
- Training Modules active
- Generated timetable slots
- All other system stats
```

#### 2. **Quick Links**
- Training Modules: `/dos/modules.php`
- User Management
- Student Management
- Class Management
- Reports
- Activity Logs

---

## 📌 Key Statistics Dashboard Shows

### Admin Dashboard
- Total Users
- Active Students
- Active Classes
- **Pending Applications** (NEW)
- **Training Modules** (NEW)
- **Timetable Slots Generated** (NEW)
- Today's Activities

### Secretary Dashboard
- **Pending Applications** (NEW)
- Total Students
- Unassigned Students
- Meeting Documents

### Teacher Dashboard
- **My Modules** (UPDATED)
- Total Classes
- Total Students
- Attendance Marked Today

---

## 🔗 Quick Links

### For Public
- Homepage: `http://localhost/SchoolManagementSystem/`
- Apply Online: `http://localhost/SchoolManagementSystem/public/apply.php`

### For Staff
- Login: `http://localhost/SchoolManagementSystem/auth/login.php`
- Admin: `http://localhost/SchoolManagementSystem/admin/dashboard.php`
- Secretary: `http://localhost/SchoolManagementSystem/secretary/dashboard.php`
- Teacher: `http://localhost/SchoolManagementSystem/teacher/dashboard.php`
- DOS: `http://localhost/SchoolManagementSystem/dos/dashboard.php`

### Database Import
- Importer: `http://localhost/SchoolManagementSystem/database/import_extensions.php`

---

## ✅ Pre-Loaded Sample Data

### 10 Training Modules
```
1. Electrical Installation (Level 3-5)
2. Motor Vehicle Mechanic (Level 3-5)
3. Plumbing (Level 3-5)
4. Welding (Level 3-5)
5. Culinary Arts (Level 3-5)
6. Building Construction (Level 3-5)
7. Carpentry (Level 4-5)
8. HVAC Systems (Level 4-5)
9. Industrial Maintenance (Level 5)
10. Advanced Electronics (Level 5)
```

Each module has:
- Module code (ELE001, MVE001, etc.)
- Credits and hours
- Tuition fee
- Description
- Active status

---

## 🎯 Complete User Journey

```
┌─────────────────────────────────────────────────┐
│         COMPLETE SYSTEM WORKFLOW                │
└─────────────────────────────────────────────────┘

1. STUDENT APPLIES
   └─→ Visit /public/apply.php
   └─→ Submit application
   └─→ Get Application Number

2. SECRETARY REVIEWS
   └─→ /secretary/applications.php
   └─→ Review application
   └─→ Accept/Reject
   └─→ Click "Admit"

3. AUTO ENROLLMENT
   └─→ Student ID generated
   └─→ Added to students table
   └─→ Class assigned by level
   └─→ Application marked "admitted"

4. DOS MANAGES MODULES
   └─→ /dos/modules.php
   └─→ Assign teachers to modules
   └─→ Define class assignments

5. DOS GENERATES TIMETABLE
   └─→ /dos/generate_timetable.php
   └─→ Select class + term
   └─→ Auto-generate schedule
   └─→ Timetable ready

6. TEACHER TEACHES & MARKS
   └─→ Login /teacher/dashboard.php
   └─→ See assigned modules
   └─→ View timetable
   └─→ Mark attendance
   └─→ Enter marks

7. SYSTEM TRACKS PERFORMANCE
   └─→ Attendance recorded
   └─→ Marks stored
   └─→ Reports generated

8. REPORTS & ANALYTICS
   └─→ View performance
   └─→ Export reports
   └─→ Track progress
```

---

## 🐛 Troubleshooting

### Issue: Database tables not found
**Solution**: Run import script
```
Visit: http://localhost/SchoolManagementSystem/database/import_extensions.php
```

### Issue: No modules available for teacher assignment
**Solution**: They are pre-loaded. Check:
1. `/dos/modules.php` - Confirm modules listed
2. phpMyAdmin: Check `modules` table has 10 records
3. Query: `SELECT * FROM modules WHERE status = 'active'`

### Issue: Timetable generation not working
**Solution**:
1. Ensure teacher-module assignments exist
2. Check `/dos/modules.php` for assignments
3. Verify classes exist
4. Check academic year setting

### Issue: Student not appearing after admission
**Solution**:
1. Verify student ID generated
2. Check `students` table for new record
3. Ensure class_id is set
4. Check application status = "admitted"

### Issue: Teacher can't see assigned modules
**Solution**:
1. Verify assignment in `/dos/modules.php`
2. Check teacher_id is correct
3. Verify academic_year matches CURRENT_ACADEMIC_YEAR
4. Test query: 
   ```sql
   SELECT * FROM module_teachers 
   WHERE teacher_id = ? AND academic_year = ?
   ```

---

## 📊 Database Queries for Verification

### Check applications
```sql
SELECT * FROM applications 
WHERE status IN ('pending', 'accepted', 'rejected', 'admitted');
```

### Check modules
```sql
SELECT * FROM modules WHERE status = 'active' LIMIT 5;
```

### Check teacher assignments
```sql
SELECT mt.*, t.username, m.module_name, c.class_name
FROM module_teachers mt
JOIN users t ON mt.teacher_id = t.id
JOIN modules m ON mt.module_id = m.id
JOIN classes c ON mt.class_id = c.id;
```

### Check generated timetable
```sql
SELECT * FROM timetable_slots 
WHERE academic_year = YEAR(NOW())
LIMIT 10;
```

### Check module marks
```sql
SELECT * FROM module_marks 
ORDER BY created_at DESC LIMIT 10;
```

---

## 🎓 Key System Features

✅ **Online Student Applications**
- Self-service application portal
- Level and module selection
- Document upload
- Automatic application numbers

✅ **Automated Enrollment**
- One-click admission
- Auto student ID generation
- Automatic class assignment
- Instant activation

✅ **Module Management**
- Pre-loaded training modules
- Teacher assignments
- Flexible scheduling
- Conflict prevention

✅ **Intelligent Timetabling**
- Auto-generated schedules
- Conflict-free scheduling
- 2-hour time blocks
- Room assignment

✅ **Teacher Module Support**
- Module-based teaching
- Multiple assessment types
- Per-module attendance
- Module performance reports

✅ **Integrated Dashboards**
- Role-based views
- Real-time statistics
- Quick action buttons
- Performance insights

---

## 🔐 Security Features

✅ Role-based access control
✅ Secure login with session management
✅ Password hashing (PHP password_hash)
✅ Activity logging
✅ SQL injection prevention (PDO prepared statements)
✅ XSS protection (htmlspecialchars)

---

## 📞 Support

For issues or questions:
1. Check the QUICK_START.md file
2. Review IMPLEMENTATION_GUIDE.md
3. Check database tables in phpMyAdmin
4. Review application logs

---

**System Status**: ✅ READY FOR PRODUCTION

All modules tested and working. User-friendly interface with modern design.
Enjoy your new School Management System!
