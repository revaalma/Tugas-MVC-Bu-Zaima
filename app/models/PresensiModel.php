<?php

class PresensiModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Ambil ID Murid atau Insert Baru Jika Belum Ada
    public function getOrInsertMurid($nama_siswa)
    {
        $queryCheck = "SELECT id FROM murid WHERE nama_siswa = :nama";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->execute([
            ':nama' => $nama_siswa
        ]);

        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id'];
        }

        $queryInsert = "INSERT INTO murid (nama_siswa) VALUES (:nama)";
        $stmtInsert = $this->conn->prepare($queryInsert);
        $stmtInsert->execute([
            ':nama' => $nama_siswa
        ]);

        return $this->conn->lastInsertId();
    }

    // Simpan Data Absensi ke Tabel Absensi
    public function saveAbsensi(
        $murid_id,
        $mapel,
        $kelas,
        $guru,
        $bulan,
        $tanggal,
        $status
    ) {
        // Cek apakah data untuk murid, mapel, kelas, bulan, dan tanggal ini sudah ada
        $queryCheck = "SELECT id FROM absensi
                        WHERE murid_id = :murid_id
                        AND mata_pelajaran = :mapel
                        AND kelas = :kelas
                        AND bulan = :bulan
                        AND tanggal = :tanggal";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->execute([
            ':murid_id' => $murid_id,
            ':mapel'    => $mapel,
            ':kelas'    => $kelas,
            ':bulan'    => $bulan,
            ':tanggal'  => $tanggal
        ]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Sudah ada -> UPDATE status & nama guru, jangan insert baru
            $queryUpdate = "UPDATE absensi
                            SET status = :status, nama_guru = :guru
                            WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            return $stmtUpdate->execute([
                ':status' => $status,
                ':guru'   => $guru,
                ':id'     => $existing['id']
            ]);
        } else {
            // Belum ada -> INSERT baru
            $queryInsert = "INSERT INTO absensi
                            (murid_id, mata_pelajaran, kelas, nama_guru, bulan, tanggal, status)
                            VALUES
                            (:murid_id, :mapel, :kelas, :guru, :bulan, :tanggal, :status)";
            $stmtInsert = $this->conn->prepare($queryInsert);
            return $stmtInsert->execute([
                ':murid_id' => $murid_id,
                ':mapel'    => $mapel,
                ':kelas'    => $kelas,
                ':guru'     => $guru,
                ':bulan'    => $bulan,
                ':tanggal'  => $tanggal,
                ':status'   => $status
            ]);
        }
    }
        // Ambil Data Presensi Berdasarkan Mapel, Kelas, dan Bulan
    public function getPresensi($mapel, $kelas, $bulan)
    {
        $query = "SELECT a.murid_id, a.mata_pelajaran, a.kelas, a.nama_guru,
                        a.bulan, a.tanggal, a.status, m.nama_siswa
                FROM absensi a
                JOIN murid m ON m.id = a.murid_id
                WHERE a.mata_pelajaran = :mapel
                    AND a.kelas = :kelas
                    AND a.bulan = :bulan
                ORDER BY a.murid_id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':mapel' => $mapel,
            ':kelas' => $kelas,
            ':bulan' => $bulan
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $r) {
            $mid = $r['murid_id'];

            if (!isset($data[$mid])) {
                $data[$mid] = [
                    'nama'      => $r['nama_siswa'],
                    'nama_guru' => $r['nama_guru'],
                    'presensi'  => []
                ];
            }

            // Kolom tanggal berisi angka hari (1-30) langsung, bukan tanggal penuh
            $tgl = (int) $r['tanggal'];
            $data[$mid]['presensi'][$tgl] = $r['status'];
        }

        return $data;
    }
}