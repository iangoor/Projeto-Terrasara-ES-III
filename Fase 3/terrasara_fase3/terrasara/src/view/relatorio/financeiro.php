<section class="table-container">
    <div class="titulo-area">
        <h1>Relatório financeiro</h1>
        <form action="index.php" method="get" class="buscar-form">
            <input type="hidden" name="r" value="relatorio/financeiro">
            <label for="inicio">De</label>
            <input type="date" id="inicio" name="inicio" value="<?= e($inicio) ?>">
            <label for="fim">até</label>
            <input type="date" id="fim" name="fim" value="<?= e($fim) ?>">
            <button type="submit" class="btn-add">Filtrar</button>
        </form>
    </div>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <div class="cards">
        <div class="card"><span>Vendas concluídas</span><strong><?= (int) $resumo['vendas'] ?></strong></div>
        <div class="card"><span>Faturamento</span><strong><?= e(moeda($resumo['faturamento'])) ?></strong></div>
        <div class="card"><span>Lucro</span><strong><?= e(moeda($resumo['lucro'])) ?></strong></div>
        <div class="card"><span>Margem sobre a venda</span><strong><?= e(number_format($resumo['margem'], 2, ',', '.')) ?>%</strong></div>
        <div class="card"><span>Ticket médio</span><strong><?= e(moeda($resumo['ticket_medio'])) ?></strong></div>
        <div class="card card-aviso"><span>Pendentes de pagamento</span><strong><?= (int) $resumo['pendentes_qtd'] ?> · <?= e(moeda($resumo['pendentes_valor'])) ?></strong></div>
    </div>
    <p class="dica">Vendas concluídas = pedidos com pagamento confirmado no período. Lucro = soma de (preço de venda − custo) × quantidade.</p>

    <h2>Produtos mais vendidos</h2>
    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>#</th><th>Produto</th><th>Qtd vendida</th><th>Receita</th><th>Lucro</th></tr></thead>
        <tbody>
        <?php foreach ($maisVendidos as $i => $linha): ?>
            <tr>
                <td><?= (int) ($i + 1) ?></td>
                <td><strong><?= e($linha['descricao']) ?></strong></td>
                <td><?= (int) $linha['quantidade'] ?></td>
                <td><?= e(moeda($linha['receita'])) ?></td>
                <td><?= e(moeda($linha['lucro'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($maisVendidos)): ?>
            <tr><td colspan="5" class="vazio">Não existem dados financeiros registrados no período selecionado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <h2>Pagamentos pendentes há mais de 15 dias</h2>
    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Pedido</th><th>Cliente</th><th>Telefone</th><th>Total</th><th>Dias em aberto</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pendentes as $p): ?>
            <tr class="linha-proximo">
                <td>#<?= (int) $p['id'] ?></td>
                <td><?= e($p['cliente']) ?></td>
                <td><?= e(telefone_br($p['telefone'])) ?></td>
                <td><?= e(moeda($p['total'])) ?></td>
                <td><?= (int) $p['dias'] ?></td>
                <td><a class="btn-success" href="<?= e(rota('venda/ver', array('id' => $p['id']))) ?>">Abrir</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pendentes)): ?>
            <tr><td colspan="6" class="vazio">Nenhum cliente com pagamento atrasado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
