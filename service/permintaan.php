<?php


header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$pdo = require_once('../model/db.php'); 


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);  
    exit();  
}


$data = json_decode(file_get_contents("php://input"), true);
$date = $data['date'] ?? null;


if (!$date) {
    http_response_code(400);
    echo json_encode(["error" => "Date is required"]);
    exit;
}


$sql = "
SELECT 
  pm.no_permintaan, 
  b1.nm_bangsal AS dari, 
  b2.nm_bangsal AS untuk, 
  p1.nama,
  pm.tanggal, 
  pm.status
FROM permintaan_medis pm
JOIN pegawai p1 ON pm.nip = p1.nik
JOIN bangsal b1 ON pm.kd_bangsal = b1.kd_bangsal
JOIN bangsal b2 ON pm.kd_bangsaltujuan = b2.kd_bangsal
WHERE DATE(pm.tanggal) = ? AND pm.status = 'Baru'
";


$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode(["success" => true, "data" => $rows]);

?>
