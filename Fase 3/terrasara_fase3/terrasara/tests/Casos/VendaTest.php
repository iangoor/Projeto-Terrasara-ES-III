<?php
/** CT06-CT10: venda, estoque, pagamento e cancelamento (Factory de Pedido + DAO transacional). */
class PedidoFactoryTest extends TesteBase
{
    private function produto($validade = '2027-12-31', $estoque = 10)
    {
        $p = new Produto();
        $p->setId(1);
        $p->setDescricao('Tomate orgânico');
        $p->setPreco(13.5);
        $p->setPrecoCusto(10.0);
        $p->setValidade($validade);
        $p->setQuantEstoque($estoque);
        return $p;
    }

    private function dados($sobrescrever = array())
    {
        return array_merge(array(
            'forma_pagamento' => 'pix', 'produto_id' => array(1), 'quantidade' => array('3'), 'cliente_id' => '5',
        ), $sobrescrever);
    }

    private function criar($dados, $produto = null)
    {
        $produto = $produto ? $produto : $this->produto();
        return PedidoFactory::criarDeArray($dados, array(1 => $produto), 1, '2026-11-09');
    }

    public function testCT06_pedidoValidoFicaPendenteComTotalELucro()
    {
        $pedido = $this->criar($this->dados());
        $this->assertSame(Pedido::PENDENTE, $pedido->getStatus());
        $this->assertSame(1, count($pedido->getItens()));
        $this->assertMoney(40.5, $pedido->getTotal(), '3 x 13,50');
        $this->assertMoney(10.5, $pedido->getLucro(), '3 x (13,50 - 10,00)');
        $this->assertSame(5, $pedido->getClienteId());
    }

    public function testCT06_precoVemDoBancoENuncaDoNavegador()
    {
        $pedido = $this->criar($this->dados(array('preco_unitario' => array('0.01'))));
        $itens = $pedido->getItens();
        $this->assertMoney(13.5, $itens[0]->getPrecoUnitario());
    }

    public function testCT06_somaQuantidadesDoMesmoProdutoEmLinhasRepetidas()
    {
        $pedido = $this->criar($this->dados(array('produto_id' => array(1, 1), 'quantidade' => array('2', '3'))));
        $itens = $pedido->getItens();
        $this->assertSame(1, count($itens));
        $this->assertSame(5, $itens[0]->getQuantidade());
        $this->assertMoney(67.5, $pedido->getTotal());
    }

    public function testCT06_linhasEmBrancoSaoIgnoradas()
    {
        $pedido = $this->criar($this->dados(array('produto_id' => array('', 1), 'quantidade' => array('', '2'))));
        $this->assertSame(1, count($pedido->getItens()));
    }

    public function testCT06_formaDePagamentoInvalidaOuAusente()
    {
        $d = $this->dados(array('forma_pagamento' => 'boleto'));
        $this->assertLanca('InvalidArgumentException', function () use ($d) { $this->criar($d); }, 'forma de pagamento');
    }

    public function testCT06_pedidoSemItensEhRejeitado()
    {
        $d = $this->dados(array('produto_id' => array(), 'quantidade' => array()));
        $this->assertLanca('InvalidArgumentException', function () use ($d) { $this->criar($d); }, 'pelo menos um produto');
    }

    public function testCT06_quantidadeInvalida()
    {
        foreach (array('0', '-2', 'abc', '1.5') as $q) {
            $d = $this->dados(array('quantidade' => array($q)));
            $this->assertLanca('InvalidArgumentException', function () use ($d) { $this->criar($d); }, 'quantidade inteira');
        }
    }

    public function testCT06_produtoInexistenteEObservacaoLonga()
    {
        $d = $this->dados(array('produto_id' => array(99)));
        $this->assertLanca('InvalidArgumentException', function () use ($d) { $this->criar($d); }, 'Produto não encontrado');
        $o = $this->dados(array('observacao' => str_repeat('x', 501)));
        $this->assertLanca('InvalidArgumentException', function () use ($o) { $this->criar($o); }, '500');
    }

    public function testCT07_quantidadeAcimaDoEstoqueEhRejeitada()
    {
        $d = $this->dados(array('quantidade' => array('11')));
        $this->assertLanca('InvalidArgumentException', function () use ($d) { $this->criar($d); }, 'Estoque insuficiente');
    }

    public function testCT07_vendaDeTodoOEstoqueDisponivelEhPermitida()
    {
        $pedido = $this->criar($this->dados(array('quantidade' => array('10'))));
        $this->assertMoney(135.0, $pedido->getTotal());
    }

    public function testCT08_produtoVencidoNaoPodeSerVendido()
    {
        $d = $this->dados();
        $vencido = $this->produto('2026-11-01');
        $this->assertLanca('InvalidArgumentException', function () use ($d, $vencido) { $this->criar($d, $vencido); }, 'vencido');
    }

    public function testCT08_produtoQueVenceHojeAindaPodeSerVendido()
    {
        $pedido = $this->criar($this->dados(), $this->produto('2026-11-09'));
        $this->assertSame(1, count($pedido->getItens()));
    }
}

class VendaDaoTest extends TesteBase
{
    public function setUp()
    {
        parent::setUp();
        $this->prepararCenario();
    }

    public function testCT06_vendaGravaPedidoItensEDaBaixaNoEstoque()
    {
        $id = $this->venderProduto(1, 3);
        $dao = new PedidoDAO();
        $pedido = $dao->buscarPorId($id);
        $this->assertSame('pendente', $pedido->getStatus());
        $this->assertMoney(40.5, $pedido->getTotal());
        $this->assertMoney(10.5, $pedido->getLucro());
        $this->assertSame(1, count($pedido->getItens()));
        $this->assertSame(7, $this->estoqueDe(1), '10 - 3');
        $this->assertSame(2, $this->contar('movimentacoes_estoque'), 'entrada inicial + saída da venda');
    }

    public function testCT06_clienteNovoEhCadastradoJuntoComAPrimeiraCompra()
    {
        $porId = (new ProdutoDAO())->buscarPorIds(array(1));
        $pedido = PedidoFactory::criarDeArray(array(
            'forma_pagamento' => 'dinheiro', 'produto_id' => array(1), 'quantidade' => array('1'),
        ), $porId, 1, '2026-11-09');
        $novo = ClienteFactory::criarDeArray(array('nome' => 'Joana Lima', 'telefone' => '(11) 97777-6666', 'email' => 'JOANA@teste.com'));
        $id = (new PedidoDAO())->salvar($pedido, $novo, '2026-11-09 09:00:00');
        $this->assertSame(1, $this->contar('clientes'));
        $this->assertSame('Joana Lima', (new PedidoDAO())->buscarPorId($id)->getClienteNome());
    }

    public function testCT06_vendaSemClienteEhRejeitadaENadaEhGravado()
    {
        $porId = (new ProdutoDAO())->buscarPorIds(array(1));
        $pedido = PedidoFactory::criarDeArray(array(
            'forma_pagamento' => 'pix', 'produto_id' => array(1), 'quantidade' => array('1'),
        ), $porId, 1, '2026-11-09');
        $this->assertLanca('InvalidArgumentException', function () use ($pedido) {
            (new PedidoDAO())->salvar($pedido, null, '2026-11-09 09:00:00');
        }, 'Selecione um cliente');
        $this->assertSame(0, $this->contar('pedidos'));
        $this->assertSame(10, $this->estoqueDe(1));
    }

    public function testCT07_falhaNoMeioDaVendaDesfazTudo_tudoOuNada()
    {
        // Pedido montado "na mão" (sem a Factory) para forçar a falha na baixa de estoque, no meio da transação.
        $pedido = new Pedido();
        $pedido->setUsuarioId(1);
        $pedido->setFormaPagamento('pix');
        $pedido->adicionarItem(new ItemPedido(1, 'Tomate orgânico', 99, 13.5, 10));
        $novo = ClienteFactory::criarDeArray(array('nome' => 'Joana Lima', 'telefone' => '(11) 97777-6666', 'email' => 'joana@teste.com'));

        $this->assertLanca('InvalidArgumentException', function () use ($pedido, $novo) {
            (new PedidoDAO())->salvar($pedido, $novo, '2026-11-09 09:00:00');
        }, 'Estoque insuficiente');

        $this->assertSame(0, $this->contar('pedidos'), 'pedido desfeito');
        $this->assertSame(0, $this->contar('itens_pedido'), 'itens desfeitos');
        $this->assertSame(0, $this->contar('clientes'), 'cliente novo também desfeito');
        $this->assertSame(10, $this->estoqueDe(1), 'estoque intacto');
        $this->assertSame(1, $this->contar('movimentacoes_estoque'), 'só a entrada inicial');
    }

    public function testCT05_daoAceitaEstrategiaDeLucroInjetada()
    {
        $id = $this->venderProduto(1, 3, '2026-11-09 09:00:00', new CalculoPrecoContext(new LucroLiquidoStrategy(10)));
        $lucro = (float) $this->pdo->query('SELECT lucro FROM pedidos WHERE id = ' . $id)->fetchColumn();
        $this->assertMoney(6.45, $lucro, '3 x (13,50 - 10% - 10,00)');
    }

    public function testCT09_confirmarPagamentoConcluiOPedido()
    {
        $id = $this->venderProduto(1, 3);
        $dao = new PedidoDAO();
        $dao->confirmarPagamento($id, '2026-11-10 10:00:00');
        $pedido = $dao->buscarPorId($id);
        $this->assertSame('concluido', $pedido->getStatus());
        $this->assertSame('2026-11-10 10:00:00', $pedido->getPagoEm());
    }

    public function testCT09_naoEhPossivelConfirmarDuasVezesNemPedidoInexistente()
    {
        $id = $this->venderProduto(1, 1);
        $dao = new PedidoDAO();
        $dao->confirmarPagamento($id);
        $this->assertLanca('InvalidArgumentException', function () use ($dao, $id) { $dao->confirmarPagamento($id); }, 'não está pendente');
        $this->assertLanca('InvalidArgumentException', function () use ($dao) { $dao->confirmarPagamento(999); }, 'não está pendente');
    }

    public function testCT10_cancelarPedidoPendenteDevolveOEstoque()
    {
        $id = $this->venderProduto(1, 3);
        $this->assertSame(7, $this->estoqueDe(1));
        $dao = new PedidoDAO();
        $dao->cancelar($id, 1, '2026-11-09 10:00:00');
        $this->assertSame(10, $this->estoqueDe(1), 'itens devolvidos');
        $this->assertSame('cancelado', $dao->buscarPorId($id)->getStatus());
        $this->assertSame(3, $this->contar('movimentacoes_estoque'), 'entrada inicial + saída + entrada do cancelamento');
    }

    public function testCT10_pedidoConcluidoOuJaCanceladoNaoPodeSerCancelado()
    {
        $dao = new PedidoDAO();
        $pago = $this->venderProduto(1, 1);
        $dao->confirmarPagamento($pago);
        $this->assertLanca('InvalidArgumentException', function () use ($dao, $pago) { $dao->cancelar($pago, 1); }, 'Somente pedidos pendentes');

        $cancelado = $this->venderProduto(1, 1);
        $dao->cancelar($cancelado, 1);
        $this->assertLanca('InvalidArgumentException', function () use ($dao, $cancelado) { $dao->cancelar($cancelado, 1); }, 'Somente pedidos pendentes');
        $this->assertSame(9, $this->estoqueDe(1), 'só a venda concluída (1 un.) saiu do estoque');
    }

    public function testCT06_listarFiltraPorStatus()
    {
        $dao = new PedidoDAO();
        $a = $this->venderProduto(1, 1);
        $this->venderProduto(1, 1);
        $dao->confirmarPagamento($a);
        $this->assertSame(2, count($dao->listar()));
        $this->assertSame(1, count($dao->listar('pendente')));
        $this->assertSame(1, count($dao->listar('concluido')));
        $this->assertSame(2, count($dao->listar('status-invalido')), 'status desconhecido é ignorado');
    }
}
