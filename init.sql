CREATE DATABASE IF NOT EXISTS quiz_db;
USE quiz_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    duration_minutes INT DEFAULT 15,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    content TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dữ liệu demo
INSERT INTO users (username, full_name) VALUES ('sinhvien01', 'Nguyễn Văn A');
INSERT INTO quizzes (title, duration_minutes) VALUES ('Kiểm tra An ninh mạng', 15);
INSERT INTO questions (quiz_id, content, option_a, option_b, option_c, option_d, correct_option) VALUES 
(1, 'Cổng (port) mặc định của giao thức SSH là bao nhiêu?', '21', '22', '80', '443', 'B'),
(1, 'Cổng mặc định của MySQL là bao nhiêu?', '3306', '5432', '8080', '9090', 'A'),
(1, 'Lệnh nào dùng để cấu hình tường lửa trên Ubuntu?', 'iptables', 'ufw', 'firewalld', 'Cả A và B', 'D');
