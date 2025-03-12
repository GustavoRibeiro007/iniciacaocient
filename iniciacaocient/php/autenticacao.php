<?php
session_start();
include 'dbconfig.php';

// Função para criptografar o nome do arquivo
function encryptFileName($fileName) {
    return hash('sha256', $fileName . time());
}

// Função para verificar se o arquivo é um .docx e tem no máximo 5 MB
function isValidFile($file) {
    $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    if (in_array($file['type'], $allowedMimeTypes) && $file['size'] <= $maxFileSize) {
        return true;
    }
    return false;
}

// Função para extrair o texto do arquivo .docx
function extractTextFromDocx($filePath) {
    $zip = new ZipArchive;
    $text = '';
    if ($zip->open($filePath) === TRUE) {
        if (($index = $zip->locateName('word/document.xml')) !== false) {
            $data = $zip->getFromIndex($index);
            $xml = simplexml_load_string($data);
            $text = strip_tags($xml->asXML());
        }
        $zip->close();
    }
    return $text;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['projectTitle'])) {
    $file = $_FILES['file'];
    $projectTitle = trim($_POST['projectTitle']);

    if (isValidFile($file) && !empty($projectTitle)) {
        $encryptedFileName = encryptFileName($file['name']) . '.docx';
        $uploadDir = '../uploads/';
        $uploadFilePath = $uploadDir . $encryptedFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            $_SESSION['uploadFilePath'] = $uploadFilePath;
            $_SESSION['originalFileName'] = $file['name'];
            $_SESSION['projectTitle'] = $projectTitle;
            $_SESSION['fileUploaded'] = true;

            // Inserir dados no banco de dados
            $stmt = $conn->prepare("INSERT INTO Uploads (Nome_Arquivo, Caminho_Arquivo, Tamanho, Validacao) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $encryptedFileName, $uploadFilePath, $file['size'], $validacao);

            // Definir valores para $validacao
            $validacao = 'pendente'; // Exemplo de valor

            if ($stmt->execute()) {
                $_SESSION['message'] = "Arquivo enviado e registrado com sucesso!";
            } else {
                $_SESSION['message'] = "Erro ao registrar o arquivo no banco de dados.";
            }

            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['message'] = "Erro ao enviar o arquivo.";
        }
    } else {
        $_SESSION['message'] = "Arquivo inválido ou nome do projeto não fornecido. Certifique-se de que é um arquivo .docx, tem no máximo 5 MB e que o nome do projeto foi fornecido.";
    }
}

// Função para baixar o arquivo com o nome alterado
if (isset($_GET['download']) && isset($_SESSION['uploadFilePath'])) {
    $filePath = $_SESSION['uploadFilePath'];
    $projectTitle = $_SESSION['projectTitle'];
    $newFileName = $projectTitle . '.docx';

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $newFileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        $_SESSION['message'] = "Arquivo não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Upload de Arquivo</title>
</head>
<body>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label for="file">Escolha um arquivo .docx (máximo 5 MB):</label>
        <input type="file" name="file" id="file" required>
        <br>
        <label for="projectTitle">Nome do Projeto:</label>
        <input type="text" name="projectTitle" id="projectTitle" required>
        <br>
        <button type="submit">Enviar</button>
    </form>

    <?php if (isset($_SESSION['fileUploaded']) && $_SESSION['fileUploaded']): ?>
        <h2>Arquivo Enviado</h2>
        <p><strong>Nome do Projeto:</strong> <?php echo $_SESSION['projectTitle']; ?></p>
        <p><strong>Conteúdo do Arquivo:</strong></p>
        <pre><?php echo extractTextFromDocx($_SESSION['uploadFilePath']); ?></pre>
        <a href="?download=true">Baixar Arquivo</a>
        <?php unset($_SESSION['fileUploaded']); ?>
    <?php endif; ?>
</body>
</html>