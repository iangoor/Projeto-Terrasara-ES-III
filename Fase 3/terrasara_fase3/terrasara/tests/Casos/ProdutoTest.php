<?php
/** CT01-CT03 (Factory de Produto), CT13 (validade) e DAO de produtos. */
class ProdutoFactoryTest extends TesteBase
{
    public function testCT01_cadastroValidoCriaProdutoComLucroCalculado()
    {
        $p = ProdutoFactory::criarDeArray($this->dadosProduto());
        $this->assertSame('Tomate orgânico', $p->getDescricao());
        $this->assertMoney(13.5, $p->getPreco());
        $this->assertMoney(10.0, $p->getPrecoCusto());
        $this->assertMoney(3.5, $p->getLucro(), 'lucro calculado pela Strategy');
        $this->assertSame(10, $p->getQuantEstoque());
        $this->assertSame(1, $p->getFornecedorId());
    }

    public function testCT01_aceitaVirgulaOuPontoComoSeparadorDecimal()
    {
        $a = ProdutoFactory::criarDeArray($this->dadosProduto(array('preco' => '13,50')));
        $b = ProdutoFactory::criarDeArray($this->dadosProduto(array('preco' => '13.50')));
        $this->assertMoney($a->getPreco(), $b->getPreco());
        $this->assertSame(12.5, ProdutoFactory::numero('12,50'));
        $this->assertNull(ProdutoFactory::numero('abc'));
    }

    public function testCT02_precoAbaixoDaMargemMinimaDe35PorCentoEhRejeitado()
    {
        $dados = $this->dadosProduto(array('preco' => '13,00'));
        $this->assertLanca('InvalidArgumentException', function () use ($dados) {
            ProdutoFactory::criarDeArray($dados);
        }, 'R$ 13,50');
    }

    public function testCT02_precoExatamenteNaMargemMinimaEhAceito()
    {
        $p = ProdutoFactory::criarDeArray($this->dadosProduto(array('preco' => '13,50')));
        $this->assertMoney(3.5, $p->getLucro());
        $dados = $this->dadosProduto(array('preco' => '13,49'));
        $this->assertLanca('InvalidArgumentException', function () use ($dados) {
            ProdutoFactory::criarDeArray($dados);
        }, 'margem mínima');
    }

    public function testCT02_precoAbaixoDoCustoEhRejeitado()
    {
        $dados = $this->dadosProduto(array('preco' => '8,00'));
        $this->assertLanca('InvalidArgumentException', function () use ($dados) {
            ProdutoFactory::criarDeArray($dados);
        }, 'pelo menos');
    }

    public function testCT02_factoryUsaARegraDaEstrategiaInjetada()
    {
        $ctx = new CalculoPrecoContext(new LucroLiquidoStrategy(10)); // taxa de 10% => mínimo sobe para R$ 15,00
        $ruim = $this->dadosProduto(array('preco' => '14,00'));
        $this->assertLanca('InvalidArgumentException', function () use ($ruim, $ctx) {
            ProdutoFactory::criarDeArray($ruim, $ctx);
        }, 'R$ 15,00');
        $p = ProdutoFactory::criarDeArray($this->dadosProduto(array('preco' => '15,00')), $ctx);
        $this->assertMoney(3.5, $p->getLucro(), '15,00 - 10% - 10,00');
    }

    public function testCT03_camposObrigatoriosVazios()
    {
        $dados = $this->dadosProduto(array('descricao' => ''));
        $this->assertLanca('InvalidArgumentException', function () use ($dados) {
            ProdutoFactory::criarDeArray($dados);
        }, 'Preencha o campo: Descrição');
    }

    public function testCT03_unidadeECategoriaAdulteradasNoPost()
    {
        $a = $this->dadosProduto(array('unidadeMedida' => 'Litro'));
        $b = $this->dadosProduto(array('categoria' => 'Bebidas'));
        $this->assertLanca('InvalidArgumentException', function () use ($a) { ProdutoFactory::criarDeArray($a); }, 'Unidade de medida inválida');
        $this->assertLanca('InvalidArgumentException', function () use ($b) { ProdutoFactory::criarDeArray($b); }, 'Categoria inválida');
    }

    public function testCT03_precosNaoNumericosOuNaoPositivos()
    {
        foreach (array('0', '-5', 'abc') as $ruim) {
            $dados = $this->dadosProduto(array('preco' => $ruim));
            $this->assertLanca('InvalidArgumentException', function () use ($dados) {
                ProdutoFactory::criarDeArray($dados);
            }, 'maiores que zero');
        }
    }

    public function testCT03_dataDeValidadeInvalida()
    {
        foreach (array('31/12/2027', '2027-02-30', 'amanha') as $ruim) {
            $dados = $this->dadosProduto(array('validade' => $ruim));
            $this->assertLanca('InvalidArgumentException', function () use ($dados) {
                ProdutoFactory::criarDeArray($dados);
            }, 'Data de validade inválida');
        }
    }

    public function testCT03_estoqueEFornecedorInvalidos()
    {
        $a = $this->dadosProduto(array('quantEstoque' => '2.5'));
        $b = $this->dadosProduto(array('quantEstoque' => '-1'));
        $c = $this->dadosProduto(array('fornecedor_id' => '0'));
        $this->assertLanca('InvalidArgumentException', function () use ($a) { ProdutoFactory::criarDeArray($a); }, 'inteiro');
        $this->assertLanca('InvalidArgumentException', function () use ($b) { ProdutoFactory::criarDeArray($b); }, 'inteiro');
        $this->assertLanca('InvalidArgumentException', function () use ($c) { ProdutoFactory::criarDeArray($c); }, 'fornecedor');
    }

    public function testCT03_descricaoComMaisDe255Caracteres()
    {
        $dados = $this->dadosProduto(array('descricao' => str_repeat('a', 256)));
        $this->assertLanca('InvalidArgumentException', function () use ($dados) {
            ProdutoFactory::criarDeArray($dados);
        }, '255');
    }

    public function testCT03_estoqueZeroEhPermitido()
    {
        $p = ProdutoFactory::criarDeArray($this->dadosProduto(array('quantEstoque' => '0')));
        $this->assertSame(0, $p->getQuantEstoque());
    }
}

class ValidadeProdutoTest extends TesteBase
{
    private function comValidade($data)
    {
        $p = new Produto();
        $p->setValidade($data);
        return $p;
    }

    public function testCT13_produtoVencidoOntem()
    {
        $p = $this->comValidade('2026-11-21');
        $this->assertSame(-1, $p->diasParaVencer('2026-11-22'));
        $this->assertSame(Produto::VENCIDO, $p->statusValidade('2026-11-22'));
    }

    public function testCT13_venceHojeContaComoProximoDoVencimento()
    {
        $this->assertSame(Produto::PROXIMO, $this->comValidade('2026-11-22')->statusValidade('2026-11-22'));
    }

    public function testCT13_limiteDe7DiasDoAlerta()
    {
        $this->assertSame(Produto::PROXIMO, $this->comValidade('2026-11-29')->statusValidade('2026-11-22'), 'em 7 dias ainda alerta');
        $this->assertSame(Produto::OK, $this->comValidade('2026-11-30')->statusValidade('2026-11-22'), 'em 8 dias está ok');
    }

    public function testCT13_diasParaVencer()
    {
        $this->assertSame(3, $this->comValidade('2026-11-25')->diasParaVencer('2026-11-22'));
    }
}

class ProdutoDaoTest extends TesteBase
{
    public function setUp()
    {
        parent::setUp();
        $this->prepararCenario();
    }

    public function testCT01_salvarERecuperarPorId()
    {
        $p = (new ProdutoDAO())->buscarPorId(1);
        $this->assertSame('Tomate orgânico', $p->getDescricao());
        $this->assertMoney(3.5, $p->getLucro());
        $this->assertSame(10, $p->getQuantEstoque());
        $this->assertSame('Sítio Verde', $p->getFornecedorNome());
    }

    public function testCT01_estoqueInicialGeraMovimentacaoDeEntradaParaAuditoria()
    {
        $this->assertSame(1, $this->contar('movimentacoes_estoque'));
        $m = $this->pdo->query('SELECT tipo, quantidade, usuario_id FROM movimentacoes_estoque')->fetch();
        $this->assertSame('entrada', $m['tipo']);
        $this->assertSame(10, (int) $m['quantidade']);
        $this->assertSame(1, (int) $m['usuario_id']);
    }

    public function testCT01_buscarIdInexistenteDevolveNull()
    {
        $this->assertNull((new ProdutoDAO())->buscarPorId(999));
    }

    public function testCT01_editarAtualizaPrecoELucroMasNaoMexeNoEstoque()
    {
        $dao = new ProdutoDAO();
        $novo = ProdutoFactory::criarDeArray($this->dadosProduto(array('preco' => '14,00', 'quantEstoque' => '500')));
        $novo->setId(1);
        $dao->atualizar($novo);
        $p = $dao->buscarPorId(1);
        $this->assertMoney(14.0, $p->getPreco());
        $this->assertMoney(4.0, $p->getLucro(), 'lucro recalculado');
        $this->assertSame(10, $p->getQuantEstoque(), 'estoque só muda pelo módulo de Estoque');
    }

    public function testCT01_buscaPorTextoEPorId()
    {
        $this->criarProduto(array('descricao' => 'Alface crespa', 'precoCusto' => '2,00', 'preco' => '3,00'));
        $dao = new ProdutoDAO();
        $this->assertSame(2, count($dao->listar()));
        $this->assertSame(1, count($dao->listar('Alface')));
        $this->assertSame('Alface crespa', $dao->listar('alface')[0]->getDescricao());
    }

    public function testCT01_excluirProdutoNuncaVendidoApagaTambemOHistorico()
    {
        (new ProdutoDAO())->excluir(1);
        $this->assertSame(0, $this->contar('produtos'));
        $this->assertSame(0, $this->contar('movimentacoes_estoque'));
    }

    public function testCT01_excluirProdutoJaVendidoEhBloqueadoPelaChaveEstrangeira()
    {
        $this->venderProduto(1, 2);
        $this->assertLanca('PDOException', function () { (new ProdutoDAO())->excluir(1); });
        $this->assertSame(1, $this->contar('produtos'), 'o produto continua cadastrado');
        $this->assertSame(1, $this->contar('itens_pedido'));
    }

    public function testCT08_listagemParaVendaIgnoraVencidosESemEstoque()
    {
        $this->criarProduto(array('descricao' => 'Leite vencido', 'validade' => '2026-11-01'));
        $this->criarProduto(array('descricao' => 'Queijo sem estoque', 'quantEstoque' => '0'));
        $this->criarProduto(array('descricao' => 'Iogurte quase vencendo', 'validade' => '2026-11-25'));
        $lista = (new ProdutoDAO())->listarDisponiveisParaVenda('2026-11-22');
        $nomes = array();
        foreach ($lista as $p) {
            $nomes[] = $p->getDescricao();
        }
        $this->assertSame(array('Iogurte quase vencendo', 'Tomate orgânico'), $nomes);
    }

    public function testCT13_alertasListamVencidosEProximosComEstoque()
    {
        $this->criarProduto(array('descricao' => 'Leite vencido', 'validade' => '2026-11-01'));
        $this->criarProduto(array('descricao' => 'Queijo vencido sem estoque', 'validade' => '2026-11-01', 'quantEstoque' => '0'));
        $this->criarProduto(array('descricao' => 'Iogurte quase vencendo', 'validade' => '2026-11-25'));
        $alertas = (new ProdutoDAO())->listarAlertas('2026-11-22', 7);
        $this->assertSame(2, count($alertas));
        $this->assertSame('Leite vencido', $alertas[0]->getDescricao(), 'ordenado pela validade');
        $this->assertSame('Iogurte quase vencendo', $alertas[1]->getDescricao());
    }
}
