#!/bin/bash
# ================================================================
# notewarehouse — Sunucu Güncelleme Script'i
# Tek seferde: git safe.directory + pull + SQL + composer + apache
# Kullanim:  sudo bash sunucu_guncelle.sh
# ================================================================

set -e  # Hata olunca dur

# Renkli çıktı
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}=================================================${NC}"
echo -e "${YELLOW}  notewarehouse sunucu guncelleme baslatildi${NC}"
echo -e "${YELLOW}=================================================${NC}"
echo ""

# 1) Git safe.directory (hem root hem ubuntu icin)
echo -e "${GREEN}[1/6]${NC} Git safe.directory ayarlaniyor..."
git config --global --add safe.directory /var/www/html 2>/dev/null || true
sudo -u ubuntu git config --global --add safe.directory /var/www/html 2>/dev/null || true

# 2) Proje dizinine geç
cd /var/www/html
echo -e "${GREEN}[2/6]${NC} /var/www/html dizinindeyiz"

# 3) GitHub'dan son state'i fetch et + force reset (cakisma olmaz)
echo -e "${GREEN}[3/6]${NC} GitHub'dan son kod cekiliyor (force sync)..."
git fetch origin main
git reset --hard origin/main
echo -e "      ${GREEN}OK${NC} Son commit: $(git log --oneline -1)"

# 5) MySQL'de eksik tablolari ekle
echo -e "${GREEN}[4/6]${NC} Veritabani tablolari kontrol ediliyor..."
if [ -f "eksik_tablolar.sql" ]; then
    # .env'den DB adini cek
    DB_NAME=$(grep -E "^DB_NAME=" .env 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | tr -d ' ')
    DB_NAME=${DB_NAME:-notdeposu}  # varsayilan notdeposu
    DB_USER=$(grep -E "^DB_USER=" .env 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | tr -d ' ')
    DB_USER=${DB_USER:-root}
    DB_PASS=$(grep -E "^DB_PASS=" .env 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | tr -d ' ')

    echo -e "      DB: $DB_NAME, User: $DB_USER"

    if [ -n "$DB_PASS" ]; then
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < eksik_tablolar.sql && \
            echo -e "      ${GREEN}OK${NC} Eksik tablolar eklendi (varsa)"
    else
        sudo mysql "$DB_NAME" < eksik_tablolar.sql && \
            echo -e "      ${GREEN}OK${NC} Eksik tablolar eklendi (varsa)"
    fi
else
    echo -e "      ${YELLOW}Atlandi${NC} (eksik_tablolar.sql bulunamadi)"
fi

# 6) Gerekli PHP extension'lari kontrol et
echo -e "${GREEN}[5a/6]${NC} PHP extension'lari kontrol ediliyor..."
NEEDED_EXTS="gd mbstring curl xml zip"
MISSING_EXTS=""
for ext in $NEEDED_EXTS; do
    if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
        MISSING_EXTS="$MISSING_EXTS php-${ext}"
    fi
done
if [ -n "$MISSING_EXTS" ]; then
    echo -e "      ${YELLOW}Eksik:${NC}$MISSING_EXTS - kuruluyor..."
    sudo apt-get install -y $MISSING_EXTS
    sudo systemctl restart apache2
fi

# 6b) Composer install (Dompdf, Google API, PHPMailer)
echo -e "${GREEN}[5b/6]${NC} Composer paketleri yukleniyor..."
if [ -f "composer.json" ]; then
    # vendor yoksa veya autoload eksikse, temizden basla
    if [ ! -f "vendor/autoload.php" ] || [ ! -d "vendor/composer" ]; then
        echo -e "      ${YELLOW}vendor bozuk veya eksik, temizleniyor...${NC}"
        sudo rm -rf vendor composer.lock
    fi

    # GERCEK exit code ile composer calistir (tail trick'i kullanma!)
    COMPOSER_OK=0
    if sudo -u www-data composer install --no-interaction --no-dev --optimize-autoloader; then
        COMPOSER_OK=1
    elif sudo composer install --no-interaction --no-dev --optimize-autoloader; then
        COMPOSER_OK=1
    fi

    if [ $COMPOSER_OK -eq 0 ]; then
        echo -e "      ${RED}HATA: Composer install basarisiz!${NC}"
        echo -e "      ${RED}Manuel kontrol:${NC} cd /var/www/html && sudo composer install --no-dev"
        exit 1
    fi

    # autoload.php gercekten olusturuldu mu dogrula
    if [ ! -f "vendor/autoload.php" ]; then
        echo -e "      ${RED}HATA: vendor/autoload.php olusturulamadi!${NC}"
        exit 1
    fi

    echo -e "      ${GREEN}OK${NC} vendor/autoload.php var ($(stat -c '%s' vendor/autoload.php) byte)"
else
    echo -e "      ${YELLOW}Atlandi${NC} (composer.json yok)"
fi

# 7) Izinleri duzelt
echo -e "${GREEN}[6/6]${NC} Dosya izinleri duzeltiliyor..."
sudo chown -R www-data:www-data /var/www/html
sudo find /var/www/html -type f -exec chmod 644 {} \;
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo chmod +x /var/www/html/sunucu_guncelle.sh

# 8) Apache restart
echo -e "${GREEN}[+]${NC} Apache yeniden baslatiliyor..."
sudo systemctl restart apache2

echo ""
echo -e "${GREEN}=================================================${NC}"
echo -e "${GREEN}  ✓ GUNCELLEME TAMAMLANDI${NC}"
echo -e "${GREEN}=================================================${NC}"
echo ""
echo "Son commit: $(git log --oneline -1)"
echo ""
echo "Test icin:"
echo "  - https://notewarehouse.com (anasayfa)"
echo "  - https://notewarehouse.com/dersler?seviye=Lise (AI test)"
echo "  - Footer'da 'Abone Ol' (newsletter test)"
echo ""
