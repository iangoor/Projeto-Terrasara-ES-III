<?php
require_once __DIR__ . '/CalculoPrecoStrategy.php';
require_once __DIR__ . '/LucroProdutoStrategy.php';

class CalculoPrecoContext
{
    private CalculoPrecoStrategy $estrategia;

    public function __construct(?CalculoPrecoStrategy $estrategia = null)
    {
        $this->estrategia = $estrategia ?? new LucroProdutoStrategy();
    }

    public function setEstrategia(CalculoPrecoStrategy $estrategia): void
    {
        $this->estrategia = $estrategia;
    }

    public function getEstrategia(): CalculoPrecoStrategy
    {
        return $this->estrategia;
    }

    public function calcularLucro(float $precoVenda, float $precoCusto): float
    {
        return $this->estrategia->calcularLucro($precoVenda, $precoCusto);
    }
}
