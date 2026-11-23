<section class="form-container form-estreito">
    <h1>Entrar</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form action="<?= e(rota('auth/login')) ?>" method="post" autocomplete="on">
        <?= Csrf::campo() ?>
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button class="btn-primary" type="submit">Entrar</button>
    </form>
    <p class="dica">Esqueceu a senha? Peça para a administradora redefinir em <strong>Usuários</strong>.</p>
</section>
