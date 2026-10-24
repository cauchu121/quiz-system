CREATE DATABASE IF NOT EXISTS quiz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quiz_db;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
('Công cụ nào được sử dụng để thu thập Metrics hệ thống và container trong đề tài?', 'Prometheus', 'MySQL', 'Apache', 'PHP', 'A'),
('Ngôn ngữ truy vấn log trên hệ thống Grafana Loki được gọi là gì?', 'SQL', 'LogQL', 'GraphQL', 'PromQL', 'B'),
('Cổng mặc định của giao thức HTTPS trên Nginx Reverse Proxy là bao nhiêu?', '80', '3306', '443', '9090', 'C'),
('Mục đích chính của Nginx Reverse Proxy trong kiến trúc hệ thống là gì?', 'Lưu trữ database', 'Điều phối tải, bảo mật SSL và định tuyến dịch vụ', 'Thu thập metrics CPU', 'Ghi nhật ký tập trung', 'B'),
('Trong kiến trúc hệ thống giám sát, Promtail đóng vai trò gì?', 'Lưu trữ log tập trung', 'Hiển thị đồ thị trực quan', 'Thu thập log từ các container và chuyển tiếp về Loki', 'Quản lý cơ sở dữ liệu MySQL', 'C');
