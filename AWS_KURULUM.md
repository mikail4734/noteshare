# 🚀 NoteShare — AWS Lightsail ile Canlıya Alma Rehberi

Bu rehberi takip ederek **30-45 dakika** içinde sitenizi canlıya alabilirsiniz.

**Tahmini Maliyet:** $5-10 / ay (AWS Lightsail $5 plan + opsiyonel domain $12/yıl)

---

## 📋 ÖN HAZIRLIK (5 dk)

### Gereksinimler
- ✅ AWS hesabı: https://aws.amazon.com → "Create an AWS Account"
- ✅ Kredi kartı (AWS faturalama için)
- ✅ (Opsiyonel) Domain adı — Namecheap, GoDaddy veya AWS Route 53'ten

---

## 🌐 ADIM 1: AWS Lightsail Instance Oluştur (10 dk)

1. **AWS Console'a gir** → https://lightsail.aws.amazon.com
2. Sol üstte **bölge seç:** "Europe (Frankfurt) eu-central-1" (Türkiye'ye en yakın)
3. **"Create instance"** tıkla
4. **Platform:** Linux/Unix
5. **Blueprint:** ✅ **LAMP (PHP 8.x)** — Bitnami imajı
6. **Plan:** En ucuzu **$5/ay (1 GB RAM, 40 GB SSD)** seç
7. **Instance adı:** `noteshare-prod`
8. **"Create instance"** tıkla

⏳ ~2 dakika içinde "Running" olacak.

---

## 🔑 ADIM 2: Statik IP + Güvenlik Duvarı (3 dk)

### Statik IP
- Sol menü → **"Networking"** → **"Create static IP"**
- Region: Aynı bölge
- Instance: `noteshare-prod` seç
- Adı: `noteshare-ip`
- **Create** — IP adresini bir yere not et (örn: `3.71.45.123`)

### Firewall
- Instance → **"Networking" sekmesi**
- Firewall'a şunları **Add rule** ile ekle:
  - ✅ **HTTP** (80) — Anywhere
  - ✅ **HTTPS** (443) — Anywhere
  - ✅ **SSH** (22) — Anywhere (zaten var)

---

## 💾 ADIM 3: Veritabanını Aktar (10 dk)

### Sunucuya SSH ile Bağlan
1. Instance sayfasında **"Connect using SSH"** butonu → sarı buton → tarayıcıdan terminal açılır
2. Şu komutu çalıştır (admin şifresi):
   ```bash
   cat /home/bitnami/bitnami_application_password
   ```
   ⚠️ Bu şifreyi **kaydet**, MySQL için kullanacağız.

### Veritabanı Oluştur
```bash
# MySQL'e bağlan
mysql -u root -p
# (yukarıdaki şifreyi yapıştır)
```

MySQL içinde:
```sql
CREATE DATABASE notdeposu CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'noteshare_user'@'localhost' IDENTIFIED BY 'GUCLU_SIFRE_BURAYA';
GRANT ALL PRIVILEGES ON notdeposu.* TO 'noteshare_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> ⚠️ `GUCLU_SIFRE_BURAYA` yerine **güçlü** bir parola yaz (örn: `Note$h@re2026!XK`). Bunu da kaydet.

### SQL Şemasını Yükle
Daha sonra adım 5'te dosyalar yüklendiğinde:
```bash
cd /opt/bitnami/apache2/htdocs
mysql -u noteshare_user -p notdeposu < notdeposu_yeni.sql
```

---

## 📦 ADIM 4: Dosyaları Sunucuya Yükle (10 dk)

### Yöntem A: ZIP yükleme (Kolay)

**Local'de (Windows):**
1. `C:\xampp\htdocs\norwarhouse.php\` klasörünü ZIP yap
2. ZIP içine **bunları DAHIL ETME**:
   - ❌ `node_modules/`
   - ❌ `vendor/`
   - ❌ `.claude/`
   - ❌ `notdeposu.sql` (eski)
   - ❌ `.env` (local'deki, ayrı kuracağız)

3. Lightsail SSH'a dön, ZIP'i indir veya yükle. En kolayı:
   - ZIP'i bir bulut servise yükle (Google Drive, transfer.sh)
   - Lightsail'de:
   ```bash
   cd /opt/bitnami/apache2/htdocs
   sudo rm -rf *
   sudo wget https://drive.google.com/sizin-zip-link -O site.zip
   sudo unzip site.zip
   sudo chown -R bitnami:daemon .
   sudo chmod -R 775 uploads/
   ```

### Yöntem B: Git ile (Profesyonel)
GitHub'a private repo aç, sonra Lightsail'de:
```bash
cd /opt/bitnami/apache2/htdocs
sudo rm -rf *
git clone https://github.com/KULLANICIN/noteshare.git .
sudo chown -R bitnami:daemon .
sudo chmod -R 775 uploads/
```

### Composer paketlerini kur
```bash
cd /opt/bitnami/apache2/htdocs
sudo composer install --no-dev --optimize-autoloader
```

---

## ⚙️ ADIM 5: .env Dosyasını Production İçin Ayarla (3 dk)

```bash
cd /opt/bitnami/apache2/htdocs
sudo cp .env.example .env
sudo nano .env
```

Şu içeriği yapıştır (değerleri **kendininkilerle** doldur):
```
APP_ENV=production
APP_URL=http://3.71.45.123       # Statik IP'n veya domain'in

DB_HOST=localhost
DB_NAME=notdeposu
DB_USER=noteshare_user
DB_PASS=GUCLU_SIFRE_BURAYA

ANTHROPIC_API_KEY=sk-ant-api03-XXXXXXXX
GOOGLE_CLIENT_ID=XXXXXXXX.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-XXXXXX
GOOGLE_REDIRECT_URI=http://3.71.45.123/google-login-calistir.php
```

**Ctrl+O → Enter → Ctrl+X** ile kaydet.

```bash
# .env sadece sahibi okuyabilsin
sudo chmod 600 .env
sudo chown bitnami:bitnami .env
```

### SQL'i yükle
```bash
mysql -u noteshare_user -p notdeposu < notdeposu_yeni.sql
```

---

## ✅ ADIM 6: Apache'yi Yeniden Başlat ve Test Et (2 dk)

```bash
sudo /opt/bitnami/ctlscript.sh restart apache
```

Tarayıcıdan aç: `http://STATIK_IP_NUMARAN`

🎉 Sitenin canlıda olmalı!

İlk yapacağın:
1. `http://STATIK_IP/kaydol.php` → **mikailcelik4734@gmail.com** ile kaydol
   → Otomatik admin olacaksın
2. Çıkış yap → Tekrar giriş yap

---

## 🌐 ADIM 7 (Opsiyonel): Domain Bağlama (15 dk)

### Namecheap/GoDaddy'den aldıysan
1. Domain panelinde **DNS Records**'a git
2. **A Record** ekle:
   - Host: `@`
   - Value: `STATIK_IP_NUMARAN`
   - TTL: Automatic
3. **A Record** ekle:
   - Host: `www`
   - Value: `STATIK_IP_NUMARAN`

⏳ 5-30 dakika DNS yayılması.

### Apache'ye domain tanıt
```bash
sudo nano /opt/bitnami/apache2/conf/vhosts/noteshare.conf
```

Yapıştır:
```apache
<VirtualHost *:80>
    ServerName senin-domain.com
    ServerAlias www.senin-domain.com
    DocumentRoot "/opt/bitnami/apache2/htdocs"
    <Directory "/opt/bitnami/apache2/htdocs">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo /opt/bitnami/ctlscript.sh restart apache
```

---

## 🔒 ADIM 8 (Opsiyonel ama ÖNEMLİ): HTTPS Kur — Ücretsiz Let's Encrypt (10 dk)

Domain bağladıysan SSL şart:
```bash
sudo /opt/bitnami/bncert-tool
```

- Domain'i gir: `senin-domain.com www.senin-domain.com`
- HTTPS yönlendirme: **Y**
- E-mail: kendi e-posta'n
- Devam: **Y**

🎉 5 dakikada SSL kurulur, siten artık `https://senin-domain.com`'da.

### .env'i güncelle (HTTPS için)
```bash
sudo nano /opt/bitnami/apache2/htdocs/.env
```
- `APP_URL=https://senin-domain.com`
- `GOOGLE_REDIRECT_URI=https://senin-domain.com/google-login-calistir.php`

### Google Console'a yeni redirect URI ekle
1. https://console.cloud.google.com → Credentials
2. OAuth client'ı düzenle
3. Authorized redirect URIs'a: `https://senin-domain.com/google-login-calistir.php` ekle

---

## 📊 ADIM 9: İzleme & Yedekleme (Opsiyonel)

### Otomatik Snapshot (yedek)
- Lightsail → Instance → **"Snapshots"** → **"Enable automatic snapshots"**
- Her gece yedek alır, $0.50/ay

### CloudWatch Logları
```bash
# Apache loglarını izle
tail -f /opt/bitnami/apache2/logs/error_log
```

---

## 🚨 SORUN GİDERME

### "500 Internal Server Error"
```bash
# Apache hata logunu kontrol et
tail -50 /opt/bitnami/apache2/logs/error_log
```

### Veritabanı bağlanmıyor
- `.env` içindeki `DB_PASS` doğru mu?
- `mysql -u noteshare_user -p notdeposu` ile manuel dene

### Dosya yükleme çalışmıyor
```bash
sudo chmod -R 775 /opt/bitnami/apache2/htdocs/uploads
sudo chown -R bitnami:daemon /opt/bitnami/apache2/htdocs/uploads
```

### Google login "redirect_uri_mismatch"
- Google Console'da kayıtlı URL ile `.env`'deki `GOOGLE_REDIRECT_URI` **birebir aynı** olmalı

### "Maximum execution time exceeded" (AI yavaş)
```bash
sudo nano /opt/bitnami/php/etc/php.ini
```
- `max_execution_time = 60`'a çıkar
- `sudo /opt/bitnami/ctlscript.sh restart apache`

---

## 💰 Maliyet Özeti

| Kalem | Aylık |
|---|---|
| Lightsail $5 plan | $5.00 |
| Statik IP | Ücretsiz (instance'a bağlıyken) |
| Snapshot (yedek) | ~$0.50 |
| Let's Encrypt SSL | Ücretsiz |
| Domain (yıllık $12) | ~$1.00 |
| **TOPLAM** | **~$6.50/ay (~₺220/ay)** |

Anthropic API ücreti ayrı: Her AI çağrısı **~$0.0003** (3 binde 1 cent). 10.000 kullanım = ~$3.

---

## ✅ Canlıya Alındı Kontrol Listesi

- [ ] Lightsail instance running
- [ ] Statik IP atandı
- [ ] HTTP/HTTPS firewall açık
- [ ] MySQL DB ve user oluşturuldu
- [ ] Dosyalar `/opt/bitnami/apache2/htdocs`'ta
- [ ] `.env` doğru ayarlı, izinler 600
- [ ] `notdeposu_yeni.sql` import edildi
- [ ] `composer install` çalıştı
- [ ] Apache restart edildi
- [ ] `uploads/` yazılabilir (775)
- [ ] Anasayfa açılıyor
- [ ] Kaydol/Giriş çalışıyor
- [ ] Admin olarak mikailcelik4734 giriş yaptı
- [ ] (Opsiyonel) Domain bağlandı
- [ ] (Opsiyonel) HTTPS aktif

🎉 **Sitende artık binlerce öğrenci not paylaşabilir!**
