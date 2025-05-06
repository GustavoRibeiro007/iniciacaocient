CREATE TABLE Uploads 
( 
    ID INT PRIMARY KEY AUTO_INCREMENT,  
    Nome_Arquivo VARCHAR(255) NOT NULL,  
    Caminho_Arquivo VARCHAR(512) NOT NULL,  
    Tamanho INT NOT NULL,  
    Data_Upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,  
    Validacao VARCHAR(50) NOT NULL
);


CREATE TABLE Formularios (
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

CREATE TABLE Participantes (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Formulario_ID INT NOT NULL,
    Nome VARCHAR(255) NOT NULL,
    RA VARCHAR(50) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    FOREIGN KEY (Formulario_ID) REFERENCES Formularios(ID)
);
