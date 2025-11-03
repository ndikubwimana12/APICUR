<?php

/**
 * Update Existing Database with New Tables
 * Adds applications, modules, module_teachers, timetable_slots, and module_marks
 */

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Connection failed!");
}

try {
    // 1. Create modules table first (no dependencies)
    $conn->exec("CREATE TABLE IF NOT EXISTS modules (
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
    )");

    // 2. Create applications table
    $conn->exec("CREATE TABLE IF NOT EXISTS applications (
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
    )");

    // 3. Create module_teachers table
    $conn->exec("CREATE TABLE IF NOT EXISTS module_teachers (
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
    )");

    // 4. Create timetable_slots table
    $conn->exec("CREATE TABLE IF NOT EXISTS timetable_slots (
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
    )");

    // 5. Create module_marks table
    $conn->exec("CREATE TABLE IF NOT EXISTS module_marks (
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
    )");

    // 6. Insert modules if not exists
    $checkModules = $conn->query("SELECT COUNT(*) as cnt FROM modules")->fetch();

    if ($checkModules['cnt'] == 0) {
        $modules = [
            ['ELE101', 'Electrical Installation', 'Level 3 Electrical Installation', 'Basic electrical installation and maintenance', 'Level 3', 3, 240, 15000],
            ['ELE102', 'Electrical Installation Advanced', 'Level 4 Electrical Installation', 'Advanced electrical installation and troubleshooting', 'Level 4', 4, 320, 20000],
            ['ELE103', 'Electrical Engineering', 'Level 5 Electrical Engineering', 'Professional electrical engineering and design', 'Level 5', 5, 400, 25000],
            ['CAR101', 'Motor Vehicle Mechanics', 'Level 3 Motor Vehicle Mechanics', 'Basic motor vehicle maintenance and repair', 'Level 3', 3, 240, 15000],
            ['CAR102', 'Motor Vehicle Engineering', 'Level 4 Motor Vehicle Engineering', 'Advanced vehicle systems and diagnostics', 'Level 4', 4, 320, 20000],
            ['CAR103', 'Automotive Engineering', 'Level 5 Automotive Engineering', 'Professional automotive design and engineering', 'Level 5', 5, 400, 25000],
            ['PLU101', 'Plumbing', 'Level 3 Plumbing', 'Basic plumbing installation and repair', 'Level 3', 3, 240, 15000],
            ['WEL101', 'Welding & Metal Fabrication', 'Level 3 Welding', 'Basic welding and metal work', 'Level 3', 3, 240, 15000],
            ['CUI101', 'Culinary Arts', 'Level 3 Culinary Arts', 'Basic cooking and food preparation', 'Level 3', 3, 240, 15000],
            ['BUI101', 'Building Construction', 'Level 3 Building Construction', 'Basic construction and masonry', 'Level 3', 3, 240, 15000]
        ];

        $stmt = $conn->prepare("INSERT INTO modules (module_code, module_name, module_title, description, level, credits, total_hours, tuition_fee) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($modules as $module) {
            $stmt->execute($module);
        }
    }

    // 7. Insert test applications if none exist
    $checkApps = $conn->query("SELECT COUNT(*) as cnt FROM applications")->fetch();

    if ($checkApps['cnt'] == 0) {
        $conn->exec("INSERT INTO applications 
                    (application_number, first_name, last_name, date_of_birth, gender, email, phone, parent_name, parent_phone, level_applied, status) 
                    VALUES 
                    ('APP-2024-0001', 'John', 'Doe', '2005-03-15', 'Male', 'john@example.com', '0712345678', 'Jane Doe', '0712345679', 'Level 3', 'pending'),
                    ('APP-2024-0002', 'Mary', 'Smith', '2004-05-20', 'Female', 'mary@example.com', '0712345680', 'Sarah Smith', '0712345681', 'Level 3', 'pending'),
                    ('APP-2024-0003', 'Peter', 'Johnson', '2006-07-10', 'Male', 'peter@example.com', '0712345682', 'Robert Johnson', '0712345683', 'Level 4', 'pending')");
    }

    echo "<div style='font-family: Arial; padding: 30px; background: #f0f9ff; border-radius: 8px; margin: 20px; max-width: 600px;'>";
    echo "<div style='background: #10b981; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<h2 style='margin: 0; font-size: 24px;'>✅ Database Updated Successfully!</h2>";
    echo "</div>";

    echo "<div style='background: white; padding: 20px; border-radius: 5px; border-left: 4px solid #10b981;'>";
    echo "<h3 style='margin-top: 0; color: #1f2937;'>Tables Created:</h3>";
    echo "<ul style='line-height: 1.8;'>";
    echo "<li><strong>modules</strong> - 10 vocational modules added</li>";
    echo "<li><strong>applications</strong> - 3 test applications added</li>";
    echo "<li><strong>module_teachers</strong> - Ready for teacher assignments</li>";
    echo "<li><strong>timetable_slots</strong> - Ready for schedule generation</li>";
    echo "<li><strong>module_marks</strong> - Ready for assessments</li>";
    echo "</ul>";

    echo "<h3 style='color: #1f2937;'>Next Steps:</h3>";
    echo "<ol style='line-height: 1.8;'>";
    echo "<li>Go to <a href='../auth/login.php' style='color: #0084ff;'>Login</a> with admin/admin123</li>";
    echo "<li>Visit <a href='../secretary/dashboard.php' style='color: #0084ff;'>Secretary Dashboard</a> to review applications</li>";
    echo "<li>Visit <a href='../dos/dashboard.php' style='color: #0084ff;'>DOS Dashboard</a> to manage modules</li>";
    echo "<li>Visit <a href='../admin/dashboard.php' style='color: #0084ff;'>Admin Dashboard</a> to see updated stats</li>";
    echo "</ol>";

    echo "<p style='margin-top: 20px; font-size: 13px; color: #666;'><strong>All new features are now active!</strong> The system will work without needing separate import.</p>";
    echo "</div>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div style='font-family: Arial; padding: 30px; background: #fee; border-radius: 8px; margin: 20px; color: #c00;'>";
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<p>" . $e->getTraceAsString() . "</p>";
    echo "</div>";
}
