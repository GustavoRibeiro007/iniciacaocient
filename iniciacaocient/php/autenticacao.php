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

// Função para obter o nome do arquivo dentro do .docx
function getFileNameFromDocx($filePath) {
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        if (($index = $zip->locateName('docProps/core.xml')) !== false) {
            $data = $zip->getFromIndex($index);
            $xml = simplexml_load_string($data);
            $namespaces = $xml->getNamespaces(true);
            $core = $xml->children($namespaces['cp']);
            $title = (string)$core->title;
            $zip->close();
            return $title;
        }
        $zip->close();
    }
    return null;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if (isValidFile($file)) {
        $encryptedFileName = encryptFileName($file['name']) . '.docx';
        $uploadDir = '../uploads/';
        $uploadFilePath = $uploadDir . $encryptedFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            $_SESSION['uploadFilePath'] = $uploadFilePath;
            $_SESSION['originalFileName'] = $file['name'];

            // Inserir dados no banco de dados
            $stmt = $conn->prepare("INSERT INTO Uploads (Nome_Arquivo, Caminho_Arquivo, Tamanho, Validacao) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $file['name'], $uploadFilePath, $file['size'], $validacao);

            // Definir valores para $validacao
            $validacao = 'pendente'; // Exemplo de valor

            if ($stmt->execute()) {
                echo "Arquivo enviado e registrado com sucesso!";
            } else {
                echo "Erro ao registrar o arquivo no banco de dados.";
            }

            $stmt->close();
        } else {
            echo "Erro ao enviar o arquivo.";
        }
    } else {
        echo "Arquivo inválido. Certifique-se de que é um arquivo .docx e tem no máximo 5 MB.";
    }
}

// Função para baixar o arquivo com o nome alterado
if (isset($_GET['download']) && isset($_SESSION['uploadFilePath'])) {
    $filePath = $_SESSION['uploadFilePath'];
    $originalFileName = $_SESSION['originalFileName'];
    $newFileName = getFileNameFromDocx($filePath) . '.docx';

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
        echo "Arquivo não encontrado.";
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
    <form action="" method="post" enctype="multipart/form-data">
        <label for="file">Escolha um arquivo .docx (máximo 5 MB):</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Enviar</button>
    </form>

    <?php if (isset($_SESSION['uploadFilePath'])): ?>
        <a href="?download=true">Baixar Arquivo</a>
    <?php endif; ?>
</body>
</html>