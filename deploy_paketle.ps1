# =============================================
# NoteShare Deployment Paketleme Script (v2 - düzeltilmiş)
# =============================================

$ErrorActionPreference = "Stop"
$proje = $PSScriptRoot
$cikti = "$proje\..\noteshare-deploy.zip"
$gecici = "$env:TEMP\noteshare-build-$(Get-Random)"

Write-Host "`n📦 NoteShare Deployment Paketi Hazırlanıyor..." -ForegroundColor Cyan
Write-Host "Kaynak: $proje" -ForegroundColor DarkGray
Write-Host "Çıktı:  $cikti" -ForegroundColor Yellow

# Geçici klasör oluştur
New-Item -ItemType Directory -Path $gecici -Force | Out-Null

Write-Host "`n📋 Dosyalar kopyalanıyor (gereksizler atlanıyor)..." -ForegroundColor Cyan

# Robocopy direkt çağrı (en güvenilir yöntem)
robocopy $proje $gecici /E `
    /XD node_modules vendor .claude .git .vscode .idea uploads `
    /XF ".env" "*.log" "*.bak" "notdeposu.sql" "deploy_paketle.ps1" "noteshare-key.pem" "noteshare-deploy*.zip" `
    /NFL /NDL /NJH /NJS /NC /NS | Out-Null

# Boş uploads klasörü oluştur
New-Item -ItemType Directory -Path "$gecici\uploads" -Force | Out-Null
New-Item -ItemType File -Path "$gecici\uploads\.gitkeep" -Force | Out-Null

Write-Host "✅ Dosyalar hazır" -ForegroundColor Green

# Eski ZIP varsa sil
if (Test-Path $cikti) { Remove-Item $cikti -Force }

# ZIP oluştur
Write-Host "`n🗜️  ZIP oluşturuluyor..." -ForegroundColor Cyan
Compress-Archive -Path "$gecici\*" -DestinationPath $cikti -CompressionLevel Optimal -Force

# Temizlik
Remove-Item $gecici -Recurse -Force

$zipBoyut = [math]::Round((Get-Item $cikti).Length / 1MB, 2)

Write-Host "`n✅ TAMAMLANDI!" -ForegroundColor Green
Write-Host "📦 Paket: $cikti ($zipBoyut MB)" -ForegroundColor Yellow

Write-Host "`n📤 Sıradaki adımlar:" -ForegroundColor Cyan
Write-Host "   1. Tarayıcıda https://file.io aç"
Write-Host "   2. ZIP'i yükle"
Write-Host "   3. Verilen linki kopyala ve Claude'a yaz"
Write-Host "`n"
