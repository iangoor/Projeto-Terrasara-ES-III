<?php
/**
 * Consultas do relatório financeiro (UC07, UC08, UC09).
 * Datas de entrada no formato AAAA-MM-DD; o período inclui o dia final.
 */
class RelatorioDAO extends BaseDAO
{
    private function limites($inicio, $fim)
    {
        $fimExclusivo = date('Y-m-d', strtotime($fim . ' +1 day'));
        return array($inicio . ' 00:00:00', $fimExclusivo . ' 00:00:00');
    }

    /** Vendas concluídas (pagas) no período + pendentes criados no período. */
    public function resumo($inicio, $fim)
    {
        list($de, $ate) = $this->limites($inicio, $fim);

        $v = $this->consultar(
            "SELECT COUNT(*) AS qtd, COALESCE(SUM(total), 0) AS faturamento, COALESCE(SUM(lucro), 0) AS lucro
             FROM pedidos WHERE status = 'concluido' AND pago_em >= ? AND pago_em < ?",
            array($de, $ate)
        )->fetch();

        $p = $this->consultar(
            "SELECT COUNT(*) AS qtd, COALESCE(SUM(total), 0) AS valor
             FROM pedidos WHERE status = 'pendente' AND criado_em >= ? AND criado_em < ?",
            array($de, $ate)
        )->fetch();

        $qtd = (int) $v['qtd'];
        $faturamento = round((float) $v['faturamento'], 2);
        $lucro = round((float) $v['lucro'], 2);

        return array(
            'vendas'          => $qtd,
            'faturamento'     => $faturamento,
            'lucro'           => $lucro,
            'custo'           => round($faturamento - $lucro, 2),
            'ticket_medio'    => $qtd > 0 ? round($faturamento / $qtd, 2) : 0.0,
            'margem'          => $faturamento > 0 ? round($lucro / $faturamento * 100, 2) : 0.0,
            'pendentes_qtd'   => (int) $p['qtd'],
            'pendentes_valor' => round((float) $p['valor'], 2),
        );
    }

    public function maisVendidos($inicio, $fim, $limite = 10)
    {
        list($de, $ate) = $this->limites($inicio, $fim);
        $stmt = $this->consultar(
            "SELECT i.produto_id, i.descricao, SUM(i.quantidade) AS quantidade, SUM(i.subtotal) AS receita,
                    SUM(i.quantidade * (i.preco_unitario - i.custo_unitario)) AS lucro
             FROM itens_pedido i JOIN pedidos pe ON pe.id = i.pedido_id
             WHERE pe.status = 'concluido' AND pe.pago_em >= ? AND pe.pago_em < ?
             GROUP BY i.produto_id, i.descricao
             ORDER BY quantidade DESC, i.descricao
             LIMIT " . (int) $limite,
            array($de, $ate)
        );
        return $stmt->fetchAll();
    }

    /** RN08: pedidos sem pagamento há mais de $dias dias. */
    public function pagamentosPendentes($dias = 15, $agora = null)
    {
        $agora = $agora ? $agora : time();
        $corte = date('Y-m-d H:i:s', $agora - ($dias * 86400));
        $stmt = $this->consultar(
            "SELECT pe.id, pe.total, pe.criado_em, c.nome AS cliente, c.telefone
             FROM pedidos pe LEFT JOIN clientes c ON c.id = pe.cliente_id
             WHERE pe.status = 'pendente' AND pe.criado_em < ?
             ORDER BY pe.criado_em",
            array($corte)
        );
        $lista = $stmt->fetchAll();
        foreach ($lista as $i => $linha) {
            $lista[$i]['dias'] = (int) floor(($agora - strtotime($linha['criado_em'])) / 86400);
        }
        return $lista;
    }
}
