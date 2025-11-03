# Complete Student Application & Timetable System Implementation Guide

## Overview
This guide documents the complete implementation of:
1. **Student Application System** - Students apply through online form
2. **Application Review & Admission** - Secretary reviews and admits students
3. **Module/Trade Management** - DOS assigns teachers to modules
4. **Automated Timetable Generation** - System auto-generates class timetables

---

## PART 1: Fixed Database Errors

### Errors Fixed

1. **my_classes.php Line 102** - Fixed undefined array key
   - ❌ OLD: `$class['stream']`
   - ✅ NEW: `$class['section']`
   - **Reason**: Database schema uses `section` column

2. **marks.php Line 132** - Fixed column name and parameter binding
   - ❌ OLD: `m.marks_obtained` with named parameters (`:class_id`)
   - ✅ NEW: `m.marks` with positional parameters (?)
   - **Reason**: Schema defines column as `marks`, not `marks_obtained`

3. **reports.php Line 52** - Fixed non-existent column and parameter binding
   - ❌ OLD: `a.subject_id` in attendance join
   - ✅ NEW: Removed subject_id (attendance table has no such column)
   - ✅ Changed `marks_obtained` to `marks` and `exam_type` to `assessment_type`
   - ✅ Fixed named parameters to positional parameters
   - **Reason**: Attendance table only has class_id, not subject_id

---

## PART 2: Database Schema Extensions

### New Tables Created

#### 1. **applications** Table
Stores student applications before admission
```sql
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(50) UNIQUE,
    first_name, middle_name, last_name, date_of_birth, gender,
    email, phone, parent_name, parent_phone, parent_email, address,
    level_applied VARCHAR(50),
    trade_module_id INT,
    result_slip_path VARCHAR(255),
    qualification_document_path VARCHAR(255),
    status ENUM('pending', 'under_review', 'accepted', 'rejected', 'admitted'),
    reviewer_notes TEXT,
    reviewed_by INT, reviewed_at TIMESTAMP,
    admitted_by INT, admitted_date TIMESTAMP
);
```

#### 2. **modules** Table
Defines training modules/trades with credits and hours
```sql
CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(50) UNIQUE,
    module_name VARCHAR(100),
    module_title VARCHAR(150),
    description TEXT,
    level VARCHAR(20),
    credits INT,
    total_hours INT,
    tuition_fee DECIMAL(10,2),
    status ENUM('active', 'inactive', 'archived')
);
```

#### 3. **module_teachers** Table
Assigns teachers to modules for specific classes
```sql
CREATE TABLE module_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    module_id INT,
    class_id INT,
    academic_year VARCHAR(20),
    hours_per_week INT,
    assigned_date TIMESTAMP
);
```

#### 4. **timetable_slots** Table
Auto-generated timetable slots for each class
```sql
CREATE TABLE timetable_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT, module_id INT, teacher_id INT,
    day_of_week ENUM('Monday' to 'Sunday'),
    start_time TIME, end_time TIME,
    room VARCHAR(50),
    term ENUM('1', '2', '3'),
    academic_year VARCHAR(20),
    hours_allocated INT
);
```

#### 5. **module_marks** Table
Student performance tracking for modules
```sql
CREATE TABLE module_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT, module_id INT, class_id INT,
    academic_year VARCHAR(20), term ENUM('1', '2', '3'),
    assessment_type ENUM('practical', 'theory', 'project', 'quiz'),
    marks DECIMAL(5,2), max_marks DECIMAL(5,2),
    assessment_date DATE, remarks TEXT
);
```

### Sample Data
10 modules are pre-loaded:
- Electrical Installation (Levels 3, 4, 5)
- Motor Vehicle Mechanics (Levels 3, 4, 5)
- Plumbing (Level 3)
- Welding & Metal Fabrication (Level 3)
- Culinary Arts (Level 3)
- Building Construction (Level 3)

---

## PART 3: Student Application Workflow

### Step 1: Apply to School
**URL**: `/public/apply.php`

**Process**:
1. Student fills out personal information
2. Selects training level (Level 3, 4, or 5)
3. Chooses desired trade/module
4. Uploads result slip and qualification documents
5. Submits application

**System Actions**:
- Generates unique application number (APP-YYYY-XXXX)
- Stores in `applications` table with status = `pending`
- Stores uploaded files in `uploads/applications/` directory

**Fields Collected**:
- Personal: First name, middle name, last name, DOB, gender
- Contact: Email, phone, address
- Parent: Name, phone, email
- Education: Previous school
- Program: Level and module choice
- Documents: Result slip, qualification document

---

### Step 2: Application Review & Admission
**URL**: `/secretary/applications.php`
**Role**: Secretary/Admission Board

**Features**:
- View all applications with filters (status, search)
- Review individual applications
- Add reviewer notes
- Accept or reject applications
- Admit students to enrollment system

**Workflow**:
1. Secretary views pending applications
2. Clicks "Review" button
3. Reads applicant details
4. Updates status: `under_review` → `accepted` or `rejected`
5. Adds comments/notes
6. If accepted, clicks "Admit" button
7. System automatically:
   - Generates student ID (STU-YYYY-XXXXX)
   - Creates entry in `students` table
   - Updates application status to `admitted`
   - Assigns to appropriate class based on level

**Automatic Class Assignment**:
- Level 3 applicants → First available Level 3 class
- Level 4 applicants → First available Level 4 class
- Level 5 applicants → First available Level 5 class

---

## PART 4: DOS Module & Timetable Management

### Step 1: Assign Modules to Teachers
**URL**: `/dos/modules.php`
**Role**: DOS (Director of Studies)

**Process**:
1. DOS selects teacher from dropdown
2. Selects module (trade)
3. Selects class
4. Clicks "Assign"
5. System stores in `module_teachers` table

**View**:
- Grid of all available modules with details
- Table of current assignments
- Option to remove assignments

**Example**: Assign Teacher "John Nyaruai" to teach "Electrical Installation" to class "Level 3 - A"

---

### Step 2: Generate Timetable
**URL**: `/dos/generate_timetable.php`
**Role**: DOS

**Process**:
1. DOS selects a class
2. Selects academic term (1, 2, or 3)
3. Clicks "Generate Timetable"
4. System automatically:
   - Retrieves all module-teacher assignments for that class
   - Distributes modules across available time slots
   - Creates 2-hour blocks for each module
   - Prevents scheduling conflicts
   - Generates timetable in `timetable_slots` table

**Scheduling Algorithm**:
- **Days**: Monday to Friday
- **Time Slots**:
  - 08:00 - 10:00
  - 10:15 - 12:15
  - 13:00 - 15:00 (Break: 12:15-13:00)
  - 15:15 - 17:15
- **Room Assignment**: Automatic (Room 1, 2, or 3)
- **Module Duration**: Calculated based on total hours ÷ term weeks ÷ 2 (2-hour blocks)

**Example Output**:
```
Monday: 08:00-10:00 - Electrical Installation (Teacher A, Room 1)
Tuesday: 08:00-10:00 - Motor Vehicle Mechanics (Teacher B, Room 2)
...
```

---

## PART 5: Teacher Module Features

### Teacher Dashboard Features

#### 1. View Assigned Modules
**Previous**: Teachers saw subjects only
**Now**: Teachers see modules with:
- Module name and code
- Level
- Credit hours
- Student count per module
- Links to manage each module

#### 2. Mark Attendance (for their modules)
- Teachers mark attendance for students in assigned modules
- Recorded in `attendance` table
- System verifies teacher-module assignment

#### 3. Enter Marks (per module, per assessment)
- Teachers enter marks for students
- Assessment types: Formative, Continuous, Exam
- Marks stored in `module_marks` table
- Can track multiple assessments per student per module

#### 4. Generate Reports
- Attendance reports per class/module
- Performance reports showing:
  - Formative assessments
  - Continuous assessment
  - Exam scores
  - Average marks
  - Max marks

#### 5. View Timetable
- Teachers see their complete module timetable
- Shows all time slots for their assigned modules
- Organized by day and time

---

## PART 6: System Data Flow Diagram

```
STUDENT APPLIES
    ↓
┌─────────────────────────────────────┐
│ public/apply.php                     │
│ - Fill personal info                 │
│ - Select level & module              │
│ - Upload documents                   │
│ - Submit → applications table        │
└─────────────────────────────────────┘
    ↓ (status: pending)
SECRETARY REVIEWS
    ↓
┌─────────────────────────────────────┐
│ secretary/applications.php           │
│ - View pending applications          │
│ - Review details                     │
│ - Accept/Reject                      │
│ - Update status                      │
└─────────────────────────────────────┘
    ↓ (if accepted)
┌─────────────────────────────────────┐
│ Click "Admit" Button                │
│ - Create students entry              │
│ - Generate student ID                │
│ - Assign to class                    │
│ - Update app status: admitted        │
└─────────────────────────────────────┘
    ↓
STUDENT ENROLLED
    ↓
┌─────────────────────────────────────┐
│ Teacher Assignment Phase             │
│ - DOS assigns teachers to modules    │
│ - via dos/modules.php                │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ Timetable Generation                 │
│ - DOS generates schedule             │
│ - via dos/generate_timetable.php     │
│ - Creates timetable_slots            │
└─────────────────────────────────────┘
    ↓
TEACHING & ASSESSMENT
    ↓
┌─────────────────────────────────────┐
│ Teacher Activities                   │
│ - Mark attendance                    │
│ - Enter module marks                 │
│ - View timetable                     │
│ - Generate reports                   │
└─────────────────────────────────────┘
```

---

## PART 7: Setup Instructions

### Step 1: Update Database
```bash
# Option A: Using MySQL directly
mysql -u root -p school_management < extensions.sql

# Option B: Import through phpMyAdmin
1. Go to phpMyAdmin → school_management database
2. Click "Import" tab
3. Select extensions.sql file
4. Click "Go"
```

### Step 2: Create Upload Directory
```bash
mkdir uploads/applications
chmod 755 uploads/applications
```

### Step 3: Verify Files Exist
- ✅ `public/apply.php` - Student application form
- ✅ `secretary/applications.php` - Review and admission
- ✅ `dos/modules.php` - Module assignments
- ✅ `dos/generate_timetable.php` - Timetable generation

### Step 4: Test the System
1. **Apply**: Visit `/public/apply.php`
2. **Review**: Login as secretary, go to applications
3. **Admit**: Click admit for one student
4. **Assign**: Login as DOS, go to modules
5. **Generate**: Create timetable for a class

---

## PART 8: User Workflows by Role

### Student Workflow
```
1. Visit /public/apply.php
2. Fill application form
3. Select module (Level 3/4/5)
4. Upload documents
5. Submit application
6. Wait for review (check email for status)
7. Upon admission → Auto-enrolled in class
```

### Secretary/Admission Board Workflow
```
1. Login to system
2. Go to secretary/applications.php
3. View pending applications (filtered)
4. Click "Review" on application
5. Read applicant details
6. Make decision: Accept/Reject/Review
7. Add reviewer notes
8. If accepted, click "Admit"
9. Student automatically enrolled
10. Verify student in students table
```

### DOS Workflow
```
1. Login to system
2. Go to dos/modules.php
3. View available modules
4. Assign module to teacher:
   - Select teacher
   - Select module
   - Select class
   - Click "Assign"
5. Repeat for all modules/classes
6. Go to dos/generate_timetable.php
7. Select class and term
8. Click "Generate Timetable"
9. System creates all timetable slots
10. Teachers can now view schedule
```

### Teacher Workflow
```
1. Login to system
2. Dashboard shows assigned modules
3. Click on module to:
   - View enrolled students
   - Mark attendance
   - Enter module marks
   - View timetable
   - Generate performance reports
4. Mark attendance for classes
5. Enter assessment marks (practical, theory, project, quiz)
6. Generate reports for principal
```

---

## PART 9: Key Features Implemented

### ✅ Application System
- [x] Online application form
- [x] Level selection (3, 4, 5)
- [x] Module/trade selection
- [x] Document upload (result slip, qualifications)
- [x] Application number generation
- [x] Status tracking

### ✅ Review & Admission
- [x] Secretary reviews applications
- [x] Accept/Reject decision
- [x] Reviewer notes
- [x] Auto-admission to students table
- [x] Auto-student ID generation
- [x] Auto-class assignment by level

### ✅ Module Management
- [x] 10 pre-loaded modules
- [x] Module details (code, name, level, credits, hours, fee)
- [x] Teacher-module assignment per class
- [x] Assignment removal

### ✅ Timetable Generation
- [x] Auto-generate based on assignments
- [x] 5-day schedule
- [x] 4 time slots per day (2-hour blocks)
- [x] Avoid teacher conflicts
- [x] Room assignment
- [x] Term-based scheduling

### ✅ Teacher Features
- [x] View assigned modules
- [x] Mark attendance per module
- [x] Enter module marks (multiple assessment types)
- [x] View personal timetable
- [x] Generate performance reports

---

## PART 10: Database Queries Reference

### Useful Queries

**View all pending applications**:
```sql
SELECT * FROM applications WHERE status = 'pending' ORDER BY created_at DESC;
```

**View teacher's assigned modules**:
```sql
SELECT DISTINCT m.*, mt.class_id, c.class_name
FROM modules m
INNER JOIN module_teachers mt ON m.id = mt.module_id
INNER JOIN classes c ON mt.class_id = c.id
WHERE mt.teacher_id = ? AND mt.academic_year = CURRENT_ACADEMIC_YEAR
ORDER BY m.module_name;
```

**View class timetable**:
```sql
SELECT ts.*, m.module_name, u.full_name as teacher_name
FROM timetable_slots ts
INNER JOIN modules m ON ts.module_id = m.id
INNER JOIN users u ON ts.teacher_id = u.id
WHERE ts.class_id = ? AND ts.academic_year = CURRENT_ACADEMIC_YEAR
ORDER BY FIELD(ts.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), ts.start_time;
```

**View student marks per module**:
```sql
SELECT mm.*, m.module_name, SUM(mm.marks) as total_marks, AVG(mm.marks) as average
FROM module_marks mm
INNER JOIN modules m ON mm.module_id = m.id
WHERE mm.student_id = ? AND mm.academic_year = CURRENT_ACADEMIC_YEAR
GROUP BY mm.module_id;
```

---

## PART 11: Troubleshooting

### Issue: "No modules available for selected level"
**Solution**: 
1. Check modules table has entries for that level
2. Verify module status is 'active'
3. Run: `SELECT * FROM modules WHERE level = 'Level 3' AND status = 'active';`

### Issue: "No suitable class found for this level"
**Solution**:
1. Create classes for each level
2. Classes must have status = 'active'
3. Classes must have matching level name

### Issue: "Module assignment already exists"
**Solution**:
1. Teacher may already be assigned to this module for this class
2. View current assignments in dos/modules.php
3. Remove and reassign if needed

### Issue: "No module assignments found for this class"
**Solution**:
1. Go to dos/modules.php
2. Assign at least one module to the class
3. Then generate timetable

---

## PART 12: Next Steps for Enhancement

### Recommended Future Features
1. **Application Fee Payment** - Online payment gateway
2. **Email Notifications** - Status updates sent to applicants
3. **Bulk Import** - Upload multiple applications as CSV
4. **Auto-grading** - Calculate final grades from module marks
5. **Certificate Generation** - Auto-generate certificates on completion
6. **Mobile App** - Responsive PWA for students
7. **Analytics Dashboard** - Application statistics, completion rates
8. **Attendance Reports** - Generate attendance PDF reports
9. **Performance Analysis** - Compare module performance trends
10. **Module Feedback** - Student evaluation of modules/teachers

---

**Implementation Date**: 2024
**Last Updated**: January 2024
**Status**: Complete and Tested ✅