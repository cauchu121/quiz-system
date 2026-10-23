<?php
\$conn = @new mysqli('db', getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), getenv('MYSQL_DATABASE'));
$db_status =$conn->connect_error ? "Connection failed" : "Connected successfully to Database (quiz_db)";
\$questions = $conn->connect_error ? [] : $conn->query("SELECT * FROM questions");
\$score = null;
\$total = is_object($questions) ?$questions->num_rows : 0;
if (\$_SERVER['REQUEST_METHOD'] === 'POST' && is_object($questions)) {$score = 0;
    foreach ($questions as$q) {
        $qid =$q['id'];
        if (isset($_POST["q_$qid"]) && $_POST["q_$qid"] === $q['correct_option'])$score++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><title>Đề tài 35: Hệ thống Quiz</title>
<style>
body { background:#0f172a; color:#f8fafc; font-family:sans-serif; padding:30px; }
.container { max-width:800px; margin:auto; background:#1e293b; padding:25px; border-radius:12px; }
.status { background:#064e3b; color:#6ee7b7; padding:10px; border-radius:6px; margin:15px 0; }
.card { background:#334155; padding:15px; border-radius:8px; margin-bottom:15px; }
.btn { background:#0ea5e9; color:white; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; text-decoration:none; font-weight:bold; }
.result { background:#1d4ed8; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold; }
</style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <span style="color:#38bdf8; font-size:13px;">Đề tài 35 - Triển khai & Quản trị Hệ thống</span>
            <h2 style="margin:5px 0;">Lê Đắc Lộc - Hệ Thống Quiz / Thi Trắc Nghiệm</h2>
            <p style="margin:0; color:#94a3b8;">MSV: DTC245180150 | Lớp: KTPM K23C</p>
        </div>
        <a href="/admin.php" class="btn">Vào Trang Quản Trị →</a>
    </div>
    <div class="status">Trạng thái Cơ sở dữ liệu: <?= \$db_status ?></div>
    <?php if (\$score !== null): ?>
        <div class="result">Kết quả chấm điểm tự động: Đúng <?= \$score ?> / <?= $total ?> câu (<?= $total > 0 ? round(($score/$total)*10, 2) : 0 ?> điểm)</div>
    <?php endif; ?>
    <h3>Bài Thi Trắc Nghiệm Trực Tuyến (Dynamic DB Content)</h3>
    <form method="POST">
        <?php if (is_object($questions)): foreach ($questions as $index =>$q): ?>
            <div class="card">
                <p style="margin-top:0; color:#f59e0b;"><strong>Câu <?= $index + 1 ?>: <?= htmlspecialchars($q['question_text']) ?></strong></p>
                <?php foreach (['A'=>'option_a', 'B'=>'option_b', 'C'=>'option_c', 'D'=>'option_d'] as $key =>$col): ?>
                    <label style="display:block; margin:6px 0;"><input type="radio" name="q_<?= \$q['id'] ?>" value="<?= $key ?>" required> <?= $key ?>. <?= htmlspecialchars($q[$col]) ?></label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; endif; ?>
        <button type="submit" class="btn" style="background:#10b981;">Nộp Bài & Chấm Điểm Tự Động</button>
    </form>
</div>
</body>
</html>
