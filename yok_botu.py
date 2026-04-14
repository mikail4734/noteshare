import requests
from bs4 import BeautifulSoup
import json
import os

url = "https://yokatlas.yok.gov.tr/lisans-anasayfa.php"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}

print("🚀 Bot başlatıldı. YÖK'ten veriler çekiliyor...")

try:
    r = requests.get(url, headers=headers)
    soup = BeautifulSoup(r.text, "html.parser")
    uni_options = soup.select('#univ2 option')

    unis = [u.text.strip() for u in uni_options if u.text.strip() not in ["", "Üniversite Seçiniz"]]
    
    # Bölümleri manuel ekliyoruz (YÖK her üni için ayrı yüklediği için en mantıklısı bu)
    bolumler = ["Bilgisayar Mühendisliği", "Yazılım Mühendisliği", "Yapay Zeka Mühendisliği", 
                "Bilgisayar Programcılığı", "Yönetim Bilişim Sistemleri", "Hukuk", "Tıp"]

    veri = {
        "universiteler": sorted(unis),
        "bolumler": sorted(bolumler)
    }

    # Dosyayı kaydet
    with open('yok_verileri.json', 'w', encoding='utf-8') as f:
        json.dump(veri, f, ensure_ascii=False, indent=4)

    print(f"✅ Başarılı! {len(unis)} üniversite kaydedildi.")
    print(f"📍 Dosya konumu: {os.getcwd()}\\yok_verileri.json")

except Exception as e:
    print(f"❌ Hata oluştu: {e}")