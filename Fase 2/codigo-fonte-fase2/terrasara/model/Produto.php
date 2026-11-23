<?php
require_once __DIR__ . '/../strategy/CalculoPrecoContext.php';

class Produto
{
    private $id;
    private $descricao;
    private $preco;
    private $validade;
    private $precoCusto;
    private $fornecedor;
    private $quantEstoque;
    private $categoria;
    private $unidadeMedida;
    private $lucro;

    public function getId()            { return $this->id; }
    public function getDescricao()     { return $this->descricao; }
    public function getPreco()         { return $this->preco; }
    public function getValidade()      { return $this->validade; }
    public function getPrecoCusto()    { return $this->precoCusto; }
    public function getFornecedor()    { return $this->fornecedor; }
    public function getQuantEstoque()  { return $this->quantEstoque; }
    public function getCategoria()     { return $this->categoria; }
    public function getUnidadeMedida() { return $this->unidadeMedida; }
    public function getLucro()         { return $this->lucro; }

    public function setId($id)                       { $this->id = $id; }
    public function setDescricao($descricao)         { $this->descricao = $descricao; }
    public function setPreco($preco)                 { $this->preco = $preco; }
    public function setValidade($validade)           { $this->validade = $validade; }
    public function setPrecoCusto($precoCusto)       { $this->precoCusto = $precoCusto; }
    public function setFornecedor($fornecedor)       { $this->fornecedor = $fornecedor; }
    public function setQuantEstoque($quantEstoque)   { $this->quantEstoque = $quantEstoque; }
    public function setCategoria($categoria)         { $this->categoria = $categoria; }
    public function setUnidadeMedida($unidadeMedida) { $this->unidadeMedida = $unidadeMedida; }
    public function setLucro($lucro)                 { $this->lucro = $lucro; }

    public function atualizarLucro(CalculoPrecoContext $contexto): void
    {
        $this->lucro = $contexto->calcularLucro((float) $this->preco, (float) $this->precoCusto);
    }
}
