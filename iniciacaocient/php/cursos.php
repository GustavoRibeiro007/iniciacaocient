<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    $acao = $_REQUEST['acao'] ?? '';
    $pdo = getConexao();

    switch ($acao) {
        case 'cadastrar':
            $pdo->beginTransaction();
            
            // Inserir curso
            $stmt = $pdo->prepare("
                INSERT INTO Cursos (
                    Codigo, 
                    Nome, 
                    Descricao, 
                    Categoria, 
                    Duracao, 
                    Formato, 
                    Status
                ) VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $_POST['codigo'],
                $_POST['nome'],
                $_POST['descricao'],
                $_POST['categoria'],
                $_POST['duracao'],
                $_POST['formato']
            ]);
            
            $cursoId = $pdo->lastInsertId();
            
            // Inserir professores
            if (isset($_POST['professores']) && is_array($_POST['professores'])) {
                $stmtProf = $pdo->prepare("
                    INSERT INTO Professores_Curso (
                        Curso_ID, 
                        Nome_Professor, 
                        Identificacao
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($_POST['professores'] as $index => $professor) {
                    $stmtProf->execute([
                        $cursoId,
                        $professor,
                        $_POST['ids_professores'][$index]
                    ]);
                }
            }
            
            // Inserir matérias
            if (isset($_POST['materias']) && is_array($_POST['materias'])) {
                $stmtMat = $pdo->prepare("
                    INSERT INTO Materias_Curso (
                        Curso_ID, 
                        Nome_Materia, 
                        Carga_Horaria
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($_POST['materias'] as $index => $materia) {
                    $stmtMat->execute([
                        $cursoId,
                        $materia,
                        $_POST['carga_horaria'][$index]
                    ]);
                }
            }
            
            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Curso cadastrado com sucesso']);
            break;

        case 'listar':
            $stmt = $pdo->query("
                SELECT 
                    c.*,
                    GROUP_CONCAT(DISTINCT p.Nome_Professor) as Professores,
                    GROUP_CONCAT(DISTINCT m.Nome_Materia) as Materias
                FROM Cursos c
                LEFT JOIN Professores_Curso p ON c.ID = p.Curso_ID
                LEFT JOIN Materias_Curso m ON c.ID = m.Curso_ID
                GROUP BY c.ID
                ORDER BY c.Data_Cadastro DESC
            ");
            
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'cursos' => $cursos]);
            break;

        case 'buscar':
            $id = $_REQUEST['id'] ?? 0;
            
            // Buscar dados do curso
            $stmt = $pdo->prepare("SELECT * FROM Cursos WHERE ID = ?");
            $stmt->execute([$id]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($curso) {
                // Buscar professores
                $stmt = $pdo->prepare("SELECT * FROM Professores_Curso WHERE Curso_ID = ?");
                $stmt->execute([$id]);
                $curso['professores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Buscar matérias
                $stmt = $pdo->prepare("SELECT * FROM Materias_Curso WHERE Curso_ID = ?");
                $stmt->execute([$id]);
                $curso['materias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['sucesso' => true, 'curso' => $curso]);
            } else {
                throw new Exception("Curso não encontrado");
            }
            break;

        case 'editar':
            $id = $_POST['id'] ?? 0;
            
            $pdo->beginTransaction();
            
            // Atualizar curso
            $stmt = $pdo->prepare("
                UPDATE Cursos SET 
                    Nome = ?,
                    Descricao = ?,
                    Categoria = ?,
                    Duracao = ?,
                    Formato = ?
                WHERE ID = ?
            ");
            
            $stmt->execute([
                $_POST['nome'],
                $_POST['descricao'],
                $_POST['categoria'],
                $_POST['duracao'],
                $_POST['formato'],
                $id
            ]);
            
            // Atualizar professores
            $stmt = $pdo->prepare("DELETE FROM Professores_Curso WHERE Curso_ID = ?");
            $stmt->execute([$id]);
            
            if (isset($_POST['professores']) && is_array($_POST['professores'])) {
                $stmtProf = $pdo->prepare("
                    INSERT INTO Professores_Curso (
                        Curso_ID, 
                        Nome_Professor, 
                        Identificacao
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($_POST['professores'] as $index => $professor) {
                    $stmtProf->execute([
                        $id,
                        $professor,
                        $_POST['ids_professores'][$index]
                    ]);
                }
            }
            
            // Atualizar matérias
            $stmt = $pdo->prepare("DELETE FROM Materias_Curso WHERE Curso_ID = ?");
            $stmt->execute([$id]);
            
            if (isset($_POST['materias']) && is_array($_POST['materias'])) {
                $stmtMat = $pdo->prepare("
                    INSERT INTO Materias_Curso (
                        Curso_ID, 
                        Nome_Materia, 
                        Carga_Horaria
                    ) VALUES (?, ?, ?)
                ");
                
                foreach ($_POST['materias'] as $index => $materia) {
                    $stmtMat->execute([
                        $id,
                        $materia,
                        $_POST['carga_horaria'][$index]
                    ]);
                }
            }
            
            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Curso atualizado com sucesso']);
            break;

        case 'excluir':
            $id = $_POST['id'] ?? 0;
            
            $pdo->beginTransaction();
            
            // Excluir professores
            $stmt = $pdo->prepare("DELETE FROM Professores_Curso WHERE Curso_ID = ?");
            $stmt->execute([$id]);
            
            // Excluir matérias
            $stmt = $pdo->prepare("DELETE FROM Materias_Curso WHERE Curso_ID = ?");
            $stmt->execute([$id]);
            
            // Excluir curso
            $stmt = $pdo->prepare("DELETE FROM Cursos WHERE ID = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Curso excluído com sucesso']);
            break;

        default:
            throw new Exception("Ação inválida");
    }
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao ' . $acao . ' curso: ' . $e->getMessage()]);
} 