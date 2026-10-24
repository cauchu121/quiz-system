#!/bin/bash
set -e

echo "=========================================================="
echo "   HỆ THỐNG QUIZ / THI TRẮC NGHIỆM - ĐỀ TÀI 35           "
echo "   Sinh viên: Lê Đắc Lộc - MSV: DTC245180150              "
echo "=========================================================="

echo "[1/4] Khắc phục lỗi Docker credentials store (D-Bus)..."
sudo rm -f /root/.docker/config.json 2>/dev/null || true
rm -f ~/.docker/config.json 2>/dev/null || true

echo "[2/4] Kiểm tra và tạo file cấu hình môi trường .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "-> Đã tạo file .env thành công."
fi

echo "[3/4] Dọn dẹp các container cũ đang chạy dở..."
sudo docker compose down --remove-orphans 2>/dev/null || true
sudo docker rm -f quiz_nginx quiz_web quiz_db quiz_pma quiz_grafana quiz_prometheus quiz_cadvisor quiz_node_exporter quiz_loki quiz_promtail loki 2>/dev/null || true

echo "[4/4] Đang build và khởi động toàn bộ hệ thống..."
sudo docker compose up -d --build

echo ""
echo "=========================================================="
echo "               TRẠNG THÁI CÁC DỊCH VỤ DOCKER              "
echo "=========================================================="
sudo docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "🎉 KHỞI ĐỘNG HỆ THỐNG THÀNH CÔNG!"
echo "Truy cập các dịch vụ trên trình duyệt Windows:"
echo "1. Website Thi Trắc Nghiệm: https://192.168.31.128"
echo "2. Trang Quản Trị Đề Thi:   https://192.168.31.128/admin.php (User: admin | Pass: admin123)"
echo "3. phpMyAdmin Quản lý CSDL: https://192.168.31.128/pma/"
echo "4. Grafana Giám sát & Logs: http://192.168.31.128:3000 (User: admin | Pass: admin)"
echo "5. Prometheus Metrics:      http://192.168.31.128:9090"
echo "=========================================================="
