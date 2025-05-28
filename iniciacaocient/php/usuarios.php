<?php
require_once 'conexao.php';

// Função para cadastrar usuário
function cadastrarUsuario($tipo, $nome, $identificacao = null, $telefone = null, $email = null) {
    global $conn;
    
    try {
        $sql = "INSERT INTO usuarios (tipo, nome, identificacao, telefone, email) 
                VALUES (:tipo, :nome, :identificacao, :telefone, :email)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':identificacao', $identificacao);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Função para listar usuários
function listarUsuarios($tipo = null) {
    global $conn;
    
    try {
        $sql = "SELECT * FROM usuarios";
        if ($tipo) {
            $sql .= " WHERE tipo = :tipo";
        }
        
        $stmt = $conn->prepare($sql);
        if ($tipo) {
            $stmt->bindParam(':tipo', $tipo);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Função para atualizar usuário
function atualizarUsuario($id, $dados) {
    global $conn;
    
    try {
        $campos = [];
        $valores = [];
        
        foreach ($dados as $campo => $valor) {
            if ($valor !== null) {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        $valores[':id'] = $id;
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute($valores);
    } catch(PDOException $e) {
        return false;
    }
}

// Função para excluir usuário
function excluirUsuario($id) {
    global $conn;
    
    try {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Processar requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    switch ($acao) {
        case 'cadastrar':
            $tipo = $_POST['tipo'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $identificacao = $_POST['identificacao'] ?? null;
            $telefone = $_POST['telefone'] ?? null;
            $email = $_POST['email'] ?? null;
            
            $resultado = cadastrarUsuario($tipo, $nome, $identificacao, $telefone, $email);
            echo json_encode(['sucesso' => $resultado]);
            break;
            
        case 'atualizar':
            $id = $_POST['id'] ?? 0;
            $dados = [
                'nome' => $_POST['nome'] ?? null,
                'identificacao' => $_POST['identificacao'] ?? null,
                'telefone' => $_POST['telefone'] ?? null,
                'email' => $_POST['email'] ?? null
            ];
            
            $resultado = atualizarUsuario($id, $dados);
            echo json_encode(['sucesso' => $resultado]);
            break;
            
        case 'excluir':
            $id = $_POST['id'] ?? 0;
            $resultado = excluirUsuario($id);
            echo json_encode(['sucesso' => $resultado]);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tipo = $_GET['tipo'] ?? null;
    $usuarios = listarUsuarios($tipo);
    echo json_encode($usuarios);
}
?> 