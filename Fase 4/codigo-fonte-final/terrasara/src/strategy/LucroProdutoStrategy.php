<?php
require_once dirname(__FILE__) . '/CalculoPrecoStrategy.php';

/**
 * Calcula o lucro unitário do produto pela diferença entre venda e custo.
 */
class LucroProdutoStrategy implements CalculoPrecoStrategy
{
    public function calcularLucro($precoVenda, $precoCusto)
    {
        return round((float) $precoVenda - (float) $precoCusto, 2);
    }

    public function calcularMargem($precoVenda, $precoCusto)
    {
        $custo = (float) $precoCusto;
        if ($custo <= 0) {
            return 0.0;
        }
        return round(((float) $precoVenda - $custo) / $custo * 100, 2);
    }

    public function precoSugerido($precoCusto, $margemPercentual)
    {
        return round((float) $precoCusto * (1 + (float) $margemPercentual / 100), 2);
    }

    public function nome()
    {
        return 'Lucro por produto (venda - custo)';
    }
}
