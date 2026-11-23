<section class="form-container form-largo">
    <h1>Pedido #<?= (int) $pedido->getId() ?></h1>

    <div class="detalhes">
        <p><strong>Status:</strong> <span class="selo selo-<?= e($pedido->getStatus()) ?>"><?= e($pedido->getNomeStatus()) ?></span></p>
        <p><strong>Cliente:</strong> <?= e($pedido->getClienteNome()) ?></p>
        <p><strong>Data:</strong> <?= e(datahora_br($pedido->getCriadoEm())) ?></p>
        <p><strong>Forma de pagamento:</strong> <?= e($pedido->getNomeForma()) ?></p>
        <?php if ($pedido->getPagoEm()): ?>
            <p><strong>Pago em:</strong> <?= e(datahora_br($pedido->getPagoEm())) ?></p>
        <?php endif; ?>
        <?php if ($pedido->getObservacao() !== ''): ?>
            <p><strong>Observações:</strong> <?= nl2br(e($pedido->getObservacao())) ?></p>
        <?php endif; ?>
    </div>

    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Produto</th><th>Qtd</th><th>Preço unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($pedido->getItens() as $item): ?>
            <tr>
                <td><?= e($item->getDescricao()) ?></td>
                <td><?= (int) $item->getQuantidade() ?></td>
                <td><?= e(moeda($item->getPrecoUnitario())) ?></td>
                <td><?= e(moeda($item->getSubtotal())) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <div class="total-box">Total: <strong><?= e(moeda($pedido->getTotal())) ?></strong></div>

    <?php if ($pedido->getStatus() === Pedido::PENDENTE): ?>
        <div class="acoes-pedido">
            <?php if (Router::permitido('venda/confirmar')): ?>
            <form action="<?= e(rota('venda/confirmar')) ?>" method="post" class="form-inline"
                  onsubmit="return confirm('Confirmar o recebimento do pagamento deste pedido?');">
                <?= Csrf::campo() ?>
                <input type="hidden" name="id" value="<?= (int) $pedido->getId() ?>">
                <button type="submit" class="btn-primary">Confirmar pagamento</button>
            </form>
            <?php endif; ?>
            <?php if (Router::permitido('venda/cancelar')): ?>
            <form action="<?= e(rota('venda/cancelar')) ?>" method="post" class="form-inline"
                  onsubmit="return confirm('Cancelar este pedido? Os itens voltam ao estoque.');">
                <?= Csrf::campo() ?>
                <input type="hidden" name="id" value="<?= (int) $pedido->getId() ?>">
                <button type="submit" class="btn-danger btn-grande">Cancelar pedido</button>
            </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p><a href="<?= e(rota('venda/listar')) ?>" class="btn-primary btn-link">Voltar</a></p>
</section>
