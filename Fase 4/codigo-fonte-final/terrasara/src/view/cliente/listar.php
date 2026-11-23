<?php $podeExcluir = Router::permitido('cliente/excluir'); ?>
<section class="table-container">
    <div class="titulo-area">
        <h1>Clientes</h1>
        <div class="acoes-topo">
            <a href="<?= e(rota('cliente/criar')) ?>" class="btn-add">Novo cliente +</a>
            <form action="index.php" method="get" class="buscar-form">
                <input type="hidden" name="r" value="cliente/listar">
                <input type="text" name="q" value="<?= e($busca) ?>" placeholder="id, nome, e-mail ou telefone">
                <button type="submit" class="btn-add">Buscar</button>
            </form>
        </div>
    </div>

    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?= (int) $c->getId() ?></td>
                <td><strong><?= e($c->getNome()) ?></strong></td>
                <td><?= e(telefone_br($c->getTelefone())) ?></td>
                <td><?= e($c->getEmail()) ?></td>
                <td>
                    <a href="<?= e(rota('cliente/editar', array('id' => $c->getId()))) ?>" class="btn-success">Editar</a>
                    <?php if ($podeExcluir): ?>
                    <form action="<?= e(rota('cliente/excluir')) ?>" method="post" class="form-inline"
                          onsubmit="return confirm('Excluir este cliente?');">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $c->getId() ?>">
                        <button type="submit" class="btn-danger">Excluir</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($clientes)): ?>
            <tr><td colspan="5" class="vazio">Nenhum cliente encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
