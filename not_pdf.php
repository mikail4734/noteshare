<?php
/**
 * Not PDF Üretici
 *
 * Kullanım: not_pdf.php?id=NOTE_ID
 * Çıktı: Profesyonel PDF (kapak + içerik + filigran + sayfa numaraları)
 */

require_once __DIR__ . '/oturum_baslat.php';
require_once __DIR__ . '/baglan.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$noteId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$noteId) {
    http_response_code(400);
    die("Geçersiz istek. <a href='index.php'>← Anasayfa</a>");
}

// Notu çek
$sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
$sorgu->execute([$noteId]);
$not = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$not) {
    http_response_code(404);
    die("Not bulunamadı. <a href='index.php'>← Anasayfa</a>");
}

// Yazar bilgisi
$yazarAd = $not['author'] ?? 'Anonim';
if (!empty($not['kullanici_email'])) {
    $u = $db->prepare("SELECT ad FROM users WHERE email = ?");
    $u->execute([$not['kullanici_email']]);
    $yazarRow = $u->fetch(PDO::FETCH_ASSOC);
    if ($yazarRow) $yazarAd = $yazarRow['ad'];
}

// Kim indiriyor (filigran için)
$indirenAd = $_SESSION['user_name'] ?? 'Misafir';

// PDF için veriler
$baslik    = htmlspecialchars($not['title'] ?? 'Başlıksız Not');
$icerik    = $not['content'] ?? '';
$kategori  = htmlspecialchars($not['category'] ?? 'Genel');
$seviye    = htmlspecialchars($not['edu_level'] ?? 'Belirtilmemiş');
$ders      = htmlspecialchars($not['subject'] ?? 'Genel');
$tarih     = date('d F Y', strtotime($not['created_at']));
$yazar     = htmlspecialchars($yazarAd);
$indiren   = htmlspecialchars($indirenAd);

// HTML şablonu
$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 50px 40px 70px 40px; }
    body { font-family: "DejaVu Sans", sans-serif; color: #1e293b; line-height: 1.6; font-size: 11pt; }

    /* KAPAK */
    .kapak {
        text-align: center;
        page-break-after: always;
        padding-top: 60px;
    }
    .kapak .logo-wrap {
        background: #4f46e5;
        color: white;
        padding: 30px 20px;
        border-radius: 12px;
        margin: 0 auto 50px auto;
        width: 70%;
    }
    .kapak .logo {
        font-size: 30pt;
        font-weight: 900;
        margin: 0;
    }
    .kapak .subtitle {
        font-size: 10pt;
        margin-top: 8px;
        opacity: 0.9;
    }
    .kapak .badge {
        display: inline-block;
        background: #ede9fe;
        color: #6d28d9;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 30px;
    }
    .kapak h1 {
        font-size: 26pt;
        color: #1e293b;
        font-weight: 900;
        margin: 0 50px 50px 50px;
        line-height: 1.3;
    }
    .kapak .info-box {
        background: #f8fafc;
        border-left: 4px solid #4f46e5;
        padding: 25px 35px;
        margin: 40px 50px;
        text-align: left;
        font-size: 11pt;
    }
    .kapak .info-box p {
        margin: 8px 0;
        color: #475569;
    }
    .kapak .info-box strong {
        color: #1e293b;
        display: inline-block;
        width: 130px;
    }
    .kapak .footer-kapak {
        margin-top: 60px;
        font-size: 9pt;
        color: #94a3b8;
        line-height: 1.6;
    }

    /* İÇERİK BAŞLIĞI */
    .icerik-baslik {
        background: #4f46e5;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        font-size: 14pt;
        font-weight: bold;
        margin-bottom: 25px;
    }

    /* İÇERİK */
    .icerik {
        padding: 0 5px;
    }
    .icerik h1, .icerik h2, .icerik h3 {
        color: #1e293b;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
    }
    .icerik h1 { font-size: 18pt; }
    .icerik h2 { font-size: 15pt; color: #4f46e5; }
    .icerik h3 { font-size: 13pt; }
    .icerik p { margin-bottom: 1em; }
    .icerik img { max-width: 100%; height: auto; }
    .icerik blockquote {
        border-left: 4px solid #4f46e5;
        padding: 10px 20px;
        color: #64748b;
        font-style: italic;
        background: #f8fafc;
        margin: 15px 0;
    }
    .icerik strong { color: #1e293b; font-weight: bold; }
    .icerik em { color: #6d28d9; }
    .icerik code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 10pt;
        color: #be185d;
    }
    .icerik pre {
        background: #1e293b;
        color: #f1f5f9;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 10pt;
        overflow-x: auto;
    }
    .icerik ul, .icerik ol {
        margin: 10px 0 15px 30px;
    }
    .icerik li { margin-bottom: 5px; }
    .icerik table {
        border-collapse: collapse;
        width: 100%;
        margin: 15px 0;
    }
    .icerik th, .icerik td {
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
    }
    .icerik th {
        background: #f1f5f9;
        font-weight: bold;
    }

    .meta-bilgi {
        margin-top: 40px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 9pt;
        color: #64748b;
        border: 1px dashed #cbd5e1;
    }
</style>
</head>
<body>

<!-- KAPAK SAYFASI -->
<div class="kapak">
    <div class="logo-wrap">
        <h2 class="logo">notewarehouse</h2>
        <p class="subtitle">Türkiye\'nin Ücretsiz Öğrenci Not Platformu</p>
    </div>

    <span class="badge">' . $kategori . '</span>

    <h1>' . $baslik . '</h1>

    <div class="info-box">
        <p><strong>Yazar:</strong> ' . $yazar . '</p>
        <p><strong>Ders:</strong> ' . $ders . '</p>
        <p><strong>Seviye:</strong> ' . $seviye . '</p>
        <p><strong>Yayın Tarihi:</strong> ' . $tarih . '</p>
        <p><strong>Görüntülenme:</strong> ' . intval($not['goruntulenme']) . ' kez</p>
        <p><strong>Beğeni:</strong> ' . intval($not['likes']) . '</p>
    </div>

    <div class="footer-kapak">
        Bu PDF <strong>notewarehouse.com</strong> tarafından üretilmiştir.<br>
        Eğitim amaçlı ücretsiz dağıtım için hazırlanmıştır.<br>
        ' . date('d F Y H:i') . '
    </div>
</div>

<!-- İÇERİK SAYFASI -->
<div class="icerik-baslik">İçerik</div>

<div class="icerik">
    ' . $icerik . '
</div>

<div class="meta-bilgi">
    <strong>📚 Not Bilgisi:</strong><br>
    Bu not <em>' . $yazar . '</em> tarafından <strong>notewarehouse.com</strong>\'da paylaşılmıştır.<br>
    Orijinal kaynağa erişmek için: <strong>notewarehouse.com</strong><br>
    PDF\'i indiren: <em>' . $indiren . '</em> | Tarih: ' . date('d.m.Y H:i') . '
</div>

</body>
</html>';

// Dompdf konfigürasyonu
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('chroot', __DIR__);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Sayfa numaraları + filigran (kapak hariç tüm sayfalarda)
$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
$canvasWidth = $canvas->get_width();
$canvasHeight = $canvas->get_height();

// Filigran (sol alt)
$canvas->page_text(
    40,
    $canvasHeight - 25,
    '© notewarehouse.com • İndiren: ' . $indiren,
    $font,
    8,
    [0.55, 0.55, 0.55]
);

// Sayfa numarası (sağ alt)
$canvas->page_text(
    $canvasWidth - 90,
    $canvasHeight - 25,
    'Sayfa {PAGE_NUM} / {PAGE_COUNT}',
    $font,
    8,
    [0.55, 0.55, 0.55]
);

// Üst çizgi (her sayfada)
$canvas->page_line(40, 30, $canvasWidth - 40, 30, [0.85, 0.85, 0.85], 0.5);

// PDF'i indir
$dosyaAdi = preg_replace('/[^a-zA-Z0-9-_ÜĞİŞÇÖüğışçö]/u', '-', $not['title']);
$dosyaAdi = substr($dosyaAdi, 0, 60);
$dompdf->stream($dosyaAdi . '.pdf', ['Attachment' => true]);
exit;
