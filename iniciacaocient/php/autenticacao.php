<?php
session_start();
include 'dbconfig.php';
require '../../vendor/autoload.php'; // Inclua o autoload do Composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Função para criptografar o nome do arquivo
function encryptFileName($fileName) {
    return hash('sha256', $fileName . time());
}

// Função para gerar o PDF a partir dos dados do formulário
function generatePDF($formData, $filePath) {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    // Conteúdo do PDF
    $html = "
        <h1>Projeto Interdisciplinar</h1>
        <p><strong>Nome do Projeto:</strong> {$formData['projectTitle']}</p>
        <p><strong>Quantidade de Participantes:</strong> {$formData['quantParticipantes']}</p>
        <p><strong>Curso:</strong> {$formData['tipoCurso']}</p>
        <p><strong>Semestre:</strong> {$formData['semestre']}</p>
        <p><strong>Orientador:</strong> {$formData['orientador']}</p>
        <p><strong>Resumo:</strong> {$formData['resumo']}</p>
    ";

    // Adicionar participantes
    for ($i = 1; $i <= $formData['quantParticipantes']; $i++) {
        $html .= "<p><strong>Participante {$i}:</strong> Nome: {$formData["participanteNome{$i}"]}, RA: {$formData["participanteRA{$i}"]}</p>";
    }

    // Adicionar link do GitHub, se disponível
    if (!empty($formData['githubLink'])) {
        $html .= "<p><strong>GitHub:</strong> <a href='{$formData['githubLink']}'>{$formData['githubLink']}</a></p>";
    }

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Salvar o PDF no caminho especificado
    file_put_contents($filePath, $dompdf->output());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['projectTitle'])) {
    $formData = [
        'projectTitle' => trim($_POST['projectTitle']),
        'quantParticipantes' => $_POST['quantParticipantes'],
        'tipoCurso' => $_POST['tipo-curso'],
        'semestre' => $_POST['semestre'],
        'orientador' => trim($_POST['orientador']),
        'resumo' => trim($_POST['resumo']),
        'githubLink' => isset($_POST['githubLink']) ? trim($_POST['githubLink']) : ''
    ];

    // Adicionar participantes ao array
    for ($i = 1; $i <= $formData['quantParticipantes']; $i++) {
        $formData["participanteNome{$i}"] = trim($_POST["participanteNome{$i}"]);
        $formData["participanteRA{$i}"] = trim($_POST["participanteRA{$i}"]);
    }

    // Diretório de uploads
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Cria o diretório se não existir
    }

    // Processar o arquivo enviado pelo usuário
    $uploadedFilePath = null;
    $fileValidation = "Válido"; // Valor padrão para validação
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Validar tipo e tamanho do arquivo
        $allowedExtensions = ['docx'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($fileExtension, $allowedExtensions)) {
            $fileValidation = "Inválido: Extensão não permitida";
            $_SESSION['message'] = "Erro: Apenas arquivos .docx são permitidos.";
            header("Location: ../index.php");
            exit;
        }

        if ($fileSize > $maxFileSize) {
            $fileValidation = "Inválido: Tamanho excedido";
            $_SESSION['message'] = "Erro: O arquivo excede o tamanho máximo de 5 MB.";
            header("Location: ../index.php");
            exit;
        }

        // Gerar um nome único para o arquivo
        $newFileName = encryptFileName($fileName) . '.' . $fileExtension;
        $uploadedFilePath = $uploadDir . $newFileName;

        // Mover o arquivo para o diretório de uploads
        if (!move_uploaded_file($fileTmpPath, $uploadedFilePath)) {
            $_SESSION['message'] = "Erro ao salvar o arquivo enviado.";
            header("Location: ../index.php");
            exit;
        }

        // Inserir informações do arquivo na tabela Uploads
        $stmtUpload = $conn->prepare("INSERT INTO Uploads (Nome_Arquivo, Caminho_Arquivo, Tamanho, Validacao) VALUES (?, ?, ?, ?)");
        $stmtUpload->bind_param("ssis", $fileName, $uploadedFilePath, $fileSize, $fileValidation);
        $stmtUpload->execute();
        $stmtUpload->close();
    } else {
        $_SESSION['message'] = "Erro: Nenhum arquivo foi enviado.";
        header("Location: ../index.php");
        exit;
    }

    // Gerar o PDF
    $encryptedFileName = encryptFileName($formData['projectTitle']) . '.pdf';
    $pdfFilePath = $uploadDir . $encryptedFileName;
    generatePDF($formData, $pdfFilePath);

    // Inserir dados no banco de dados
    $stmt = $conn->prepare("INSERT INTO Formularios (Nome_Projeto, Quantidade_Participantes, Curso, Semestre, Orientador, Resumo, GitHub_Link, Caminho_PDF) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sissssss",
        $formData['projectTitle'],
        $formData['quantParticipantes'],
        $formData['tipoCurso'],
        $formData['semestre'],
        $formData['orientador'],
        $formData['resumo'],
        $formData['githubLink'],
        $pdfFilePath
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Formulário enviado, PDF gerado e arquivo salvo com sucesso!";
        $_SESSION['uploadFilePath'] = $pdfFilePath;
        $_SESSION['projectTitle'] = $formData['projectTitle'];
        header("Location: sucess.php");
        exit;
    } else {
        $_SESSION['message'] = "Erro ao registrar o formulário no banco de dados.";
    }

    $stmt->close();
}


// Função para visualizar o PDF no navegador
if (isset($_GET['view']) && isset($_SESSION['uploadFilePath'])) {
    $filePath = $_SESSION['uploadFilePath'];

    if (file_exists($filePath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    } else {
        $_SESSION['message'] = "Arquivo não encontrado.";
        header("Location: sucess.php");
        exit;
    }
}

// Função para baixar o PDF com o nome do projeto
if (isset($_GET['download']) && isset($_SESSION['uploadFilePath']) && isset($_SESSION['projectTitle'])) {
    $filePath = $_SESSION['uploadFilePath'];
    $projectTitle = $_SESSION['projectTitle']; // Nome do projeto

    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        // Usar o nome do projeto como nome do arquivo ao baixar
        header('Content-Disposition: attachment; filename="' . $projectTitle . '.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($filePath);
        exit;
    } else {
        $_SESSION['message'] = "Arquivo não encontrado.";
        header("Location: sucess.php");
        exit;
    }
}
?>