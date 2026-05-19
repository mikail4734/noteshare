<?php
// =====================================================
//  Ayarlar .env'den otomatik yüklenir
// =====================================================
require_once __DIR__ . '/config.php';

$openai_api_key = $config['OPENAI_API_KEY'] ?? '';

$db_host = $config['DB_HOST'] ?? 'localhost';
$db_name = $config['DB_NAME'] ?? 'notdeposu';
$db_user = $config['DB_USER'] ?? 'root';
$db_pass = $config['DB_PASS'] ?? '';
// =====================================================
 
// Veritabanı bağlantısı
function db_connect($host, $db, $user, $pass) {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
 
// AJAX isteği mi?
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
    set_time_limit(300);
    ini_set("max_execution_time", 300);
    header('Content-Type: application/json; charset=utf-8');
 
    $input = json_decode(file_get_contents('php://input'), true);
    $islem = isset($input['islem']) ? $input['islem'] : 'mesaj';
 
    try {
        $pdo = db_connect($db_host, $db_name, $db_user, $db_pass);
 
        // Geçmiş sohbetleri getir
        if ($islem === 'gecmis') {
            $stmt = $pdo->query("SELECT session_id, baslik, guncelleme FROM dersbotu_sessions ORDER BY guncelleme DESC LIMIT 30");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }
 
        // Sohbet detayını getir
        if ($islem === 'sohbet_getir') {
            $sid = $input['session_id'];
            $stmt = $pdo->prepare("SELECT rol, icerik FROM dersbotu_mesajlar WHERE session_id = ? ORDER BY id ASC");
            $stmt->execute([$sid]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }
 
        // Sohbeti sil
        if ($islem === 'sil') {
            $sid = $input['session_id'];
            $pdo->prepare("DELETE FROM dersbotu_sessions WHERE session_id = ?")->execute([$sid]);
            echo json_encode(['success' => true]);
            exit;
        }
 
        // Normal mesaj gönder
        $messages  = isset($input['messages'])   ? $input['messages']   : [];
        $session_id = isset($input['session_id']) ? $input['session_id'] : null;
        $file_data  = isset($input['file'])       ? $input['file']       : null;
 
        if (empty($messages)) {
            echo json_encode(['error' => 'Mesaj boş']);
            exit;
        }
 
        // Dosya varsa son kullanıcı mesajına ekle (OpenAI Vision formati)
        $api_messages = $messages;
        if ($file_data && isset($file_data['base64']) && isset($file_data['type'])) {
            $last = end($api_messages);
            $idx  = count($api_messages) - 1;
            $content = [
                ['type' => 'text', 'text' => $last['content']]
            ];

            // Resim - OpenAI gpt-4o-mini gorebilir
            if (strpos($file_data['type'], 'image/') === 0) {
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:' . $file_data['type'] . ';base64,' . $file_data['base64']
                    ]
                ];
            } elseif ($file_data['type'] === 'application/pdf') {
                // OpenAI PDF'i dogrudan desteklemiyor - kullaniciya uyari
                $content[0]['text'] = "[PDF DOSYASI YÜKLENDI - icerik metin olarak okunmali] " . $last['content'];
            }

            $api_messages[$idx]['content'] = $content;
        }

        // OpenAI messages formati - system role'u messages arrayinde ilk eleman
        $systemMessage = [
            'role' => 'system',
            'content' => 'Sen DersBotu\'sun — Türkçe konuşan öğrencilere ve geliştiricilere yardımcı olan kapsamlı bir asistansın. TEMEL KURALLAR: 1) Kod isteklerinde ASLA eksik bırakma, tüm kodu eksiksiz yaz. HTML/CSS/JS/PHP isteklerinde baştan sona tam dosyayı ver. 2) Eğer çok uzun olacaksa bile kes bölme, devam et. 3) Her zaman tam ve çalışır kodu ver. 4) Asla \'geri kalan kısmı kendin tamamla\' deme. 5) Dosya veya resim yüklenirse analiz et ve açıkla. 6) Cevaplarını Türkçe ver, kod içindeki yorumlar da Türkçe olsun.'
        ];

        $finalMessages = array_merge([$systemMessage], $api_messages);

        $payload = json_encode([
            'model'      => 'gpt-4o-mini',
            'max_tokens' => 16000,
            'messages'   => $finalMessages
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openai_api_key
            ],
            CURLOPT_TIMEOUT => 180,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo json_encode(['error' => 'cURL hatası: ' . $curlError]);
            exit;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errMsg = isset($data['error']['message']) ? $data['error']['message'] : $response;
            echo json_encode(['error' => 'HTTP ' . $httpCode . ': ' . $errMsg]);
            exit;
        }

        $reply = isset($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : '';
 
        // Veritabanına kaydet
        $last_user = '';
        foreach ($messages as $m) {
            if ($m['role'] === 'user') $last_user = $m['content'];
        }
        $baslik = mb_substr($last_user, 0, 60);
 
        // Session yoksa oluştur
        $stmt = $pdo->prepare("INSERT IGNORE INTO dersbotu_sessions (session_id, baslik) VALUES (?, ?)");
        $stmt->execute([$session_id, $baslik]);
 
        // Başlık güncelle (ilk mesajsa)
        $stmt = $pdo->prepare("UPDATE dersbotu_sessions SET baslik = ?, guncelleme = NOW() WHERE session_id = ?");
        $stmt->execute([$baslik, $session_id]);
 
        // Sadece yeni mesajları ekle — son user + assistant
        $stmt = $pdo->prepare("INSERT INTO dersbotu_mesajlar (session_id, rol, icerik) VALUES (?, ?, ?)");
        $stmt->execute([$session_id, 'user',      $last_user]);
        $stmt->execute([$session_id, 'assistant', $reply]);
 
        echo json_encode(['success' => true, 'cevap' => $reply]);
 
    } catch (Exception $e) {
        echo json_encode(['error' => 'Sunucu hatası: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png">
    <link rel="apple-touch-icon" href="/favicon-180.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DersBotu</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; height: 100%; background: #fff; }
        .dersbotu-container { display: flex; height: 100vh; width: 100%; }
 
        /* Sidebar */
        .dersbotu-sidebar { width: 260px; background: #f9f9f9; border-right: 1px solid #eaeaea; padding: 16px; display: flex; flex-direction: column; }
        .new-chat-btn { background: #fff; border: 1px solid #ddd; padding: 11px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; width: 100%; margin-bottom: 20px; }
        .new-chat-btn:hover { background: #f0f0f0; }
        .history-title { font-size: 11px; color: #aaa; font-weight: 700; letter-spacing: 0.8px; margin-bottom: 8px; }
        .history-list { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; }
        .history-item { padding: 9px 10px; border-radius: 8px; cursor: pointer; color: #333; font-size: 13px; line-height: 1.3; display: flex; align-items: center; justify-content: space-between; gap: 4px; }
        .history-item:hover { background: #eee; }
        .history-item.active { background: #e8e8e8; font-weight: 500; }
        .history-item-title { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .delete-btn { background: none; border: none; color: #ccc; cursor: pointer; font-size: 15px; padding: 2px 4px; border-radius: 4px; flex-shrink: 0; display: none; }
        .history-item:hover .delete-btn { display: block; }
        .delete-btn:hover { color: #e55; background: #fee; }
 
        /* Main */
        .dersbotu-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 30px 15%; display: flex; flex-direction: column; }
        .welcome-screen { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; }
        .welcome-screen h1 { font-size: 30px; color: #1a1a1a; font-weight: 500; margin: 0; }
        .welcome-screen p { font-size: 15px; color: #888; margin: 0; }
 
        .user-message { background: #f4f4f4; padding: 12px 18px; border-radius: 18px 18px 4px 18px; margin-bottom: 12px; align-self: flex-end; max-width: 80%; font-size: 15px; color: #333; line-height: 1.5; }
        .bot-message { background: #e3eaff; padding: 12px 18px; border-radius: 18px 18px 18px 4px; margin-bottom: 12px; align-self: flex-start; max-width: 80%; font-size: 15px; color: #333; border: 1px solid #d0daff; line-height: 1.6; }
        .bot-message.typing { color: #888; font-style: italic; }
        .bot-message strong { font-weight: 600; }
        .bot-message code { background: #dde6f5; border-radius: 4px; padding: 1px 5px; font-family: monospace; font-size: 13px; }
        .bot-message pre { background: #1e1e2e; color: #cdd6f4; border-radius: 10px; padding: 14px; overflow-x: auto; margin: 8px 0; }
        .bot-message pre code { background: transparent; color: inherit; padding: 0; }
 
        /* Dosya önizleme */
        .file-preview { background: #f0f4ff; border: 1px solid #c8d8ff; border-radius: 10px; padding: 10px 14px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; align-self: flex-end; max-width: 80%; }
        .file-preview-icon { font-size: 22px; }
        .file-preview-name { font-size: 13px; color: #444; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-preview-remove { background: none; border: none; color: #aaa; cursor: pointer; font-size: 18px; padding: 0; line-height: 1; }
        .file-preview-remove:hover { color: #e55; }
 
        /* Input alanı */
        .input-area { padding: 12px 15% 16px; background: #fff; border-top: 1px solid #f0f0f0; }
        .pending-file-bar { display: none; background: #f0f4ff; border: 1px solid #c8d8ff; border-radius: 10px; padding: 8px 14px; margin-bottom: 8px; align-items: center; gap: 10px; }
        .pending-file-bar.visible { display: flex; }
        .pending-file-icon { font-size: 20px; }
        .pending-file-name { font-size: 13px; color: #444; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pending-file-remove { background: none; border: none; color: #aaa; cursor: pointer; font-size: 18px; }
        .pending-file-remove:hover { color: #e55; }
 
        .input-wrapper { display: flex; align-items: center; background: #f4f4f4; border-radius: 50px; padding: 6px 6px 6px 8px; gap: 4px; }
        .attach-btn { background: #fff; border: 1px solid #ddd; border-radius: 50%; width: 36px; height: 36px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .attach-btn:hover { background: #f0f0f0; }
        .input-wrapper input { flex: 1; border: none; background: transparent; padding: 8px 6px; font-size: 15px; outline: none; color: #222; }
        .send-btn { background: #000; color: #fff; border: none; border-radius: 50%; width: 36px; height: 36px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .send-btn:hover { background: #333; }
        .send-btn:disabled { background: #ccc; cursor: not-allowed; }
        .footer-text { text-align: center; font-size: 12px; color: #bbb; margin-top: 8px; }
 
        /* Dosya input gizli */
        #file-input { display: none; }
 
        /* Toast */
        #toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 10px 20px; border-radius: 20px; font-size: 14px; opacity: 0; pointer-events: none; transition: opacity 0.3s; z-index: 999; }
        #toast.show { opacity: 1; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body>
<div id="toast"></div>
<input type="file" id="file-input" accept="image/*,.pdf,.txt">
 
<div class="dersbotu-container">
    <aside class="dersbotu-sidebar">
        <button class="new-chat-btn" id="new-chat-btn">＋ Yeni Sohbet</button>
        <p class="history-title">GEÇMİŞ SOHBETLER</p>
        <ul class="history-list" id="history-list"></ul>
    </aside>
 
    <main class="dersbotu-main">
        <div class="chat-messages" id="chat-container">
            <div class="welcome-screen" id="welcome-screen">
                <h1>Sen hazır olduğunda hazırım.</h1>
                <p>Matematik, fen, tarih, dil — her konuda yardımcıyım.</p>
            </div>
        </div>
 
        <div class="input-area">
            <div class="pending-file-bar" id="pending-file-bar">
                <span class="pending-file-icon" id="pending-file-icon">📎</span>
                <span class="pending-file-name" id="pending-file-name"></span>
                <button class="pending-file-remove" id="pending-file-remove" title="Dosyayı kaldır">×</button>
            </div>
            <div class="input-wrapper">
                <button class="attach-btn" id="attach-btn" title="Dosya ekle">+</button>
                <input type="text" id="user-input" placeholder="DersBotu'na herhangi bir şey sor...">
                <button class="send-btn" id="send-btn">&#x2191;</button>
            </div>
            <p class="footer-text">DersBotu bazen hatalı cevaplar verebilir. Önemli bilgileri kontrol etmeyi unutma.</p>
        </div>
    </main>
</div>
 
<script>
(function () {
    let chatHistory    = [];
    let activeSessionId = null;
    let isLoading      = false;
    let pendingFile    = null; // {base64, type, name}
 
    const chatContainer    = document.getElementById('chat-container');
    const userInput        = document.getElementById('user-input');
    const sendBtn          = document.getElementById('send-btn');
    const historyList      = document.getElementById('history-list');
    const newChatBtn       = document.getElementById('new-chat-btn');
    const attachBtn        = document.getElementById('attach-btn');
    const fileInput        = document.getElementById('file-input');
    const pendingFileBar   = document.getElementById('pending-file-bar');
    const pendingFileName  = document.getElementById('pending-file-name');
    const pendingFileIcon  = document.getElementById('pending-file-icon');
    const pendingFileRemove= document.getElementById('pending-file-remove');
    const toast            = document.getElementById('toast');
 
    // Toast bildirimi
    function showToast(msg, dur = 2500) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), dur);
    }
 
    // Yeni session id üret
    function newSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
    }
 
    // Geçmişi yükle
    async function loadHistory() {
        try {
            const res  = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ islem: 'gecmis' })
            });
            const list = await res.json();
            historyList.innerHTML = '';
            list.forEach(s => addHistoryItem(s.session_id, s.baslik));
        } catch(e) { console.error(e); }
    }
 
    function addHistoryItem(sid, baslik) {
        // Varsa güncelle
        const existing = document.querySelector(`.history-item[data-sid="${sid}"]`);
        if (existing) {
            existing.querySelector('.history-item-title').textContent = baslik;
            historyList.insertBefore(existing, historyList.firstChild);
            return;
        }
        const li = document.createElement('li');
        li.className = 'history-item';
        li.dataset.sid = sid;
        li.innerHTML = `<span class="history-item-title">${escHtml(baslik)}</span><button class="delete-btn" title="Sil">🗑</button>`;
 
        li.querySelector('.history-item-title').addEventListener('click', () => loadChat(sid, li));
        li.querySelector('.delete-btn').addEventListener('click', async (e) => {
            e.stopPropagation();
            if (!confirm('Bu sohbeti silmek istiyor musun?')) return;
            await fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ islem: 'sil', session_id: sid })
            });
            li.remove();
            if (activeSessionId === sid) {
                activeSessionId = null;
                chatHistory = [];
                renderWelcome();
            }
            showToast('Sohbet silindi.');
        });
 
        historyList.insertBefore(li, historyList.firstChild);
    }
 
    async function loadChat(sid, li) {
        try {
            const res  = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ islem: 'sohbet_getir', session_id: sid })
            });
            const msgs = await res.json();
            chatContainer.innerHTML = '';
            chatHistory = [];
            msgs.forEach(m => {
                appendMessage(m.icerik, m.rol === 'user');
                chatHistory.push({ role: m.rol, content: m.icerik });
            });
            activeSessionId = sid;
            document.querySelectorAll('.history-item').forEach(el => el.classList.remove('active'));
            if (li) li.classList.add('active');
        } catch(e) { showToast('Sohbet yüklenemedi.'); }
    }
 
    function renderWelcome() {
        chatContainer.innerHTML = '<div class="welcome-screen" id="welcome-screen"><h1>Sen hazır olduğunda hazırım.</h1><p>Matematik, fen, tarih, dil — her konuda yardımcıyım.</p></div>';
    }
 
    function hideWelcome() {
        const w = document.getElementById('welcome-screen');
        if (w) w.remove();
    }
 
    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
 
    function simpleMarkdown(text) {
        return text
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/```([\s\S]*?)```/g,'<pre><code>$1</code></pre>')
            .replace(/`([^`]+)`/g,'<code>$1</code>')
            .replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
            .replace(/\*(.+?)\*/g,'<em>$1</em>')
            .replace(/\n/g,'<br>');
    }
 
    function appendMessage(text, isUser) {
        hideWelcome();
        const div = document.createElement('div');
        div.className = isUser ? 'user-message' : 'bot-message';
        isUser ? (div.textContent = text) : (div.innerHTML = simpleMarkdown(text));
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        return div;
    }
 
    function appendFileChip(name, type) {
        hideWelcome();
        const icon = type.startsWith('image/') ? '🖼️' : type === 'application/pdf' ? '📄' : '📎';
        const div = document.createElement('div');
        div.className = 'file-preview';
        div.innerHTML = `<span class="file-preview-icon">${icon}</span><span class="file-preview-name">${escHtml(name)}</span>`;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
 
    // Dosya seç
    attachBtn.addEventListener('click', () => fileInput.click());
 
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;
 
        const maxMB = 5;
        if (file.size > maxMB * 1024 * 1024) {
            showToast(`Dosya çok büyük. Maksimum ${maxMB} MB.`);
            fileInput.value = '';
            return;
        }
 
        const reader = new FileReader();
        reader.onload = (e) => {
            const base64 = e.target.result.split(',')[1];
            pendingFile = { base64, type: file.type, name: file.name };
 
            const icon = file.type.startsWith('image/') ? '🖼️' : file.type === 'application/pdf' ? '📄' : '📎';
            pendingFileIcon.textContent = icon;
            pendingFileName.textContent = file.name;
            pendingFileBar.classList.add('visible');
            userInput.placeholder = 'Dosya hakkında bir şey sor...';
            userInput.focus();
        };
        reader.readAsDataURL(file);
        fileInput.value = '';
    });
 
    pendingFileRemove.addEventListener('click', () => {
        pendingFile = null;
        pendingFileBar.classList.remove('visible');
        userInput.placeholder = "DersBotu'na herhangi bir şey sor...";
    });
 
    // Yeni sohbet
    newChatBtn.addEventListener('click', () => {
        chatHistory = [];
        activeSessionId = null;
        pendingFile = null;
        pendingFileBar.classList.remove('visible');
        userInput.placeholder = "DersBotu'na herhangi bir şey sor...";
        renderWelcome();
        document.querySelectorAll('.history-item').forEach(el => el.classList.remove('active'));
    });
 
    // Mesaj gönder
    async function sendMessage() {
        if (isLoading) return;
        const text = userInput.value.trim();
        if (!text && !pendingFile) return;
 
        const msgText = text || (pendingFile ? `Bu dosyayı analiz et: ${pendingFile.name}` : '');
 
        if (!activeSessionId) activeSessionId = newSessionId();
 
        userInput.value = '';
 
        // Dosya chip'i göster
        if (pendingFile) appendFileChip(pendingFile.name, pendingFile.type);
        appendMessage(msgText, true);
        chatHistory.push({ role: 'user', content: msgText });
 
        const typingDiv = document.createElement('div');
        typingDiv.className = 'bot-message typing';
        typingDiv.textContent = 'Yazıyor… (Uzun içerik üretiliyor, lütfen bekle)';
        chatContainer.appendChild(typingDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
 
        isLoading = true;
        sendBtn.disabled = true;
        userInput.disabled = true;
 
        const fileToSend = pendingFile;
        pendingFile = null;
        pendingFileBar.classList.remove('visible');
        userInput.placeholder = "DersBotu'na herhangi bir şey sor...";
 
        try {
            const body = {
                islem: 'mesaj',
                messages: chatHistory,
                session_id: activeSessionId
            };
            if (fileToSend) body.file = fileToSend;
 
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body)
            });
 
            const data = await response.json();
 
            if (!data.success) {
                typingDiv.textContent = '⚠️ ' + (data.error || 'Bilinmeyen hata');
                typingDiv.classList.remove('typing');
                chatHistory.pop();
                return;
            }
 
            typingDiv.className = 'bot-message';
            typingDiv.innerHTML = simpleMarkdown(data.cevap);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            chatHistory.push({ role: 'assistant', content: data.cevap });
 
            // Sidebar'ı güncelle
            addHistoryItem(activeSessionId, chatHistory.find(m=>m.role==='user')?.content?.slice(0,40) || 'Sohbet');
            document.querySelectorAll('.history-item').forEach(el =>
                el.classList.toggle('active', el.dataset.sid === activeSessionId));
 
        } catch (err) {
            typingDiv.textContent = '⚠️ Sunucuya bağlanılamadı: ' + err.message;
            typingDiv.classList.remove('typing');
            chatHistory.pop();
        } finally {
            isLoading = false;
            sendBtn.disabled = false;
            userInput.disabled = false;
            userInput.focus();
        }
    }
 
    sendBtn.addEventListener('click', sendMessage);
    userInput.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) sendMessage(); });
 
    // Başlangıçta geçmişi yükle
    loadHistory();
    userInput.focus();
})();
</script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>
 