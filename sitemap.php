<?php
/**
 * Dinamik Sitemap Üreteci
 * Erişim: https://notewarehouse.com/sitemap.xml
 * (Apache rewrite ile .xml uzantısı bu dosyaya yönlendirilir)
 */
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/baglan.php';

$siteUrl = "https://notewarehouse.com";
$bugun = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Sabit sayfalar (priority yüksek)
$sabitSayfalar = [
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/universite.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/lise.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/ortaokul.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/ilkokul.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/dersler.php', 'priority' => '0.8', 'changefreq' => 'daily'],
    ['url' => '/en_cok_begenilenler.php', 'priority' => '0.7', 'changefreq' => 'daily'],
    ['url' => '/kaydol.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/giris.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['url' => '/liderlik.php', 'priority' => '0.6', 'changefreq' => 'daily'],
    ['url' => '/canli_sinavlar.php', 'priority' => '0.7', 'changefreq' => 'daily'],
    ['url' => '/sss.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['url' => '/kosullar.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['url' => '/sozlesme.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

foreach ($sabitSayfalar as $sayfa) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($siteUrl . $sayfa['url']) . "</loc>\n";
    echo "    <lastmod>$bugun</lastmod>\n";
    echo "    <changefreq>{$sayfa['changefreq']}</changefreq>\n";
    echo "    <priority>{$sayfa['priority']}</priority>\n";
    echo "  </url>\n";
}

// Tüm notları sitemap'e ekle
try {
    $stmt = $db->query("SELECT id, title, created_at FROM notes ORDER BY id DESC LIMIT 5000");
    while ($not = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lastmod = date('Y-m-d', strtotime($not['created_at']));
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($siteUrl . "/notlar.php?id=" . $not['id']) . "</loc>\n";
        echo "    <lastmod>$lastmod</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
} catch (Exception $e) {}

echo '</urlset>';
?>
