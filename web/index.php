<?php
// Kết nối cơ sở dữ liệu MySQL
$conn = @new mysqli('db', getenv('MYSQL_USER') ?: 'quiz_user', getenv('MYSQL_PASSWORD') ?: 'User@QuizSystem2026!', getenv('MYSQL_DATABASE') ?: 'quiz_db');
$db_connected = !$conn->connect_error;
if ($db_connected) {
    $conn->set_charset("utf8mb4");
}

// Lấy danh sách câu hỏi từ database
$questions = [];
if ($db_connected) {
    $result = $conn->query("SELECT * FROM questions ORDER BY id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
    }
}

// Xử lý nộp bài và chấm điểm tự động
$score = null;
$total = count($questions);
$user_answers = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $total > 0) {
    $score = 0;
    foreach ($questions as $q) {
        $qid = $q['id'];
        $selected = isset($_POST["q_$qid"]) ? trim($_POST["q_$qid"]) : null;
        $user_answers[$qid] = $selected;
        if ($selected !== null && strtoupper($selected) === strtoupper($q['correct_option'])) {
            $score++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đề tài 35: Hệ Thống Quiz Trắc Nghiệm - Lê Đắc Lộc</title>
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
        .container {
            max-width: 860px;
            margin: 0 auto;
        }
        .header-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header-left .badge-topic {
            display: inline-block;
            background: rgba(56, 189, 248, 0.15);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-left h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .header-left .student-info {
            font-size: 13px;
            color: var(--text-muted);
        }
        .nav-btn {
            background: linear-gradient(135deg, var(--accent) 0%, var(--primary-hover) 100%);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .score-banner {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(56, 189, 248, 0.15) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .score-number {
            font-size: 32px;
            font-weight: 800;
            color: #34d399;
        }
        .btn-restart {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .question-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 18px;
            transition: border-color 0.2s;
        }
        .question-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
        }
        .question-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #f8fafc;
            display: flex;
            gap: 8px;
        }
        .q-number {
            color: var(--primary);
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .option-label {
            display: flex;
            align-items: center;
            background: var(--bg-subcard);
            border: 1px solid var(--border-color);
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            gap: 12px;
        }
        .option-label:hover {
            background: #233566;
            border-color: rgba(56, 189, 248, 0.4);
        }
        .option-label input[type="radio"] {
            accent-color: var(--primary);
            width: 17px;
            height: 17px;
        }
        .option-label.is-correct {
            background: rgba(16, 185, 129, 0.2) !important;
            border-color: #10b981 !important;
            color: #a7f3d0;
        }
        .option-label.is-wrong {
            background: rgba(239, 68, 68, 0.2) !important;
            border-color: #ef4444 !important;
            color: #fca5a5;
        }
        .submit-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
        }
        .submit-btn:hover {
            opacity: 0.95;
            transform: translateY(-2px);
        }
        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 12px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header-card">
        <div class="header-left">
            <h1 style="font-size: 22px; font-weight: 700; color: #ffffff;">Hệ Thống Thi Trắc Nghiệm Trực Tuyến (Quiz System)</h1>
        </div>
        <div>
            <a href="/admin.php" class="nav-btn">Quản Trị Đề Thi →</a>
        </div>
    </div>

    <!-- Kết quả chấm điểm tự động -->
    <?php if ($score !== null): 
        $calc_score = $total > 0 ? round(($score / $total) * 10, 2) : 0;
    ?>
        <div class="score-banner">
            <div>
                <p style="font-size: 14px; color: #94a3b8; margin-bottom: 4px;">Kết quả chấm điểm tự động:</p>
                <div class="score-number"><?= $calc_score ?> <span style="font-size: 16px; font-weight: 500; color: #94a3b8;">/ 10 điểm</span></div>
                <p style="font-size: 14px; color: #e2e8f0; margin-top: 4px;">Đúng <strong><?= $score ?></strong> trên tổng số <strong><?= $total ?></strong> câu hỏi.</p>
            </div>
            <a href="/" class="btn-restart">↻ Làm lại bài thi</a>
        </div>
    <?php endif; ?>

    <!-- Form làm bài thi -->
    <?php if ($total === 0): ?>
        <div class="question-card" style="text-align: center; padding: 40px;">
            <p style="color: var(--warning); font-size: 16px; margin-bottom: 12px;">Chưa có câu hỏi nào trong cơ sở dữ liệu!</p>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Vui lòng truy cập trang Quản trị để thêm đề thi trắc nghiệm.</p>
            <a href="/admin.php" class="nav-btn">Thêm câu hỏi mới</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <?php foreach ($questions as $index => $q): 
                $qid = $q['id'];
                $user_choice = $user_answers[$qid] ?? null;
                $correct = strtoupper($q['correct_option']);
                $clean_q_text = preg_replace('/^Câu\s*\d+[\s\.\:\-]+/ui', '', $q['question_text']);
            ?>
                <div class="question-card">
                    <div class="question-title">
                        <span class="q-number">Câu <?= $index + 1 ?>:</span>
                        <span><?= htmlspecialchars($clean_q_text) ?></span>
                    </div>

                    <?php 
                    $options = [
                        'A' => $q['option_a'],
                        'B' => $q['option_b'],
                        'C' => $q['option_c'],
                        'D' => $q['option_d']
                    ];
                    foreach ($options as $key => $text):
                        $checked = ($user_choice === $key) ? 'checked' : '';
                        $class = '';
                        if ($score !== null) {
                            if ($key === $correct) {
                                $class = 'is-correct';
                            } elseif ($user_choice === $key && $key !== $correct) {
                                $class = 'is-wrong';
                            }
                        }
                    ?>
                        <label class="option-label <?= $class ?>">
                            <input type="radio" name="q_<?= $qid ?>" value="<?= $key ?>" required <?= $checked ?> <?= ($score !== null) ? 'disabled' : '' ?>>
                            <span><strong><?= $key ?>.</strong> <?= htmlspecialchars($text) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($score === null): ?>
                <button type="submit" class="submit-btn">Nộp Bài & Chấm Điểm Tự Động</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <div class="footer">
        <p>Hệ thống Triển khai & Quản trị Website Thi Trắc Nghiệm • Nginx Reverse Proxy • Docker Compose</p>
    </div>
</div>
</body>
</html>
