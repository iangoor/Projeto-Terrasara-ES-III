<?php
require_once dirname(__FILE__) . '/CalculoPrecoStrategy.php';

/**
 * PADRÃO GoF: STRATEGY — segunda regra de cálculo: lucro LÍQUIDO, descontando uma taxa
 * percentual sobre a venda (ex.: taxa da maquininha de cartão).
 * Entra no sistema sem alterar Produto, Pedido, ItemPedido nem CalculoPrecoContext.
 */
class LucroLiquidoStrategy implements CalculoPrecoStrategy
{
    private $taxa; // % descontado do preço de venda

    public function __construct($taxaPercentual = 0)
    {
        $taxaPercentual = (float) $taxaPercentual;
        if ($taxaPercentual < 0 || $taxaPercentual >= 100) {
            throw new InvalidArgumentException('A taxa deve estar entre 0 e 99,99%.');
        }
        $this->taxa = $taxaPercentual;
    }

    private function liquido($precoVenda)
    {
        return (float) $precoVenda * (1 - $this->taxa / 100);
    }

    public function calcularLucro($precoVenda, $precoCusto)
    {
        return round($this->liquido($precoVenda) - (float) $precoCusto, 2);
    }

    public function calcularMargem($precoVenda, $precoCusto)
    {
        $custo = (float) $precoCusto;
        if ($custo <= 0) {
            return 0.0;
        }
        return round(($this->liquido($precoVenda) - $custo) / $custo * 100, 2);
    }

    public function precoSugerido($precoCusto, $margemPercentual)
    {
        $alvoLiquido = (float) $precoCusto * (1 + (float) $margemPercentual / 100);
        return round($alvoLiquido / (1 - $this->taxa / 100), 2);
    }

    public function nome()
    {
        return 'Lucro líquido (venda - taxa - custo)';
    }
}
