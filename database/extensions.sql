-- EXTENSIONS TO SCHOOL MANAGEMENT SYSTEM
-- Add Student Applications, Modules, and Enhanced Timetable Features

USE school_management;

-- Student Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    parent_name VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    parent_email VARCHAR(100),
    address TEXT,
    level_applied VARCHAR(50) NOT NULL,
    trade_module_id INT,
    result_slip_path VARCHAR(255),
    previous_school VARCHAR(255),
    qualification_document_path VARCHAR(255),
    status ENUM('pending', 'under_review', 'accepted', 'rejected', 'admitted') DEFAULT 'pending',
    reviewer_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    admitted_date TIMESTAMP NULL,
    admitted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trade_module_id) REFERENCES modules(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (admitted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_level (level_applied),
    INDEX idx_created_at (created_at)
);

-- Modules/Trades Table (for technical training)
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(50) UNIQUE NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    module_title VARCHAR(150),
    description TEXT,
    level VARCHAR(20) NOT NULL,
    credits INT DEFAULT 3,
    total_hours INT NOT NULL,
    tuition_fee DECIMAL(10,2),
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_status (status)
);

-- Module-Teacher Assignment (replaces subject-based assignments for vocational training)
CREATE TABLE IF NOT EXISTS module_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    module_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    hours_per_week INT DEFAULT 4,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_module_assignment (teacher_id, module_id, class_id, academic_year),
    INDEX idx_teacher_year (teacher_id, academic_year)
);

-- Enhanced Timetable with Module Support
CREATE TABLE IF NOT EXISTS timetable_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    module_id INT NOT NULL,
    teacher_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    term ENUM('1', '2', '3') NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    hours_allocated INT DEFAULT 2,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_class_day (class_id, day_of_week),
    INDEX idx_teacher_day (teacher_id, day_of_week),
    INDEX idx_term_year (term, academic_year)
);

-- Module Performance Tracking
CREATE TABLE IF NOT EXISTS module_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    term ENUM('1', '2', '3') NOT NULL,
    assessment_type ENUM('practical', 'theory', 'project', 'quiz') NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    assessment_date DATE,
    remarks TEXT,
    entered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_term (student_id, term, academic_year),
    INDEX idx_module (module_id)
);

-- Sample Modules (Trades/Programs)
INSERT INTO modules (module_code, module_name, module_title, description, level, credits, total_hours, tuition_fee, status) VALUES
('ELE101', 'Electrical Installation', 'Level 3 Electrical Installation', 'Basic electrical installation and maintenance', 'Level 3', 3, 240, 15000, 'active'),
('ELE102', 'Electrical Installation Advanced', 'Level 4 Electrical Installation', 'Advanced electrical installation and troubleshooting', 'Level 4', 4, 320, 20000, 'active'),
('ELE103', 'Electrical Engineering', 'Level 5 Electrical Engineering', 'Professional electrical engineering and design', 'Level 5', 5, 400, 25000, 'active'),
('CAR101', 'Motor Vehicle Mechanics', 'Level 3 Motor Vehicle Mechanics', 'Basic motor vehicle maintenance and repair', 'Level 3', 3, 240, 15000, 'active'),
('CAR102', 'Motor Vehicle Engineering', 'Level 4 Motor Vehicle Engineering', 'Advanced vehicle systems and diagnostics', 'Level 4', 4, 320, 20000, 'active'),
('CAR103', 'Automotive Engineering', 'Level 5 Automotive Engineering', 'Professional automotive design and engineering', 'Level 5', 5, 400, 25000, 'active'),
('PLU101', 'Plumbing', 'Level 3 Plumbing', 'Basic plumbing installation and repair', 'Level 3', 3, 240, 15000, 'active'),
('WEL101', 'Welding & Metal Fabrication', 'Level 3 Welding', 'Basic welding and metal work', 'Level 3', 3, 240, 15000, 'active'),
('CUI101', 'Culinary Arts', 'Level 3 Culinary Arts', 'Basic cooking and food preparation', 'Level 3', 3, 240, 15000, 'active'),
('BUI101', 'Building Construction', 'Level 3 Building Construction', 'Basic construction and masonry', 'Level 3', 3, 240, 15000, 'active');