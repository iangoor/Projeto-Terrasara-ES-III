# Projeto-Terrasara-ES-III

# 🌱 TerraSara Orgânicos & Naturais

> **Sistema Web de Gestão para Estabelecimento de Produtos Orgânicos**

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-yellow)
![PHP](https://img.shields.io/badge/PHP-5.6%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![FATEC](https://img.shields.io/badge/FATEC-Mogi%20das%20Cruzes-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## 📌 Sobre o Projeto

O **TerraSara Orgânicos & Naturais** é um sistema web de gestão desenvolvido como projeto acadêmico do curso de **Tecnologia em Análise e Desenvolvimento de Sistemas** da **FATEC Mogi das Cruzes**, na disciplina de **Engenharia de Software III**.

O sistema centraliza e automatiza os processos de gerenciamento de **produtos, pedidos, vendas, estoque, clientes, fornecedores e informações financeiras** de um estabelecimento especializado em produtos orgânicos, reduzindo processos manuais, minimizando erros operacionais e apoiando a tomada de decisões estratégicas.

## ✨ Funcionalidades

### 🔐 Autenticação e Segurança
- Login com e-mail e senha (hash bcrypt)
- **Bloqueio temporário** após 5 tentativas inválidas (15 minutos)
- Sessão com **timeout de 30 minutos** por inatividade
- Proteção contra **session fixation** (`session_regenerate_id`)
- **Token CSRF** obrigatório em todos os formulários POST
- Controle de acesso por perfil (RBAC)
- Troca de senha pelo próprio usuário
- Gestão de usuários pela administradora

### 👥 Perfis de Acesso (RBAC)

| Perfil | Acesso |
|--------|--------|
| **Administradora** | Acesso completo (todas as áreas) |
| **Vendedor** | Produtos, Clientes, Vendas, Estoque |
| **Estoquista** | Produtos, Estoque |
| **Gerente Financeiro** | Relatórios |
| **Analista de Cadastro** | Produtos, Fornecedores |

### 📦 Produtos e Fornecedores
- CRUD completo com validação via Factory
- Cálculo automático de lucro via **Strategy Pattern**
- **Margem mínima de 35% sobre o custo** (RN02)
- Cadastro de fornecedores com indicador de certificação orgânica (RN10)
- Busca por descrição ou ID

### 🛒 Vendas e Pedidos
- Registro de vendas com múltiplos produtos
- **Cadastro de cliente junto com a primeira compra** (RN05/RN06)
- **Baixa automática de estoque** em transação atômica
- Proteção contra estoque negativo em operações concorrentes
- Status: **Pendente → Concluído** (após pagamento — RN07) ou **Cancelado** (devolve estoque)
- Formas de pagamento: Dinheiro, Pix, Débito, Crédito

### 📊 Estoque
- Movimentações: **entrada, saída e descarte**
- **Auditoria completa** (usuário, data/hora, quantidade, motivo — RN11)
- Alertas de validade (vencidos ou vencendo em até **7 dias** — RN09)
- Histórico das últimas 30 movimentações

### 💰 Relatórios Financeiros
- Resumo do período: faturamento, lucro, custo, ticket médio e margem
- Ranking de produtos mais vendidos
- **Alerta de pagamentos pendentes há mais de 15 dias** (RN08)
- Filtro por período (data inicial e final)

## 🏗️ Arquitetura

O projeto segue o padrão **MVC** com separação em camadas e aplica padrões **GoF**:

┌─────────────────────────────────────────────────────────────┐
│ APRESENTAÇÃO │
│ View (PHP templates) ←→ Controllers │
│ Router (front controller) + Auth + Csrf + Flash │
└─────────────────────────────────────────────────────────────┘
↕
┌─────────────────────────────────────────────────────────────┐
│ NEGÓCIO │
│ Model (Entidades) + Factory (validação) │
│ Strategy (cálculo de preço) + Service (AuthService) │
└─────────────────────────────────────────────────────────────┘
↕
┌─────────────────────────────────────────────────────────────┐
│ PERSISTÊNCIA │
│ DAO (BaseDAO + específicos) + Conexao (Singleton PDO) │
└─────────────────────────────────────────────────────────────┘
↕
MySQL / SQLite


### 🎯 Padrões de Projeto Aplicados

| Padrão | Onde | Objetivo |
|--------|------|----------|
| **Singleton** | `Conexao` | Instância única de conexão PDO por requisição |
| **DAO** | `BaseDAO` + DAOs específicos | Isolar acesso ao banco de dados |
| **Factory** | `ProdutoFactory`, `ClienteFactory`, `PedidoFactory`, `FornecedorFactory`, `UsuarioFactory` | Centralizar validação e criação de entidades |
| **Strategy** | `CalculoPrecoStrategy` → `LucroProdutoStrategy` | Permitir trocar regra de cálculo de lucro sem alterar consumidores |
| **Front Controller** | `public/index.php` + `Router` | Roteamento centralizado com lista branca |
| **Template Method** | `BaseDAO::transacao()` | Envolver operações em transações reutilizáveis |
| **Injeção de Dependência** | `BaseDAO::__construct(PDO $pdo = null)` | Facilita testes com mocks |
| **Service Layer** | `AuthService` | Isolar regra de autenticação (testável) |

### 🛡️ Segurança Implementada

- ✅ **Senhas** com `password_hash()` (bcrypt)
- ✅ **Prepared statements** em todas as queries (previne SQL Injection)
- ✅ **Token CSRF** com `hash_equals()` em todo POST
- ✅ **Cookie de sessão** `HttpOnly` (JS não lê) e `Secure` em HTTPS
- ✅ **Session fixation** prevenida (`session_regenerate_id(true)`)
- ✅ **Timeout de sessão** por inatividade (30 min)
- ✅ **Mensagem genérica** no login (evita enumeração de usuários)
- ✅ **Bloqueio temporário** após tentativas falhas
- ✅ **Escapamento HTML** via `e()` (ENT_QUOTES + UTF-8)
- ✅ **Credenciais do banco** em `config/database.php` (fora do versionamento)
- ✅ **Erros do banco** não expõem dados sensíveis ao usuário final

### 📋 Regras de Negócio Implementadas

| Código | Regra |
|--------|-------|
| **RN02** | Margem mínima de lucro de 35% sobre o custo |
| **RN05** | Nome, telefone e e-mail obrigatórios no cadastro de cliente |
| **RN06** | Cliente pode ser cadastrado junto com a primeira compra |
| **RN07** | Pedido só é concluído após confirmação do pagamento |
| **RN08** | Alerta de pedidos pendentes há mais de 15 dias |
| **RN09** | Alerta de produtos vencendo em até 7 dias |
| **RN10** | Fornecedores certificados aparecem primeiro |
| **RN11** | Toda movimentação de estoque gera registro de auditoria |
| **RNF05/06** | Operações críticas em transação atômica (tudo ou nada) |

## 🛠️ Tecnologias

| Camada | Tecnologia |
|--------|------------|
| **Backend** | PHP 5.6+ (compatível com 7.x e 8.x) |
| **Banco de Dados** | MySQL 5.7+ / MariaDB (InnoDB, utf8mb4) |
| **Testes** | SQLite em memória (opcional para CI) |
| **Frontend** | HTML5, CSS3, JavaScript (server-side rendering) |
| **Servidor Local** | USBWebserver v10 (Apache + PHP + MySQL portátil) |
| **Controle de Versão** | Git / GitHub |
| **Compatibilidade** | Windows, Linux, Android, iOS (via navegador) |

## 📁 Estrutura do Repositório

terrasara/
├── public/ # Document root
│ ├── index.php # Front Controller
│ └── assets/ # CSS, JS, imagens
├── app/
│ ├── controllers/ # Auth, Cliente, Produto, Fornecedor,
│ │ # Venda, Estoque, Relatorio, Usuario
│ ├── dao/ # BaseDAO, ClienteDAO, ProdutoDAO,
│ │ # PedidoDAO, FornecedorDAO, UsuarioDAO,
│ │ # MovimentacaoDAO, RelatorioDAO
│ ├── factory/ # ProdutoFactory, ClienteFactory,
│ │ # PedidoFactory, FornecedorFactory,
│ │ # UsuarioFactory
│ ├── model/ # Cliente, Fornecedor, Produto,
│ │ # Pedido, ItemPedido, Usuario
│ ├── service/ # AuthService
│ ├── strategy/ # CalculoPrecoStrategy,
│ │ # LucroProdutoStrategy, CalculoPrecoContext
│ └── views/ # Templates PHP (auth, cliente, produto,
│ # fornecedor, venda, estoque, relatorio,
│ # usuario, erro, layout)
├── config/ # Conexao, Router, Auth, Csrf, Flash,
│ │ # helpers, routes.php, database.php
│ └── database.example.php # Modelo de config (versionado)
├── database/
│ └── terrasara.sql # Schema + seed
├── docs/ # Diagramas, casos de uso, plano de testes
├── tests/ # Testes unitários/integração
└── README.md


## 🚀 Como Executar

### ⚙️ Pré-requisitos

- **PHP 5.6+** (recomendado 7.4+)
- **MySQL 5.7+** ou MariaDB
- Extensão **`pdo_mysql`** habilitada
- Extensão **`openssl`** habilitada (para geração segura de tokens CSRF no PHP 5.6)
- **USBWebserver v10** (ou Apache/Nginx + PHP)

### 1️⃣ Habilitar a extensão `pdo_mysql`

No **USBWebserver v10**:

1. Abra a pasta `USBWebserver v10/` → `settings/`
2. Edite o arquivo **`php.ini`** no Bloco de Notas
3. Localize a linha `;extension=pdo_mysql`
4. **Remova o `;`** do início e salve
5. Reinicie o USBWebserver

```ini
; antes
;extension=pdo_mysql

; depois
extension=pdo_mysql****


Criar o banco de dados
No phpMyAdmin (ou MySQL CLI), execute o script database/terrasara.sql:

sql
CREATE DATABASE IF NOT EXISTS terrasara
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE terrasara;

-- Tabelas: usuarios, fornecedores, produtos, clientes,
--          pedidos, itens_pedido, movimentacoes_estoque
-- + índices + usuário administrador padrão
O script já cria as 7 tabelas, 4 índices e o usuário administrador inicial.

3️⃣ Configurar a conexão
Copie o modelo e ajuste as credenciais:

bash
cp config/database.example.php config/database.php
Edite config/database.php:

php
return array(
    'driver' => 'mysql',
    'host'   => 'localhost',
    'port'   => 3306,
    'name'   => 'terrasara',
    'user'   => 'root',
    'pass'   => '',
    'charset'=> 'utf8mb4',
);
💡 Variáveis de ambiente têm prioridade (úteis em CI):
TERRASARA_DB_HOST, TERRASARA_DB_NAME, TERRASARA_DB_USER, TERRASARA_DB_PASS.

4️⃣ Acessar o sistema
Abra no navegador:

text
http://localhost/terrasara/public/
🔑 Credenciais iniciais
Campo	Valor
📧 E-mail	admin@terrasara.com.br
🔑 Senha	Terrasara@2026
⚠️ Importante: altere a senha do administrador após o primeiro login.

🔀 Rotas Principais
O roteamento é feito por front controller com lista branca (config/routes.php). Formato: index.php?r=modulo/acao.

Rota	Método	Acesso	Descrição
auth/login	GET/POST	Público	Tela de login
auth/logout	GET	Logado	Encerrar sessão
auth/senha	GET/POST	Logado	Trocar própria senha
produto/listar	GET	Logado	Listagem de produtos
produto/criar	GET/POST	Cadastro/Admin	Novo produto
produto/editar	GET/POST	Cadastro/Admin	Editar produto
produto/excluir	POST	Admin	Excluir produto
cliente/listar	GET	Vendedor/Admin	Listar clientes
venda/novo	GET/POST	Vendedor/Admin	Registrar venda
venda/confirmar	POST	Vendedor/Admin	Confirmar pagamento
venda/cancelar	POST	Vendedor/Admin	Cancelar pedido
estoque/index	GET	Estoquista/Admin	Consultar estoque
estoque/registrar	POST	Estoquista/Admin	Entrada/descarte
relatorio/financeiro	GET	Financeiro/Admin	Relatório do período
usuario/index	GET	Admin	Lista de usuários


🗄️ Modelo de Dados

Tabelas

Tabela	Descrição
usuarios	Login, senha (bcrypt), perfil, controle de tentativas e bloqueio
fornecedores	Nome, contato, certificação orgânica
produtos	Descrição, preço venda/custo, validade, categoria, unidade, estoque, fornecedor
clientes	Nome, telefone e e-mail (únicos)
pedidos	Cliente, usuário, pagamento, status, total, lucro, datas
itens_pedido	Produto, quantidade, preço e custo unitário, subtotal
movimentacoes_estoque	Auditoria: usuário, tipo, quantidade, motivo, data

🔒 Integridade Referencial
Cliente com pedidos não pode ser excluído

Fornecedor com produtos não pode ser excluído

Produto já vendido não pode ser excluído

Exclusão de produto remove histórico de estoque apenas se nunca vendido

🧩 Casos de Uso
Código	Caso de Uso	Módulo
UC01	Realizar Login	Autenticação
UC02	Recuperar Senha (via administradora)	Autenticação
UC03	Encerrar Sessão	Autenticação
UC04	Realizar Vendas	Vendas
UC05	Verificar Estoque	Estoque
UC06	Calcular Preço do Produto	Precificação
UC07	Gerar Relatórios Financeiros	Financeiro
UC08	Acompanhar Indicadores de Desempenho	Financeiro
UC15	Registrar Entrada / Descarte	Estoque
UC16	Consultar Movimentações	Estoque

🧪 Testes
O projeto segue o Plano de Testes — Fase 3 (Testes, Integração e Refatoração).

A arquitetura foi projetada para testabilidade:

BaseDAO recebe PDO por injeção de dependência (permite mocks)

Conexao::definir($pdo) permite substituir a conexão em testes

Suporte a SQLite em memória (ideal para CI)

Regras de negócio isoladas em Factories e Services (testáveis sem banco)

Uso de transações permite testar cenários de rollback

📚 Documentação
📄 Artigo: Concepção e Arquitetura Básica do Sistema

📊 Diagrama de Classes de Domínio

📋 Diagrama de Casos de Uso

📝 Especificação de Casos de Uso

🧪 Plano de Testes (Fase 3)

Disponíveis em /docs.

👨‍💻 Autores
Nome	E-mail
Vinicius Kenzo Tomo	vinicius.tomo@aluno.cps.sp.gov.br
Leonardo Hideki Nakayama Silva	leonardo.silva97@aluno.cps.sp.gov.br
Ian Goor	ian.goor@aluno.cps.sp.gov.br
Isabella Monagatti Fujita	isabella.fujita@aluno.cps.sp.gov.br
Orientadora: Prof.ª Elisabete Ferreira dos Santos Gomes Silva

🏫 Instituição
FATEC Mogi das Cruzes
Curso Superior de Tecnologia em Análise e Desenvolvimento de Sistemas
Disciplina: Engenharia de Software III — 2026
