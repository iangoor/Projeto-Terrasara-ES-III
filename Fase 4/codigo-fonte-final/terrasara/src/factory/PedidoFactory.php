<?php
require_once dirname(dirname(__FILE__)) . '/model/Pedido.php';
require_once dirname(dirname(__FILE__)) . '/model/ItemPedido.php';

/**
 * PADRÃO GoF: FACTORY — monta um Pedido válido a partir do formulário de venda.
 * Os preços vêm SEMPRE do banco ($produtosPorId), nunca do navegador.
 */
class PedidoFactory
{
    /**
     * @param array $dados          $_POST: produto_id[], quantidade[], forma_pagamento, observacao, cliente_id
     * @param array $produtosPorId  array(id => Produto) com os produtos citados no formulário
     */
    public static function criarDeArray(array $dados, array $produtosPorId, $usuarioId, $hoje = null)
    {
        $hoje = $hoje ? $hoje : date('Y-m-d');

        $forma = isset($dados['forma_pagamento']) ? $dados['forma_pagamento'] : '';
        if (!array_key_exists($forma, Pedido::$FORMAS)) {
            throw new InvalidArgumentException('Selecione a forma de pagamento.');
        }

        $observacao = isset($dados['observacao']) ? trim($dados['observacao']) : '';
        if (tamanho_texto($observacao) > 500) {
            throw new InvalidArgumentException('A observação deve ter no máximo 500 caracteres.');
        }

        $ids = isset($dados['produto_id']) && is_array($dados['produto_id']) ? $dados['produto_id'] : array();
        $qtds = isset($dados['quantidade']) && is_array($dados['quantidade']) ? $dados['quantidade'] : array();

        // Soma as quantidades se o mesmo produto aparecer em mais de uma linha.
        $pedidos = array();
        foreach ($ids as $i => $idBruto) {
            if (!is_scalar($idBruto) || trim((string) $idBruto) === '') {
                continue; // linha em branco ou valor inválido
            }
            $id = (int) $idBruto;
            $q = (isset($qtds[$i]) && is_scalar($qtds[$i])) ? filter_var($qtds[$i], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
            if ($q === false) {
                throw new InvalidArgumentException('Informe uma quantidade inteira (1 ou mais) para cada produto.');
            }
            $pedidos[$id] = (isset($pedidos[$id]) ? $pedidos[$id] : 0) + $q;
        }
        if (empty($pedidos)) {
            throw new InvalidArgumentException('Adicione pelo menos um produto ao pedido.');
        }

        $pedido = new Pedido();
        $pedido->setUsuarioId($usuarioId);
        $pedido->setFormaPagamento($forma);
        $pedido->setObservacao($observacao);
        $pedido->setStatus(Pedido::PENDENTE);

        $clienteId = isset($dados['cliente_id']) ? (int) $dados['cliente_id'] : 0;
        $pedido->setClienteId($clienteId > 0 ? $clienteId : null);

        foreach ($pedidos as $id => $quantidade) {
            if (!isset($produtosPorId[$id])) {
                throw new InvalidArgumentException('Produto não encontrado.');
            }
            $p = $produtosPorId[$id];
            if ($p->getValidade() < $hoje) {
                throw new InvalidArgumentException('O produto "' . $p->getDescricao() . '" está vencido e não pode ser vendido.');
            }
            if ($quantidade > (int) $p->getQuantEstoque()) {
                throw new InvalidArgumentException(
                    'Estoque insuficiente para "' . $p->getDescricao() . '" (disponível: ' . (int) $p->getQuantEstoque() . ').'
                );
            }
            $pedido->adicionarItem(new ItemPedido($p->getId(), $p->getDescricao(), $quantidade, $p->getPreco(), $p->getPrecoCusto()));
        }

        return $pedido;
    }
}
