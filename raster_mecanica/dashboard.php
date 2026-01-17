<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config.php';

// logo padrão
$logo = 'img/logo.png';
$res = $conn->query('SELECT logo FROM configuracoes WHERE id=1 LIMIT 1');
if ($res && $r = $res->fetch_assoc() && !empty($r['logo'])) {
    $logo = $r['logo'];
}

// estatísticas básicas
$countCars = $conn->query('SELECT COUNT(*) AS c FROM carros')->fetch_assoc()['c'] ?? 0;
$lastReading = $conn->query('SELECT * FROM obd_dados ORDER BY data_hora DESC LIMIT 1')->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Dashboard - Mecânica Volmar</title>
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f7f8fa;
    margin: 0;
    padding: 0;
  }
  header.site-header {
    background: #101828;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
  }
  header .logo {
    height: 40px;
    margin-right: 10px;
  }
  nav.main-nav {
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 10px;
  }
  nav.main-nav a {
    margin-right: 20px;
    color: #333;
    text-decoration: none;
  }
  .container {
    padding: 20px;
  }
  .cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }
  .card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 20px;
    flex: 1;
    min-width: 200px;
    text-align: center;
  }
  .big {
    font-size: 28px;
    font-weight: bold;
  }
  .charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
    margin-top: 20px;
  }
  table.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  table.table th, table.table td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
  }
  table.table th {
    background: #1565c0;
    color: white;
  }
  canvas {
    width: 100% !important;
    height: 250px !important;
  }
</style>
</head>
<body>
<header class="site-header">
  <div class="header-left" style="display:flex;align-items:center;">
    <img src="<?=htmlspecialchars($logo)?>" alt="Logo" class="logo">
    <h1>Mecânica Volmar</h1>
  </div>
  <div class="header-right">
    <span>Olá, <?=htmlspecialchars($_SESSION['user_name'])?></span>
    <a href="logout.php" class="btn-link" style="color:white;margin-left:10px;">Sair</a>
  </div>
</header>

<nav class="main-nav">
  <a href="dashboard.php">Dashboard</a>
  <a href="carros.php">Carros</a>
  <a href="obd.php">OBD Simulado</a>
</nav>

<main class="container">
  <section class="cards">
    <div class="card">
      <h3>Carros cadastrados</h3>
      <p class="big"><?=$countCars?></p>
    </div>
    <div class="card">
      <h3>Última velocidade</h3>
      <p class="big"><?=$lastReading['velocidade'] ?? '--'?> km/h</p>
    </div>
    <div class="card">
      <h3>Último RPM</h3>
      <p class="big"><?=$lastReading['rpm'] ?? '--'?></p>
    </div>
  </section>

  <form id="formCarro" style="margin-top:25px;margin-bottom:15px;">
    <label for="carroSelect"><strong>Filtrar por carro:</strong></label>
    <select id="carroSelect" name="carro_id">
      <option value="0">Todos</option>
      <?php
      $cars = $conn->query('SELECT id, placa, modelo FROM carros ORDER BY placa');
      while ($c = $cars->fetch_assoc()) {
          echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['placa']).' - '.htmlspecialchars($c['modelo']).'</option>';
      }
      ?>
    </select>
  </form>

  <section>
    <h2>Gráficos em tempo real</h2>
    <div class="charts-grid">
      <div>
        <h3>Velocidade (km/h)</h3>
        <canvas id="chartVel"></canvas>
      </div>
      <div>
        <h3>RPM</h3>
        <canvas id="chartRPM"></canvas>
      </div>
      <div>
        <h3>Temperatura (°C)</h3>
        <canvas id="chartTemp"></canvas>
      </div>
    </div>
  </section>

  <section>
    <h2>Histórico (últimas 10 leituras)</h2>
    <table class="table">
      <thead><tr><th>Data/Hora</th><th>Carro</th><th>Vel</th><th>RPM</th><th>Temp</th></tr></thead>
      <tbody>
      <?php
      $stmt = $conn->query('SELECT o.*, c.placa FROM obd_dados o LEFT JOIN carros c ON c.id = o.carro_id ORDER BY o.data_hora DESC LIMIT 10');
      while ($row = $stmt->fetch_assoc()) {
          echo '<tr><td>'.$row['data_hora'].'</td><td>'.htmlspecialchars($row['placa']).'</td><td>'.$row['velocidade'].'</td><td>'.$row['rpm'].'</td><td>'.$row['temperatura'].'</td></tr>';
      }
      ?>
      </tbody>
    </table>
  </section>
</main>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let chartVel = null;
let chartRPM = null;
let chartTemp = null;

async function fetchData() {
  try {
    const carroSelect = document.getElementById('carroSelect');
    const carro_id = carroSelect ? carroSelect.value : 0;
    const response = await fetch('get_data.php?limit=10&carro_id=' + carro_id);
    const json = await response.json();

    if (!json || !json.data || json.data.length === 0) {
      console.warn("Nenhum dado retornado para gráficos");
      // Apaga gráficos existentes, se houver
      if (chartVel instanceof Chart) chartVel.destroy();
      if (chartRPM instanceof Chart) chartRPM.destroy();
      if (chartTemp instanceof Chart) chartTemp.destroy();
      return;
    }

    // Dados
    const labels = json.data.map(r => new Date(r.data_hora).toLocaleTimeString());
    const vel = json.data.map(r => r.velocidade);
    const rpm = json.data.map(r => r.rpm);
    const temp = json.data.map(r => r.temperatura);

    const options = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      scales: {
        x: { title: { display: true, text: 'Hora da Leitura' } },
        y: { beginAtZero: true }
      }
    };

    // Destruir gráficos anteriores antes de redesenhar
    if (chartVel instanceof Chart) chartVel.destroy();
    if (chartRPM instanceof Chart) chartRPM.destroy();
    if (chartTemp instanceof Chart) chartTemp.destroy();

    // Criar novos gráficos
    chartVel = new Chart(document.getElementById('chartVel').getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Velocidade (km/h)',
          data: vel,
          borderColor: '#1976d2',
          borderWidth: 2,
          tension: 0.3,
          pointRadius: 3,
          fill: false
        }]
      },
      options
    });

    chartRPM = new Chart(document.getElementById('chartRPM').getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'RPM',
          data: rpm,
          borderColor: '#43a047',
          borderWidth: 2,
          tension: 0.3,
          pointRadius: 3,
          fill: false
        }]
      },
      options
    });

    chartTemp = new Chart(document.getElementById('chartTemp').getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Temperatura (°C)',
          data: temp,
          borderColor: '#e53935',
          borderWidth: 2,
          tension: 0.3,
          pointRadius: 3,
          fill: false
        }]
      },
      options
    });

  } catch (error) {
    console.error("Erro ao atualizar gráficos:", error);
  }
}

// Atualização automática e filtro dinâmico
document.getElementById('carroSelect')?.addEventListener('change', fetchData);
fetchData();
setInterval(fetchData, 3000);
</script>

</body>
</html>
