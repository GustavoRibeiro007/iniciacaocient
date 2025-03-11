CREATE TABLE Usuarios 
( 
    Usuario_ID INT PRIMARY KEY AUTO_INCREMENT,  
    Nome VARCHAR(255) NOT NULL,  
    Email VARCHAR(255) NOT NULL
);

CREATE TABLE Uploads 
( 
    ID INT PRIMARY KEY AUTO_INCREMENT,  
    Nome_Arquivo VARCHAR(255) NOT NULL,  
    Caminho_Arquivo VARCHAR(512) NOT NULL,  
    Tamanho INT NOT NULL,  
    Data_Upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,  
    Validacao VARCHAR(50) NOT NULL,  
    Usuario_ID INT NOT NULL, 
    CONSTRAINT fk_usuario FOREIGN KEY (Usuario_ID) REFERENCES Usuarios (Usuario_ID) 
);
