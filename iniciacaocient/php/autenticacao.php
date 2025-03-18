<?php
session_start();
include 'dbconfig.php';
require '../../vendor/autoload.php'; // Inclua o autoload do Composer

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

// Configure PhpWord para usar DomPDF
Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
Settings::setPdfRendererPath('../../vendor/dompdf/dompdf');

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

            $validacao = 'pendente'; // Status inicial

            if ($stmt->execute()) {
                $_SESSION['message'] = "Arquivo enviado e registrado com sucesso!";
                header("Location: sucess.php");
                exit;
            } else {
                $_SESSION['message'] = "Erro ao registrar o arquivo no banco de dados.";
            }

            $stmt->close();
        } else {
            $_SESSION['message'] = "Erro ao enviar o arquivo.";
        }
    } else {
        $_SESSION['message'] = "Arquivo inválido ou nome do projeto não fornecido. Certifique-se de que é um arquivo .docx, tem no máximo 5 MB e que o nome do projeto foi fornecido.";
    }
}

// Função para baixar o arquivo como PDF com o nome do projeto
if (isset($_GET['download']) && isset($_SESSION['uploadFilePath'])) {
    $filePath = $_SESSION['uploadFilePath'];
    $projectTitle = $_SESSION['projectTitle'];
    $newFileName = $projectTitle . '.pdf';

    if (file_exists($filePath)) {
        $phpWord = IOFactory::load($filePath, 'Word2007');
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');

        // Cabeçalhos para forçar o download do PDF
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $newFileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        $pdfWriter->save('php://output');
        exit;
    } else {
        $_SESSION['message'] = "Arquivo não encontrado.";
    }
}

// Função para visualizar o arquivo como PDF no navegador
if (isset($_GET['view']) && isset($_SESSION['uploadFilePath'])) {
    $filePath = $_SESSION['uploadFilePath'];
    $projectTitle = $_SESSION['projectTitle'];
    $newFileName = $projectTitle . '.pdf';

    if (file_exists($filePath)) {
        $phpWord = IOFactory::load($filePath, 'Word2007');
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');

        // Cabeçalhos para exibir o PDF no navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $newFileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        $pdfWriter->save('php://output');
        exit;
    } else {
        $_SESSION['message'] = "Arquivo não encontrado.";
        header("Location: sucess.php");
        exit;
    }
}
?>