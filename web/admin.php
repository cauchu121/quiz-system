<?php
session_start();

// Kết nối CSDL MySQL
$conn = @new mysqli('db', getenv('MYSQL_USER') ?: 'quiz_user', getenv('MYSQL_PASSWORD') ?: 'User@QuizSystem2026!', getenv('MYSQL_DATABASE') ?: 'quiz_db');
$db_connected = !$conn->connect_error;
if ($db_connected) {
    $conn->set_charset("utf8mb4");
}

$msg = '';
$msg_type = 'success';

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    if ($user === 'admin' && $pass === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: /admin.php");
        exit;
    } else {
        $msg = "Sai tài khoản hoặc mật khẩu quản trị!";
        $msg_type = 'danger';
    }
}

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /admin.php");
    exit;
}

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Xử lý CRUD khi đã đăng nhập
if ($is_admin && $db_connected) {
    // Thêm câu hỏi
    if (isset($_POST['action']) && $_POST['action'] === 'add_question') {
        $q = trim($_POST['question_text'] ?? '');
        $a = trim($_POST['option_a'] ?? '');
        $b = trim($_POST['option_b'] ?? '');
        $c = trim($_POST['option_c'] ?? '');
        $d = trim($_POST['option_d'] ?? '');
        $correct = strtoupper(trim($_POST['correct_option'] ?? 'A'));

        if (!empty($q) && !empty($a) && !empty($b) && !empty($c) && !empty($d)) {
            $stmt = $conn->prepare("INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $q, $a, $b, $c, $d, $correct);
            $stmt->execute();
            header("Location: /admin.php?success=1");
            exit;
        } else {
            $msg = "Vui lòng nhập đầy đủ nội dung câu hỏi và 4 đáp án!";
            $msg_type = 'danger';
        }
    }

    // Xóa câu hỏi
    if (isset($_GET['delete'])) {
        $del_id = (int)$_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        header("Location: /admin.php?deleted=1");
        exit;
    }
}

// Lấy danh sách câu hỏi
$questions = [];
if ($db_connected) {
    $res = $conn->query("SELECT * FROM questions ORDER BY id DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $questions[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị Đề Thi Trắc Nghiệm - Lê Đắc Lộc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #0b1329;
            --bg-card: #131f3d;
            --bg-subcard: #1c2b54;
            --primary: #38bdf8;
            --primary-hover: #0284c7;
            --accent: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #090e1a 0%, #0f172a 50%, #172554 100%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 30px 15px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 22px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .nav-links { display: flex; gap: 12px; align-items: center; }
        .btn {
            background: var(--primary);
            color: #0b1329;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }
        .btn-outline:hover { background: rgba(255,255,255,0.05); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-success { background: var(--success); color: white; }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .form-group { margin-bottom: 14px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
        input, select, textarea {
            width: 100%;
            background: var(--bg-subcard);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            color: white;
            font-family: inherit;
            font-size: 14px;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 600px) {
            .options-grid { grid-template-columns: 1fr; }
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 18px;
        }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #a7f3d0; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; }
        .q-item {
            background: var(--bg-subcard);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }
        .q-item-text { font-size: 15px; font-weight: 600; color: white; margin-bottom: 8px; }
        .q-item-options { font-size: 13px; color: var(--text-muted); display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
        .badge-correct {
            display: inline-block;
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-card">
        <div>
            <h1 style="font-size: 20px; font-weight: 700;">Quản Trị Đề Thi (Admin CRUD)</h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Đề tài 35: Hệ thống Quiz • Sinh viên: <strong>Lê Đắc Lộc</strong></p>
        </div>
        <div class="nav-links">
            <a href="/" class="btn btn-outline">← Xem Trang Thi</a>
            <?php if ($is_admin): ?>
                <a href="/pma/" target="_blank" class="btn btn-outline">phpMyAdmin</a>
                <a href="?logout=1" class="btn btn-danger">Đăng xuất</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ Thêm câu hỏi trắc nghiệm mới thành công!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">✓ Đã xóa câu hỏi khỏi cơ sở dữ liệu!</div>
    <?php endif; ?>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if (!$is_admin): ?>
        <!-- Form đăng nhập Admin -->
        <div class="card" style="max-width: 450px; margin: 40px auto;">
            <h2 style="font-size: 18px; margin-bottom: 6px; text-align: center;">Đăng Nhập Quản Trị Viên</h2>
            <p style="font-size: 13px; color: var(--text-muted); text-align: center; margin-bottom: 20px;">Quản lý câu hỏi, thêm mới và thiết lập đáp án đúng</p>
            <form method="POST">
                <div class="form-group">
                    <label>Tài khoản</label>
                    <input type="text" name="user" value="admin" required autofocus>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="pass" placeholder="••••••••" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display:block;">Mặc định: <strong>admin123</strong></small>
                </div>
                <button type="submit" name="login" class="btn btn-success" style="width: 100%; justify-content: center; margin-top: 10px;">Đăng Nhập Vào Hệ Thống</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Giao diện Thêm câu hỏi mới -->
        <div class="card">
            <h2 style="font-size: 17px; margin-bottom: 16px; color: var(--primary);">+ Thêm Câu Hỏi Trắc Nghiệm Mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_question">
                <div class="form-group">
                    <label>Nội dung câu hỏi</label>
                    <textarea name="question_text" rows="2" placeholder="Ví dụ: Cổng mặc định của giao thức HTTPS là bao nhiêu?" required></textarea>
                </div>
                <div class="options-grid">
                    <div class="form-group">
                        <label>Đáp án A</label>
                        <input type="text" name="option_a" placeholder="Nội dung đáp án A" required>
                    </div>
                    <div class="form-group">
                        <label>Đáp án B</label>
                        <input type="text" name="option_b" placeholder="Nội dung đáp án B" required>
                    </div>
                    <div class="form-group">
                        <label>Đáp án C</label>
                        <input type="text" name="option_c" placeholder="Nội dung đáp án C" required>
                    </div>
                    <div class="form-group">
                        <label>Đáp án D</label>
                        <input type="text" name="option_d" placeholder="Nội dung đáp án D" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 6px;">
                    <label>Đáp án chính xác</label>
                    <select name="correct_option">
                        <option value="A">Đáp án A</option>
                        <option value="B">Đáp án B</option>
                        <option value="C">Đáp án C</option>
                        <option value="D">Đáp án D</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top: 8px;">Lưu Câu Hỏi Vào Database</button>
            </form>
        </div>

        <!-- Danh sách câu hỏi hiện có -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 17px;">Danh Sách Câu Hỏi Trong Database (<?= count($questions) ?> câu)</h2>
            </div>

            <?php if (empty($questions)): ?>
                <p style="color: var(--text-muted); font-size: 14px;">Chưa có câu hỏi nào.</p>
            <?php else: ?>
                <?php foreach ($questions as $q): ?>
                    <div class="q-item">
                        <div style="flex: 1;">
                            <div class="q-item-text">
                                <?= htmlspecialchars($q['question_text']) ?>
                                <span class="badge-correct">Đáp án đúng: <?= $q['correct_option'] ?></span>
                            </div>
                            <div class="q-item-options">
                                <div>A. <?= htmlspecialchars($q['option_a']) ?></div>
                                <div>B. <?= htmlspecialchars($q['option_b']) ?></div>
                                <div>C. <?= htmlspecialchars($q['option_c']) ?></div>
                                <div>D. <?= htmlspecialchars($q['option_d']) ?></div>
                            </div>
                        </div>
                        <div>
                            <a href="?delete=<?= $q['id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này không?');" style="padding: 6px 12px; font-size: 12px;">Xóa</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
