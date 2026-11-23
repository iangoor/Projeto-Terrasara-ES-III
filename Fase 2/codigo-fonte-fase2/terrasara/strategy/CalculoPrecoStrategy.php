<?php
interface CalculoPrecoStrategy
{
    public function calcularLucro(float $precoVenda, float $precoCusto): float;
    public function nome(): string;
}
