<?php
require 'config.php';
$logo = 'img/logo.png';
$res = $conn->query('SELECT logo FROM configuracoes WHERE id=1 LIMIT 1');
if ($res && $r = $res->fetch_assoc()) {
    if (!empty($r['logo'])) $logo = $r['logo'];
}
?>
<header class="site-header">
  <div class="header-left">
    <img src="<?=htmlspecialchars($logo)?>" alt="Logo" class="logo">
    <h1>Mecânica Volmar</h1>
  </div>
  <div class="header-right">
    <span>Olá, <?=htmlspecialchars($_SESSION['user_name'])?></span>
    <a href="logout.php" class="btn-link">Sair</a>
  </div>
</header>
<nav class="main-nav">
  <a href="dashboard.php">Dashboard</a>
  <a href="carros.php">Carros</a>
  <a href="obd.php">OBD Simulado</a>
</nav>
