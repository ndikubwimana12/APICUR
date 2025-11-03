-- Test Data for DOS Timetable Display
-- This script populates sample data to display the timetable professionally
-- Academic Year: 2024, Term: 1

-- STEP 1: Verify/Create Sample Classes (if not exists)
-- SELECT * FROM classes WHERE academic_year = 2024;

-- STEP 2: Verify/Create Sample Modules (if not exists)
-- SELECT * FROM modules WHERE status = 'active' LIMIT 10;

-- STEP 3: Verify/Create Sample Teachers (if not exists)
-- SELECT * FROM users WHERE role = 'teacher' AND status = 'active' LIMIT 10;

-- STEP 4: Insert Sample Module Assignments (Teacher → Module → Class)
-- These link teachers to modules for specific classes
INSERT IGNORE INTO module_teachers (teacher_id, module_id, class_id, academic_year, hours_per_week)
SELECT 
    u.id as teacher_id,
    m.id as module_id,
    c.id as class_id,
    2024 as academic_year,
    3 as hours_per_week
FROM users u
JOIN modules m ON m.status = 'active'
JOIN classes c ON c.academic_year = 2024 AND c.status = 'active'
WHERE u.role = 'teacher' AND u.status = 'active'
LIMIT 10;

-- STEP 5: Insert Sample Timetable Slots (Actual Schedule)
-- Format: Time slot + Day + Module + Teacher + Room
-- Monday 08:00-08:40 Period 1

INSERT IGNORE INTO timetable_slots (
    class_id, module_id, teacher_id, 
    day_of_week, start_time, end_time, 
    room, academic_year, term, created_at
)
SELECT 
    c.id, m.id, u.id,
    'Monday', '08:00', '08:40',
    'Room 101', 2024, 1, NOW()
FROM classes c
JOIN modules m ON m.status = 'active'
JOIN users u ON u.role = 'teacher' AND u.status = 'active'
WHERE c.academic_year = 2024 AND c.status = 'active'
LIMIT 1;

-- Tuesday 08:00-08:40 Period 1
INSERT IGNORE INTO timetable_slots (
    class_id, module_id, teacher_id, 
    day_of_week, start_time, end_time, 
    room, academic_year, term, created_at
)
SELECT 
    c.id, m.id, u.id,
    'Tuesday', '08:00', '08:40',
    'Room 102', 2024, 1, NOW()
FROM classes c
JOIN modules m ON m.status = 'active'
JOIN users u ON u.role = 'teacher' AND u.status = 'active'
WHERE c.academic_year = 2024 AND c.status = 'active'
ORDER BY u.id DESC
LIMIT 1;

-- Wednesday 08:00-08:40 Period 1
INSERT IGNORE INTO timetable_slots (
    class_id, module_id, teacher_id, 
    day_of_week, start_time, end_time, 
    room, academic_year, term, created_at
)
SELECT 
    c.id, m.id, u.id,
    'Wednesday', '08:00', '08:40',
    'Room 103', 2024, 1, NOW()
FROM classes c
JOIN modules m ON m.status = 'active'
JOIN users u ON u.role = 'teacher' AND u.status = 'active'
WHERE c.academic_year = 2024 AND c.status = 'active'
ORDER BY u.id ASC
LIMIT 1;

-- Thursday 08:00-08:40 Period 1
INSERT IGNORE INTO timetable_slots (
    class_id, module_id, teacher_id, 
    day_of_week, start_time, end_time, 
    room, academic_year, term, created_at
)
SELECT 
    c.id, m.id, u.id,
    'Thursday', '08:00', '08:40',
    'Room 104', 2024, 1, NOW()
FROM classes c
JOIN modules m ON m.status = 'active'
JOIN users u ON u.role = 'teacher' AND u.status = 'active'
WHERE c.academic_year = 2024 AND c.status = 'active'
LIMIT 1;

-- Friday 08:00-08:40 Period 1
INSERT IGNORE INTO timetable_slots (
    class_id, module_id, teacher_id, 
    day_of_week, start_time, end_time, 
    room, academic_year, term, created_at
)
SELECT 
    c.id, m.id, u.id,
    'Friday', '08:00', '08:40',
    'Room 105', 2024, 1, NOW()
FROM classes c
JOIN modules m ON m.status = 'active'
JOIN users u ON u.role = 'teacher' AND u.status = 'active'
WHERE c.academic_year = 2024 AND c.status = 'active'
ORDER BY u.id DESC
LIMIT 1;

-- STEP 6: Verify Data Insertion
SELECT 'Timetable Slots Summary:' as Info;
SELECT COUNT(*) as Total_Slots FROM timetable_slots WHERE academic_year = 2024;

SELECT 'Classes:' as Info;
SELECT COUNT(*) as Total_Classes FROM classes WHERE academic_year = 2024 AND status = 'active';

SELECT 'Teachers:' as Info;
SELECT COUNT(*) as Total_Teachers FROM users WHERE role = 'teacher' AND status = 'active';

SELECT 'Modules:' as Info;
SELECT COUNT(*) as Total_Modules FROM modules WHERE status = 'active';

SELECT 'Module Assignments:' as Info;
SELECT COUNT(*) as Total_Assignments FROM module_teachers WHERE academic_year = 2024;

-- STEP 7: View Sample Data
SELECT 'Sample Timetable Entries:' as Info;
SELECT 
    ts.id,
    c.class_name,
    m.module_name,
    u.full_name as teacher_name,
    ts.day_of_week,
    ts.start_time,
    ts.end_time,
    ts.room
FROM timetable_slots ts
JOIN classes c ON ts.class_id = c.id
JOIN modules m ON ts.module_id = m.id
JOIN users u ON ts.teacher_id = u.id
WHERE ts.academic_year = 2024 AND ts.term = 1
ORDER BY ts.day_of_week, ts.start_time
LIMIT 20;

-- CLEANUP (if needed - comment out to keep data):
-- DELETE FROM timetable_slots WHERE academic_year = 2024 AND term = 1;
-- DELETE FROM module_teachers WHERE academic_year = 2024;