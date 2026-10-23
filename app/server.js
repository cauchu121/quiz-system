const express = require('express');
const mysql = require('mysql2/promise');
const client = require('prom-client');

const app = express();
app.use(express.json());

// Thu thập metrics mặc định cho Prometheus
const collectDefaultMetrics = client.collectDefaultMetrics;
collectDefaultMetrics({ register: client.register });

// Cấu hình kết nối MySQL pool
const pool = mysql.createPool({
  host: process.env.DB_HOST || 'mysql',
  user: process.env.DB_USER || 'quiz_user',
  password: process.env.DB_PASSWORD || 'quiz_password',
  database: process.env.DB_NAME || 'quiz_db',
  waitForConnections: true,
  connectionLimit: 10
});

// Endpoint metrics cho Prometheus cào dữ liệu
app.get('/metrics', async (req, res) => {
  res.set('Content-Type', client.register.contentType);
  res.end(await client.register.metrics());
});

// Giao diện web làm bài đơn giản
app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html>
    <head><meta charset="utf-8"><title>Hệ thống Quiz</title></head>
    <body style="font-family: Arial; padding: 20px;">
      <h2>Bài Thi Trắc Nghiệm</h2>
      <div id="quiz-container">Đang tải câu hỏi...</div>
      <button onclick="submitQuiz()" style="margin-top:20px; padding: 8px 16px;">Nộp bài</button>
      <h3 id="result"></h3>

      <script>
        let questionsData = [];
        fetch('/api/quizzes/1/questions')
          .then(r => r.json())
          .then(data => {
            questionsData = data;
            const container = document.getElementById('quiz-container');
            container.innerHTML = data.map((q, idx) => \`
              <div style="margin-bottom: 15px;">
                <p><b>Câu \${idx+1}: \${q.content}</b></p>
                <label><input type="radio" name="q_\${q.id}" value="A"> A. \${q.option_a}</label><br>
                <label><input type="radio" name="q_\${q.id}" value="B"> B. \${q.option_b}</label><br>
                <label><input type="radio" name="q_\${q.id}" value="C"> C. \${q.option_c}</label><br>
                <label><input type="radio" name="q_\${q.id}" value="D"> D. \${q.option_d}</label>
              </div>
            \`).join('');
          });

        function submitQuiz() {
          const answers = {};
          questionsData.forEach(q => {
            const selected = document.querySelector(\`input[name="q_\${q.id}"]:checked\`);
            if (selected) answers[q.id] = selected.value;
          });

          fetch('/api/quizzes/1/submit', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ userId: 1, answers })
          })
          .then(r => r.json())
          .then(res => {
            document.getElementById('result').innerText = \`Kết quả: \${res.score} / 10 điểm (Đúng \${res.correctCount}/\${res.total})\`;
          });
        }
      </script>
    </body>
    </html>
  `);
});

// API lấy danh sách câu hỏi theo quiz ID
app.get('/api/quizzes/:id/questions', async (req, res) => {
  try {
    const [rows] = await pool.query('SELECT id, quiz_id, content, option_a, option_b, option_c, option_d FROM questions WHERE quiz_id = ?', [req.params.id]);
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Database error' });
  }
});

// API tự động chấm điểm và lưu kết quả
app.post('/api/quizzes/:id/submit', async (req, res) => {
  const quizId = req.params.id;
  const { userId, answers } = req.body;

  try {
    const [questions] = await pool.query('SELECT id, correct_option FROM questions WHERE quiz_id = ?', [quizId]);
    if (questions.length === 0) return res.status(404).json({ error: 'Quiz not found' });

    let correctCount = 0;
    questions.forEach(q => {
      if (answers && answers[q.id] === q.correct_option) {
        correctCount++;
      }
    });

    const score = Number(((correctCount / questions.length) * 10).toFixed(2));
    await pool.query('INSERT INTO submissions (quiz_id, user_id, score) VALUES (?, ?, ?)', [quizId, userId || 1, score]);

    console.log(`[QUIZ_SUBMIT] User: ${userId || 1} - Quiz: ${quizId} - Score: ${score}`);
    res.json({ score, correctCount, total: questions.length });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Error calculating score' });
  }
});

app.listen(3000, () => console.log('Quiz app listening on port 3000'));
