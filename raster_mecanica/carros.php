<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
require 'config.php';
$msg = '';

// ======================================
// EXCLUSÃO
// ======================================
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM carros WHERE id=$id");
    $msg = "Carro removido com sucesso.";
}

// ======================================
// EDIÇÃO (buscar dados para editar)
// ======================================
$editando = false;
$carro_editar = null;
if (isset($_GET['editar'])) {
    $editando = true;
    $id_edit = intval($_GET['editar']);
    $carro_editar = $conn->query("SELECT * FROM carros WHERE id=$id_edit")->fetch_assoc();
}

// ======================================
// CADASTRO OU ATUALIZAÇÃO
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = intval($_POST['ano'] ?? 0);
    $km = intval($_POST['km'] ?? 0);
    $ultima_revisao = $_POST['ultima_revisao'] ?? '';
    $cliente = $_POST['cliente'] ?? '';

    if (!empty($_POST['id'])) {
        // Atualiza
        $id = intval($_POST['id']);
        $stmt = $conn->prepare('UPDATE carros SET placa=?, modelo=?, ano=?, km=?, ultima_revisao=?, cliente=? WHERE id=?');
        $stmt->bind_param('ssiissi', $placa, $modelo, $ano, $km, $ultima_revisao, $cliente, $id);
        if ($stmt->execute()) {
            $msg = 'Carro atualizado com sucesso.';
        } else {
            $msg = 'Erro ao atualizar: ' . $conn->error;
        }
    } else {
        // Insere novo
        $stmt = $conn->prepare('INSERT INTO carros (placa, modelo, ano, km, ultima_revisao, cliente) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssiiss', $placa, $modelo, $ano, $km, $ultima_revisao, $cliente);
        if ($stmt->execute()) {
            $msg = 'Carro cadastrado com sucesso.';
        } else {
            $msg = 'Erro ao cadastrar: ' . $conn->error;
        }
    }
}

// Busca os carros cadastrados
$rows = $conn->query('SELECT * FROM carros ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Carros - Mecânica Volmar</title>
<link rel="stylesheet" href="css/style.css">
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; }
  .container { padding: 20px; }
  h2 { color: #0d47a1; }

  .alert {
    background: #c8e6c9;
    padding: 10px;
    border: 1px solid #2e7d32;
    color: #2e7d32;
    border-radius: 5px;
    margin-bottom: 10px;
  }

  /* === FORMULÁRIOS === */
  .form-cadastro, .form-edicao {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px 16px;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 25px;
  }

  .form-cadastro {
    background: #ffffff;
  }
  .form-edicao {
    background: #fff8e1;
    border: 1px solid #fbc02d;
  }

  .form-cadastro label, .form-edicao label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
  }
  .form-cadastro input, .form-edicao input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .form-cadastro button, .form-edicao button {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 14px;
    cursor: pointer;
    transition: 0.3s;
  }
  .form-cadastro button:hover, .form-edicao button:hover {
    background: #0d47a1;
  }

  .btn-cancelar {
    background: #d32f2f;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 14px;
    text-decoration: none;
    text-align: center;
    transition: 0.3s;
  }
  .btn-cancelar:hover { background: #b71c1c; }

  /* === TABELA === */
  table.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
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
  .btn-editar, .btn-excluir {
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
  }
  .btn-editar { background: #1976d2; }
  .btn-excluir { background: #d32f2f; }
  .btn-editar:hover { background: #0d47a1; }
  .btn-excluir:hover { background: #b71c1c; }

  /* === PESQUISA === */
  .search-bar { display: flex; justify-content: flex-end; margin-bottom: 10px; }
  .search-bar input { padding: 6px 10px; width: 280px; border-radius: 5px; border: 1px solid #ccc; }
</style>
</head>
<body>

<?php include 'dashboard_header.inc.php'; ?>

<main class="container">
  <?php if ($msg) echo '<div class="alert">'.$msg.'</div>'; ?>

  <!-- FORMULÁRIO DE CADASTRO OU EDIÇÃO -->
  <h2><?= $editando ? '✏️ Edição de Carro' : '🆕 Cadastro de Carro' ?></h2>
  <form method="post" class="<?= $editando ? 'form-edicao' : 'form-cadastro' ?>">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= $carro_editar['id'] ?>">
    <?php endif; ?>

    <label>Placa:</label>
    <input name="placa" required maxlength="10" value="<?= $editando ? htmlspecialchars($carro_editar['placa']) : '' ?>">
    
    <label>Modelo:</label>
    <input name="modelo" required value="<?= $editando ? htmlspecialchars($carro_editar['modelo']) : '' ?>">
    
    <label>Ano:</label>
    <input name="ano" type="number" min="1900" max="2099" required value="<?= $editando ? $carro_editar['ano'] : '' ?>">
    
    <label>KM:</label>
    <input name="km" type="number" min="0" required value="<?= $editando ? $carro_editar['km'] : '' ?>">
    
    <label>Última Revisão:</label>
    <input name="ultima_revisao" type="date" required value="<?= $editando ? $carro_editar['ultima_revisao'] : '' ?>">
    
    <label>Cliente:</label>
    <input name="cliente" required value="<?= $editando ? htmlspecialchars($carro_editar['cliente']) : '' ?>">

    <?php if ($editando): ?>
      <button type="submit">💾 Salvar Alterações</button>
      <a href="carros.php" class="btn-cancelar">Cancelar</a>
    <?php else: ?>
      <button type="submit">Salvar</button>
    <?php endif; ?>
  </form>

  <!-- LISTA DE CARROS -->
  <h2>🚗 Lista de Carros</h2>
  <div class="search-bar">
    <input type="text" id="pesquisa" placeholder="🔍 Pesquisar por placa, modelo, ano ou cliente...">
  </div>

  <table class="table" id="tabelaCarros">
    <thead>
      <tr>
        <th>ID</th>
        <th>Placa</th>
        <th>Modelo</th>
        <th>Ano</th>
        <th>KM</th>
        <th>Última Revisão</th>
        <th>Cliente</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['placa']) ?></td>
        <td><?= htmlspecialchars($r['modelo']) ?></td>
        <td><?= $r['ano'] ?></td>
        <td><?= $r['km'] ?></td>
        <td><?= date('d/m/Y', strtotime($r['ultima_revisao'])) ?></td>
        <td><?= htmlspecialchars($r['cliente']) ?></td>
        <td>
          <a href="carros.php?editar=<?= $r['id'] ?>" class="btn-editar">✏️ Editar</a>
          <a href="carros.php?excluir=<?= $r['id'] ?>" class="btn-excluir" onclick="return confirm('Deseja realmente excluir este carro?')">🗑️ Excluir</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>

<script>
document.getElementById('pesquisa').addEventListener('keyup', function() {
  const termo = this.value.toLowerCase();
  const linhas = document.querySelectorAll('#tabelaCarros tbody tr');
  linhas.forEach(linha => {
    const texto = linha.innerText.toLowerCase();
    linha.style.display = texto.includes(termo) ? '' : 'none';
  });
});
</script>

</body>
</html>
