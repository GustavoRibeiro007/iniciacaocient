CREATE TABLE IF NOT EXISTS Uploads 
( 
    ID INT PRIMARY KEY AUTO_INCREMENT,  
    Nome_Arquivo VARCHAR(255) NOT NULL,  
    Caminho_Arquivo VARCHAR(512) NOT NULL,  
    Tamanho INT NOT NULL,  
    Data_Upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,  
    Validacao VARCHAR(50) NOT NULL
);


CREATE TABLE IF NOT EXISTS Formularios (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome_Projeto VARCHAR(255) NOT NULL,
    Quantidade_Participantes INT NOT NULL,
    Curso VARCHAR(255) NOT NULL,
    Semestre VARCHAR(50) NOT NULL,
    Orientador VARCHAR(255) NOT NULL,
    Resumo TEXT NOT NULL,
    GitHub_Link VARCHAR(512),
    Caminho_PDF VARCHAR(512) NOT NULL,
    Data_Envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS Participantes (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Formulario_ID INT NOT NULL,
    Nome VARCHAR(255) NOT NULL,
    RA VARCHAR(50) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID)
);

CREATE TABLE IF NOT EXISTS Usuarios (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Senha VARCHAR(255),
    Telefone VARCHAR(50),
    Tipo_Usuario ENUM('admin','coordenador','avaliador','usuario','professor') NOT NULL DEFAULT 'usuario',
    Identificacao VARCHAR(100),
    Status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'
);

CREATE TABLE IF NOT EXISTS Cursos (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Codigo VARCHAR(50) NOT NULL,
    Nome VARCHAR(255) NOT NULL,
    Descricao TEXT,
    Categoria VARCHAR(100),
    Duracao INT,
    Formato VARCHAR(50),
    Status TINYINT DEFAULT 1,
    Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS Professores_Curso (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Curso_ID INT NOT NULL,
    Nome_Professor VARCHAR(255) NOT NULL,
    Identificacao VARCHAR(100),
    FOREIGN KEY (Curso_ID) REFERENCES Cursos(ID)
);

CREATE TABLE IF NOT EXISTS Materias_Curso (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Curso_ID INT NOT NULL,
    Nome_Materia VARCHAR(255) NOT NULL,
    Carga_Horaria VARCHAR(50),
    FOREIGN KEY (Curso_ID) REFERENCES Cursos(ID)
);

CREATE TABLE IF NOT EXISTS Agendamentos (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Formulario_ID INT NOT NULL,
    Data_Apresentacao DATETIME NOT NULL,
    Local VARCHAR(255) NOT NULL,
    Status ENUM('ativo','cancelado') DEFAULT 'ativo',
    FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID)
);

CREATE TABLE IF NOT EXISTS Logins (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Usuario VARCHAR(255) NOT NULL UNIQUE,
    Senha VARCHAR(255) NOT NULL
);

-- Usuário admin de teste (senha: admin123)
INSERT IGNORE INTO Logins (Usuario, Senha) VALUES ('admin', '$2y$10$wHk2Qw8Qw8Qw8Qw8Qw8QeQw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8'); -- hash simulado

-- Exemplo de usuário admin
INSERT IGNORE INTO Usuarios (Nome, Email, Senha, Tipo_Usuario, Status) VALUES 
('Administrador', 'admin@teste.com', '$2y$10$wHk2Qw8Qw8Qw8Qw8Qw8QeQw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8', 'admin', 'ativo'),
('João Avaliador', 'joao.avaliador@teste.com', NULL, 'avaliador', 'ativo'),
('Maria Coordenadora', 'maria.coord@teste.com', NULL, 'coordenador', 'ativo'),
('Carlos Professor', 'carlos.prof@teste.com', NULL, 'professor', 'ativo');

-- Cursos simulados
INSERT IGNORE INTO Cursos (Codigo, Nome, Descricao, Categoria, Duracao, Formato, Status) VALUES
('CUR-2024-1001', 'Gestão da Produção Industrial', 'Curso voltado para processos industriais.', 'tecnologia', 6, 'presencial', 1),
('CUR-2024-1002', 'Gestão Empresarial', 'Curso de gestão para empresas.', 'negocios', 6, 'online', 1),
('CUR-2024-1003', 'Desenvolvimento de Software Multiplataforma', 'Curso de programação e desenvolvimento.', 'tecnologia', 6, 'hibrido', 1);

-- Professores simulados
INSERT IGNORE INTO Professores_Curso (Curso_ID, Nome_Professor, Identificacao) VALUES
(1, 'Carlos Professor', 'CPF123456'),
(2, 'Ana Professora', 'CPF654321'),
(3, 'Pedro Silva', 'CPF987654');

-- Matérias simuladas
INSERT IGNORE INTO Materias_Curso (Curso_ID, Nome_Materia, Carga_Horaria) VALUES
(1, 'Processos Industriais', '80'),
(1, 'Gestão de Qualidade', '60'),
(2, 'Administração', '70'),
(3, 'Lógica de Programação', '80'),
(3, 'Banco de Dados', '60');

-- Projetos simulados
INSERT IGNORE INTO Formularios (Nome_Projeto, Quantidade_Participantes, Curso, Semestre, Orientador, Resumo, GitHub_Link, Caminho_PDF) VALUES
('Sistema de Controle de Estoque', 3, 'desenvolvimento_soft', '4', 'Carlos Professor', 'Projeto para controle de estoque em empresas.', 'https://github.com/exemplo/estoque', '../uploads/estoque.pdf'),
('Aplicativo de Gestão Escolar', 2, 'gestao_empresarial', '3', 'Ana Professora', 'Aplicativo para gestão de escolas.', 'https://github.com/exemplo/escolar', '../uploads/escolar.pdf'),
('Automação Industrial', 4, 'gestao_producao', '5', 'Pedro Silva', 'Projeto de automação para linhas de produção.', NULL, '../uploads/automacao.pdf');

-- Participantes simulados
INSERT IGNORE INTO Participantes (Formulario_ID, Nome, RA, Email) VALUES
(1, 'Lucas Souza', '2021001', 'lucas@teste.com'),
(1, 'Fernanda Lima', '2021002', 'fernanda@teste.com'),
(1, 'Rafael Costa', '2021003', 'rafael@teste.com'),
(2, 'Juliana Alves', '2022001', 'juliana@teste.com'),
(2, 'Marcos Paulo', '2022002', 'marcos@teste.com'),
(3, 'Amanda Rocha', '2023001', 'amanda@teste.com'),
(3, 'Bruno Dias', '2023002', 'bruno@teste.com'),
(3, 'Patrícia Melo', '2023003', 'patricia@teste.com'),
(3, 'Thiago Nunes', '2023004', 'thiago@teste.com');

-- Agendamentos simulados
INSERT IGNORE INTO Agendamentos (Formulario_ID, Data_Apresentacao, Local, Status) VALUES
(1, '2024-06-20 10:00:00', 'Sala 101', 'ativo'),
(2, '2024-06-21 14:00:00', 'Sala 202', 'ativo'),
(3, '2024-06-22 09:00:00', 'Laboratório', 'ativo');
