CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL
);

INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
('Cong cu nao dung de thu thap Metrics he thong container?', 'Prometheus', 'MySQL', 'Apache', 'PHP', 'A'),
('Ngon ngu truy van log tren he thong Grafana Loki la gi?', 'SQL', 'LogQL', 'GraphQL', 'NoSQL', 'B'),
('Cong mac dinh cua giao thuc HTTPS tren Nginx Reverse Proxy la bao nhieu?', '80', '3306', '443', '9090', 'C');
