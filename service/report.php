<?php
require_once '../model/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


$input = json_decode(file_get_contents("php://input"), true);
$date = $input['date'] ?? null;


if (!$date || !preg_match('/^\d{4}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing date. Format must be YYYY-MM']);
    exit;
}


list($year, $monthStr) = explode('-', $date);
$month = (int) $monthStr; 

$startDate = "$year-$month-01"; 
$endDate = date("Y-m-d", strtotime("$startDate +1 month")); 

try {
    $stmt = $pdo->prepare("
        SELECT 
            d.nama_brng AS nama_barang,
            g.stok AS stok_awal,
            COALESCE(ek.total_keluar, 0) AS total_keluar,
            GREATEST(g.stok - COALESCE(ek.total_keluar, 0), 0) AS sisa_stok,
            d.kode_brng,
            d.expire,
            d.kode_kategori,
            d.kode_sat,
            d.dasar AS harga_dasar,
            s.nama_suplier  
        FROM 
            databarang d
        JOIN 
            gudangbarang g ON d.kode_brng = g.kode_brng
        LEFT JOIN (
            SELECT 
                kode_brng,
                SUM(jml) AS total_keluar
            FROM 
                mutasibarang
            WHERE 
                kd_bangsaldari = 'GDF'
                AND tanggal >= ? AND tanggal < ?
            GROUP BY 
                kode_brng
        ) ek ON d.kode_brng = ek.kode_brng
        LEFT JOIN 
            datasuplier s ON d.kode_suplier = s.kode_suplier 
        WHERE 
            g.kd_bangsal = 'GDF' AND d.status = '1'
        GROUP BY 
            d.nama_brng,
            g.stok,
            ek.total_keluar,
            d.kode_brng,
            d.expire,
            d.kode_kategori,
            d.kode_sat,
            d.dasar,
            s.nama_suplier
        ORDER BY 
            sisa_stok DESC;
    ");

    $stmt->execute([$startDate, $endDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'tanggal' => "$month-$year",
        'total' => count($data),
        'data' => $data
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching report data', 'detail' => $e->getMessage()]);
}
?>
