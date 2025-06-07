<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
    $pdo = getConexao();

    switch ($acao) {
        case 'cadastrar':
            cadastrarUsuario($pdo);
            break;
        case 'listar':
            listarUsuarios($pdo);
            break;
        case 'listar_avaliadores':
            listarAvaliadores($pdo);
            break;
        case 'editar':
            editarUsuario($pdo);
            break;
        case 'excluir':
            excluirUsuario($pdo);
            break;
        case 'buscar':
            buscarUsuario($pdo);
            break;
        default:
            throw new Exception('Ação não especificada');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}

function cadastrarUsuario($pdo) {
    $nome = $_POST['nome'] ?? '';
    $tipoUsuario = $_POST['tipoUsuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $identificacao = $_POST['n-matricula'] ?? '';

    // Validações básicas
    if (empty($nome) || empty($tipoUsuario)) {
        throw new Exception('Nome e tipo de usuário são obrigatórios');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Usuarios (
                Nome, 
                Email, 
                Telefone, 
                Tipo_Usuario, 
                Identificacao, 
                Status
            ) VALUES (?, ?, ?, ?, ?, 'ativo')
        ");
        
        $stmt->execute([
            $nome,
            $email,
            $telefone,
            $tipoUsuario,
            $identificacao
        ]);
        
        echo json_encode([
            'sucesso' => true, 
            'mensagem' => 'Usuário cadastrado com sucesso',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        throw new Exception('Erro ao cadastrar usuário: ' . $e->getMessage());
    }
}

function listarUsuarios($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT * FROM Usuarios 
            WHERE Status = 'ativo' 
            ORDER BY Nome
        ");
        
        $usuarios = $stmt->fetchAll();
        echo json_encode(['sucesso' => true, 'usuarios' => $usuarios]);
    } catch (PDOException $e) {
        throw new Exception('Erro ao listar usuários: ' . $e->getMessage());
    }
}

function editarUsuario($pdo) {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $tipoUsuario = $_POST['tipoUsuario'] ?? '';
    $identificacao = $_POST['n-matricula'] ?? '';

    if (empty($id) || empty($nome) || empty($tipoUsuario)) {
        throw new Exception('ID, nome e tipo de usuário são obrigatórios');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE Usuarios 
            SET Nome = ?, 
                Email = ?, 
                Telefone = ?, 
                Tipo_Usuario = ?, 
                Identificacao = ? 
            WHERE ID = ? AND Status = 'ativo'
        ");
        
        $stmt->execute([
            $nome,
            $email,
            $telefone,
            $tipoUsuario,
            $identificacao,
            $id
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Usuário não encontrado ou já inativo');
        }
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso']);
    } catch (PDOException $e) {
        throw new Exception('Erro ao atualizar usuário: ' . $e->getMessage());
    }
}

function excluirUsuario($pdo) {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        throw new Exception('ID do usuário é obrigatório');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE Usuarios 
            SET Status = 'inativo' 
            WHERE ID = ? AND Status = 'ativo'
        ");
        
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Usuário não encontrado ou já inativo');
        }
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário excluído com sucesso']);
    } catch (PDOException $e) {
        throw new Exception('Erro ao excluir usuário: ' . $e->getMessage());
    }
}

function buscarUsuario($pdo) {
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        throw new Exception('ID do usuário é obrigatório');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM Usuarios 
            WHERE ID = ? AND Status = 'ativo'
        ");
        
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            throw new Exception('Usuário não encontrado');
        }
        
        echo json_encode(['sucesso' => true, 'usuario' => $usuario]);
    } catch (PDOException $e) {
        throw new Exception('Erro ao buscar usuário: ' . $e->getMessage());
    }
}

function listarAvaliadores($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT ID, Nome FROM Usuarios 
            WHERE Tipo_Usuario = 'avaliador' AND Status = 'ativo' 
            ORDER BY Nome
        ");
        
        $avaliadores = $stmt->fetchAll();
        echo json_encode(['sucesso' => true, 'avaliadores' => $avaliadores]);
    } catch (PDOException $e) {
        throw new Exception('Erro ao listar avaliadores: ' . $e->getMessage());
    }
}
?>