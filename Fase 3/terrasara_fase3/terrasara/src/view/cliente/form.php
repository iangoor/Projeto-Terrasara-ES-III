<section class="form-container">
    <h1><?= e($titulo) ?></h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form action="<?= e(rota($editando ? 'cliente/editar' : 'cliente/criar')) ?>" method="post">
        <?= Csrf::campo() ?>
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) dado($dados, 'id', 0) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" maxlength="120" value="<?= e(dado($dados, 'nome')) ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telefone">Telefone (com DDD):</label>
                <input type="text" id="telefone" name="telefone" maxlength="20" value="<?= e(dado($dados, 'telefone')) ?>" placeholder="(11) 99999-9999" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" maxlength="150" value="<?= e(dado($dados, 'email')) ?>" required>
            </div>
        </div>

        <button class="btn-primary" type="submit">Salvar</button>
        <a href="<?= e(rota('cliente/listar')) ?>" class="btn-primary btn-link">Voltar</a>
    </form>
</section>
