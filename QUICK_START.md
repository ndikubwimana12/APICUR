# Quick Start Guide - Student Application & Timetable System

## 🚀 Installation (5 minutes)

### 1. Apply Database Updates
```bash
cd c:\xampp\htdocs\SchoolManagementSystem
mysql -u root -p school_management < database/extensions.sql
```
**Or via phpMyAdmin**: Import `database/extensions.sql`

### 2. Create Upload Directory
```bash
mkdir uploads/applications
```

### 3. Verify Installation
- Check new tables exist: `applications`, `modules`, `module_teachers`, `timetable_slots`, `module_marks`
- Verify 10 sample modules in database

---

## 🎯 Test Run (10 minutes)

### As Student
1. **Navigate to**: `http://localhost/SchoolManagementSystem/public/apply.php`
2. **Fill Form**:
   - Personal info: John Doe
   - Level: Level 3
   - Module: Electrical Installation
   - Upload any PDF/image files
3. **Click**: Submit Application
4. **Note**: Application number displayed (e.g., APP-2024-5432)

### As Secretary
1. **Login**: secretary account
2. **Navigate to**: Secretary Dashboard → Applications
3. **See**: John Doe's pending application
4. **Click**: Review
5. **Add Notes**: "Excellent grades"
6. **Select**: Accept
7. **Click**: Update
8. **Click**: Admit
9. **Result**: John is now enrolled! Check Students list to verify.

### As DOS
1. **Login**: DOS account
2. **Navigate to**: DOS Dashboard → Modules
3. **Assign Module**:
   - Teacher: Select any teacher
   - Module: Electrical Installation
   - Class: Select a class
   - Click: Assign
4. **Navigate to**: Generate Timetable
5. **Select**: Same class
6. **Term**: Term 1
7. **Click**: Generate
8. **Result**: Timetable created! Classes now have 4 time slots daily

### As Teacher
1. **Login**: Teacher account
2. **Navigate to**: My Classes
3. **See**: Assigned modules with students
4. **Click**: Module → View timetable
5. **See**: Generated schedule with day/time/room

---

## 📊 Database Verification Queries

### Check Applications Submitted
```sql
SELECT * FROM applications;
```
Should show John Doe's application

### Check Admitted Students
```sql
SELECT * FROM students WHERE admission_date >= NOW() - INTERVAL 1 DAY;
```
Should show newly admitted students

### Check Module Assignments
```sql
SELECT u.full_name, m.module_name, c.class_name 
FROM module_teachers mt
JOIN users u ON mt.teacher_id = u.id
JOIN modules m ON mt.module_id = m.id
JOIN classes c ON mt.class_id = c.id;
```

### Check Generated Timetables
```sql
SELECT c.class_name, m.module_name, u.full_name, ts.day_of_week, ts.start_time
FROM timetable_slots ts
JOIN classes c ON ts.class_id = c.id
JOIN modules m ON ts.module_id = m.id
JOIN users u ON ts.teacher_id = u.id
ORDER BY c.class_name, ts.day_of_week;
```

---

## 🔄 Typical Monthly Workflow

### Week 1: Module Setup
```
DOS → Modules → Assign all teachers to modules for the term
DOS → Generate Timetable → Create schedules for all classes
```

### Week 2-3: Student Admission
```
Students → Public Apply → Submit applications
Secretary → Applications → Review and admit students
```

### Week 4+: Teaching & Assessment
```
Teachers → My Classes → Mark attendance
Teachers → Marks Entry → Enter module assessments
Teachers → Reports → View student performance
```

---

## 📱 Key URLs Quick Reference

| Role | Task | URL |
|------|------|-----|
| Student | Apply | `/public/apply.php` |
| Secretary | Review Applications | `/secretary/applications.php` |
| DOS | Manage Modules | `/dos/modules.php` |
| DOS | Generate Timetable | `/dos/generate_timetable.php` |
| Teacher | My Modules | `/teacher/my_classes.php` |
| Teacher | Attendance | `/teacher/attendance.php` |
| Teacher | Enter Marks | `/teacher/marks.php` |
| Teacher | View Timetable | `/teacher/timetable.php` |
| Teacher | Reports | `/teacher/reports.php` |

---

## ⚡ Common Tasks Checklist

### Before Term Starts
- [ ] All modules created in database
- [ ] Teachers hired and accounts created
- [ ] Classes created for each level
- [ ] DOS assigns teachers to modules
- [ ] DOS generates timetables
- [ ] Students can apply online

### During Term
- [ ] Teachers mark attendance daily
- [ ] Teachers enter marks after assessments
- [ ] Secretary admits qualifying students
- [ ] Secretary tracks admission progress

### End of Term
- [ ] Compile student performance reports
- [ ] Generate certificates for completers
- [ ] Archive timetables and records

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| "No modules available" | Import extensions.sql to add 10 sample modules |
| Can't admit student | Ensure class exists with matching level |
| Timetable not generating | Assign at least 1 module to class first |
| Teacher can't see students | Teacher must be assigned to module for that class |
| Can't upload documents | Check `uploads/applications/` folder exists and is writable |

---

## 📝 Sample Test Data

### Modules Available (10 total)
1. **Electrical Installation** - All levels
2. **Motor Vehicle Mechanics** - All levels  
3. **Electrical Engineering** - All levels
4. **Automotive Engineering** - All levels
5. **Plumbing** - Level 3
6. **Welding & Metal Fabrication** - Level 3
7. **Culinary Arts** - Level 3
8. **Building Construction** - Level 3

### Expected Results After Setup
- 8 modules in database
- 7 sample classes (Level 3-7)
- Students can apply for modules
- Secretary can review and admit
- DOS can assign teachers and generate schedules
- Teachers can view their timetables

---

## 🎓 Example Scenario

**Scenario**: Enroll John Doe in Electrical Installation Level 3

**Steps**:
1. John visits `/public/apply.php`
2. Selects Level 3 → Electrical Installation
3. Uploads results → Submits
4. Application created with status: pending

5. Secretary logs in
6. Views application in Applications section
7. Clicks "Review" → Adds comment → "Accepted"
8. Clicks "Admit"

9. **System Auto-Actions**:
   - Generates student ID: STU-2024-12345
   - Creates entry in students table
   - Assigns to Level 3 class
   - Updates application status: admitted

10. John is now enrolled!

11. DOS logs in
12. Assigns teacher "Jane" to teach "Electrical Installation" to Level 3 class
13. Generates timetable for the class

14. Jane logs in
15. Sees module in "My Classes"
16. Views timetable with schedule
17. Can mark attendance for John and other students

---

## 📞 Support

All system logic implemented according to specifications:
- ✅ Student online application with module selection
- ✅ Secretary review and admission workflow
- ✅ Auto-enrollment of admitted students
- ✅ DOS module and teacher management
- ✅ Automated timetable generation
- ✅ Teacher access to assigned students and modules
- ✅ Attendance and marks entry per module
- ✅ Automatic performance reporting

**Status**: Ready for Production Use ✅

---

**Last Updated**: January 2024
**Version**: 1.0 Complete