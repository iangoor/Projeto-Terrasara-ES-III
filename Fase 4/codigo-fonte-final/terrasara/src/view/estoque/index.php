<section class="table-container">
    <h1>Estoque</h1>

    <h2>Alertas de validade</h2>
    <?php if (empty($alertas)): ?>
        <div class="alerta alerta-sucesso">Nenhum produto vencido ou próximo do vencimento.</div>
    <?php else: ?>
        <div class="tabela-scroll">
        <table class="table">
            <thead><tr><th>Produto</th><th>Estoque</th><th>Validade</th><th>Situação</th></tr></thead>
            <tbody>
            <?php foreach ($alertas as $p): ?>
                <?php $vencido = ($p->statusValidade($hoje) === Produto::VENCIDO); ?>
                <tr class="<?= $vencido ? 'linha-vencido' : 'linha-proximo' ?>">
                    <td><strong><?= e($p->getDescricao()) ?></strong></td>
                    <td><?= (int) $p->getQuantEstoque() ?> <?= e($p->getUnidadeMedida()) ?></td>
                    <td><?= e(data_br($p->getValidade())) ?></td>
                    <td><?= $vencido ? '<span class="selo selo-vencido">Vencido — descartar</span>'
                        : '<span class="selo selo-proximo">Vence em ' . (int) $p->diasParaVencer($hoje) . ' dia(s)</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

    <?php if ($podeMovimentar): ?>
    <h2>Registrar movimentação</h2>
    <form action="<?= e(rota('estoque/registrar')) ?>" method="post" class="form-linha">
        <?= Csrf::campo() ?>
        <select name="produto_id" required aria-label="Produto">
            <option value="">Produto...</option>
            <?php foreach ($produtos as $p): ?>
                <option value="<?= (int) $p->getId() ?>"><?= e($p->getDescricao()) ?> (<?= (int) $p->getQuantEstoque() ?>)</option>
            <?php endforeach; ?>
        </select>
        <select name="tipo" required aria-label="Tipo">
            <option value="entrada">Entrada (compra/reposição)</option>
            <option value="descarte">Descarte (vencido/perda)</option>
        </select>
        <input type="number" name="quantidade" min="1" step="1" placeholder="Qtd" required aria-label="Quantidade">
        <input type="text" name="motivo" maxlength="255" placeholder="Motivo (opcional)" aria-label="Motivo">
        <button type="submit" class="btn-primary">Registrar</button>
    </form>
    <?php endif; ?>

    <h2>Situação do estoque</h2>
    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Produto</th><th>Categoria</th><th>Estoque</th><th>Validade</th></tr></thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= e($p->getDescricao()) ?></td>
                <td><?= e($p->getCategoria()) ?></td>
                <td class="estoque"><?= (int) $p->getQuantEstoque() ?> <?= e($p->getUnidadeMedida()) ?></td>
                <td><?= e(data_br($p->getValidade())) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($produtos)): ?>
            <tr><td colspan="4" class="vazio">Nenhum produto cadastrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <h2>Últimas movimentações (auditoria)</h2>
    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Data/hora</th><th>Produto</th><th>Tipo</th><th>Qtd</th><th>Usuário</th><th>Motivo</th></tr></thead>
        <tbody>
        <?php foreach ($movimentacoes as $m): ?>
            <tr>
                <td><?= e(datahora_br($m['criado_em'])) ?></td>
                <td><?= e($m['produto']) ?></td>
                <td><?= e(isset(MovimentacaoDAO::$TIPOS[$m['tipo']]) ? MovimentacaoDAO::$TIPOS[$m['tipo']] : $m['tipo']) ?></td>
                <td><?= (int) $m['quantidade'] ?></td>
                <td><?= e($m['usuario']) ?></td>
                <td><?= e($m['motivo']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($movimentacoes)): ?>
            <tr><td colspan="6" class="vazio">Nenhuma movimentação registrada.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
