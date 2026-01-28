-- Database initialization
USE todo_db;

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample data for medical tasks
INSERT INTO tasks (title, description, status) VALUES
('Patient consultation - John Smith', 'Follow-up visit for blood pressure check', 'completed'),
('Review patient results', 'Check lab results from today\'s tests', 'pending'),
('Update medical records', 'Update patient files from this week', 'pending'),
('Team meeting', 'Weekly medical team meeting - 2:30 PM', 'completed'),
('Write prescriptions', 'Create prescriptions for morning patients', 'in_progress'),
('Medical report', 'Complete expertise report for patient case', 'pending');
