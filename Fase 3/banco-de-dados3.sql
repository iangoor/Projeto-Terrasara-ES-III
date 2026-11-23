-- =====================================================================
-- Terrasara (fase 3) - banco de dados e tabelas
-- Como usar: phpMyAdmin > aba "Importar" > escolher este arquivo > Executar
--            (ou aba "SQL" > colar tudo > Executar)
-- O nome do banco precisa ser o mesmo de 'name' em config/database.php
-- =====================================================================
 
CREATE DATABASE IF NOT EXISTS terrasara3
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;
 
USE terrasara3;
 
-- ---------------------------------------------------------------------
-- Usuarios (login e perfis de acesso)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios3 (
    id                INT          NOT NULL AUTO_INCREMENT,
    nome              VARCHAR(150) NOT NULL,
    email             VARCHAR(150) NOT NULL,
    senha_hash        VARCHAR(255) NOT NULL,
    perfil            VARCHAR(20)  NOT NULL,  -- admin, vendedor, estoquista, financeiro, cadastro
    ativo             TINYINT(1)   NOT NULL DEFAULT 1,
    tentativas_falhas INT          NOT NULL DEFAULT 0,
    bloqueado_ate     DATETIME     NULL,
    criado_em         DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Fornecedores
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fornecedores3 (
    id          INT          NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(150) NOT NULL,
    telefone    VARCHAR(20)  NULL,
    email       VARCHAR(150) NULL,
    certificado TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Clientes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes3 (
    id        INT          NOT NULL AUTO_INCREMENT,
    nome      VARCHAR(150) NOT NULL,
    telefone  VARCHAR(20)  NOT NULL,
    email     VARCHAR(150) NOT NULL,
    criado_em DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_clientes_email (email),
    UNIQUE KEY uk_clientes_telefone (telefone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Produtos (o fornecedor agora e uma chave estrangeira: fornecedor_id)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos3 (
    id            INT           NOT NULL AUTO_INCREMENT,
    descricao     VARCHAR(255)  NOT NULL,
    preco         DECIMAL(10,2) NOT NULL,
    validade      DATE          NOT NULL,
    precoCusto    DECIMAL(10,2) NOT NULL,
    fornecedor_id INT           NULL,
    quantEstoque  INT           NOT NULL DEFAULT 0,
    categoria     VARCHAR(100)  NOT NULL,  -- Pereciveis, Congelados, Secos
    unidadeMedida VARCHAR(50)   NOT NULL,  -- Quilo, Grama, Unidade
    lucro         DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_produtos_fornecedor (fornecedor_id),
    KEY idx_produtos_validade (validade),
    CONSTRAINT fk_produtos_fornecedor FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores3 (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Pedidos (vendas)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos3 (
    id              INT           NOT NULL AUTO_INCREMENT,
    cliente_id      INT           NOT NULL,
    usuario_id      INT           NOT NULL,
    forma_pagamento VARCHAR(20)   NOT NULL,  -- dinheiro, pix, debito, credito
    observacao      VARCHAR(255)  NULL,
    status          VARCHAR(20)   NOT NULL DEFAULT 'pendente',  -- pendente, concluido, cancelado
    total           DECIMAL(12,2) NOT NULL,
    lucro           DECIMAL(12,2) NOT NULL,
    criado_em       DATETIME      NOT NULL,
    pago_em         DATETIME      NULL,
    PRIMARY KEY (id),
    KEY idx_pedidos_cliente (cliente_id),
    KEY idx_pedidos_usuario (usuario_id),
    KEY idx_pedidos_status (status),
    KEY idx_pedidos_criado (criado_em),
    KEY idx_pedidos_pago (pago_em),
    CONSTRAINT fk_pedidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes3 (id),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios3 (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Itens do pedido
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS itens_pedido3 (
    id             INT           NOT NULL AUTO_INCREMENT,
    pedido_id      INT           NOT NULL,
    produto_id     INT           NOT NULL,
    descricao      VARCHAR(255)  NOT NULL,
    quantidade     INT           NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    custo_unitario DECIMAL(10,2) NOT NULL,
    subtotal       DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_itens_pedido (pedido_id),
    KEY idx_itens_produto (produto_id),
    CONSTRAINT fk_itens_pedido  FOREIGN KEY (pedido_id)  REFERENCES pedidos3 (id),
    CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos3 (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Movimentacoes de estoque (auditoria)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimentacoes_estoque3 (
    id         INT          NOT NULL AUTO_INCREMENT,
    produto_id INT          NOT NULL,
    usuario_id INT          NOT NULL,
    tipo       VARCHAR(20)  NOT NULL,  -- entrada, saida, descarte
    quantidade INT          NOT NULL,
    motivo     VARCHAR(255) NULL,
    criado_em  DATETIME     NOT NULL,
    PRIMARY KEY (id),
    KEY idx_mov_produto (produto_id),
    KEY idx_mov_usuario (usuario_id),
    CONSTRAINT fk_mov_produto FOREIGN KEY (produto_id) REFERENCES produtos3 (id),
    CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios3 (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
-- ---------------------------------------------------------------------
-- Administradora inicial
--   e-mail: admin@terrasara.com.br
--   senha:  Terrasara@2026   (troque em "Alterar senha" depois de entrar)
-- ---------------------------------------------------------------------
INSERT INTO usuarios3 (nome, email, senha_hash, perfil, ativo, tentativas_falhas, bloqueado_ate, criado_em)
SELECT 'Administradora', 'admin@terrasara.com.br',
       '$2y$12$gtwKYx7SUWPxANzDDJoMIu7e594A7ysx1AOILrseMqeaim.iCxeQ6',
       'admin', 1, 0, NULL, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM usuarios3 WHERE email = 'admin@terrasara.com.br');
