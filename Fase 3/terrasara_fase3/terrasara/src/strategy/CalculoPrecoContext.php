<?php
require_once dirname(__FILE__) . '/CalculoPrecoStrategy.php';
require_once dirname(__FILE__) . '/LucroProdutoStrategy.php';

class CalculoPrecoContext
{
    private $estrategia;

    public function __construct(CalculoPrecoStrategy $estrategia = null)
    {
        $this->estrategia = $estrategia ? $estrategia : new LucroProdutoStrategy();
    }

    public function calcularLucro($precoVenda, $precoCusto)
    {
        return $this->estrategia->calcularLucro($precoVenda, $precoCusto);
    }

    public function precoSugerido($precoCusto, $margemPercentual)
    {
        return $this->estrategia->precoSugerido($precoCusto, $margemPercentual);
    }
}