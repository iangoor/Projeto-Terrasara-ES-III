<?php
class PedidoDAO extends BaseDAO
{
    /**
     * Grava o pedido, os itens e dá baixa no estoque — tudo em UMA transação (RNF05/RNF06).
     * Se qualquer passo falhar (ex.: estoque insuficiente), nada é gravado.
     *
     * @param Cliente|null $novoCliente cliente cadastrado junto com a primeira compra (RN06)
     * @param CalculoPrecoContext|null $contexto regra de cálculo de lucro (Strategy); padrão: LucroProdutoStrategy
     * @return int id do pedido
     */
    public function salvar(Pedido $pedido, Cliente $novoCliente = null, $agora = null, CalculoPrecoContext $contexto = null)
    {
        $agora = $agora ? $agora : date('Y-m-d H:i:s');
        $self = $this;

        return $this->transacao(function () use ($self, $pedido, $novoCliente, $agora, $contexto) {
            return $self->gravarPedido($pedido, $novoCliente, $agora, $contexto);
        });
    }

    /** Uso interno (precisa ser público para o closure acima em PHP 5.6). */
    public function gravarPedido(Pedido $pedido, $novoCliente, $agora, CalculoPrecoContext $contexto = null)
    {
        $clientes = new ClienteDAO($this->pdo);
        $movimentacoes = new MovimentacaoDAO($this->pdo);

        if ($novoCliente !== null) {
            $pedido->setClienteId($clientes->salvar($novoCliente));
        }
        if (!$pedido->getClienteId() || $clientes->buscarPorId($pedido->getClienteId()) === null) {
            throw new InvalidArgumentException('Selecione um cliente ou cadastre um novo.');
        }

        $contexto = $contexto ? $contexto : new CalculoPrecoContext(); // Strategy (injetável)
        $this->consultar(
            'INSERT INTO pedidos (cliente_id, usuario_id, forma_pagamento, observacao, status, total, lucro, criado_em, pago_em)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)',
            array($pedido->getClienteId(), (int) $pedido->getUsuarioId(), $pedido->getFormaPagamento(),
                $pedido->getObservacao(), Pedido::PENDENTE, $pedido->getTotal(), $pedido->getLucro($contexto), $agora)
        );
        $pedidoId = $this->ultimoId();

        foreach ($pedido->getItens() as $item) {
            $this->consultar(
                'INSERT INTO itens_pedido (pedido_id, produto_id, descricao, quantidade, preco_unitario, custo_unitario, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                array($pedidoId, $item->getProdutoId(), $item->getDescricao(), $item->getQuantidade(),
                    $item->getPrecoUnitario(), $item->getCustoUnitario(), $item->getSubtotal())
            );
            // Baixa de estoque + registro de auditoria (RN11). Lança exceção se faltar estoque.
            $movimentacoes->registrar($item->getProdutoId(), $pedido->getUsuarioId(), 'saida',
                $item->getQuantidade(), 'Pedido #' . $pedidoId, $agora);
        }

        $pedido->setId($pedidoId);
        return $pedidoId;
    }

    /** RN07: o pedido só é concluído após a confirmação do pagamento. */
    public function confirmarPagamento($id, $agora = null)
    {
        $agora = $agora ? $agora : date('Y-m-d H:i:s');
        $stmt = $this->consultar(
            "UPDATE pedidos SET status = 'concluido', pago_em = ? WHERE id = ? AND status = 'pendente'",
            array($agora, (int) $id)
        );
        if ($stmt->rowCount() === 0) {
            throw new InvalidArgumentException('Este pedido não está pendente de pagamento.');
        }
    }

    /** Cancela um pedido pendente e devolve os itens ao estoque. */
    public function cancelar($id, $usuarioId, $agora = null)
    {
        $agora = $agora ? $agora : date('Y-m-d H:i:s');
        $self = $this;

        $this->transacao(function () use ($self, $id, $usuarioId, $agora) {
            $self->executarCancelamento($id, $usuarioId, $agora);
        });
    }

    public function executarCancelamento($id, $usuarioId, $agora)
    {
        $pedido = $this->buscarPorId($id);
        if ($pedido === null || $pedido->getStatus() !== Pedido::PENDENTE) {
            throw new InvalidArgumentException('Somente pedidos pendentes de pagamento podem ser cancelados.');
        }
        $movimentacoes = new MovimentacaoDAO($this->pdo);
        foreach ($pedido->getItens() as $item) {
            $movimentacoes->registrar($item->getProdutoId(), $usuarioId, 'entrada',
                $item->getQuantidade(), 'Cancelamento do pedido #' . (int) $id, $agora);
        }
        $this->consultar("UPDATE pedidos SET status = 'cancelado' WHERE id = ?", array((int) $id));
    }

    public function listar($status = '', $limite = 200)
    {
        $sql = 'SELECT pe.*, c.nome AS cliente_nome FROM pedidos pe LEFT JOIN clientes c ON c.id = pe.cliente_id';
        $params = array();
        if ($status !== '' && array_key_exists($status, Pedido::$STATUS)) {
            $sql .= ' WHERE pe.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY pe.id DESC LIMIT ' . (int) $limite;

        $stmt = $this->consultar($sql, $params);
        $lista = array();
        while ($row = $stmt->fetch()) {
            $lista[] = $this->montar($row, false);
        }
        return $lista;
    }

    public function buscarPorId($id)
    {
        $row = $this->consultar(
            'SELECT pe.*, c.nome AS cliente_nome FROM pedidos pe LEFT JOIN clientes c ON c.id = pe.cliente_id WHERE pe.id = ?',
            array((int) $id)
        )->fetch();
        return $row ? $this->montar($row, true) : null;
    }

    private function montar(array $row, $comItens)
    {
        $p = new Pedido();
        $p->setId((int) $row['id']);
        $p->setClienteId($row['cliente_id'] === null ? null : (int) $row['cliente_id']);
        $p->setClienteNome(isset($row['cliente_nome']) ? $row['cliente_nome'] : '');
        $p->setUsuarioId((int) $row['usuario_id']);
        $p->setFormaPagamento($row['forma_pagamento']);
        $p->setObservacao((string) $row['observacao']);
        $p->setStatus($row['status']);
        $p->setCriadoEm($row['criado_em']);
        $p->setPagoEm($row['pago_em']);
        $p->setTotalSalvo($row['total']);
        $p->setLucroSalvo($row['lucro']);

        if ($comItens) {
            $stmt = $this->consultar('SELECT * FROM itens_pedido WHERE pedido_id = ? ORDER BY id', array((int) $row['id']));
            while ($i = $stmt->fetch()) {
                $p->adicionarItem(new ItemPedido($i['produto_id'], $i['descricao'], $i['quantidade'],
                    $i['preco_unitario'], $i['custo_unitario']));
            }
        }
        return $p;
    }
}
