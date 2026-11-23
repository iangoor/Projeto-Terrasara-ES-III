<?php
class Pedido
{
    const PENDENTE  = 'pendente';   // "Pendente de Pagamento" (UC04)
    const CONCLUIDO = 'concluido';  // RN07: só após confirmação do pagamento
    const CANCELADO = 'cancelado';

    public static $FORMAS = array(
        'dinheiro' => 'Dinheiro',
        'pix'      => 'Pix',
        'debito'   => 'Cartão de débito',
        'credito'  => 'Cartão de crédito',
    );

    public static $STATUS = array(
        'pendente'  => 'Pendente de Pagamento',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    );

    private $id;
    private $clienteId;
    private $clienteNome;
    private $usuarioId;
    private $formaPagamento;
    private $observacao = '';
    private $status = self::PENDENTE;
    private $criadoEm;
    private $pagoEm;
    private $totalSalvo;
    private $lucroSalvo;
    private $itens = array();

    public function getId()             { return $this->id; }
    public function getClienteId()      { return $this->clienteId; }
    public function getClienteNome()    { return $this->clienteNome; }
    public function getUsuarioId()      { return $this->usuarioId; }
    public function getFormaPagamento() { return $this->formaPagamento; }
    public function getObservacao()     { return $this->observacao; }
    public function getStatus()         { return $this->status; }
    public function getCriadoEm()       { return $this->criadoEm; }
    public function getPagoEm()         { return $this->pagoEm; }
    public function getItens()          { return $this->itens; }

    public function setId($v)             { $this->id = $v; }
    public function setClienteId($v)      { $this->clienteId = $v; }
    public function setClienteNome($v)    { $this->clienteNome = $v; }
    public function setUsuarioId($v)      { $this->usuarioId = $v; }
    public function setFormaPagamento($v) { $this->formaPagamento = $v; }
    public function setObservacao($v)     { $this->observacao = $v; }
    public function setStatus($v)         { $this->status = $v; }
    public function setCriadoEm($v)       { $this->criadoEm = $v; }
    public function setPagoEm($v)         { $this->pagoEm = $v; }
    /** Valores gravados no banco (usados quando o pedido é carregado). */
    public function setTotalSalvo($v)     { $this->totalSalvo = (float) $v; }
    public function setLucroSalvo($v)     { $this->lucroSalvo = (float) $v; }

    public function adicionarItem(ItemPedido $item)
    {
        $this->itens[] = $item;
    }

    public function getTotal()
    {
        if (empty($this->itens) && $this->totalSalvo !== null) {
            return $this->totalSalvo;
        }
        $total = 0.0;
        foreach ($this->itens as $item) {
            $total += $item->getSubtotal();
        }
        return round($total, 2);
    }

    public function getLucro(CalculoPrecoContext $contexto = null)
    {
        $contexto = $contexto ? $contexto : new CalculoPrecoContext();
        if (empty($this->itens) && $this->lucroSalvo !== null) {
            return $this->lucroSalvo;
        }
        $lucro = 0.0;
        foreach ($this->itens as $item) {
            $lucro += $item->getLucro($contexto);
        }
        return round($lucro, 2);
    }

    public function getNomeStatus()
    {
        return isset(self::$STATUS[$this->status]) ? self::$STATUS[$this->status] : $this->status;
    }

    public function getNomeForma()
    {
        return isset(self::$FORMAS[$this->formaPagamento]) ? self::$FORMAS[$this->formaPagamento] : $this->formaPagamento;
    }
}
