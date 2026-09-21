<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/controllers/PresensiController.php';

$database = new Database();
$db = $database->getConnection();

$controller = new PresensiController($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if ($action === 'simpan') {
    $controller->simpan();
} else {
    $controller->index();
}