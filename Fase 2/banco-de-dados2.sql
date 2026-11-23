Abrir no navegador:
http://localhost/terrasara-Entrega2/index.php

Criar Banco e tabela:
CREATE DATABASE IF NOT EXISTS terrasara
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE terrasara;

CREATE TABLE IF NOT EXISTS produtos (
    id            INT NOT NULL AUTO_INCREMENT,
    descricao     VARCHAR(255)  NOT NULL,
    preco         DECIMAL(10,2) NOT NULL,
    validade      DATE          NOT NULL,
    precoCusto    DECIMAL(10,2) NOT NULL,
    fornecedor    VARCHAR(255)  NOT NULL,
    quantEstoque  INT           NOT NULL,
    categoria     VARCHAR(100)  NOT NULL,
    unidadeMedida VARCHAR(50)   NOT NULL,
    lucro         DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
