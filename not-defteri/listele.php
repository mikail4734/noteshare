<?php include("baglan.php"); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Not Listesi | notewarehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-10">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-3xl shadow-lg">
        <div class="flex justify-between mb-6">
            <h2 class="text-xl font-bold">Kayıtlı Notlar</h2>
            <a href="notlar.html" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm"> + Yeni Ekle</a>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100">
                    <th class="p-4 border-b">ID</th>
                    <th class="p-4 border-b">Başlık</th>
                    <th class="p-4 border-b">Ders</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sorgu = mysqli_query($baglanti, "SELECT * FROM notlar ORDER BY id DESC");
                
                while ($satir = mysqli_fetch_assoc($sorgu)) {
                    echo "<tr class='hover:bg-slate-50'>";
                    echo "<td class='p-4 border-b'>" . $satir['id'] . "</td>";
                    echo "<td class='p-4 border-b font-bold'>" . $satir['baslik'] . "</td>";
                    echo "<td class='p-4 border-b'>" . $satir['ders_adi'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>