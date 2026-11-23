<?php
require_once __DIR__ . '/CalculoPrecoStrategy.php';

/**
 * Calcula o lucro unitário do produto pela diferença entre venda e custo.
 */
class LucroProdutoStrategy implements CalculoPrecoStrategy
{
    public function calcularLucro(float $precoVenda, float $precoCusto): float
    {
        return round($precoVenda - $precoCusto, 2);
    }

    public function nome(): string
    {
        return 'Lucro por produto (venda - custo)';
    }
}
