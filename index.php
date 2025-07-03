<?php
session_start();
require_once __DIR__ . '/php/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email && $senha) {
        $pdo = getConexao();
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Email = ? AND Status = 'ativo' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['Senha'])) {
            $_SESSION['usuario_id'] = $user['ID'];
            $_SESSION['usuario_nome'] = $user['Nome'];
            $_SESSION['usuario_tipo'] = $user['Tipo_Usuario'];
            if ($user['Tipo_Usuario'] === 'admin' || $user['Tipo_Usuario'] === 'coordenador') {
                header('Location: HTML/telaadm.html');
            } else {
                header('Location: php/form.php');
            }
            exit;
        } else {
            $erro = 'Usuário ou senha inválidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Projetos</title>
    <link rel="stylesheet" href="css/form.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            padding: 32px 24px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .login-container h2 {
            text-align: center;
            color: darkred;
            margin-bottom: 24px;
        }
        .login-container label {
            margin-top: 12px;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            margin-top: 24px;
            padding: 12px;
            background: darkred;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 17px;
            cursor: pointer;
        }
        .login-container .erro {
            color: #b00;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($erro): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required autofocus>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
