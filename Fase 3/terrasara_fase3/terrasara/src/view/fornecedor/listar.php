<?php $podeEditar = Router::permitido('fornecedor/criar'); ?>
<section class="table-container">
    <div class="titulo-area">
        <h1>Fornecedores</h1>
        <div class="acoes-topo">
            <?php if ($podeEditar): ?>
                <a href="<?= e(rota('fornecedor/criar')) ?>" class="btn-add">Novo fornecedor +</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>ID</th><th>Nome</th><th>Telefone</th><th>E-mail</th><th>Selo orgânico</th><?php if ($podeEditar): ?><th>Ações</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($fornecedores as $f): ?>
            <tr>
                <td><?= (int) $f->getId() ?></td>
                <td><strong><?= e($f->getNome()) ?></strong></td>
                <td><?= e(telefone_br($f->getTelefone())) ?></td>
                <td><?= e($f->getEmail()) ?></td>
                <td><?= $f->isCertificado() ? '<span class="selo selo-ok">Certificado</span>' : '<span class="selo">Não informado</span>' ?></td>
                <?php if ($podeEditar): ?>
                <td>
                    <a href="<?= e(rota('fornecedor/editar', array('id' => $f->getId()))) ?>" class="btn-success">Editar</a>
                    <form action="<?= e(rota('fornecedor/excluir')) ?>" method="post" class="form-inline"
                          onsubmit="return confirm('Excluir este fornecedor?');">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $f->getId() ?>">
                        <button type="submit" class="btn-danger">Excluir</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($fornecedores)): ?>
            <tr><td colspan="6" class="vazio">Nenhum fornecedor cadastrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
