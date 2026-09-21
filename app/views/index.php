<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir Murid</title>

    <style>
    /* Hanya mengatur Tabel Presensi Utama */
    #tabelPresensi {
        border-collapse: collapse;
        background-color: #fdfaf6; /* Latar tabel warna coksu sangat tipis */
    }

    #tabelPresensi th, #tabelPresensi td {
        border: 1px solid #8b5a2b; /* Garis tabel warna coklat */
        padding: 5px;
    }

    #tabelPresensi th {
        background-color: #5c3a21; /* Header tabel warna coklat pramuka */
        color: white;
    }

    /* Memastikan kolom Nama Murid tetap rapi di kiri */
    #tabelPresensi td:nth-child(2) {
        text-align: left;
    }

    /* Gaya simpel untuk tombol */
    button {
        background-color: #5c3a21; /* Warna coklat pramuka */
        color: white;
        border: 1px solid #3b2818;
        padding: 6px 15px;
        cursor: pointer;
        font-family: inherit;
    }

    /* Warna tombol sedikit lebih gelap saat disentuh mouse */
    button:hover {
        background-color: #4a2e1b; 
    }

    /* Notifikasi sukses disesuaikan warnanya dengan tema coksu */
    #notifikasi {
        background-color: #eaddcf; /* Coksu */
        color: #5c3a21;
        padding: 10px;
        border: 1px solid #5c3a21;
        margin-bottom: 15px;
        display: inline-block;
    }
</style>
</head>
<body>
    <h2>Daftar Hadir Murid</h2>

    <!-- Notifikasi Visual -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div id="notifikasi" style="background-color: #d4edda; color: #155724; padding: 10px 15px; border: 1px solid #c3e6cb; margin-bottom: 15px; border-radius: 5px; display: inline-block;">
            <strong>Berhasil!</strong> Data presensi telah berhasil disimpan.
        </div>
    <?php endif; ?>

    <!-- autocomplete="off" agar browser tidak mengingat isian -->
    <form action="index.php?action=simpan" method="POST" autocomplete="off" id="formPresensi">

        <!-- Field Informasi Kelas/Mapel -->
        <table>
            <tr>
                <td><label for="mata_pelajaran">Mata Pelajaran</label></td>
                <td>: <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="<?= htmlspecialchars($filter_mapel ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="kelas">Kelas</label></td>
                <td>: <input type="text" id="kelas" name="kelas" value="<?= htmlspecialchars($filter_kelas ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="nama_guru">Nama Guru</label></td>
                <td>: <input type="text" id="nama_guru" name="nama_guru" value="<?= htmlspecialchars($dataPresensi[array_key_first($dataPresensi ?? [])]['nama_guru'] ?? $filter_guru ?? '') ?>"></td>
            </tr>
            <tr>
                <td><label for="bulan">Bulan</label></td>
                <td>: <input type="month" id="bulan" name="bulan" value="<?= htmlspecialchars($filter_bulan ?? '') ?>"></td>
            </tr>
        </table>

        <br>

        <!-- Tabel Presensi Utamanya -->
        <table border="1" id="tabelPresensi">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Murid</th>
                    <th colspan="30">Tanggal</th>
                </tr>
                <tr>
                    <?php for ($i = 1; $i <= 30; $i++): ?>
                        <th><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
            <?php 
            $row = 0;
            if (!empty($dataPresensi)): 
                foreach ($dataPresensi as $murid_id => $murid): ?>
                    <tr>
                        <td align="center"><?= $row + 1 ?></td>
                        <td>
                            <input type="text"
                                name="rows[<?= $row ?>][nama]"
                                value="<?= htmlspecialchars($murid['nama']) ?>">
                        </td>

                        <?php for ($tgl = 1; $tgl <= 30; $tgl++): ?>
                            <?php $statusTersimpan = $murid['presensi'][$tgl] ?? ''; ?>

                            <td>
                                <select name="rows[<?= $row ?>][presensi][<?= $tgl ?>]">
                                    <option value="" <?= $statusTersimpan === '' ? 'selected' : '' ?>>-</option>
                                    <option value="Hadir" <?= $statusTersimpan === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                    <option value="Ijin" <?= $statusTersimpan === 'Ijin' ? 'selected' : '' ?>>Ijin</option>
                                    <option value="Sakit" <?= $statusTersimpan === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                    <option value="Alpha" <?= $statusTersimpan === 'Alpha' ? 'selected' : '' ?>>Alpha</option>
                                </select>
                            </td>
                        <?php endfor; ?>
                    </tr>
                    <?php $row++; 
                endforeach; 
            else: 
                for ($i = 0; $i < 3; $i++): ?>
                    <tr>
                        <td align="center"><?= $row + 1 ?></td>
                        <td>
                            <input type="text" name="rows[<?= $row ?>][nama]" value="">
                        </td>

                        <?php for ($tgl = 1; $tgl <= 30; $tgl++): ?>
                            <td>
                                <select name="rows[<?= $row ?>][presensi][<?= $tgl ?>]">
                                    <option value="" selected>-</option>
                                    <option value="Hadir">H</option>
                                    <option value="Ijin">I</option>
                                    <option value="Sakit">S</option>
                                    <option value="Alpha">A</option>
                                </select>
                            </td>
                        <?php endfor; ?>
                    </tr>
                    <?php $row++; 
                endfor; 
            endif; ?>
            </tbody>
        </table>

        <br>

        <button type="button" onclick="tambahBaris()">+ Tambah Baris</button>
        <button type="submit">Simpan</button>

    </form>

    <script>
    let rowCount = <?= !empty($dataPresensi) ? count($dataPresensi) : 3 ?>;

    function tambahBaris() {
        const tableBody = document.querySelector("#tabelPresensi tbody");
        const newRow = document.createElement("tr");

        let rowHTML = `<td align="center">${rowCount + 1}</td>`;
        rowHTML += `<td><input type="text" name="rows[${rowCount}][nama]"></td>`;

        for (let tgl = 1; tgl <= 30; tgl++) {
            rowHTML += `
                <td>
                    <select name="rows[${rowCount}][presensi][${tgl}]">
                        <option value="">-</option>
                        <option value="Hadir">H</option>
                        <option value="Ijin">I</option>
                        <option value="Sakit">S</option>
                        <option value="Alpha">A</option>
                    </select>
                </td>`;
        }

        newRow.innerHTML = rowHTML;
        tableBody.appendChild(newRow);
        rowCount++;
    }

    document.addEventListener("DOMContentLoaded", function() {
        // 1. TAMPILKAN POPUP SUKSES & HAPUS NOTIF
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status') && urlParams.get('status') === 'success') {
            alert("✅ Notifikasi: Data presensi berhasil disimpan!");
            setTimeout(() => {
                const notif = document.getElementById('notifikasi');
                if (notif) notif.style.display = 'none';
            }, 4000);
        }

        // 2. KEMBALIKAN NAMA *HANYA* JIKA INI RELOAD KARENA MENGGANTI BULAN
        if (sessionStorage.getItem('is_ganti_bulan') === 'true') {
            const draftNamesJSON = sessionStorage.getItem('draft_names');
            
            if (draftNamesJSON) {
                const draftNames = JSON.parse(draftNamesJSON);
                
                while (rowCount < draftNames.length) {
                    tambahBaris();
                }

                const nameInputs = document.querySelectorAll('input[name$="[nama]"]');
                draftNames.forEach((name, index) => {
                    if (nameInputs[index] && nameInputs[index].value === '') {
                        nameInputs[index].value = name;
                    }
                });
            }
            sessionStorage.removeItem('is_ganti_bulan');
            sessionStorage.removeItem('draft_names');
        } else {
            sessionStorage.removeItem('draft_names');
        }

        // 3. JURUS PAMUNGKAS: BERSIHKAN URL DARI FILTER
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }

        // 4. JURUS BRUTAL: DETEKSI TOMBOL REFRESH (F5)
        // Memaksa browser menghapus seluruh cache input yang sedang diketik
        const navEntries = performance.getEntriesByType("navigation");
        if (navEntries.length > 0 && navEntries[0].type === "reload") {
            document.getElementById('formPresensi').reset();
            
            // Sapu bersih paksa semua teks dan bulan
            document.querySelectorAll('input[type="text"], input[type="month"]').forEach(input => {
                input.value = "";
            });

            // Sapu bersih paksa semua dropdown
            document.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
        }
    });

    // Event saat input bulan diubah
    document.getElementById('bulan').addEventListener('change', function() {
        const mapel = document.getElementById('mata_pelajaran').value.trim();
        const kelas = document.getElementById('kelas').value.trim();
        const guru  = document.getElementById('nama_guru').value.trim();
        const bulan = this.value;

        if (!mapel || !kelas) {
            alert('Peringatan: Isi dulu Mata Pelajaran dan Kelas sebelum mengganti Bulan.');
            return;
        }

        // Amankan ketikan nama sementara
        const nameInputs = document.querySelectorAll('input[name$="[nama]"]');
        const names = Array.from(nameInputs).map(input => input.value);
        sessionStorage.setItem('draft_names', JSON.stringify(names));
        sessionStorage.setItem('is_ganti_bulan', 'true'); 

        // Reload data
        const url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('mapel', mapel);
        url.searchParams.set('kelas', kelas);
        url.searchParams.set('bulan', bulan);
        if (guru) url.searchParams.set('guru', guru);
        
        window.location.href = url.toString();
    });
    </script>
</body>
</html>