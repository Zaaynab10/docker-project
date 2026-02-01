-- Database initialization for Medical Task Manager
-- Single init file for clean database setup

-- Grant permissions for app user to connect from any host
CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'apppassword';
GRANT ALL PRIVILEGES ON todo_db.* TO 'appuser'@'%';
FLUSH PRIVILEGES;

USE todo_db;

-- Create tasks table with order column for drag-and-drop
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    task_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_order (status, task_order)
);

-- Insert sample data for medical tasks
INSERT INTO tasks (title, description, status, task_order) VALUES
('Patient consultation - John Smith', 'Follow-up visit for blood pressure check', 'completed', 0),
('Review patient results', 'Check lab results from today''s tests', 'pending', 0),
('Update medical records', 'Update patient files from this week', 'pending', 1),
('Team meeting', 'Weekly medical team meeting - 2:30 PM', 'completed', 1),
('Medical report', 'Complete expertise report for patient case', 'pending', 2);

