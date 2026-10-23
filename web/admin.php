<?php
session_start();
$conn = @new mysqli('db', getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), getenv('MYSQL_DATABASE'));
if (isset($_POST['login']) && $_POST['user'] === 'admin' && $_POST['pass'] === 'admin123') $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }
if (isset($_SESSION['admin']) && !$conn->connect_error) {
    if (isset($_POST['add'])) {
        $stmt = $conn->prepare("INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $_POST['q'], $_POST['a'], $_POST['b'], $_POST['c'], $_POST['d'], $_POST['correct']);
        $stmt->execute();
    }
    if (isset($_GET['del'])) {
        $id = (int)$_GET['del'];
        $conn->query("DELETE FROM questions WHERE id=$id");
        header("Location: admin.php"); exit;
    }
}
$questions = $conn->connect_error ? [] : $conn->query("SELECT * FROM questions");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><title>Quản Trị Đề Thi (CRUD)</title>
<style>
body { background:#0f172a; color:#f8fafc; font-family:sans-serif; padding:30px; }
.box { max-width:750px; margin:auto; background:#1e293b; padding:25px; border-radius:12px; }
input, select { width:100%; padding:8px; margin:6px 0 12px; border-radius:5px; border:1px solid #475569; background:#0f172a; color:white; box-sizing:border-box; }
.btn { background:#10b981; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer; text-decoration:none; font-weight:bold; }
</style>
</head>
<body>
<div class="box">
<?php if (!isset($_SESSION['admin'])): ?>
    <h2>Đăng Nhập Quản Trị Viên</h2>
    <form method="POST">
        <label>Tài khoản</label><input type="text" name="user" required>
        <label>Mật khẩu</label><input type="password" name="pass" required>
        <button type="submit" name="login" class="btn">Đăng Nhập</button>
    </form>
<?php else: ?>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Bảng Điều Khiển Quản Trị (CRUD Content)</h2>
        <div><a href="/index.php" style="color:#38bdf8; margin-right:15px;">Xem Website</a><a href="?logout=1" style="color:#ef4444;">Đăng xuất</a></div>
    </div>
    <form method="POST" style="background:#334155; padding:15px; border-radius:8px; margin-bottom:20px;">
        <h3 style="color:#f59e0b; margin-top:0;">Thêm Câu Hỏi Trắc Nghiệm Mới</h3>
        <input type="text" name="q" placeholder="Nội dung câu hỏi..." required>
        <input type="text" name="a" placeholder="Đáp án A" required>
        <input type="text" name="b" placeholder="Đáp án B" required>
        <input type="text" name="c" placeholder="Đáp án C" required>
        <input type="text" name="d" placeholder="Đáp án D" required>
        <select name="correct"><option value="A">Đáp án đúng: A</option><option value="B">Đáp án đúng: B</option><option value="C">Đáp án đúng: C</option><option value="D">Đáp án đúng: D</option></select>
        <button type="submit" name="add" class="btn">+ Thêm Câu Hỏi</button>
    </form>
    <h3>Danh Sách Câu Hỏi Đang Hiển Thị</h3>
    <?php if (is_object($questions)): foreach ($questions as $q): ?>
        <div style="background:#334155; padding:12px; border-radius:6px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
            <div><strong style="color:#38bdf8;"><?= htmlspecialchars($q['question_text']) ?></strong> (Đáp án: <?= $q['correct_option'] ?>)</div>
            <a href="?del=<?= $q['id'] ?>" class="btn" style="background:#ef4444;">Xóa</a>
        </div>
    <?php endforeach; endif; ?>
<?php endif; ?>
</div>
</body>
</html>
