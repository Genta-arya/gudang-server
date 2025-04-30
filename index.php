<?php
// Contoh routing sederhana jika perlu
if ($_SERVER['REQUEST_URI'] === '/api/gudang-farmasi/permintaan') {
    require_once 'service/permintaan.php';  // Pastikan path ke service yang benar
}
?>
