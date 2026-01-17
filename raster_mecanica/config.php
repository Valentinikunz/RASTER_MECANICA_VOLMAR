<?php
$servername = "localhost";
$username = "root";   // padrão do XAMPP
$password = "";       // senha vazia por padrão
$dbname = "raster_mecanica";  // nome exato do seu banco

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>
