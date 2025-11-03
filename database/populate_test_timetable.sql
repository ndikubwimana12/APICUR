-- ============================================
-- Test Timetable Data Population Script
-- School Management System - APICUR TSS
-- ============================================
-- 
-- This script populates the timetable_slots table with sample data
-- for testing and demonstration purposes.
--
-- IMPORTANT: Before running this script:
-- 1. Ensure you have at least 3 active teachers
-- 2. Ensure you have at least 3 active modules
-- 3. Ensure you have at least 1 active class
-- 4. Check the IDs by running the verification queries below
--
-- ============================================

-- VERIFICATION QUERIES (Run these first to get the correct IDs)
-- ============================================

-- Check available teachers
SELECT 'Teachers:' as info;
SELECT id, full_name, role FROM users WHERE role = 'teacher' AND status = 'active' LIMIT 5;

-- Check available modules
SELECT 'Modules:' as info;
SELECT id, module_code, module_name FROM modules WHERE status = 'active' LIMIT 5;

-- Check available classes
SELECT 'Classes:' as info;
SELECT id, class_name, class_level FROM classes WHERE status = 'active' AND academic_year = 2024 LIMIT 5;

-- ============================================
-- TEST DATA INSERTION (Sample with IDs 1, 2, 3, 4)
-- ============================================
-- 
-- Replace the IDs (1, 2, 3, 4) with actual IDs from your database
-- Example: If teachers are IDs 5, 6, 7 - change "1, 2, 3" to "5, 6, 7"
--

-- MONDAY Schedule
INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 1, 1, 'Monday', '08:00', '10:00', 1, 2024, 'A101', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 2, 2, 'Monday', '10:15', '12:15', 1, 2024, 'B102', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 3, 3, 'Monday', '13:00', '15:00', 1, 2024, 'C103', 2, 1);

-- TUESDAY Schedule
INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 2, 2, 'Tuesday', '08:00', '10:00', 1, 2024, 'B102', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 3, 3, 'Tuesday', '10:15', '12:15', 1, 2024, 'C103', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 1, 1, 'Tuesday', '13:00', '15:00', 1, 2024, 'A101', 2, 1);

-- WEDNESDAY Schedule
INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 3, 3, 'Wednesday', '08:00', '10:00', 1, 2024, 'C103', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 1, 1, 'Wednesday', '10:15', '12:15', 1, 2024, 'A101', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 2, 2, 'Wednesday', '13:00', '15:00', 1, 2024, 'B102', 2, 1);

-- THURSDAY Schedule
INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 1, 1, 'Thursday', '08:00', '10:00', 1, 2024, 'A101', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 3, 3, 'Thursday', '10:15', '12:15', 1, 2024, 'C103', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 2, 2, 'Thursday', '13:00', '15:00', 1, 2024, 'B102', 2, 1);

-- FRIDAY Schedule
INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 2, 2, 'Friday', '08:00', '10:00', 1, 2024, 'B102', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 3, 3, 'Friday', '10:15', '12:15', 1, 2024, 'C103', 2, 1);

INSERT INTO timetable_slots 
(class_id, module_id, teacher_id, day_of_week, start_time, end_time, term, academic_year, room, hours_allocated, created_by) 
VALUES 
(1, 1, 1, 'Friday', '13:00', '15:00', 1, 2024, 'A101', 2, 1);

-- ============================================
-- VERIFICATION (Run this after insertion)
-- ============================================

-- Check how many slots were inserted
SELECT 'Total slots inserted:' as info;
SELECT COUNT(*) as total_slots FROM timetable_slots WHERE academic_year = 2024 AND term = 1;

-- View the inserted data grouped by day
SELECT 'Data by day:' as info;
SELECT 
    day_of_week,
    COUNT(*) as slots_count,
    GROUP_CONCAT(DISTINCT m.module_name) as modules
FROM timetable_slots ts
JOIN modules m ON ts.module_id = m.id
WHERE ts.academic_year = 2024 AND ts.term = 1
GROUP BY day_of_week;

-- ============================================
-- CLEANUP (Run this if you want to delete test data)
-- ============================================
-- 
-- DELETE FROM timetable_slots 
-- WHERE academic_year = 2024 AND term = 1 AND class_id = 1;
--
-- ============================================
