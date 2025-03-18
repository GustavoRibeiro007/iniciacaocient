<?php
session_start();
?>
<!DOCTYPE html>

<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Formulário Enviado</title>
    <link rel="stylesheet" href="../css/sucess.css">
</head>
<body>
    <div class="container">
        <div class="message">
            <i class="fas fa-check-circle"></i>
            <h1>Formulário Enviado!</h1>
            <p>Seu formulário foi enviado com sucesso.</p>
            <p><strong>Nome do Projeto:</strong> <?php echo htmlspecialchars($_SESSION['projectTitle'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if (isset($_SESSION['encryptedFileName'])): ?>
                <a href="../php/autenticacao.php?view=true" target="_blank" class="view-btn">Visualizar Arquivo</a>
            <?php else: ?>
                <p>O arquivo PDF não está disponível para visualização.</p>
            <?php endif; ?>
            <a href="../php/autenticacao.php?download=true" class="download-btn">Baixar Arquivo em PDF</a>
            <a href="../php/form.php" class="back-btn">Voltar para o formulário</a>
        </div>
    </div>
</body>
</html>