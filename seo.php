<?php
/**
 * SEO Yardımcısı - her sayfanın head bölümünde include edilir.
 * Kullanım:
 *   <?php require_once 'seo.php'; seoMeta('Sayfa Başlığı', 'Sayfa açıklaması'); ?>
 */

function seoMeta($title = '', $description = '', $image = '') {
    $siteAdi = 'notewarehouse';
    $siteUrl = 'https://notewarehouse.com';

    // Mevcut sayfa URL'si (canonical için)
    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
    $cleanPath = strtok($currentPath, '?');
    $canonical = $siteUrl . $cleanPath;

    // Title
    $fullTitle = $title
        ? htmlspecialchars($title) . ' | notewarehouse'
        : 'notewarehouse - Ücretsiz Ders Notu Paylaşım Platformu';

    // Description
    $desc = $description
        ?: 'notewarehouse; üniversite, lise, ortaokul ve ilkokul öğrencilerinin ders notlarını ücretsiz paylaştığı, yapay zeka destekli not deposu platformudur.';
    $desc = htmlspecialchars($desc);

    // Image
    $img = $image ?: ($siteUrl . '/og-image.png');

    echo "    <title>$fullTitle</title>\n";
    echo "    <meta name=\"description\" content=\"$desc\">\n";
    echo "    <meta name=\"robots\" content=\"index, follow, max-image-preview:large\">\n";
    echo "    <meta name=\"language\" content=\"Turkish\">\n";
    echo "    <link rel=\"canonical\" href=\"$canonical\">\n";

    // Favicon
    echo "    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon-32.png\">\n";
    echo "    <link rel=\"icon\" type=\"image/png\" sizes=\"180x180\" href=\"/favicon-180.png\">\n";
    echo "    <link rel=\"apple-touch-icon\" href=\"/favicon-180.png\">\n";

    // Open Graph
    echo "    <meta property=\"og:type\" content=\"website\">\n";
    echo "    <meta property=\"og:site_name\" content=\"$siteAdi\">\n";
    echo "    <meta property=\"og:title\" content=\"$fullTitle\">\n";
    echo "    <meta property=\"og:description\" content=\"$desc\">\n";
    echo "    <meta property=\"og:url\" content=\"$canonical\">\n";
    echo "    <meta property=\"og:image\" content=\"$img\">\n";
    echo "    <meta property=\"og:locale\" content=\"tr_TR\">\n";

    // Twitter
    echo "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "    <meta name=\"twitter:title\" content=\"$fullTitle\">\n";
    echo "    <meta name=\"twitter:description\" content=\"$desc\">\n";
    echo "    <meta name=\"twitter:image\" content=\"$img\">\n";
}
?>
