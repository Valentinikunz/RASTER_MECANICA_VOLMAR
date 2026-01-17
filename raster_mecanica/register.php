<?php
require 'config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $errors[] = 'Preencha todos os campos.';
    } else {
        // verifica se email já existe
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = 'E-mail já cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nome, $email, $hash);
            if ($stmt->execute()) {
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Erro ao cadastrar: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Registro - Mecânica Volmar</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="centered-body">
  <div class="card login-card">
    <img src="img/logo.png" alt="Logo" class="logo-small">
    <h2>Cadastre-se - Mecânica Volmar</h2>
    <?php if ($errors): ?>
      <div class="alert"><?php foreach($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
    <?php endif; ?>
    <form method="post">
      <label>Nome</label>
      <input type="text" name="nome" required>
      <label>E-mail</label>
      <input type="email" name="email" required>
      <label>Senha</label>
      <input type="password" name="senha" required>
      <button type="submit">Cadastrar</button>
    </form>
    <p>Já tem conta? <a href="login.php">Entrar</a></p>
  </div>
</body>
</html>
