<?php
require_once dirname(dirname(__FILE__)) . '/strategy/CalculoPrecoContext.php';

class Produto
{
    const VENCIDO = 'vencido';
    const PROXIMO = 'proximo';
    const OK = 'ok';
    const DIAS_ALERTA = 7; // RN09: alerta de produtos próximos do vencimento

    private $id;
    private $descricao;
    private $preco;
    private $validade;
    private $precoCusto;
    private $fornecedorId;
    private $fornecedorNome;
    private $quantEstoque;
    private $categoria;
    private $unidadeMedida;
    private $lucro;

    public function getId()             { return $this->id; }
    public function getDescricao()      { return $this->descricao; }
    public function getPreco()          { return $this->preco; }
    public function getValidade()       { return $this->validade; }
    public function getPrecoCusto()     { return $this->precoCusto; }
    public function getFornecedorId()   { return $this->fornecedorId; }
    public function getFornecedorNome() { return $this->fornecedorNome; }
    public function getQuantEstoque()   { return $this->quantEstoque; }
    public function getCategoria()      { return $this->categoria; }
    public function getUnidadeMedida()  { return $this->unidadeMedida; }
    public function getLucro()          { return $this->lucro; }

    public function setId($id)                       { $this->id = $id; }
    public function setDescricao($descricao)         { $this->descricao = $descricao; }
    public function setPreco($preco)                 { $this->preco = $preco; }
    public function setValidade($validade)           { $this->validade = $validade; }
    public function setPrecoCusto($precoCusto)       { $this->precoCusto = $precoCusto; }
    public function setFornecedorId($fornecedorId)   { $this->fornecedorId = $fornecedorId; }
    public function setFornecedorNome($nome)         { $this->fornecedorNome = $nome; }
    public function setQuantEstoque($quantEstoque)   { $this->quantEstoque = $quantEstoque; }
    public function setCategoria($categoria)         { $this->categoria = $categoria; }
    public function setUnidadeMedida($unidadeMedida) { $this->unidadeMedida = $unidadeMedida; }
    public function setLucro($lucro)                 { $this->lucro = $lucro; }

    public function atualizarLucro(CalculoPrecoContext $contexto)
    {
        $this->lucro = $contexto->calcularLucro((float) $this->preco, (float) $this->precoCusto);
    }

    /** Dias até o vencimento (negativo = já venceu). */
    public function diasParaVencer($hoje = null)
    {
        $hoje = $hoje ? $hoje : date('Y-m-d');
        $d1 = new DateTime($hoje);
        $d2 = new DateTime($this->validade);
        return (int) $d1->diff($d2)->format('%r%a');
    }

    /** 'vencido', 'proximo' (vence em até DIAS_ALERTA dias) ou 'ok'. */
    public function statusValidade($hoje = null, $diasAlerta = null)
    {
        $diasAlerta = ($diasAlerta === null) ? self::DIAS_ALERTA : (int) $diasAlerta;
        $dias = $this->diasParaVencer($hoje);
        if ($dias < 0) {
            return self::VENCIDO;
        }
        return ($dias <= $diasAlerta) ? self::PROXIMO : self::OK;
    }
}
