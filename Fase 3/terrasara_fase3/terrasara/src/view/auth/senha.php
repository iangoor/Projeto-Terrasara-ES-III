<section class="form-container form-estreito">
    <h1>Alterar senha</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form action="<?= e(rota('auth/senha')) ?>" method="post">
        <?= Csrf::campo() ?>
        <div class="form-group">
            <label for="senha_atual">Senha atual:</label>
            <input type="password" id="senha_atual" name="senha_atual" required>
        </div>
        <div class="form-group">
            <label for="senha_nova">Nova senha (mínimo 8 caracteres):</label>
            <input type="password" id="senha_nova" name="senha_nova" minlength="8" required>
        </div>
        <div class="form-group">
            <label for="senha_confirmacao">Confirmar nova senha:</label>
            <input type="password" id="senha_confirmacao" name="senha_confirmacao" minlength="8" required>
        </div>
        <button class="btn-primary" type="submit">Salvar</button>
        <a href="<?= e(rota('produto/listar')) ?>" class="btn-primary btn-link">Voltar</a>
    </form>
</section>
