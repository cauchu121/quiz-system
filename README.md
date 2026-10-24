# 🎓 Hệ Thống Triển Khai & Quản Trị Website Thi Trắc Nghiệm (Quiz System)

> **Đề tài 35 - Môn:** Triển khai và Quản trị Hệ thống Phần mềm  
> **Sinh viên thực hiện:** Lê Đắc Lộc  
> **Mã sinh viên:** DTC245180150  
> **Lớp:** KTPM K23C  

---

## 📌 1. Giới thiệu Kiến trúc Hệ thống

Dự án triển khai một hệ thống Website Thi Trắc Nghiệm Trực Tuyến hoàn chỉnh, đáp ứng đầy đủ các tiêu chuẩn hiện đại về Container hóa, An toàn thông tin, Giám sát hiệu năng và Thu thập nhật ký tập trung gồm **10 Docker Containers**:

| Thành phần | Công nghệ / Container | Vai trò kiến trúc |
| :--- | :--- | :--- |
| **Reverse Proxy & SSL** | `quiz_nginx` (Nginx Alpine) | Định tuyến HTTPS (443), cân bằng tải, SSL tự ký và Hardening Headers |
| **Web Application** | `quiz_web` (PHP 8.2 Apache) | Giao diện thi trắc nghiệm, tự động chấm điểm và trang Admin CRUD |
| **Cơ sở dữ liệu** | `quiz_db` (MySQL 8.0) | Lưu trữ ngân hàng câu hỏi, phân quyền Least Privilege cho `quiz_user` |
| **Quản trị CSDL** | `quiz_pma` (phpMyAdmin) | Giao diện Web trực quan quản lý CSDL qua đường dẫn `/pma/` |
| **Giám sát Metrics** | `quiz_prometheus` | Thu thập chỉ số tài nguyên định kỳ qua cổng 9090 |
| **Trực quan hóa** | `quiz_grafana` | Dashboard hiển thị biểu đồ CPU, RAM, Network và LogQL qua cổng 3000 |
| **Container Metrics** | `quiz_cadvisor` | Thu thập chỉ số tài nguyên chi tiết của từng container Docker |
| **Host Metrics** | `quiz_node_exporter` | Thu thập chỉ số tài nguyên phần cứng máy chủ Linux |
| **Nhật ký tập trung** | `quiz_loki` (Loki 2.9.2) | Lưu trữ và lập chỉ mục log tập trung |
| **Thu thập Log** | `quiz_promtail` | Đọc log từ Docker Socket và chuyển tiếp về Loki |

---

## 🚀 2. Hướng dẫn Khởi chạy Nhanh Hệ thống

### Bước 1: Kéo mã nguồn về máy chủ
```bash
git clone https://github.com/cauchu121/quiz-system.git
cd quiz-system
```

### Bước 2: Thiết lập file môi trường
```bash
cp .env.example .env
```

### Bước 3: Khởi chạy toàn bộ 10 dịch vụ
```bash
# Khởi chạy bằng script tự động hóa:
bash run_project.sh

# Hoặc dùng trực tiếp lệnh Docker Compose:
sudo docker compose up -d --build
```

### Bước 4: Kiểm tra trạng thái các Container
```bash
sudo docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
```

---

## 🌐 3. Danh mục Cổng và Địa chỉ Truy cập Dịch vụ

| Dịch vụ | Giao thức / Cổng | URL Truy cập | Tài khoản / Mật khẩu |
| :--- | :--- | :--- | :--- |
| **Website Thi Trắc Nghiệm** | HTTPS / 443 | `https://<IP_MAY_CHU>/` | Tự do làm bài |
| **Quản Trị Đề Thi (CRUD)** | HTTPS / 443 | `https://<IP_MAY_CHU>/admin.php` | `admin` / `admin123` |
| **phpMyAdmin** | HTTPS / 443 | `https://<IP_MAY_CHU>/pma/` | `root` / `Root@QuizSystem2026!` |
| **Grafana Dashboard** | HTTP / 3000 | `http://<IP_MAY_CHU>:3000` | `admin` / `admin` |
| **Prometheus Targets** | HTTP / 9090 | `http://<IP_MAY_CHU>:9090/targets` | Không yêu cầu |
| **Loki Log Engine** | HTTP / 3100 | `http://<IP_MAY_CHU>:3100/ready` | Trả về `ready` |

---

## 🛡️ 4. An toàn Thông tin & Hardening
- **Network Isolation:** Phân chia 3 phân vùng mạng Docker riêng biệt (`frontend_net`, `backend_net`, `monitor_net`).
- **Non-root Execution:** Các dịch vụ giám sát Prometheus (`nobody:65534`) và Grafana (`grafana:472`) vận hành dưới người dùng không đặc quyền.
- **Least Privilege:** Tài khoản ứng dụng `quiz_user` chỉ được cấp quyền giới hạn tối thiểu trên database `quiz_db`.
- **Security Headers:** Nginx cấu hình sẵn `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`.
