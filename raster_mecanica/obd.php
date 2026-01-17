<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
require 'config.php';

$msg = '';
$carroSelecionado = null;

// Buscar todos os carros cadastrados
$carros = $conn->query('SELECT * FROM carros ORDER BY placa ASC');
if ($carros->num_rows == 0) {
    $noCar = true;
} else {
    $noCar = false;

    // Verifica se o usuário escolheu um carro
    if (isset($_POST['carro_id'])) {
        $carro_id = (int)$_POST['carro_id'];
    } else {
        // Pega o primeiro carro como padrão
        $primeiro = $carros->fetch_assoc();
        $carro_id = $primeiro['id'];
        // Volta o ponteiro da query
        $carros->data_seek(0);
    }

    // Busca os dados do carro selecionado
    $stmt = $conn->prepare('SELECT * FROM carros WHERE id = ?');
    $stmt->bind_param('i', $carro_id);
    $stmt->execute();
    $carroSelecionado = $stmt->get_result()->fetch_assoc();

    // Se clicou no botão de gerar leitura
    if (isset($_POST['gerar'])) {
        $rpm = rand(700, 4000);
        $vel = rand(0, 120);
        $temp = rand(70, 110);

        // Insere a nova leitura OBD
        $stmt = $conn->prepare('INSERT INTO obd_dados (carro_id, rpm, velocidade, temperatura, data_hora) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('iiii', $carroSelecionado['id'], $rpm, $vel, $temp);

        if ($stmt->execute()) {
            // Atualiza a data da última revisão no carro
            $conn->query("UPDATE carros SET ultima_revisao = CURDATE() WHERE id = {$carroSelecionado['id']}");
            $msg = 'Leitura simulada inserida e data de revisão atualizada.';
        } else {
            $msg = 'Erro ao inserir leitura: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>OBD Simulado - Mecânica Volmar</title>
<link rel="stylesheet" href="css/style.css">
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; }
  .container { padding: 20px; }
  h2 { color: #0d47a1; }
  .alert { background: #e3f2fd; border: 1px solid #1976d2; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
  .alert.error { background: #ffcdd2; border-color: #d32f2f; color: #b71c1c; }
  select, button {
    padding: 6px 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
  }
  button {
    background: #1976d2;
    color: white;
    border: none;
    cursor: pointer;
  }
  button:hover { background: #0d47a1; }
  table.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
</style>
</head>
<body>
<?php include 'dashboard_header.inc.php'; ?>
<main class="container">
  <h2>OBD Simulado</h2>

  <?php if ($noCar): ?>
    <div class="alert">Nenhum carro cadastrado. Vá em <a href="carros.php">Carros</a> e cadastre um.</div>
  <?php else: ?>
    <form method="post" style="margin-bottom:15px;">
      <label for="carro_id"><strong>Selecione o carro:</strong></label>
      <select name="carro_id" id="carro_id" onchange="this.form.submit()">
        <?php while ($car = $carros->fetch_assoc()): ?>
          <option value="<?= $car['id'] ?>" <?= ($car['id'] == $carroSelecionado['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($car['placa']) ?> - <?= htmlspecialchars($car['modelo']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>

    <p>Simulando leituras para o carro: <strong><?= htmlspecialchars($carroSelecionado['placa']) ?></strong></p>

    <?php if ($msg): ?>
      <div class="alert"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="carro_id" value="<?= $carroSelecionado['id'] ?>">
      <button type="submit" name="gerar">Gerar Leitura Simulada</button>
    </form>

    <h3 style="margin-top:25px;">Últimas 10 leituras</h3>
    <table class="table">
      <thead><tr><th>Data/Hora</th><th>Velocidade (km/h)</th><th>RPM</th><th>Temperatura (°C)</th></tr></thead>
      <tbody>
      <?php
      $stmt = $conn->prepare('SELECT * FROM obd_dados WHERE carro_id = ? ORDER BY data_hora DESC LIMIT 10');
      $stmt->bind_param('i', $carroSelecionado['id']);
      $stmt->execute();
      $res2 = $stmt->get_result();
      while ($row = $res2->fetch_assoc()):
      ?>
        <tr>
          <td><?= $row['data_hora'] ?></td>
          <td><?= $row['velocidade'] ?></td>
          <td><?= $row['rpm'] ?></td>
          <td><?= $row['temperatura'] ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <p style="margin-top:15px;">
      Última revisão deste carro atualizada para: 
      <strong><?= date('d/m/Y', strtotime($carroSelecionado['ultima_revisao'] ?? date('Y-m-d'))) ?></strong>
    </p>
  <?php endif; ?>
</main>
</body>
</html>
