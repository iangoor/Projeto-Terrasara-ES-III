<?php $podeEditar = Router::permitido('produto/criar'); ?>
<section class="table-container">
    <div class="titulo-area">
        <h1>Produtos cadastrados</h1>
        <div class="acoes-topo">
            <?php if ($podeEditar): ?>
                <a href="<?= e(rota('produto/criar')) ?>" class="btn-add">Adicionar produto +</a>
            <?php endif; ?>
            <form action="index.php" method="get" class="buscar-form">
                <input type="hidden" name="r" value="produto/listar">
                <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Nome ou ID">
                <button type="submit" class="btn-add">Buscar</button>
            </form>
        </div>
    </div>

    <?php if ($vencidos > 0): ?>
        <div class="alerta alerta-erro"><strong><?= (int) $vencidos ?></strong> produto(s) com estoque já estão <strong>vencidos</strong>. Faça o descarte no módulo Estoque.</div>
    <?php endif; ?>
    <?php if ($proximos > 0): ?>
        <div class="alerta alerta-aviso"><strong><?= (int) $proximos ?></strong> produto(s) vencem em até <?= (int) Produto::DIAS_ALERTA ?> dias. Considere desconto ou doação.</div>
    <?php endif; ?>

    <div class="tabela-scroll">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th><th>Descrição</th><th>Preço venda</th><th>Preço custo</th><th>Lucro</th>
                <th>Estoque</th><th>Validade</th><th>Categoria</th><th>Fornecedor</th>
                <?php if ($podeEditar): ?><th>Ações</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <?php
            $status = $p->statusValidade($hoje);
            $semEstoque = ((int) $p->getQuantEstoque() <= 0);
            $classe = $semEstoque ? '' : ($status === Produto::VENCIDO ? 'linha-vencido' : ($status === Produto::PROXIMO ? 'linha-proximo' : ''));
            ?>
            <tr class="<?= e($classe) ?>">
                <td><?= (int) $p->getId() ?></td>
                <td><strong><?= e($p->getDescricao()) ?></strong></td>
                <td class="preco"><?= e(moeda($p->getPreco())) ?></td>
                <td><?= e(moeda($p->getPrecoCusto())) ?></td>
                <td><?= e(moeda($p->getLucro())) ?></td>
                <td class="estoque"><?= (int) $p->getQuantEstoque() ?> <?= e($p->getUnidadeMedida()) ?></td>
                <td>
                    <?= e(data_br($p->getValidade())) ?>
                    <?php if (!$semEstoque && $status === Produto::VENCIDO): ?>
                        <span class="selo selo-vencido">Vencido</span>
                    <?php elseif (!$semEstoque && $status === Produto::PROXIMO): ?>
                        <span class="selo selo-proximo">Vence em <?= (int) $p->diasParaVencer($hoje) ?> d</span>
                    <?php endif; ?>
                </td>
                <td><?= e($p->getCategoria()) ?></td>
                <td><?= e($p->getFornecedorNome()) ?></td>
                <?php if ($podeEditar): ?>
                <td>
                    <a href="<?= e(rota('produto/editar', array('id' => $p->getId()))) ?>" class="btn-success">Editar</a>
                    <form action="<?= e(rota('produto/excluir')) ?>" method="post" class="form-inline"
                          onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $p->getId() ?>">
                        <button type="submit" class="btn-danger">Excluir</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($produtos)): ?>
            <tr><td colspan="10" class="vazio">Nenhum produto encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <p class="legenda"><span class="selo selo-vencido">Vencido</span> <span class="selo selo-proximo">Vence em até <?= (int) Produto::DIAS_ALERTA ?> dias</span></p>
</section>
