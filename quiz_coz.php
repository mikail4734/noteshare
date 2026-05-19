<?php
require_once __DIR__ . '/oturum_baslat.php';
require_once 'baglan.php';

$noteId = isset($_GET['id']) ? intval($_GET['id']) : null;
$mevcutNot = null;
$sorular = [];

if ($noteId) {

    $sorgu = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $sorgu->execute([$noteId]);
    $mevcutNot = $sorgu->fetch(PDO::FETCH_ASSOC);


    if ($mevcutNot) {
        // GIZLILIK: Grup notu ise grup uyesi kontrolu
        if (!empty($mevcutNot['grup_id'])) {
            $kEmail = $_SESSION['user_email'] ?? null;
            $kRol = $_SESSION['rol'] ?? 'guest';
            $sahibi = ($mevcutNot['kullanici_email'] === $kEmail);
            $grupUyesi = false;
            if ($kEmail) {
                $u = $db->prepare("SELECT 1 FROM grup_uyeleri WHERE grup_id = ? AND kullanici_email = ?");
                $u->execute([$mevcutNot['grup_id'], $kEmail]);
                $grupUyesi = $u->rowCount() > 0;
            }
            if (!$grupUyesi && !$sahibi && $kRol !== 'admin') {
                http_response_code(403);
                die("<div style='text-align:center;padding:50px;font-family:sans-serif'>🔒 Bu quiz özel bir grup notuna ait. Sadece grup üyeleri çözebilir. <a href='index.php'>← Anasayfa</a></div>");
            }
        }

        $soruSorgu = $db->prepare("SELECT * FROM not_sorulari WHERE note_id = ? ORDER BY id ASC");
        $soruSorgu->execute([$noteId]);
        $sorular = $soruSorgu->fetchAll(PDO::FETCH_ASSOC);
    }
}


if (!$mevcutNot || count($sorular) === 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>Bu nota ait soru bulunamadı veya not silinmiş.</div>");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="icon" type="image/png" sizes="180x180" href="/favicon-180.png"><link rel="apple-touch-icon" href="/favicon-180.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($mevcutNot['title']) ?> - Soru Çözümü</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .option-btn { transition: all 0.2s ease-in-out; }
        .option-btn.disabled { pointer-events: none; opacity: 0.9; }
        .correct-ans { background-color: #22c55e !important; color: white !important; border-color: #22c55e !important; }
        .wrong-ans { background-color: #ef4444 !important; color: white !important; border-color: #ef4444 !important; }
        .show-correct { border: 2px solid #22c55e !important; background-color: #f0fdf4 !important; }
    </style>
    <?php if (file_exists(__DIR__ . '/global_assets.php')) require_once __DIR__ . '/global_assets.php'; ?>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-20">

    <nav class="bg-white border-b px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-arrow-left text-xl"></i></a>
            <div class="h-6 w-px bg-slate-200 mx-2"></div>
            <h1 class="font-bold text-slate-800 text-lg truncate max-w-md"><?= htmlspecialchars($mevcutNot['title']) ?></h1>
        </div>
        <div class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-bold text-sm border border-indigo-100">
            <i class="fas fa-tasks mr-2"></i> Toplam <?= count($sorular) ?> Soru
        </div>
    </nav>

    <main class="max-w-3xl mx-auto mt-8 px-4" id="quizContainer">
        
        <?php foreach ($sorular as $index => $soru): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 question-card" data-question-id="<?= $soru['id'] ?>" data-correct="<?= $soru['dogru_cevap'] ?>">
                
                <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-4">
                    <span class="text-xs font-black text-indigo-400 uppercase tracking-widest">Soru <?= $index + 1 ?></span>
                </div>

                <p class="text-lg font-medium text-slate-800 mb-6 leading-relaxed">
                    <?= nl2br(htmlspecialchars($soru['soru_metni'])) ?>
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 options-container">
                    <button class="option-btn text-left p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-300 hover:bg-indigo-50 bg-white font-medium text-slate-700" data-option="A">
                        <span class="font-black text-indigo-500 mr-2">A)</span> <?= htmlspecialchars($soru['secenek_a']) ?>
                    </button>
                    <button class="option-btn text-left p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-300 hover:bg-indigo-50 bg-white font-medium text-slate-700" data-option="B">
                        <span class="font-black text-indigo-500 mr-2">B)</span> <?= htmlspecialchars($soru['secenek_b']) ?>
                    </button>
                    <button class="option-btn text-left p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-300 hover:bg-indigo-50 bg-white font-medium text-slate-700" data-option="C">
                        <span class="font-black text-indigo-500 mr-2">C)</span> <?= htmlspecialchars($soru['secenek_c']) ?>
                    </button>
                    <button class="option-btn text-left p-4 rounded-xl border-2 border-slate-100 hover:border-indigo-300 hover:bg-indigo-50 bg-white font-medium text-slate-700" data-option="D">
                        <span class="font-black text-indigo-500 mr-2">D)</span> <?= htmlspecialchars($soru['secenek_d']) ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="text-center mt-10">
            <button id="finishBtn" onclick="calculateResults()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-10 rounded-2xl shadow-lg transition-all hover:scale-105 flex items-center justify-center mx-auto text-lg">
                <i class="fas fa-flag-checkered mr-3"></i> Testi Bitir ve Sonuçları Gör
            </button>
        </div>
    </main>

    <div id="resultModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center">
            <div class="w-20 h-20 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-6">Test Sonucun</h3>
            
            <div class="space-y-3 mb-8">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-xl border border-green-100 text-green-700 font-bold">
                    <span>Doğru:</span> <span id="resCorrect" class="text-xl">0</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-xl border border-red-100 text-red-700 font-bold">
                    <span>Yanlış:</span> <span id="resWrong" class="text-xl">0</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600 font-bold">
                    <span>Boş:</span> <span id="resBlank" class="text-xl">0</span>
                </div>
            </div>

            <button onclick="closeModal()" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition">
                Cevaplarımı İncele
            </button>
        </div>
    </div>

    <script>
        let totalQuestions = <?= count($sorular) ?>;
        let answeredCount = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let isFinished = false;

        document.addEventListener('DOMContentLoaded', () => {
            const optionBtns = document.querySelectorAll('.option-btn');

            optionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (isFinished) return; 

                    const card = this.closest('.question-card');
                    
                  
                    if (card.classList.contains('answered')) return; 

                    const selectedOption = this.getAttribute('data-option');
                    const correctOption = card.getAttribute('data-correct');
                    const allBtnsInCard = card.querySelectorAll('.option-btn');

                    card.classList.add('answered');
                    answeredCount++;

                  
                    allBtnsInCard.forEach(b => b.classList.add('disabled'));

                   
                    if (selectedOption === correctOption) {
                        this.classList.add('correct-ans');
                        correctCount++;
                    } else {
                        this.classList.add('wrong-ans');
                        wrongCount++;
                       
                        const correctBtn = card.querySelector(`.option-btn[data-option="${correctOption}"]`);
                        if (correctBtn) correctBtn.classList.add('show-correct');
                    }
                });
            });
        });

        function calculateResults() {
            isFinished = true;
            const blankCount = totalQuestions - answeredCount;

            const cards = document.querySelectorAll('.question-card');
            cards.forEach(card => {
                if (!card.classList.contains('answered')) {
                    const correctOption = card.getAttribute('data-correct');
                    const correctBtn = card.querySelector(`.option-btn[data-option="${correctOption}"]`);
                    if (correctBtn) correctBtn.classList.add('show-correct');
                    const allBtns = card.querySelectorAll('.option-btn');
                    allBtns.forEach(b => b.classList.add('disabled'));
                }
            });

            document.getElementById('resCorrect').innerText = correctCount;
            document.getElementById('resWrong').innerText = wrongCount;
            document.getElementById('resBlank').innerText = blankCount;
            document.getElementById('resultModal').classList.remove('hidden');
            document.getElementById('finishBtn').style.display = 'none';

            // Sonucu veritabanına kaydet
            fetch('islem.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    islem: 'quiz_sonuc_kaydet',
                    note_id: <?= $noteId ?>,
                    dogru_sayisi: correctCount,
                    toplam_soru: totalQuestions
                })
            }).catch(()=>{});
        }

        function closeModal() {
            document.getElementById('resultModal').classList.add('hidden');
        }
    </script>
<?php if (file_exists(__DIR__ . '/footer_partial.php')) include __DIR__ . '/footer_partial.php'; ?>
</body>
</html>