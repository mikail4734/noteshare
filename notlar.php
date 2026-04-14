<?php
/**
 * NoteShare - Profesyonel Not Editörü (PHP Versiyonu)
 */

// 1. Veritabanı Bağlantısı ve Notu Çekme İşlemi (Burası eksikti)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'baglan.php'; // Veritabanı bağlantın

$noteId = isset($_GET['id']) ? intval($_GET['id']) : null;
$mevcutNot = null;

if ($noteId) {
    $sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $sorgu->execute([$noteId]);
    $mevcutNot = $sorgu->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteShare | Profesyonel Not Editörü</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        .sidebar-item:hover { background-color: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .sidebar-active { background-color: #4f46e5; color: white !important; box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.3); }
        .ql-toolbar.ql-snow { border: none !important; border-bottom: 1px solid #f1f5f9 !important; position: sticky; top: 0; background: white; z-index: 40; border-radius: 1.5rem 1.5rem 0 0; padding: 1rem !important; }
        .ql-container.ql-snow { border: none !important; font-size: 1.1rem; }
        .ql-editor { min-height: 600px; padding: 3rem !important; background: white; }
        body.focus-mode aside, body.focus-mode nav { display: none !important; }
        body.focus-mode main { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
        body.focus-mode .editor-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; }
        .lang-bridge { padding-top: 12px; margin-top: -4px; }
        .note-card { transition: all 0.3s ease; border: 1px solid #eee !important; }
        .note-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 transition-all duration-300">

    <nav id="mainNav" class="bg-white border-b px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="font-black text-xl text-indigo-600 tracking-tighter">NoteShare</div>
            <div class="h-6 w-px bg-slate-200 mx-2"></div>
            <h1 id="navTitle" class="font-bold text-slate-500 text-sm italic uppercase tracking-wider">Editor Mode</h1>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="relative inline-block text-left group">
                <button class="flex items-center space-x-2 bg-slate-100 px-3 py-2 rounded-xl hover:bg-slate-200 transition border border-transparent">
                    <i class="fas fa-globe text-indigo-600 text-xs"></i>
                    <span id="currentLangDisplay" class="text-[10px] font-black uppercase">TR</span>
                </button>
                <div class="absolute right-0 top-full hidden group-hover:block z-[60] lang-bridge w-32">
                    <div class="bg-white rounded-xl shadow-2xl border border-slate-100 py-1 overflow-hidden">
                        <button onclick="changeLanguage('tr')" class="w-full text-left px-4 py-2 text-xs hover:bg-indigo-50 transition border-b border-slate-50 font-bold">🇹🇷 Türkçe</button>
                        <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-2 text-xs hover:bg-indigo-50 transition border-b border-slate-50 font-bold">🇺🇸 English</button>
                    </div>
                </div>
            </div>

            <button onclick="downloadNote()" class="text-slate-500 hover:text-indigo-600 transition p-2"><i class="fas fa-file-download"></i></button>
            
            <button onclick="saveToDatabase()" class="bg-emerald-500 text-white px-5 py-2 rounded-xl font-bold hover:bg-emerald-600 shadow-md transition active:scale-95 text-sm">
                <i class="fas fa-save mr-2"></i> <span id="btnSaveText">Kaydet</span>
            </button>
            
            <button onclick="openShareModal()" class="bg-indigo-600 text-white px-5 py-2 rounded-xl font-bold hover:bg-indigo-700 shadow-md transition active:scale-95 text-sm">
                <i class="fas fa-share-alt mr-2"></i> <span id="btnShareText">Paylaş</span>
            </button>
        </div>
    </nav>

    <div class="flex min-h-[calc(100vh-68px)]">
        <aside class="w-72 bg-white border-r border-slate-200 p-6 flex flex-col sticky top-[68px] h-[calc(100vh-68px)] overflow-y-auto">
            <div class="mb-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Organizasyon</p>
                <div class="space-y-2">
                    <a href="calisma_alani.php" class="sidebar-item w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all text-slate-500">
                        <i class="fas fa-th-large mr-3"></i> kişisel çalışmalarım
                    </a>
                    <button class="sidebar-item sidebar-active w-full flex items-center px-4 py-3 rounded-2xl text-sm font-bold transition-all">
                        <i class="fas fa-pen-nib mr-3"></i> grup çalışmalarım
                    </button>
                </div>
            </div>

            <div class="mt-auto pt-6 border-t border-slate-100">
                <a href="ayarlar.php" class="sidebar-item w-full flex items-center px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-indigo-600 transition-all cursor-pointer">
                    <i class="fas fa-cog mr-3"></i> Ayarlar
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8 lg:p-12 overflow-y-auto relative">
            <button onclick="toggleFocusMode()" class="fixed right-10 bottom-10 z-[45] bg-white text-slate-500 hover:text-indigo-600 w-12 h-12 rounded-full shadow-2xl border border-slate-100 transition-all flex items-center justify-center hover:scale-110">
                <i id="focusIcon" class="fas fa-expand-arrows-alt"></i>
            </button>

            <div class="max-w-4xl mx-auto">
                <div class="editor-card bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100/20 overflow-hidden border border-slate-100">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-8 bg-slate-50/50 border-b border-slate-100">
                        <div class="note-card p-4 rounded-2xl bg-white">
                            <label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Eğitim Seviyesi</label>
                            <select id="eduLevel" class="w-full bg-transparent font-bold text-sm outline-none appearance-none cursor-pointer">
                                <option value="Üniversite">Üniversite</option>
                                <option value="Lise">Lise</option>
                                <option value="Orta Okul">Orta Okul</option>
                            </select>
                        </div>
                        
                        <div id="schoolContainer" class="note-card p-4 rounded-2xl bg-white">
                            </div>
                        
                        <div id="subjectContainer" class="note-card p-4 rounded-2xl bg-white">
                            </div>

                        <div class="note-card p-4 rounded-2xl bg-white">
                            <label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Kategori</label>
                            <select id="noteCategory" class="w-full bg-transparent font-bold text-sm outline-none appearance-none cursor-pointer">
                                <option value="">Kategori Seçin</option>
                                <option value="Konu Anlatımı">Konu Anlatımı</option>
                                <option value="Soru Çözümü">Soru Çözümü</option>
                                <option value="Özet">Özet</option>
                            </select>
                        </div>
                    </div>

                    <div class="px-10 pt-10">
                        <input type="text" id="title" placeholder="Not Başlığını Buraya Girin..." 
                               class="w-full text-4xl font-black placeholder-slate-200 focus:outline-none border-b-2 border-transparent focus:border-indigo-50 pb-6 transition-all text-slate-800">
                    </div>

                    <div id="standardEditorContainer" class="block">
                        <div id="editor"></div>
                    </div>

                    <div id="questionBuilderContainer" class="hidden px-10 pb-10 mt-6">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-6 flex items-center">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-indigo-500 text-xl shadow-sm mr-4">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-indigo-800 text-lg">Soru Çözümü Modu Aktif</h3>
                                <p class="text-sm text-indigo-600/80">Buradan çoktan seçmeli sorularınızı ekleyebilirsiniz.</p>
                            </div>
                        </div>

                        <div id="questionsList" class="space-y-6 mb-6"></div>

                        <button onclick="addQuestion()" class="w-full bg-white border-2 border-dashed border-slate-300 text-slate-500 font-bold py-5 rounded-2xl hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all flex items-center justify-center group cursor-pointer">
                            <i class="fas fa-plus-circle mr-2 text-2xl group-hover:scale-110 transition-transform"></i> Yeni Soru Ekle
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="shareModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-800 tracking-tight">Notu Paylaş</h3>
                <button onclick="closeShareModal()" class="text-slate-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-8 text-center">
                <button onclick="shareOnWhatsApp()" class="group">
                    <div class="w-14 h-14 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-green-500 group-hover:text-white mx-auto transition-all"><i class="fab fa-whatsapp"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">WHATSAPP</span>
                </button>
                <button onclick="shareOnX()" class="group">
                    <div class="w-14 h-14 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-black group-hover:text-white mx-auto transition-all"><i class="fab fa-x-twitter"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">X</span>
                </button>
                <button onclick="copyLink()" class="group">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-indigo-500 group-hover:text-white mx-auto transition-all"><i class="fas fa-link"></i></div>
                    <span class="text-[9px] mt-2 block font-black text-slate-400">KOPYALA</span>
                </button>
            </div>
            <input type="text" id="shareLink" readonly class="w-full bg-slate-50 border rounded-xl py-3 px-4 text-[10px] text-slate-500 outline-none font-mono">
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <script>
        const translations = {
            tr: { navTitle: "EDİTÖR MODU", btnSave: "Kaydet", phTitle: "Not Başlığını Buraya Girin..." },
            en: { navTitle: "EDITOR MODE", btnSave: "Save", phTitle: "Enter Note Title Here..." }
        };

        let yokUniversiteler = [];
        let yokBolumler = [];

        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Notlarını buraya yaz...',
            modules: { 
                toolbar: [[{ 'header': [1, 2, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['image', 'code-block', 'link', 'clean']] 
            }
        });

        async function fetchYokData() {
            try {
                const response = await fetch('yok_verileri.json');
                if (!response.ok) throw new Error();
                const data = await response.json();
                yokUniversiteler = data.universiteler.sort();
                yokBolumler = data.bolumler.sort();
            } catch (e) { console.log("JSON Yüklenemedi, manuel girişe geçiliyor."); }
            updateFieldsByEduLevel();
        }

        function updateFieldsByEduLevel() {
            const level = document.getElementById('eduLevel').value;
            const sCont = document.getElementById('schoolContainer');
            const bCont = document.getElementById('subjectContainer');

            if (level === 'Üniversite' && yokUniversiteler.length > 0) {
                let uOptions = yokUniversiteler.map(u => `<option value="${u}">${u}</option>`).join('');
                let dOptions = yokBolumler.map(d => `<option value="${d}">${d}</option>`).join('');
                
                sCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Okul Adı</label>
                                   <select id="schoolName" class="w-full bg-transparent font-bold text-sm outline-none cursor-pointer">${uOptions}</select>`;
                bCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Ders / Bölüm</label>
                                   <select id="subjectName" class="w-full bg-transparent font-bold text-sm outline-none cursor-pointer">${dOptions}</select>`;
            } else {
                sCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Okul Adı</label>
                                   <input type="text" id="schoolName" class="w-full bg-transparent font-bold text-sm outline-none" placeholder="Okul yazın...">`;
                bCont.innerHTML = `<label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Ders / Bölüm</label>
                                   <input type="text" id="subjectName" class="w-full bg-transparent font-bold text-sm outline-none" placeholder="Ders yazın...">`;
            }
        }

        function downloadNote() {
            const title = document.getElementById('title').value || 'Not';
            const content = quill.root.innerHTML;
            const element = document.createElement('div');
            element.innerHTML = `<div style="padding:40px;"><h1>${title}</h1><hr>${content}</div>`;
            html2pdf().from(element).save(`${title}.pdf`);
        }

        async function saveToDatabase() {
            const btn = document.getElementById('btnSaveText');
            const original = btn.innerText;
            
            const payload = {
                id: "<?php echo $noteId; ?>",
                title: document.getElementById('title').value,
                content: quill.root.innerHTML,
                eduLevel: document.getElementById('eduLevel').value,
                schoolName: document.getElementById('schoolName').value,
                subjectName: document.getElementById('subjectName').value,
                category: document.getElementById('noteCategory').value
            };

            if(!payload.title) { alert("Başlık girin!"); return; }

            btn.innerText = "Kaydediliyor...";
            try {
                const res = await fetch('islem.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if(result.success) alert("Kaydedildi!");
                else alert("Hata: " + result.error);
            } catch (e) { alert("Sunucu hatası!"); }
            btn.innerText = original;
        }

        document.getElementById('noteCategory').addEventListener('change', function(e) {
            const standardEditor = document.getElementById('standardEditorContainer');
            const questionBuilder = document.getElementById('questionBuilderContainer');
            const quillToolbar = document.querySelector('.ql-toolbar');

            if (e.target.value === 'Soru Çözümü' || e.target.value === 'soru_cozumu') {
                standardEditor.classList.add('hidden');
                if(quillToolbar) quillToolbar.classList.add('hidden');
                questionBuilder.classList.remove('hidden');
            } else {
                standardEditor.classList.remove('hidden');
                if(quillToolbar) quillToolbar.classList.remove('hidden');
                questionBuilder.classList.add('hidden');
            }
        });

        function changeLanguage(lang) {
            const t = translations[lang];
            document.getElementById('navTitle').innerText = t.navTitle;
            document.getElementById('btnSaveText').innerText = t.btnSave;
            document.getElementById('title').placeholder = t.phTitle;
            document.getElementById('currentLangDisplay').innerText = lang.toUpperCase();
            localStorage.setItem('prefLang', lang);
        }

        function toggleFocusMode() { document.body.classList.toggle('focus-mode'); }
        function openShareModal() { 
            document.getElementById('shareModal').classList.remove('hidden'); 
            document.getElementById('shareLink').value = window.location.href;
        }
        function closeShareModal() { document.getElementById('shareModal').classList.add('hidden'); }
        function copyLink() { navigator.clipboard.writeText(window.location.href); alert("Kopyalandı!"); }

        // --- VERİTABANINDAN GELEN BİLGİLERİ DOLDURMA (Burası eklendi) ---
        document.getElementById('eduLevel').addEventListener('change', updateFieldsByEduLevel);
        
        window.onload = async () => {
            const savedLang = localStorage.getItem('prefLang') || 'tr';
            changeLanguage(savedLang);
            await fetchYokData();

            // PHP'den gelen veriyi JS'ye al
            const mevcutNot = <?php echo $mevcutNot ? json_encode($mevcutNot) : 'null'; ?>;

            if (mevcutNot) {
                // Bilgileri kutulara yerleştir
                document.getElementById('title').value = mevcutNot.title;
                
                const cat = document.getElementById('noteCategory');
                if(cat) {
                    cat.value = mevcutNot.category;
                    cat.dispatchEvent(new Event('change'));
                }

                const edu = document.getElementById('eduLevel');
                if(edu) {
                    edu.value = mevcutNot.edu_level;
                    updateFieldsByEduLevel(); 
                    
                    // Üniversite/Bölüm select kutularının dolmasını bekleyip seçimi yapıyoruz
                    setTimeout(() => {
                        const schoolInput = document.getElementById('schoolName');
                        const subjectInput = document.getElementById('subjectName');
                        if(schoolInput) schoolInput.value = mevcutNot.school_name;
                        if(subjectInput) subjectInput.value = mevcutNot.subject;
                    }, 200);
                }

                // Quill editöre içeriği bas
                if (mevcutNot.content) {
                    quill.clipboard.dangerouslyPasteHTML(mevcutNot.content);
                }
                
                document.getElementById('btnSaveText').innerText = "Güncelle";
            }
        };
    </script>
</body>
</html>