<?php
/**
 * PADRÃO GoF: STRATEGY — contrato das regras de cálculo de lucro e preço.
 * Novas regras (ex.: lucro líquido com taxas) são novas classes, sem mexer em quem usa.
 */
interface CalculoPrecoStrategy
{
    /** Lucro unitário em R$. */
    public function calcularLucro($precoVenda, $precoCusto);

    /** Margem de lucro em % sobre o custo (RN02). */
    public function calcularMargem($precoVenda, $precoCusto);

    /** Preço de venda sugerido para atingir a margem (%) informada sobre o custo (UC06). */
    public function precoSugerido($precoCusto, $margemPercentual);

    public function nome();
}
