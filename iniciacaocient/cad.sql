-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS cad;
USE cad;

-- Tabela de Uploads
CREATE TABLE IF NOT EXISTS Uploads 
( 
    ID INT PRIMARY KEY AUTO_INCREMENT,  
    Nome_Arquivo VARCHAR(255) NOT NULL,  
    Caminho_Arquivo VARCHAR(512) NOT NULL,  
    Tamanho INT NOT NULL,  
    Data_Upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,  
    Validacao VARCHAR(50) NOT NULL
);

-- Tabela de Formulários (Projetos)
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

-- Tabela de Participantes
CREATE TABLE IF NOT EXISTS Participantes (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Formulario_ID INT NOT NULL,
    Nome VARCHAR(255) NOT NULL,
    RA VARCHAR(50) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID) ON DELETE CASCADE
);

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS Usuarios (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(255) NOT NULL,
    Email VARCHAR(255),
    Telefone VARCHAR(20),
    Tipo_Usuario ENUM('avaliador', 'professor', 'coordenador') NOT NULL,
    Identificacao VARCHAR(50),
    Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    Status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS Agendamentos (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Formulario_ID INT NOT NULL,
    Data_Apresentacao DATETIME NOT NULL,
    Local VARCHAR(255) NOT NULL,
    Status ENUM('agendado', 'realizado', 'cancelado') DEFAULT 'agendado',
    FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID) ON DELETE CASCADE
);

-- Tabela de Cursos
CREATE TABLE IF NOT EXISTS Cursos (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Codigo VARCHAR(50) NOT NULL UNIQUE,
    Nome VARCHAR(255) NOT NULL,
    Descricao TEXT NOT NULL,
    Categoria ENUM('tecnologia', 'negocios', 'industria') NOT NULL,
    Duracao INT NOT NULL,
    Formato ENUM('presencial', 'online', 'hibrido') NOT NULL,
    Status BOOLEAN DEFAULT TRUE,
    Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- Tabela de Professores por Curso
CREATE TABLE IF NOT EXISTS Professores_Curso (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Curso_ID INT NOT NULL,
    Nome_Professor VARCHAR(255) NOT NULL,
    Identificacao VARCHAR(50) NOT NULL,
    FOREIGN KEY (Curso_ID) REFERENCES Cursos(ID) ON DELETE CASCADE
);

-- Tabela de Matérias por Curso
CREATE TABLE IF NOT EXISTS Materias_Curso (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Curso_ID INT NOT NULL,
    Nome_Materia VARCHAR(255) NOT NULL,
    Carga_Horaria INT NOT NULL,
    FOREIGN KEY (Curso_ID) REFERENCES Cursos(ID) ON DELETE CASCADE
);
