<?php
/** CT04 (Singleton) e CT05 (Strategy). */
class SingletonConexaoTest extends TesteBase
{
    public function testCT04_getConexaoDevolveSempreAMesmaInstancia()
    {
        $this->assertTrue(Conexao::getConexao() === Conexao::getConexao(), 'duas chamadas devem devolver o mesmo objeto');
    }

    public function testCT04_construtorEClonePrivadosImpedemNovasInstancias()
    {
        $r = new ReflectionClass('Conexao');
        $this->assertTrue($r->getConstructor()->isPrivate(), 'construtor deve ser privado');
        $this->assertTrue($r->getMethod('__clone')->isPrivate(), '__clone deve ser privado');
        $this->assertFalse($r->isInstantiable(), 'new Conexao() não pode ser permitido');
    }

    public function testCT04_daosDiferentesCompartilhamAMesmaConexao()
    {
        $this->prepararCenario();
        $a = new ProdutoDAO();
        $b = new ProdutoDAO();
        $this->assertTrue($a->getPdo() === $b->getPdo(), 'DAOs devem compartilhar a conexão');
        $this->assertTrue($a->getPdo() === Conexao::getConexao(), 'e ela deve ser a do Singleton');
    }

    public function testCT04_definirPermiteInjetarOutraConexaoNosTestes()
    {
        $primeira = Conexao::getConexao();
        $outra = Conexao::criar(array('driver' => 'sqlite', 'sqlite_path' => ''));
        Conexao::definir($outra);
        $this->assertTrue(Conexao::getConexao() === $outra, 'a conexão injetada deve ser a usada');
        $this->assertFalse(Conexao::getConexao() === $primeira, 'e diferente da anterior');
    }
}

class StrategyPrecoTest extends TesteBase
{
    public function testCT05_lucroSimplesCalculaLucroMargemEPrecoSugerido()
    {
        $s = new LucroProdutoStrategy();
        $this->assertMoney(3.5, $s->calcularLucro(8.5, 5));
        $this->assertMoney(35.0, $s->calcularMargem(13.5, 10));
        $this->assertMoney(13.5, $s->precoSugerido(10, 35));
        $this->assertMoney(0.0, $s->calcularMargem(10, 0), 'custo zero não pode dividir por zero');
    }

    public function testCT05_contextoUsaLucroSimplesPorPadrao()
    {
        $ctx = new CalculoPrecoContext();
        $this->assertMoney(3.5, $ctx->calcularLucro(13.5, 10));
        $this->assertMoney(13.5, $ctx->precoSugerido(10, 35));
    }

    public function testCT05_trocarAEstrategiaMudaOResultadoSemAlterarQuemUsa()
    {
        $simples = new CalculoPrecoContext(new LucroProdutoStrategy());
        $liquido = new CalculoPrecoContext(new LucroLiquidoStrategy(5));
        $this->assertMoney(20.0, $simples->calcularLucro(100, 80));
        $this->assertMoney(15.0, $liquido->calcularLucro(100, 80), 'taxa de 5% sobre 100 = 5 a menos de lucro');
        $this->assertMoney(105.26, $liquido->precoSugerido(80, 25), 'preço que garante 25% líquido');
    }

    public function testCT05_produtoRecalculaLucroComQualquerEstrategia()
    {
        $p = new Produto();
        $p->setPreco(100);
        $p->setPrecoCusto(80);
        $p->atualizarLucro(new CalculoPrecoContext(new LucroProdutoStrategy()));
        $this->assertMoney(20.0, $p->getLucro());
        $p->atualizarLucro(new CalculoPrecoContext(new LucroLiquidoStrategy(5)));
        $this->assertMoney(15.0, $p->getLucro());
    }

    public function testCT05_itemDoPedidoUsaAEstrategiaRecebida()
    {
        $item = new ItemPedido(1, 'Tomate', 3, 13.5, 10);
        $this->assertMoney(10.5, $item->getLucro(new CalculoPrecoContext()));
        $this->assertMoney(6.45, $item->getLucro(new CalculoPrecoContext(new LucroLiquidoStrategy(10))));
    }

    public function testCT05_estrategiaLiquidaRejeitaTaxaInvalida()
    {
        $this->assertLanca('InvalidArgumentException', function () { new LucroLiquidoStrategy(100); }, 'taxa');
        $this->assertLanca('InvalidArgumentException', function () { new LucroLiquidoStrategy(-1); }, 'taxa');
    }

    public function testCT05_todasAsEstrategiasImplementamOContrato()
    {
        $this->assertTrue(new LucroProdutoStrategy() instanceof CalculoPrecoStrategy);
        $this->assertTrue(new LucroLiquidoStrategy(3) instanceof CalculoPrecoStrategy);
    }
}
