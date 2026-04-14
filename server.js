const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const nodemailer = require('nodemailer');
const { OpenAI } = require("openai"); // OpenAI kütüphanesini ekledik
require('dotenv').config(); // En üste ekle

const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static("."));

// API anahtarını kullanırken şifreyi sil ve şunu yaz:
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY, 
});

// Veritabanı Bağlantısı (image_073d13'teki bilgilerine göre)
const db = mysql.createPool({
  host: "localhost",
  user: "root",
  password: "", 
  database: "notdeposu",
  waitForConnections: true,
  connectionLimit: 10
});

// 1. OpenAI Kurulumu (Kendi API anahtarını buraya yapıştır)

// 2. Yapay Zeka Sohbet Rotası
app.post('/askAI', async (req, res) => {
  try {
      const userMessage = req.body.mesaj;

      // OpenAI'ye mesajı gönderiyoruz
      const response = await openai.chat.completions.create({
          model: "gpt-4o-mini", // İstediğin modeli seçebilirsin (gpt-4o-mini de çok hızlıdır)
          messages: [
              { role: "system", content: "Sen NoteShare uygulamasının yardımcı yapay zekasısın. Öğrencilere kısa ve net cevaplar ver." },
              { role: "user", content: userMessage }
          ],
      });

      // Gelen cevabı frontend'e yolla
      res.json({ reply: response.choices[0].message.content });

  } catch (error) {
      console.error("OpenAI Hatası:", error);
      res.status(500).json({ reply: "Üzgünüm, yapay zekaya bağlanırken bir sorun oluştu." });
  }
});

// Bağlantı Testi
db.getConnection((err, connection) => {
  if (err) {
    console.error("❌ MySQL Bağlantı Hatası:", err.message);
  } else {
    console.log("✅ MySQL'e başarıyla bağlanıldı!");
    connection.release();
  }
});

function shareOnWhatsApp() {
    // Sayfa linkini ve not başlığını alıyoruz
    const url = encodeURIComponent(window.location.href);
    const titleText = document.getElementById('title').value || "NoteShare'daki bu nota göz at!";
    const text = encodeURIComponent(titleText + " - ");
    
    // WhatsApp API'sine yönlendir
    window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
}

function shareOnX() {
    const url = encodeURIComponent(window.location.href);
    const titleText = encodeURIComponent(document.getElementById('title').value || "Harika bir not buldum!");
    
    // X (Twitter) paylaşım sayfasına yönlendir
    window.open(`https://twitter.com/intent/tweet?text=${titleText}&url=${url}`, '_blank');
}
// Notları Getir
app.get('/getNotes', (req, res) => {
  db.query("SELECT * FROM notes ORDER BY id DESC", (err, results) => {
    if (err) return res.status(500).send(err);
    res.json(results);
  });
});
function shareOnWhatsApp() {
    // Sayfa linkini ve not başlığını alıyoruz
    const url = encodeURIComponent(window.location.href);
    const titleText = document.getElementById('title').value || "NoteShare'daki bu nota göz at!";
    const text = encodeURIComponent(titleText + " - ");
    
    // WhatsApp web veya uygulama üzerinden paylaşım linki
    window.open(`https://api.whatsapp.com/send?text=${text}${url}`, '_blank');
}

function shareOnX() {
    const url = encodeURIComponent(window.location.href);
    const titleText = encodeURIComponent(document.getElementById('title').value || "Harika bir not buldum!");
    
    // X (Twitter) paylaşım sayfasına yönlendir
    window.open(`https://twitter.com/intent/tweet?text=${titleText}&url=${url}`, '_blank');
}

app.listen(3000, () => {
  console.log("🚀 Server http://localhost:3000 üzerinde aktif.");
});