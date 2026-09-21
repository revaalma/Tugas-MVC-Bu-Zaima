<?php
require_once __DIR__ . '/../models/PresensiModel.php';

class PresensiController {
    private $model;

    public function __construct($db = null) {
        if ($db === null) {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
        }
        $this->model = new PresensiModel($db);
    }

    public function index() {
        $filter_mapel = isset($_GET['mapel']) ? trim($_GET['mapel']) : '';
        $filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
        $filter_bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
        $filter_guru  = isset($_GET['guru']) ? trim($_GET['guru']) : '';

        $dataPresensi = [];
        if (!empty($filter_mapel) && !empty($filter_kelas) && !empty($filter_bulan)) {
            $dataPresensi = $this->model->getPresensi($filter_mapel, $filter_kelas, $filter_bulan);
        }

        require_once __DIR__ . '/../views/index.php';
    }

    public function simpan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rows'])) {
            $mapel = isset($_POST['mata_pelajaran']) ? trim($_POST['mata_pelajaran']) : '';
            $kelas = isset($_POST['kelas']) ? trim($_POST['kelas']) : '';
            $guru  = isset($_POST['nama_guru']) ? trim($_POST['nama_guru']) : '';
            $bulan = isset($_POST['bulan']) ? trim($_POST['bulan']) : '';

            foreach ($_POST['rows'] as $row) {
                $nama = trim($row['nama']);

                if (!empty($nama) && isset($row['presensi'])) {
                    $murid_id = $this->model->getOrInsertMurid($nama);

                    foreach ($row['presensi'] as $tanggal => $status) {
                        if (!empty($status)) {
                            $this->model->saveAbsensi($murid_id, $mapel, $kelas, $guru, $bulan, $tanggal, $status);
                        }
                    }
                }
            }

            // PERBAIKAN: Hanya kirim status success, jangan bawa mapel/kelas/bulan dll
            // Ini akan membuat form otomatis kosong kembali
            header("Location: index.php?status=success");
            exit;
        }
    }
}