<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');



if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);  
    exit();  
}

require_once "../model/db.php";  

try {

    $stmt = $pdo->prepare("
        SELECT 
            d.kode_brng, 
            d.nama_brng, 
            d.expire, 
            d.kode_kategori, 
            d.kode_sat, 
            d.dasar AS harga_dasar, 
            d.jualbebas AS harga_jual,
            g.stok,
            s.nama_suplier  
        FROM databarang d
        JOIN gudangbarang g ON d.kode_brng = g.kode_brng
        LEFT JOIN datasuplier s ON d.kode_suplier = s.kode_suplier 
        WHERE g.kd_bangsal = 'GDF' AND d.status = '1'
    ");

  
    $stmt->execute();

 
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  
    echo json_encode([
        "success" => true,
        "length" => count($data),
        "data" => $data
    ]);
} catch (PDOException $e) {
   
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>
