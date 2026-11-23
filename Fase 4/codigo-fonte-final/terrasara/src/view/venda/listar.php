<section class="table-container">
    <div class="titulo-area">
        <h1>Vendas</h1>
        <div class="acoes-topo">
            <?php if (Router::permitido('venda/novo')): ?>
                <a href="<?= e(rota('venda/novo')) ?>" class="btn-add">Nova venda +</a>
            <?php endif; ?>
            <form action="index.php" method="get" class="buscar-form">
                <input type="hidden" name="r" value="venda/listar">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Todos os status</option>
                    <?php foreach (Pedido::$STATUS as $chave => $nome): ?>
                        <option value="<?= e($chave) ?>"<?= selecionado($status, $chave) ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit" class="btn-add">Filtrar</button></noscript>
            </form>
        </div>
    </div>

    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Pedido</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th>Total</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
            <tr>
                <td>#<?= (int) $p->getId() ?></td>
                <td><?= e(datahora_br($p->getCriadoEm())) ?></td>
                <td><?= e($p->getClienteNome()) ?></td>
                <td><?= e($p->getNomeForma()) ?></td>
                <td class="preco"><?= e(moeda($p->getTotal())) ?></td>
                <td><span class="selo selo-<?= e($p->getStatus()) ?>"><?= e($p->getNomeStatus()) ?></span></td>
                <td><a href="<?= e(rota('venda/ver', array('id' => $p->getId()))) ?>" class="btn-success">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($pedidos)): ?>
            <tr><td colspan="7" class="vazio">Nenhum pedido encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
