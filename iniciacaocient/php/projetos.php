<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    $acao = $_REQUEST['acao'] ?? '';
    $pdo = getConexao();

    switch ($acao) {
        case 'listar':
            $curso = $_GET['curso'] ?? '';
            $semestre = $_GET['semestre'] ?? '';
            
            $sql = "SELECT * FROM Formularios WHERE 1=1";
            $params = [];
            
            if ($curso) {
                $sql .= " AND Curso = ?";
                $params[] = $curso;
            }
            if ($semestre) {
                $sql .= " AND Semestre = ?";
                $params[] = $semestre;
            }
            
            $sql .= " ORDER BY Data_Envio DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['sucesso' => true, 'projetos' => $projetos]);
            break;

        case 'detalhes':
            $id = $_REQUEST['id'] ?? 0;
            
            // Buscar dados do projeto
            $stmt = $pdo->prepare("SELECT * FROM Formularios WHERE ID = ?");
            $stmt->execute([$id]);
            $projeto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($projeto) {
                // Buscar participantes do projeto
                $stmt = $pdo->prepare("SELECT * FROM Participantes WHERE Formulario_ID = ?");
                $stmt->execute([$id]);
                $projeto['participantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['sucesso' => true, 'projeto' => $projeto]);
            } else {
                throw new Exception("Projeto não encontrado");
            }
            break;

        case 'cadastrar':
            $pdo->beginTransaction();
            
            // Inserir projeto
            $stmt = $pdo->prepare("INSERT INTO Formularios (Nome_Projeto, Quantidade_Participantes, Curso, Semestre, Orientador, Resumo, GitHub_Link, Caminho_PDF) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Calcular quantidade de participantes
            $participantes = json_decode($_POST['participantes'], true);
            $qtdParticipantes = count($participantes);
            
            // Processar arquivo PDF
            $caminhoPDF = '';
            if (isset($_FILES['arquivo_pdf'])) {
                $arquivo = $_FILES['arquivo_pdf'];
                $nomeArquivo = uniqid() . '_' . $arquivo['name'];
                $caminhoDestino = '../uploads/' . $nomeArquivo;
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
                    $caminhoPDF = $caminhoDestino;
                    
                    // Registrar upload
                    $stmt = $pdo->prepare("INSERT INTO Uploads (Nome_Arquivo, Caminho_Arquivo, Tamanho, Validacao) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $arquivo['name'],
                        $caminhoDestino,
                        $arquivo['size'],
                        'pendente'
                    ]);
                }
            }
            
            $stmt->execute([
                $_POST['nome_projeto'],
                $qtdParticipantes,
                $_POST['curso'],
                $_POST['semestre'],
                $_POST['orientador'],
                $_POST['resumo'],
                $_POST['github_link'] ?? null,
                $caminhoPDF
            ]);
            
            $projetoId = $pdo->lastInsertId();
            
            // Inserir participantes
            $stmt = $pdo->prepare("INSERT INTO Participantes (Formulario_ID, Nome, RA, Email) VALUES (?, ?, ?, ?)");
            
            foreach ($participantes as $participante) {
                $stmt->execute([
                    $projetoId,
                    $participante['nome'],
                    $participante['ra'],
                    $participante['email']
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Projeto cadastrado com sucesso']);
            break;

        case 'excluir':
            $id = $_POST['id'] ?? 0;
            
            $pdo->beginTransaction();
            
            // Excluir uploads
            $stmt = $pdo->prepare("DELETE FROM Uploads WHERE Formulario_ID = ?");
            $stmt->execute([$id]);
            
            // Excluir participantes
            $stmt = $pdo->prepare("DELETE FROM Participantes WHERE Formulario_ID = ?");
            $stmt->execute([$id]);
            
            // Excluir agendamentos
            $stmt = $pdo->prepare("DELETE FROM Agendamentos WHERE Formulario_ID = ?");
            $stmt->execute([$id]);
            
            // Excluir projeto
            $stmt = $pdo->prepare("DELETE FROM Formularios WHERE ID = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Projeto excluído com sucesso']);
            break;

        case 'listar_disponiveis':
            $stmt = $pdo->prepare("
                SELECT f.* 
                FROM Formularios f 
                LEFT JOIN Agendamentos a ON f.ID = a.Formulario_ID 
                WHERE a.ID IS NULL OR a.Status = 'cancelado'
                ORDER BY f.Data_Envio DESC
            ");
            $stmt->execute();
            $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['sucesso' => true, 'projetos' => $projetos]);
            break;

        case 'exportar':
            $formato = $_GET['formato'] ?? 'pdf';
            $projetos = $pdo->query("SELECT * FROM Formularios ORDER BY Data_Envio DESC")->fetchAll(PDO::FETCH_ASSOC);
            
            if ($formato === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="projetos.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, array_keys($projetos[0]));
                
                foreach ($projetos as $projeto) {
                    fputcsv($output, $projeto);
                }
                
                fclose($output);
                exit;
            } else {
                // Implementar exportação PDF aqui
                require_once 'vendor/autoload.php';
                $pdf = new TCPDF();
                // ... configuração do PDF ...
                $pdf->Output('projetos.pdf', 'D');
                exit;
            }
            break;

        default:
            throw new Exception("Ação inválida");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao ' . $acao . ' projetos: ' . $e->getMessage()]);
}
?>