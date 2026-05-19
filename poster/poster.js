// ================================================================
// notewarehouse — İstanbul Gelişim Üniversitesi Proje Sergi Posteri
// A1 DİKEY (594 × 841 mm = 23.39" × 33.11")
// ================================================================

const pptxgen = require("pptxgenjs");

const pres = new pptxgen();

// A1 dikey custom layout
pres.defineLayout({ name: "A1_PORTRAIT", width: 23.39, height: 33.11 });
pres.layout = "A1_PORTRAIT";
pres.author = "Mikail Çelik & Mustafa Kabataş";
pres.title = "notewarehouse - İGÜ Proje Sergisi 2026";

const slide = pres.addSlide();

// Renk paleti
const C = {
    indigo:   "4F46E5",
    purple:   "6D28D9",
    pink:     "EC4899",
    dark:     "0F172A",
    slate:    "1E293B",
    slateMid: "475569",
    slateLt:  "94A3B8",
    bg:       "F8FAFC",
    bgAlt:    "F1F5F9",
    border:   "E2E8F0",
    white:    "FFFFFF",
    red:      "DC2626",
    green:    "10B981",
    amber:    "F59E0B",
    cyan:     "06B6D4",
};

// Arka plan
slide.background = { color: C.white };

// ===================================================================
// 1) ÜST BANT (header) — y: 0 → 1.6
// ===================================================================
// Mor banner
slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 23.39, h: 1.6,
    fill: { color: C.indigo },
    line: { type: "none" }
});

// Sağ üst köşede daha koyu mor accent
slide.addShape(pres.shapes.RECTANGLE, {
    x: 15.5, y: 0, w: 7.89, h: 1.6,
    fill: { color: C.purple },
    line: { type: "none" }
});

// İGÜ logo placeholder (sol)
slide.addShape(pres.shapes.OVAL, {
    x: 0.7, y: 0.25, w: 1.1, h: 1.1,
    fill: { color: C.white },
    line: { type: "none" }
});
slide.addText("İGÜ", {
    x: 0.7, y: 0.25, w: 1.1, h: 1.1,
    fontSize: 32, bold: true, color: C.red,
    align: "center", valign: "middle",
    fontFace: "Calibri"
});

// Üniversite adı (orta)
slide.addText("İSTANBUL GELİŞİM ÜNİVERSİTESİ", {
    x: 2.2, y: 0.25, w: 13, h: 0.7,
    fontSize: 32, bold: true, color: C.white,
    align: "left", valign: "middle",
    fontFace: "Calibri", charSpacing: 2, margin: 0
});
slide.addText("Bilgisayar Mühendisliği  •  İnternet Programcılığı II", {
    x: 2.2, y: 0.85, w: 13, h: 0.5,
    fontSize: 18, color: "CADCFC",
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});

// Yıl (sağ)
slide.addText("2026", {
    x: 19.5, y: 0.2, w: 3.4, h: 1.2,
    fontSize: 64, bold: true, color: C.white,
    align: "center", valign: "middle",
    fontFace: "Calibri", charSpacing: 4
});
slide.addText("PROJE SERGİSİ", {
    x: 19.5, y: 1.05, w: 3.4, h: 0.4,
    fontSize: 12, bold: true, color: "FBCFE8",
    align: "center", valign: "middle",
    fontFace: "Calibri", charSpacing: 6
});

// ===================================================================
// 2) BAŞLIK BÖLÜMÜ — y: 1.6 → 7.2
// ===================================================================
// Logo simgesi (kitap)
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 8.5, y: 2.0, w: 1.6, h: 1.6,
    fill: { color: C.indigo },
    line: { type: "none" },
    rectRadius: 0.3
});
slide.addText("📚", {
    x: 8.5, y: 2.0, w: 1.6, h: 1.6,
    fontSize: 72, color: C.white,
    align: "center", valign: "middle"
});

// "notewarehouse" - dev başlık
slide.addText("notewarehouse", {
    x: 10.2, y: 1.9, w: 12, h: 1.9,
    fontSize: 110, bold: true, color: C.indigo,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});

// Alt başlık
slide.addText("AI Destekli Öğrenci Not Paylaşım Platformu", {
    x: 1, y: 3.9, w: 21.39, h: 0.7,
    fontSize: 36, color: C.slate,
    align: "center", valign: "middle",
    fontFace: "Calibri", italic: true, margin: 0
});

// Web URL pill
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 8.5, y: 4.8, w: 6.4, h: 0.8,
    fill: { color: C.dark },
    line: { type: "none" },
    rectRadius: 0.4
});
slide.addText("🌐  notewarehouse.com", {
    x: 8.5, y: 4.8, w: 6.4, h: 0.8,
    fontSize: 22, bold: true, color: C.white,
    align: "center", valign: "middle",
    fontFace: "Calibri", margin: 0
});

// Ayırıcı çizgi
slide.addShape(pres.shapes.LINE, {
    x: 4, y: 6.0, w: 15.39, h: 0,
    line: { color: C.border, width: 2 }
});

// ===================================================================
// 3) GELİŞTİRİCİLER — y: 6.2 → 8.2
// ===================================================================
slide.addText("👨‍💻 GELİŞTİRİCİLER", {
    x: 0, y: 6.2, w: 23.39, h: 0.5,
    fontSize: 20, bold: true, color: C.slateLt,
    align: "center", valign: "middle",
    fontFace: "Calibri", charSpacing: 8
});

// Mikail kart
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 2.5, y: 6.8, w: 8.8, h: 1.6,
    fill: { color: C.bg },
    line: { color: C.indigo, width: 3 },
    rectRadius: 0.2,
    shadow: { type: "outer", color: "000000", blur: 8, offset: 3, angle: 135, opacity: 0.08 }
});
slide.addShape(pres.shapes.OVAL, {
    x: 2.8, y: 7.0, w: 1.2, h: 1.2,
    fill: { color: C.indigo },
    line: { type: "none" }
});
slide.addText("MÇ", {
    x: 2.8, y: 7.0, w: 1.2, h: 1.2,
    fontSize: 32, bold: true, color: C.white,
    align: "center", valign: "middle",
    fontFace: "Calibri"
});
slide.addText("Mikail ÇELİK", {
    x: 4.2, y: 7.0, w: 7, h: 0.6,
    fontSize: 28, bold: true, color: C.slate,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("Backend & DevOps", {
    x: 4.2, y: 7.55, w: 7, h: 0.4,
    fontSize: 16, color: C.indigo, bold: true,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("PHP • MySQL • AWS • AI Integration", {
    x: 4.2, y: 7.95, w: 7, h: 0.4,
    fontSize: 13, color: C.slateMid,
    align: "left", valign: "middle",
    fontFace: "Calibri", italic: true, margin: 0
});

// Mustafa kart
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 12.09, y: 6.8, w: 8.8, h: 1.6,
    fill: { color: C.bg },
    line: { color: C.purple, width: 3 },
    rectRadius: 0.2,
    shadow: { type: "outer", color: "000000", blur: 8, offset: 3, angle: 135, opacity: 0.08 }
});
slide.addShape(pres.shapes.OVAL, {
    x: 12.39, y: 7.0, w: 1.2, h: 1.2,
    fill: { color: C.purple },
    line: { type: "none" }
});
slide.addText("MK", {
    x: 12.39, y: 7.0, w: 1.2, h: 1.2,
    fontSize: 32, bold: true, color: C.white,
    align: "center", valign: "middle",
    fontFace: "Calibri"
});
slide.addText("Mustafa KABATAŞ", {
    x: 13.79, y: 7.0, w: 7, h: 0.6,
    fontSize: 28, bold: true, color: C.slate,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("Frontend & UX", {
    x: 13.79, y: 7.55, w: 7, h: 0.4,
    fontSize: 16, color: C.purple, bold: true,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("HTML5 • Tailwind • JavaScript • UI/UX", {
    x: 13.79, y: 7.95, w: 7, h: 0.4,
    fontSize: 13, color: C.slateMid,
    align: "left", valign: "middle",
    fontFace: "Calibri", italic: true, margin: 0
});

// ===================================================================
// 4) ÖZET — y: 8.7 → 11.5
// ===================================================================
slide.addShape(pres.shapes.RECTANGLE, {
    x: 1.2, y: 8.7, w: 0.15, h: 2.5,
    fill: { color: C.indigo },
    line: { type: "none" }
});
slide.addText("📋 ÖZET", {
    x: 1.6, y: 8.7, w: 10, h: 0.55,
    fontSize: 26, bold: true, color: C.indigo,
    align: "left", valign: "middle",
    fontFace: "Calibri", charSpacing: 4, margin: 0
});

slide.addText(
    "Türkiye'deki üniversite öğrencilerinin kaliteli ders notuna ulaşımındaki zorluğu çözmek için geliştirilmiş, " +
    "yapay zeka destekli ücretsiz bir not paylaşım platformudur. PHP 8, MySQL veritabanı ve modern web teknolojileri " +
    "kullanılarak AWS bulut altyapısında canlıya alınmıştır. Anthropic Claude API ile entegre çalışan platform; " +
    "otomatik özet çıkarma, öğretmen gibi anlatım yapma ve test sorusu üretme yetenekleriyle donatılmıştır.",
    {
        x: 1.6, y: 9.3, w: 20.6, h: 1.9,
        fontSize: 18, color: C.slate,
        align: "justify", valign: "top",
        fontFace: "Calibri", lineSpacingMultiple: 1.35, margin: 0
    }
);

// ===================================================================
// 5) SAYISAL VERİLER — y: 11.7 → 14.0
// ===================================================================
const stats = [
    { num: "50+", label: "WEB SAYFASI",         color: C.indigo },
    { num: "20+", label: "VERİTABANI TABLOSU",  color: C.purple },
    { num: "14",  label: "ROZET SİSTEMİ",       color: C.pink   },
    { num: "100+", label: "DERS DESTEĞİ",       color: C.cyan   },
];

stats.forEach((s, i) => {
    const x = 1.2 + i * 5.3;
    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: 11.7, w: 4.9, h: 2.3,
        fill: { color: s.color },
        line: { type: "none" },
        rectRadius: 0.2,
        shadow: { type: "outer", color: "000000", blur: 10, offset: 4, angle: 135, opacity: 0.15 }
    });
    slide.addText(s.num, {
        x: x, y: 11.8, w: 4.9, h: 1.4,
        fontSize: 76, bold: true, color: C.white,
        align: "center", valign: "middle",
        fontFace: "Calibri", margin: 0
    });
    slide.addText(s.label, {
        x: x, y: 13.2, w: 4.9, h: 0.55,
        fontSize: 14, bold: true, color: C.white,
        align: "center", valign: "middle",
        fontFace: "Calibri", charSpacing: 4, margin: 0
    });
});

// ===================================================================
// 6) ÖZELLİKLER (2x3 grid) — y: 14.4 → 20.8
// ===================================================================
slide.addShape(pres.shapes.RECTANGLE, {
    x: 1.2, y: 14.4, w: 0.15, h: 0.55,
    fill: { color: C.indigo },
    line: { type: "none" }
});
slide.addText("⚡ ÖNE ÇIKAN ÖZELLİKLER", {
    x: 1.6, y: 14.4, w: 20, h: 0.55,
    fontSize: 26, bold: true, color: C.indigo,
    align: "left", valign: "middle",
    fontFace: "Calibri", charSpacing: 4, margin: 0
});

const features = [
    { icon: "🤖", title: "AI Asistan",        desc: "Özet çıkar, öğretmen gibi anlat, otomatik test sorusu üret. Anthropic Claude API entegre.", color: C.indigo },
    { icon: "📚", title: "100+ Ders",         desc: "İlkokul, Ortaokul, Lise, Üniversite — 4 seviye, kategorize edilmiş zengin içerik.",         color: C.purple },
    { icon: "✏️", title: "Quiz Çözücü",       desc: "Çoktan seçmeli testleri gerçek sınav arayüzünde çöz, anlık puan al, geçmişi takip et.",   color: C.pink   },
    { icon: "👥", title: "Çalışma Grupları",  desc: "Arkadaşlarınla ortak notlar yazın, birlikte hazırlanın, davetler gönder.",                   color: C.cyan   },
    { icon: "🏆", title: "XP & Rozetler",     desc: "Her aksiyon XP kazandırır. 14 farklı rozet, streak takibi, liderlik tablosu.",              color: C.amber  },
    { icon: "🎯", title: "Canlı Sınav",       desc: "Belirlenen saatte tüm site katılır, sonunda sıralama açıklanır, rekabet ortamı.",            color: C.green  },
];

features.forEach((f, i) => {
    const col = i % 3;
    const row = Math.floor(i / 3);
    const x = 1.2 + col * 7.05;
    const y = 15.15 + row * 2.85;

    // Kart
    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: y, w: 6.7, h: 2.55,
        fill: { color: C.white },
        line: { color: C.border, width: 1.5 },
        rectRadius: 0.15,
        shadow: { type: "outer", color: "000000", blur: 6, offset: 2, angle: 135, opacity: 0.07 }
    });
    // Sol renkli accent bar
    slide.addShape(pres.shapes.RECTANGLE, {
        x: x, y: y, w: 0.18, h: 2.55,
        fill: { color: f.color },
        line: { type: "none" }
    });
    // İkon
    slide.addShape(pres.shapes.OVAL, {
        x: x + 0.45, y: y + 0.3, w: 1.1, h: 1.1,
        fill: { color: f.color },
        line: { type: "none" }
    });
    slide.addText(f.icon, {
        x: x + 0.45, y: y + 0.3, w: 1.1, h: 1.1,
        fontSize: 38, color: C.white,
        align: "center", valign: "middle"
    });
    // Başlık
    slide.addText(f.title, {
        x: x + 1.75, y: y + 0.35, w: 4.75, h: 0.55,
        fontSize: 24, bold: true, color: C.slate,
        align: "left", valign: "middle",
        fontFace: "Calibri", margin: 0
    });
    // Açıklama
    slide.addText(f.desc, {
        x: x + 0.45, y: y + 1.55, w: 6.05, h: 0.95,
        fontSize: 13, color: C.slateMid,
        align: "left", valign: "top",
        fontFace: "Calibri", lineSpacingMultiple: 1.25, margin: 0
    });
});

// ===================================================================
// 7) KULLANILAN TEKNOLOJİLER — y: 21.2 → 25.5
// ===================================================================
slide.addShape(pres.shapes.RECTANGLE, {
    x: 1.2, y: 21.2, w: 0.15, h: 0.55,
    fill: { color: C.indigo },
    line: { type: "none" }
});
slide.addText("⚙️ KULLANILAN TEKNOLOJİLER", {
    x: 1.6, y: 21.2, w: 20, h: 0.55,
    fontSize: 26, bold: true, color: C.indigo,
    align: "left", valign: "middle",
    fontFace: "Calibri", charSpacing: 4, margin: 0
});

const tech = [
    { cat: "BACKEND",  items: ["PHP 8.x", "MySQL 8", "PDO + Prepared Statements"], color: C.indigo },
    { cat: "FRONTEND", items: ["HTML5, CSS3", "Tailwind CSS", "Vanilla JavaScript"], color: C.purple },
    { cat: "AI",       items: ["Anthropic Claude API", "Otomatik özet & quiz üretimi", "Doğal dil işleme"], color: C.pink },
    { cat: "BULUT",    items: ["AWS EC2 (Ubuntu)", "Apache 2.4", "Let's Encrypt SSL"], color: C.cyan },
    { cat: "PAKET",    items: ["PHPMailer (e-posta)", "Dompdf (PDF üretimi)", "Google + Facebook OAuth"], color: C.green },
    { cat: "ARAÇLAR",  items: ["Git / GitHub", "Composer", "VS Code"], color: C.amber },
];

tech.forEach((t, i) => {
    const col = i % 3;
    const row = Math.floor(i / 3);
    const x = 1.2 + col * 7.05;
    const y = 21.95 + row * 1.85;

    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x, y: y, w: 6.7, h: 1.6,
        fill: { color: C.bg },
        line: { color: C.border, width: 1 },
        rectRadius: 0.12
    });
    // Kategori etiketi
    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: x + 0.25, y: y + 0.2, w: 1.6, h: 0.45,
        fill: { color: t.color },
        line: { type: "none" },
        rectRadius: 0.2
    });
    slide.addText(t.cat, {
        x: x + 0.25, y: y + 0.2, w: 1.6, h: 0.45,
        fontSize: 12, bold: true, color: C.white,
        align: "center", valign: "middle",
        fontFace: "Calibri", charSpacing: 4, margin: 0
    });
    // Liste
    slide.addText(
        t.items.map((it, idx) => ({
            text: "• " + it,
            options: { breakLine: idx < t.items.length - 1 }
        })),
        {
            x: x + 0.25, y: y + 0.75, w: 6.2, h: 0.8,
            fontSize: 12, color: C.slate,
            align: "left", valign: "top",
            fontFace: "Calibri", lineSpacingMultiple: 1.2, margin: 0
        }
    );
});

// ===================================================================
// 8) MİMARİ DİYAGRAM — y: 25.9 → 29.5
// ===================================================================
slide.addShape(pres.shapes.RECTANGLE, {
    x: 1.2, y: 25.9, w: 0.15, h: 0.55,
    fill: { color: C.indigo },
    line: { type: "none" }
});
slide.addText("🏗️ SİSTEM MİMARİSİ", {
    x: 1.6, y: 25.9, w: 20, h: 0.55,
    fontSize: 26, bold: true, color: C.indigo,
    align: "left", valign: "middle",
    fontFace: "Calibri", charSpacing: 4, margin: 0
});

// Diyagram arka plan
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 1.2, y: 26.65, w: 21, h: 2.8,
    fill: { color: C.bgAlt },
    line: { color: C.border, width: 1 },
    rectRadius: 0.15
});

// 4 kutu yan yana
const blocks = [
    { x: 1.7,  label: "👤\nKULLANICI",        sub: "Web Tarayıcı",        color: C.slate  },
    { x: 6.8,  label: "☁️\nAWS EC2",           sub: "Apache + PHP 8",      color: C.indigo },
    { x: 11.9, label: "🗄️\nMySQL DB",         sub: "20+ Tablo",           color: C.purple },
    { x: 17.0, label: "🤖\nClaude API",       sub: "Anthropic AI",        color: C.pink   },
];

blocks.forEach((b) => {
    slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
        x: b.x, y: 27.05, w: 4.5, h: 2,
        fill: { color: b.color },
        line: { type: "none" },
        rectRadius: 0.15,
        shadow: { type: "outer", color: "000000", blur: 8, offset: 3, angle: 135, opacity: 0.12 }
    });
    slide.addText(b.label, {
        x: b.x, y: 27.15, w: 4.5, h: 1.3,
        fontSize: 22, bold: true, color: C.white,
        align: "center", valign: "middle",
        fontFace: "Calibri", margin: 0
    });
    slide.addText(b.sub, {
        x: b.x, y: 28.45, w: 4.5, h: 0.45,
        fontSize: 12, color: C.white,
        align: "center", valign: "middle",
        fontFace: "Calibri", italic: true, margin: 0
    });
});

// Oklar arası (3 ok)
const arrowsY = 27.95;
for (let k = 0; k < 3; k++) {
    const ax = 6.2 + k * 5.1;
    slide.addText("➜", {
        x: ax, y: arrowsY - 0.4, w: 0.6, h: 0.8,
        fontSize: 36, bold: true, color: C.slateMid,
        align: "center", valign: "middle",
        fontFace: "Calibri", margin: 0
    });
}

// ===================================================================
// 9) ALT BANT (footer) — y: 29.8 → 33.11
// ===================================================================
slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 30.0, w: 23.39, h: 3.11,
    fill: { color: C.dark },
    line: { type: "none" }
});

// Sol: URL
slide.addText("🌐", {
    x: 0.8, y: 30.5, w: 0.8, h: 1.0,
    fontSize: 40, color: C.indigo,
    align: "center", valign: "middle"
});
slide.addText("notewarehouse.com", {
    x: 1.6, y: 30.55, w: 7, h: 0.55,
    fontSize: 24, bold: true, color: C.white,
    align: "left", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("Türkiye'nin AI destekli not platformu", {
    x: 1.6, y: 31.1, w: 7, h: 0.4,
    fontSize: 13, color: C.slateLt,
    align: "left", valign: "middle",
    fontFace: "Calibri", italic: true, margin: 0
});

// Orta: Slogan
slide.addText("\"Bilgi paylaşılınca çoğalır.\"", {
    x: 8, y: 30.5, w: 7.4, h: 1.2,
    fontSize: 26, bold: true, color: C.pink,
    align: "center", valign: "middle",
    fontFace: "Calibri", italic: true, margin: 0
});

// Sağ: QR placeholder / Yıl
slide.addShape(pres.shapes.ROUNDED_RECTANGLE, {
    x: 16.0, y: 30.3, w: 6.7, h: 1.5,
    fill: { color: C.indigo },
    line: { type: "none" },
    rectRadius: 0.15
});
slide.addText("© 2026 notewarehouse", {
    x: 16.0, y: 30.4, w: 6.7, h: 0.6,
    fontSize: 22, bold: true, color: C.white,
    align: "center", valign: "middle",
    fontFace: "Calibri", margin: 0
});
slide.addText("İstanbul Gelişim Üniversitesi — Bilgisayar Mühendisliği", {
    x: 16.0, y: 31.0, w: 6.7, h: 0.5,
    fontSize: 12, color: "CADCFC",
    align: "center", valign: "middle",
    fontFace: "Calibri", margin: 0
});

// Alt küçük yazı
slide.addText("Mikail ÇELİK & Mustafa KABATAŞ  •  İnternet Programcılığı II  •  2025-2026 Bahar Dönemi", {
    x: 0, y: 32.3, w: 23.39, h: 0.5,
    fontSize: 13, color: C.slateLt,
    align: "center", valign: "middle",
    fontFace: "Calibri", italic: true, charSpacing: 2
});

// ===================================================================
// Kaydet
// ===================================================================
pres.writeFile({ fileName: "notewarehouse_poster_a1.pptx" })
    .then(fn => console.log("✅ Oluşturuldu: " + fn));
