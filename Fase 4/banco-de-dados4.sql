1) Antes de tudo, é necessário ir na pasta USBWebserver v10 - depois em settings -  abra o arquivo php (bloco de notas) - procure por extension=pdo_mysql e apaque o ; do começo e salve a alteração

2) Crie o banco de dados e as tabelas;
CREATE DATABASE IF NOT EXISTS terrasara
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE terrasara;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS movimentacoes_estoque, itens_pedido, pedidos, clientes, produtos, fornecedores, usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nome              VARCHAR(120) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    senha_hash        VARCHAR(255) NOT NULL,
    perfil            VARCHAR(20)  NOT NULL,
    ativo             TINYINT      NOT NULL DEFAULT 1,
    tentativas_falhas INT          NOT NULL DEFAULT 0,
    bloqueado_ate     DATETIME     NULL,
    criado_em         DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE fornecedores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(255) NOT NULL,
    telefone    VARCHAR(20)  NOT NULL DEFAULT '',
    email       VARCHAR(150) NOT NULL DEFAULT '',
    certificado TINYINT      NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE produtos (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    descricao     VARCHAR(255)  NOT NULL,
    preco         DECIMAL(10,2) NOT NULL,
    validade      DATE          NOT NULL,
    precoCusto    DECIMAL(10,2) NOT NULL,
    fornecedor_id INT           NULL,
    quantEstoque  INT           NOT NULL DEFAULT 0,
    categoria     VARCHAR(100)  NOT NULL,
    unidadeMedida VARCHAR(50)   NOT NULL,
    lucro         DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE clientes (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(120) NOT NULL,
    telefone  VARCHAR(20)  NOT NULL UNIQUE,
    email     VARCHAR(150) NOT NULL UNIQUE,
    criado_em DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pedidos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT           NOT NULL,
    usuario_id      INT           NOT NULL,
    forma_pagamento VARCHAR(20)   NOT NULL,
    observacao      VARCHAR(500)  NOT NULL DEFAULT '',
    status          VARCHAR(20)   NOT NULL,
    total           DECIMAL(10,2) NOT NULL,
    lucro           DECIMAL(10,2) NOT NULL,
    criado_em       DATETIME      NOT NULL,
    pago_em         DATETIME      NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes (id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE itens_pedido (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id      INT           NOT NULL,
    produto_id     INT           NOT NULL,
    descricao      VARCHAR(255)  NOT NULL,
    quantidade     INT           NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    custo_unitario DECIMAL(10,2) NOT NULL,
    subtotal       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos (id),
    FOREIGN KEY (produto_id) REFERENCES produtos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE movimentacoes_estoque (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT          NOT NULL,
    usuario_id INT          NOT NULL,
    tipo       VARCHAR(20)  NOT NULL,
    quantidade INT          NOT NULL,
    motivo     VARCHAR(255) NOT NULL DEFAULT '',
    criado_em  DATETIME     NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos (id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE INDEX idx_pedidos_status_pago ON pedidos (status, pago_em);
CREATE INDEX idx_pedidos_criado ON pedidos (criado_em);
CREATE INDEX idx_produtos_validade ON produtos (validade);
CREATE INDEX idx_mov_produto ON movimentacoes_estoque (produto_id);

-- usuário administrador: admin@terrasara.com.br / Terrasara@2026
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, tentativas_falhas, bloqueado_ate, criado_em)
VALUES ('Administradora', 'admin@terrasara.com.br',
        '$2y$10$f2Czf1Mfgc9doN056w7kxOYN8B5QTNqNqCW9lUrYOOl6xGFe4Kc2.',
        'admin', 1, 0, NULL, NOW());

3) Abra o vscode com http://localhost/terrasara/public/ (para mim funcionou), entre com o email e senha * *E-mail:* admin@terrasara.com.br
* *Senha:* Terrasara@2026
