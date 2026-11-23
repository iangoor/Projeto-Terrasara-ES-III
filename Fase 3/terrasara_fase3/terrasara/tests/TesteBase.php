<?php
class FalhaTeste extends Exception
{
}

/**
 * Mini framework de testes (estilo xUnit/JUnit), sem dependências.
 * Cada método public "test..." de uma subclasse é um caso de teste.
 * setUp() cria um banco SQLite em memória NOVO e o registra no Singleton Conexao.
 */
abstract class TesteBase
{
    protected $pdo;

    // ---------- ciclo de vida ----------
    public function setUp()
    {
        $this->pdo = Conexao::criar(array('driver' => 'sqlite', 'sqlite_path' => ''));
        foreach ($this->esquema() as $sql) {
            $this->pdo->exec($sql);
        }
        Conexao::definir($this->pdo);
        $_SESSION = array();
    }

    public function tearDown()
    {
        Conexao::definir(null);
        $_SESSION = array();
    }

    /** Esquema equivalente ao do MySQL (colunas deduzidas das consultas dos DAOs). */
    protected function esquema()
    {
        return array(
            'CREATE TABLE usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
                senha_hash TEXT NOT NULL, perfil TEXT NOT NULL, ativo INTEGER NOT NULL DEFAULT 1,
                tentativas_falhas INTEGER NOT NULL DEFAULT 0, bloqueado_ate TEXT NULL, criado_em TEXT)',
            'CREATE TABLE fornecedores (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, telefone TEXT, email TEXT,
                certificado INTEGER NOT NULL DEFAULT 0)',
            'CREATE TABLE clientes (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, telefone TEXT, email TEXT, criado_em TEXT)',
            'CREATE TABLE produtos (id INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT NOT NULL, preco REAL NOT NULL,
                validade TEXT NOT NULL, precoCusto REAL NOT NULL, fornecedor_id INTEGER REFERENCES fornecedores(id),
                quantEstoque INTEGER NOT NULL DEFAULT 0, categoria TEXT, unidadeMedida TEXT, lucro REAL)',
            'CREATE TABLE pedidos (id INTEGER PRIMARY KEY AUTOINCREMENT, cliente_id INTEGER REFERENCES clientes(id),
                usuario_id INTEGER NOT NULL REFERENCES usuarios(id), forma_pagamento TEXT, observacao TEXT,
                status TEXT NOT NULL, total REAL, lucro REAL, criado_em TEXT, pago_em TEXT NULL)',
            'CREATE TABLE itens_pedido (id INTEGER PRIMARY KEY AUTOINCREMENT, pedido_id INTEGER NOT NULL REFERENCES pedidos(id),
                produto_id INTEGER NOT NULL REFERENCES produtos(id), descricao TEXT, quantidade INTEGER NOT NULL,
                preco_unitario REAL, custo_unitario REAL, subtotal REAL)',
            'CREATE TABLE movimentacoes_estoque (id INTEGER PRIMARY KEY AUTOINCREMENT, produto_id INTEGER NOT NULL REFERENCES produtos(id),
                usuario_id INTEGER NOT NULL REFERENCES usuarios(id), tipo TEXT NOT NULL, quantidade INTEGER NOT NULL,
                motivo TEXT, criado_em TEXT)',
        );
    }

    // ---------- fábricas de dados ----------
    protected function criarUsuario($perfil = 'vendedor', $email = 'vendedor@teste.com', $senha = 'Senha@1234')
    {
        $u = UsuarioFactory::criarDeArray(array(
            'nome' => 'Usuário Teste', 'email' => $email, 'perfil' => $perfil,
            'senha' => $senha, 'senha_confirmacao' => $senha,
        ));
        return (new UsuarioDAO())->salvar($u);
    }

    protected function criarFornecedor()
    {
        return (new FornecedorDAO())->salvar(FornecedorFactory::criarDeArray(array(
            'nome' => 'Sítio Verde', 'telefone' => '(11) 98888-7777', 'email' => 'contato@sitioverde.com', 'certificado' => '1',
        )));
    }

    protected function dadosProduto($sobrescrever = array())
    {
        return array_merge(array(
            'descricao' => 'Tomate orgânico', 'precoCusto' => '10,00', 'preco' => '13,50',
            'validade' => '2027-12-31', 'fornecedor_id' => '1', 'quantEstoque' => '10',
            'categoria' => 'Perecíveis', 'unidadeMedida' => 'Quilo',
        ), $sobrescrever);
    }

    /** Cadastra um produto (via Factory + DAO) e devolve o id. Exige usuário 1 e fornecedor 1 já criados. */
    protected function criarProduto($sobrescrever = array(), $usuarioId = 1)
    {
        $p = ProdutoFactory::criarDeArray($this->dadosProduto($sobrescrever));
        return (new ProdutoDAO())->salvarComEstoqueInicial($p, $usuarioId);
    }

    private static $seq = 0;

    /** Cenário padrão: usuário 1 (vendedor), fornecedor 1 e produto 1 (Tomate, custo 10, venda 13,50, estoque 10). */
    protected function prepararCenario()
    {
        $this->criarUsuario('vendedor');
        $this->criarFornecedor();
        $this->criarProduto();
    }

    /** Cria um cliente com e-mail/telefone únicos e devolve o id. */
    protected function criarCliente()
    {
        self::$seq++;
        $c = ClienteFactory::criarDeArray(array(
            'nome' => 'Cliente ' . self::$seq,
            'telefone' => '1199999' . str_pad((string) self::$seq, 4, '0', STR_PAD_LEFT),
            'email' => 'cliente' . self::$seq . '@teste.com',
        ));
        return (new ClienteDAO())->salvar($c);
    }

    /** Registra uma venda (pedido pendente) pelo fluxo real Factory -> DAO e devolve o id do pedido. */
    protected function venderProduto($produtoId, $quantidade, $agora = '2026-11-09 09:00:00', CalculoPrecoContext $contexto = null)
    {
        $porId = (new ProdutoDAO())->buscarPorIds(array($produtoId));
        $pedido = PedidoFactory::criarDeArray(array(
            'forma_pagamento' => 'pix', 'produto_id' => array($produtoId), 'quantidade' => array($quantidade),
            'cliente_id' => $this->criarCliente(),
        ), $porId, 1, substr($agora, 0, 10));
        return (new PedidoDAO())->salvar($pedido, null, $agora, $contexto);
    }

    protected function contar($tabela)
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM ' . $tabela)->fetchColumn();
    }

    protected function estoqueDe($produtoId)
    {
        return (int) $this->pdo->query('SELECT quantEstoque FROM produtos WHERE id = ' . (int) $produtoId)->fetchColumn();
    }

    // ---------- asserções ----------
    protected function fail($msg)
    {
        throw new FalhaTeste($msg);
    }

    protected function assertTrue($cond, $msg = 'Esperado true')
    {
        if ($cond !== true) {
            $this->fail($msg);
        }
    }

    protected function assertFalse($cond, $msg = 'Esperado false')
    {
        if ($cond !== false) {
            $this->fail($msg);
        }
    }

    protected function assertSame($esperado, $atual, $msg = '')
    {
        if ($esperado !== $atual) {
            $this->fail(($msg !== '' ? $msg . ' — ' : '') . 'esperado ' . var_export($esperado, true) . ' mas obteve ' . var_export($atual, true));
        }
    }

    /** Comparação de decimais com tolerância. */
    protected function assertMoney($esperado, $atual, $msg = '')
    {
        if (abs((float) $esperado - (float) $atual) > 0.005) {
            $this->fail(($msg !== '' ? $msg . ' — ' : '') . 'esperado ' . $esperado . ' mas obteve ' . var_export($atual, true));
        }
    }

    protected function assertNull($v, $msg = 'Esperado null')
    {
        if ($v !== null) {
            $this->fail($msg);
        }
    }

    protected function assertNotNull($v, $msg = 'Esperado não-null')
    {
        if ($v === null) {
            $this->fail($msg);
        }
    }

    protected function assertContains($trecho, $texto, $msg = '')
    {
        if (strpos((string) $texto, (string) $trecho) === false) {
            $this->fail(($msg !== '' ? $msg . ' — ' : '') . 'esperado conter "' . $trecho . '" em "' . $texto . '"');
        }
    }

    /** Executa $codigo e exige que lance $classe (e, opcionalmente, que a mensagem contenha $trecho). */
    protected function assertLanca($classe, $codigo, $trecho = null)
    {
        try {
            call_user_func($codigo);
        } catch (Exception $e) {
            if (!($e instanceof $classe)) {
                $this->fail('esperava ' . $classe . ' mas foi lançada ' . get_class($e) . ': ' . $e->getMessage());
            }
            if ($trecho !== null) {
                $this->assertContains($trecho, $e->getMessage(), 'mensagem da exceção');
            }
            return $e;
        }
        $this->fail('esperava a exceção ' . $classe . ', mas nada foi lançado');
    }
}
