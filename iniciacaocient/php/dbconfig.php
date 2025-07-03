<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cad";

// Criar conexão inicial sem especificar o banco de dados
$conn = new mysqli($servername, $username, $password);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Criar o banco de dados se não existir
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Selecionar o banco de dados
    $conn->select_db($dbname);
    
    // Criar tabela Uploads se não existir
    $sql = "CREATE TABLE IF NOT EXISTS Uploads (
        ID INT PRIMARY KEY AUTO_INCREMENT,
        Nome_Arquivo VARCHAR(255) NOT NULL,
        Caminho_Arquivo VARCHAR(512) NOT NULL,
        Tamanho INT NOT NULL,
        Data_Upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        Validacao VARCHAR(50) NOT NULL
    )";
    $conn->query($sql);

    // Criar tabela Formularios se não existir
    $sql = "CREATE TABLE IF NOT EXISTS Formularios (
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
    )";
    $conn->query($sql);

    // Criar tabela Participantes se não existir
    $sql = "CREATE TABLE IF NOT EXISTS Participantes (
        ID INT PRIMARY KEY AUTO_INCREMENT,
        Formulario_ID INT NOT NULL,
        Nome VARCHAR(255) NOT NULL,
        RA VARCHAR(50) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID)
    )";
    $conn->query($sql);

    // Criar tabela Usuarios se não existir
    $sql = "CREATE TABLE IF NOT EXISTS Usuarios (
        ID INT PRIMARY KEY AUTO_INCREMENT,
        Nome VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL UNIQUE,
        Senha VARCHAR(255) NOT NULL,
        Telefone VARCHAR(50),
        Tipo_Usuario ENUM('admin','coordenador','avaliador','usuario') NOT NULL DEFAULT 'usuario',
        Identificacao VARCHAR(100),
        Status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'
    )";
    $conn->query($sql);
} else {
    die("Erro ao criar banco de dados: " . $conn->error);
}
?>
