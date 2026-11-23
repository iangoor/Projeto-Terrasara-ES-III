<?php
class ItemPedido
{
    private $produtoId;
    private $descricao;
    private $quantidade;
    private $precoUnitario;
    private $custoUnitario;

    public function __construct($produtoId, $descricao, $quantidade, $precoUnitario, $custoUnitario)
    {
        $this->produtoId = (int) $produtoId;
        $this->descricao = $descricao;
        $this->quantidade = (int) $quantidade;
        $this->precoUnitario = (float) $precoUnitario;
        $this->custoUnitario = (float) $custoUnitario;
    }

    public function getProdutoId()     { return $this->produtoId; }
    public function getDescricao()     { return $this->descricao; }
    public function getQuantidade()    { return $this->quantidade; }
    public function getPrecoUnitario() { return $this->precoUnitario; }
    public function getCustoUnitario() { return $this->custoUnitario; }

    public function getSubtotal()
    {
        return round($this->quantidade * $this->precoUnitario, 2);
    }

    /** Lucro do item, calculado pela estratégia (Strategy) vigente. */
    public function getLucro(CalculoPrecoContext $contexto)
    {
        return round($this->quantidade * $contexto->calcularLucro($this->precoUnitario, $this->custoUnitario), 2);
    }
}
