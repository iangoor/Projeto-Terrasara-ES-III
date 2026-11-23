<?php
/**
 * Movimentações de estoque (RN11): toda entrada/saída/descarte grava usuário, data/hora e quantidade,
 * e atualiza o estoque do produto na MESMA transação.
 */
class MovimentacaoDAO extends BaseDAO
{
    public static $TIPOS = array(
        'entrada'  => 'Entrada',
        'saida'    => 'Saída (venda)',
        'descarte' => 'Descarte',
    );

    public function registrar($produtoId, $usuarioId, $tipo, $quantidade, $motivo = '', $agora = null)
    {
        if (!array_key_exists($tipo, self::$TIPOS)) {
            throw new InvalidArgumentException('Tipo de movimentação inválido.');
        }
        $quantidade = filter_var($quantidade, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($quantidade === false) {
            throw new InvalidArgumentException('A quantidade deve ser um número inteiro maior que zero.');
        }
        $agora = $agora ? $agora : date('Y-m-d H:i:s');
        $motivo = cortar_texto(trim($motivo), 255);

        $this->transacao(function () use ($produtoId, $usuarioId, $tipo, $quantidade, $motivo, $agora) {
            if ($tipo === 'entrada') {
                $stmt = $this->consultar(
                    'UPDATE produtos SET quantEstoque = quantEstoque + ? WHERE id = ?',
                    array($quantidade, (int) $produtoId)
                );
                if ($stmt->rowCount() === 0) {
                    throw new InvalidArgumentException('Produto não encontrado.');
                }
            } else {
                // A condição "quantEstoque >= ?" impede estoque negativo mesmo com duas vendas simultâneas.
                $stmt = $this->consultar(
                    'UPDATE produtos SET quantEstoque = quantEstoque - ? WHERE id = ? AND quantEstoque >= ?',
                    array($quantidade, (int) $produtoId, $quantidade)
                );
                if ($stmt->rowCount() === 0) {
                    throw new InvalidArgumentException('Estoque insuficiente ou produto não encontrado (produto #' . (int) $produtoId . ').');
                }
            }
            $this->consultar(
                'INSERT INTO movimentacoes_estoque (produto_id, usuario_id, tipo, quantidade, motivo, criado_em)
                 VALUES (?, ?, ?, ?, ?, ?)',
                array((int) $produtoId, (int) $usuarioId, $tipo, $quantidade, $motivo, $agora)
            );
        });
    }

    public function listarRecentes($limite = 30)
    {
        $stmt = $this->consultar(
            'SELECT m.*, p.descricao AS produto, u.nome AS usuario
             FROM movimentacoes_estoque m
             JOIN produtos p ON p.id = m.produto_id
             JOIN usuarios u ON u.id = m.usuario_id
             ORDER BY m.id DESC LIMIT ' . (int) $limite
        );
        return $stmt->fetchAll();
    }
}
