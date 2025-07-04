<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
    $pdo = getConexao();

    switch ($acao) {
        case 'agendar':
            agendarApresentacao();
            break;
        case 'listar':
            listarAgendamentos();
            break;
        case 'atualizar':
            atualizarAgendamento();
            break;
        case 'cancelar':
            cancelarAgendamento();
            break;
        default:
            echo json_encode(['erro' => 'Ação não especificada']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}

function agendarApresentacao() {
    global $pdo;
    
    $formularioId = $_POST['formulario_id'] ?? '';
    $dataApresentacao = $_POST['data_apresentacao'] ?? '';
    $local = $_POST['local'] ?? '';
    
    try {
        // Verificar se já existe agendamento para este projeto
        $stmt = $pdo->prepare("SELECT ID FROM Agendamentos WHERE Formulario_ID = ? AND Status != 'cancelado'");
        $stmt->execute([$formularioId]);
        
        if ($stmt->fetch()) {
            echo json_encode(['erro' => 'Já existe um agendamento para este projeto']);
            return;
        }
        
        // Inserir novo agendamento
        $stmt = $pdo->prepare("INSERT INTO Agendamentos (Formulario_ID, Data_Apresentacao, Local) VALUES (?, ?, ?)");
        $stmt->execute([$formularioId, $dataApresentacao, $local]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Apresentação agendada com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao agendar apresentação: ' . $e->getMessage()]);
    }
}

function listarAgendamentos() {
    global $pdo;
    
    $status = $_GET['status'] ?? '';
    $data = $_GET['data'] ?? '';
    
    try {
        $sql = "SELECT a.*, f.Nome_Projeto, f.Curso, f.Semestre 
                FROM Agendamentos a 
                JOIN Formularios f ON a.Formulario_ID = f.ID 
                WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND a.Status = ?";
            $params[] = $status;
        }
        
        if ($data) {
            $sql .= " AND DATE(a.Data_Apresentacao) = ?";
            $params[] = $data;
        }
        
        $sql .= " ORDER BY a.Data_Apresentacao";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['sucesso' => true, 'agendamentos' => $agendamentos]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao listar agendamentos: ' . $e->getMessage()]);
    }
}

function atualizarAgendamento() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    $dataApresentacao = $_POST['data_apresentacao'] ?? '';
    $local = $_POST['local'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE Agendamentos SET Data_Apresentacao = ?, Local = ?, Status = ? WHERE ID = ?");
        $stmt->execute([$dataApresentacao, $local, $status, $id]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Agendamento atualizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao atualizar agendamento: ' . $e->getMessage()]);
    }
}

function cancelarAgendamento() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE Agendamentos SET Status = 'cancelado' WHERE ID = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Agendamento cancelado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao cancelar agendamento: ' . $e->getMessage()]);
    }
}
?>