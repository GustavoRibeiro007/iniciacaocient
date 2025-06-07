<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$pdo = getConexao();

switch ($acao) {
    case 'upload':
        realizarUpload($pdo);
        break;
    case 'listar':
        listarUploads($pdo);
        break;
    case 'validar':
        validarUpload($pdo);
        break;
    default:
        echo json_encode(['erro' => 'Ação não especificada']);
}

function realizarUpload($pdo) {
    if (!isset($_FILES['arquivo'])) {
        echo json_encode(['erro' => 'Nenhum arquivo enviado']);
        return;
    }
    
    $arquivo = $_FILES['arquivo'];
    $tipo = $_POST['tipo'] ?? 'pdf'; // Tipo padrão é PDF
    
    // Validar tipo de arquivo
    $tiposPermitidos = [
        'pdf' => ['application/pdf'],
        'imagem' => ['image/jpeg', 'image/png', 'image/gif'],
        'documento' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    ];
    
    if (!isset($tiposPermitidos[$tipo]) || !in_array($arquivo['type'], $tiposPermitidos[$tipo])) {
        echo json_encode(['erro' => 'Tipo de arquivo não permitido']);
        return;
    }
    
    // Criar diretório de upload se não existir
    $diretorioUpload = '../uploads/' . date('Y/m');
    if (!file_exists($diretorioUpload)) {
        mkdir($diretorioUpload, 0777, true);
    }
    
    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid() . '.' . $extensao;
    $caminhoCompleto = $diretorioUpload . '/' . $nomeArquivo;
    
    try {
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            // Registrar upload no banco
            $stmt = $pdo->prepare("INSERT INTO Uploads (Nome_Arquivo, Caminho_Arquivo, Tamanho, Validacao) 
                                  VALUES (?, ?, ?, 'pendente')");
            
            $stmt->execute([
                $arquivo['name'],
                $caminhoCompleto,
                $arquivo['size']
            ]);
            
            $uploadId = $pdo->lastInsertId();
            
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Arquivo enviado com sucesso',
                'upload' => [
                    'id' => $uploadId,
                    'nome' => $arquivo['name'],
                    'caminho' => $caminhoCompleto,
                    'tamanho' => $arquivo['size']
                ]
            ]);
        } else {
            echo json_encode(['erro' => 'Erro ao mover arquivo']);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao registrar upload: ' . $e->getMessage()]);
    }
}

function listarUploads($pdo) {
    $validacao = $_GET['validacao'] ?? '';
    
    try {
        $sql = "SELECT * FROM Uploads WHERE 1=1";
        $params = [];
        
        if ($validacao) {
            $sql .= " AND Validacao = ?";
            $params[] = $validacao;
        }
        
        $sql .= " ORDER BY Data_Upload DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['sucesso' => true, 'uploads' => $uploads]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao listar uploads: ' . $e->getMessage()]);
    }
}

function validarUpload($pdo) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (!in_array($status, ['aprovado', 'rejeitado'])) {
        echo json_encode(['erro' => 'Status de validação inválido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE Uploads SET Validacao = ? WHERE ID = ?");
        $stmt->execute([$status, $id]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Upload validado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao validar upload: ' . $e->getMessage()]);
    }
}
?>