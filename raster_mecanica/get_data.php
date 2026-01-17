<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

// define limite fixo de 10 leituras
$limit = 10;

$carro_id = isset($_GET['carro_id']) ? intval($_GET['carro_id']) : 0;

// monta query
$sql = "SELECT o.carro_id, o.data_hora, o.velocidade, o.rpm, o.temperatura, c.placa
        FROM obd_dados o
        LEFT JOIN carros c ON c.id = o.carro_id";

if ($carro_id > 0) {
    $sql .= " WHERE o.carro_id = $carro_id";
}

$sql .= " ORDER BY o.data_hora DESC LIMIT $limit";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'carro_id'    => (int)$row['carro_id'],
            'placa'       => $row['placa'] ?? '',
            'data_hora'   => $row['data_hora'],
            'velocidade'  => (float)$row['velocidade'],
            'rpm'         => (int)$row['rpm'],
            'temperatura' => (float)$row['temperatura']
        ];
    }
}

// inverte para mostrar do mais antigo ao mais recente
$data = array_reverse($data);

echo json_encode(['data' => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

