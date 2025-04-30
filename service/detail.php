<?php
require_once '../model/db.php';
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');



if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);  
    exit();  
}

$input = json_decode(file_get_contents("php://input"), true);
$no_permintaan = $input['no_permintaan'] ?? null;

if (!$no_permintaan) {
    http_response_code(400);
    echo json_encode(['error' => 'No Permintaan is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            dpm.no_permintaan, 
            dpm.kode_brng, 
            db.nama_brng, 
            dpm.keterangan, 
            dpm.kode_sat, 
            dpm.jumlah,
            p1.nama,
            pm.nip,
            pm.tanggal,
            gb.stok,
            b1.nm_bangsal AS dari, 
            b2.nm_bangsal AS untuk
        FROM detail_permintaan_medis dpm
        JOIN gudangbarang gb ON gb.kode_brng = dpm.kode_brng
        JOIN permintaan_medis pm ON pm.no_permintaan = dpm.no_permintaan 
        JOIN pegawai p1 ON p1.nik = pm.nip
        JOIN bangsal b1 ON pm.kd_bangsal = b1.kd_bangsal
        JOIN bangsal b2 ON pm.kd_bangsaltujuan = b2.kd_bangsal
        JOIN databarang db ON dpm.kode_brng = db.kode_brng
        WHERE dpm.no_permintaan = ? AND gb.kd_bangsal = 'GDF'
    ");
    $stmt->execute([$no_permintaan]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($data) === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'No data found for the given no_permintaan']);
    } else {
        echo json_encode(['success' => true, 'data' => $data]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching detail permintaan data', 'detail' => $e->getMessage()]);
}
