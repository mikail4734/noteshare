const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const nodemailer = require('nodemailer');
const { OpenAI } = require("openai");
require('dotenv').config();

const app = express();

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static("."));

// OpenAI istemcisi — anahtar .env'den alınır
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

const db = mysql.createPool({
  host: process.env.DB_HOST || "localhost",
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASS || "",
  database: process.env.DB_NAME || "notdeposu",
  waitForConnections: true,
  connectionLimit: 10
});

app.post('/askAI', async (req, res) => {
  try {
      const userMessage = req.body.mesaj;
      const userEmail = req.body.email;

      const response = await openai.chat.completions.create({
          model: "gpt-4o-mini",
          messages: [
              { role: "system", content: "Sen NoteShare uygulamasının yardımcı yapay zekasısın. Öğrencilere kısa ve net cevaplar ver." },
              { role: "user", content: userMessage }
          ],
      });

      const botReply = response.choices[0].message.content;

      if (userEmail && userEmail !== "") {
          const sql = "INSERT INTO sohbet_gecmisi (kullanici_email, kullanici_mesaji, bot_cevabi) VALUES (?, ?, ?)";
          db.query(sql, [userEmail, userMessage, botReply], (err, result) => {
              if (err) console.error("Veritabanı kayıt hatası:", err);
          });
      }

      res.json({ reply: botReply });

  } catch (error) {
      console.error("OpenAI Hatası:", error);
      res.status(500).json({ reply: "Üzgünüm, yapay zekaya bağlanırken bir sorun oluştu." });
  }
});

app.post('/getChatHistory', (req, res) => {
    const userEmail = req.body.email;

    if (!userEmail) return res.json([]);

    const sql = "SELECT * FROM sohbet_gecmisi WHERE kullanici_email = ? ORDER BY id DESC LIMIT 20";
    db.query(sql, [userEmail], (err, results) => {
        if (err) {
            console.error("Geçmiş çekme hatası:", err);
            return res.status(500).send(err);
        }
        res.json(results);
    });
});

db.getConnection((err, connection) => {
  if (err) {
    console.error("❌ MySQL Bağlantı Hatası:", err.message);
  } else {
    console.log("✅ MySQL'e başarıyla bağlanıldı!");
    connection.release();
  }
});

app.get('/getNotes', (req, res) => {
  db.query("SELECT * FROM notes ORDER BY id DESC", (err, results) => {
    if (err) return res.status(500).send(err);
    res.json(results);
  });
});

app.listen(3000, () => {
  console.log("🚀 Server http://localhost:3000 üzerinde aktif.");
});
