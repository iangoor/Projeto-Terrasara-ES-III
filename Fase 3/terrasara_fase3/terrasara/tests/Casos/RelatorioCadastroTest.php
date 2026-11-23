<?php
/** CT14 (relatório financeiro), CT15 (clientes/fornecedores). */
class RelatorioTest extends TesteBase
{
    public function setUp()
    {
        parent::setUp();
        $this->prepararCenario();
    }

    public function testCT14_resumoDoPeriodoConsideraSoVendasPagas()
    {
        $pago = $this->venderProduto(1, 3, '2026-11-09 09:00:00');
        (new PedidoDAO())->confirmarPagamento($pago, '2026-11-10 10:00:00');
        $this->venderProduto(1, 2, '2026-11-12 09:00:00'); // pendente: não entra no faturamento

        $r = (new RelatorioDAO())->resumo('2026-11-01', '2026-11-30');
        $this->assertSame(1, $r['vendas']);
        $this->assertMoney(40.5, $r['faturamento']);
        $this->assertMoney(10.5, $r['lucro']);
        $this->assertMoney(30.0, $r['custo']);
        $this->assertMoney(40.5, $r['ticket_medio']);
        $this->assertMoney(25.93, $r['margem'], '10,50 / 40,50');
        $this->assertSame(1, $r['pendentes_qtd']);
        $this->assertMoney(27.0, $r['pendentes_valor']);
    }

    public function testCT14_periodoSemVendasDevolveZerosSemDividirPorZero()
    {
        $r = (new RelatorioDAO())->resumo('2026-12-01', '2026-12-31');
        $this->assertSame(0, $r['vendas']);
        $this->assertMoney(0.0, $r['faturamento']);
        $this->assertMoney(0.0, $r['ticket_medio']);
        $this->assertMoney(0.0, $r['margem']);
    }

    public function testCT14_oDiaFinalDoPeriodoEhIncluido()
    {
        $id = $this->venderProduto(1, 1);
        (new PedidoDAO())->confirmarPagamento($id, '2026-11-30 23:59:59');
        $r = (new RelatorioDAO())->resumo('2026-11-01', '2026-11-30');
        $this->assertSame(1, $r['vendas']);
        $r2 = (new RelatorioDAO())->resumo('2026-11-01', '2026-11-29');
        $this->assertSame(0, $r2['vendas']);
    }

    public function testCT14_maisVendidosOrdenaPorQuantidade()
    {
        $this->criarProduto(array('descricao' => 'Alface crespa', 'precoCusto' => '2,00', 'preco' => '3,00', 'quantEstoque' => '20'));
        $a = $this->venderProduto(1, 3);
        $b = $this->venderProduto(2, 5);
        (new PedidoDAO())->confirmarPagamento($a, '2026-11-10 10:00:00');
        (new PedidoDAO())->confirmarPagamento($b, '2026-11-10 11:00:00');
        $lista = (new RelatorioDAO())->maisVendidos('2026-11-01', '2026-11-30');
        $this->assertSame(2, count($lista));
        $this->assertSame('Alface crespa', $lista[0]['descricao']);
        $this->assertSame(5, (int) $lista[0]['quantidade']);
        $this->assertSame('Tomate orgânico', $lista[1]['descricao']);
    }

    public function testCT14_pagamentosPendentesHaMaisDe15Dias()
    {
        $antigo = $this->venderProduto(1, 1, '2026-11-01 09:00:00');
        $this->venderProduto(1, 1, '2026-11-20 09:00:00'); // recente: não aparece
        $agora = strtotime('2026-11-22 12:00:00');
        $lista = (new RelatorioDAO())->pagamentosPendentes(15, $agora);
        $this->assertSame(1, count($lista));
        $this->assertSame($antigo, (int) $lista[0]['id']);
        $this->assertSame(21, $lista[0]['dias']);
    }
}

class CadastrosTest extends TesteBase
{
    public function testCT15_clienteValidoNormalizaTelefoneEEmail()
    {
        $c = ClienteFactory::criarDeArray(array('nome' => ' Maria Silva ', 'telefone' => '(11) 99999-9999', 'email' => 'MARIA@Teste.com'));
        $this->assertSame('Maria Silva', $c->getNome());
        $this->assertSame('11999999999', $c->getTelefone());
        $this->assertSame('maria@teste.com', $c->getEmail());
    }

    public function testCT15_clienteExigeNomeTelefoneEEmailValidos()
    {
        $base = array('nome' => 'Maria', 'telefone' => '(11) 99999-9999', 'email' => 'maria@teste.com');
        $casos = array(
            array(array('nome' => ''), 'obrigatórios'),
            array(array('telefone' => '123'), 'Telefone inválido'),
            array(array('email' => 'sem-arroba'), 'E-mail inválido'),
            array(array('nome' => 'A'), 'entre 2 e 120'),
        );
        foreach ($casos as $caso) {
            $dados = array_merge($base, $caso[0]);
            $this->assertLanca('InvalidArgumentException', function () use ($dados) { ClienteFactory::criarDeArray($dados); }, $caso[1]);
        }
    }

    public function testCT15_clienteDuplicadoPorEmailOuTelefoneEhRejeitado()
    {
        $dao = new ClienteDAO();
        $dao->salvar(ClienteFactory::criarDeArray(array('nome' => 'Maria', 'telefone' => '(11) 99999-9999', 'email' => 'maria@teste.com')));
        $mesmoEmail = ClienteFactory::criarDeArray(array('nome' => 'Outra', 'telefone' => '(11) 98888-0000', 'email' => 'maria@teste.com'));
        $mesmoFone = ClienteFactory::criarDeArray(array('nome' => 'Outra', 'telefone' => '(11) 99999-9999', 'email' => 'outra@teste.com'));
        $this->assertLanca('InvalidArgumentException', function () use ($dao, $mesmoEmail) { $dao->salvar($mesmoEmail); }, 'Já existe um cliente');
        $this->assertLanca('InvalidArgumentException', function () use ($dao, $mesmoFone) { $dao->salvar($mesmoFone); }, 'Já existe um cliente');
    }

    public function testCT15_editarClienteSemMudarEmailNaoContaComoDuplicado()
    {
        $dao = new ClienteDAO();
        $id = $dao->salvar(ClienteFactory::criarDeArray(array('nome' => 'Maria', 'telefone' => '(11) 99999-9999', 'email' => 'maria@teste.com')));
        $c = $dao->buscarPorId($id);
        $c->setNome('Maria Souza');
        $dao->atualizar($c);
        $this->assertSame('Maria Souza', $dao->buscarPorId($id)->getNome());
    }

    public function testCT15_clienteComPedidoNaoPodeSerExcluido()
    {
        $this->prepararCenario();
        $this->venderProduto(1, 1);
        $this->assertLanca('PDOException', function () { (new ClienteDAO())->excluir(1); });
        $this->assertSame(1, $this->contar('clientes'));
    }

    public function testCT15_fornecedorExigeNomeEGuardaCertificadoOrganico()
    {
        $this->assertLanca('InvalidArgumentException', function () { FornecedorFactory::criarDeArray(array('nome' => '')); }, 'Informe o nome');
        $f = FornecedorFactory::criarDeArray(array('nome' => 'Sítio Verde', 'certificado' => '1'));
        $this->assertTrue($f->isCertificado(), 'RN10');
        $this->assertFalse(FornecedorFactory::criarDeArray(array('nome' => 'Sítio Azul'))->isCertificado());
    }

    public function testCT15_fornecedoresCertificadosAparecemPrimeiro()
    {
        $dao = new FornecedorDAO();
        $dao->salvar(FornecedorFactory::criarDeArray(array('nome' => 'Aaa Comum')));
        $dao->salvar(FornecedorFactory::criarDeArray(array('nome' => 'Zzz Certificado', 'certificado' => '1')));
        $lista = $dao->listar();
        $this->assertSame('Zzz Certificado', $lista[0]->getNome());
    }

    public function testCT15_fornecedorComProdutosNaoPodeSerExcluido()
    {
        $this->prepararCenario();
        $this->assertLanca('PDOException', function () { (new FornecedorDAO())->excluir(1); });
        $this->assertSame(1, $this->contar('fornecedores'));
    }
}
