<?php
require_once 'conexao.php';

// Função para cadastrar projeto
function cadastrarProjeto($dados, $participantes) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Inserir projeto
        $sql = "INSERT INTO Formularios (Nome_Projeto, Quantidade_Participantes, Curso, 
                Semestre, Orientador, Resumo, GitHub_Link, Caminho_PDF) 
                VALUES (:nome, :qtd, :curso, :semestre, :orientador, :resumo, :github, :pdf)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['projectTitle'],
            ':qtd' => $dados['quantParticipantes'],
            ':curso' => $dados['tipo-curso'],
            ':semestre' => $dados['semestre'],
            ':orientador' => $dados['orientador'],
            ':resumo' => $dados['resumo'],
            ':github' => $dados['repositorio'],
            ':pdf' => $dados['pdf_path']
        ]);
        
        $projetoId = $conn->lastInsertId();
        
        // Inserir participantes
        $sql = "INSERT INTO Participantes (Formulario_ID, Nome, RA, Email) 
                VALUES (:form_id, :nome, :ra, :email)";
        $stmt = $conn->prepare($sql);
        
        foreach ($participantes as $p) {
            $stmt->execute([
                ':form_id' => $projetoId,
                ':nome' => $p['nome'],
                ':ra' => $p['ra'],
                ':email' => $p['email']
            ]);
        }
        
        $conn->commit();
        return true;
    } catch(PDOException $e) {
        $conn->rollBack();
        return false;
    }
}

// Função para listar projetos
function listarProjetos($filtros = []) {
    global $conn;
    
    try {
        $sql = "SELECT f.*, GROUP_CONCAT(p.Nome) as participantes 
                FROM Formularios f 
                LEFT JOIN Participantes p ON f.ID = p.Formulario_ID";
        
        $where = [];
        $params = [];
        
        if (!empty($filtros['curso'])) {
            $where[] = "f.Curso = :curso";
            $params[':curso'] = $filtros['curso'];
        }
        
        if (!empty($filtros['semestre'])) {
            $where[] = "f.Semestre = :semestre";
            $params[':semestre'] = $filtros['semestre'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " GROUP BY f.ID";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Função para obter detalhes do projeto
function obterProjeto($id) {
    global $conn;
    
    try {
        // Buscar dados do projeto
        $sql = "SELECT * FROM Formularios WHERE ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projeto) {
            return null;
        }
        
        // Buscar participantes
        $sql = "SELECT * FROM Participantes WHERE Formulario_ID = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $projeto['participantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $projeto;
    } catch(PDOException $e) {
        return null;
    }
}

// Processar requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    switch ($acao) {
        case 'cadastrar':
            // Processar upload do arquivo
            $arquivo = $_FILES['file'] ?? null;
            $pdf_path = '';
            
            if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
                $pdf_path = '../uploads/' . basename($arquivo['name']);
                move_uploaded_file($arquivo['tmp_name'], $pdf_path);
            }
            
            // Preparar dados dos participantes
            $participantes = [];
            $qtd = $_POST['quantParticipantes'] ?? 0;
            
            for ($i = 1; $i <= $qtd; $i++) {
                $participantes[] = [
                    'nome' => $_POST["participanteNome$i"] ?? '',
                    'ra' => $_POST["participanteRA$i"] ?? '',
                    'email' => $_POST["participanteEmail$i"] ?? ''
                ];
            }
            
            $_POST['pdf_path'] = $pdf_path;
            $resultado = cadastrarProjeto($_POST, $participantes);
            echo json_encode(['sucesso' => $resultado]);
            break;
            
        case 'detalhes':
            $id = $_POST['id'] ?? 0;
            $projeto = obterProjeto($id);
            echo json_encode($projeto);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtros = [
        'curso' => $_GET['curso'] ?? null,
        'semestre' => $_GET['semestre'] ?? null
    ];
    
    $projetos = listarProjetos($filtros);
    echo json_encode($projetos);
}
?> 