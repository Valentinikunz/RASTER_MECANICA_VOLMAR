<?php
session_start();
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nome'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Senha inválida.';
        }
    } else {
        $error = 'Usuário não encontrado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Login - Mecânica Volmar</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="centered-body">
  <div class="card login-card">
    <img src="img/logo.png" alt="Logo" class="logo-small">
    <h2>Mecânica Volmar</h2>
    <?php if ($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <label>E-mail</label>
      <input type="email" name="email" required>
      <label>Senha</label>
      <input type="password" name="senha" required>
      <button type="submit">Entrar</button>
    </form>
    <p>Não tem conta? <a href="register.php">Cadastrar</a></p>
  </div>
</body>
</html>
