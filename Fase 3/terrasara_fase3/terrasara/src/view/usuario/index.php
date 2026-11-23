<section class="table-container">
    <h1>Usuários</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <div class="tabela-scroll">
    <table class="table">
        <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><strong><?= e($u->getNome()) ?></strong></td>
                <td><?= e($u->getEmail()) ?></td>
                <td><?= e(Auth::nomePerfil($u->getPerfil())) ?></td>
                <td>
                    <?php if (!$u->isAtivo()): ?><span class="selo">Inativo</span>
                    <?php elseif ($u->estaBloqueado()): ?><span class="selo selo-vencido">Bloqueado</span>
                    <?php else: ?><span class="selo selo-ok">Ativo</span><?php endif; ?>
                </td>
                <td>
                    <form action="<?= e(rota('usuario/redefinir')) ?>" method="post" class="form-inline form-linha">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                        <input type="password" name="senha" placeholder="Nova senha" minlength="8" required aria-label="Nova senha">
                        <input type="password" name="senha_confirmacao" placeholder="Confirmar" minlength="8" required aria-label="Confirmar senha">
                        <button type="submit" class="btn-success">Redefinir senha</button>
                    </form>
                    <?php if ((int) $u->getId() !== Auth::id()): ?>
                    <form action="<?= e(rota('usuario/alternar')) ?>" method="post" class="form-inline">
                        <?= Csrf::campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                        <button type="submit" class="btn-danger"><?= $u->isAtivo() ? 'Desativar' : 'Reativar' ?></button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <h2>Novo usuário</h2>
    <form action="<?= e(rota('usuario/criar')) ?>" method="post" class="form-container form-largo sem-padding">
        <?= Csrf::campo() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" maxlength="120" value="<?= e(dado($dados, 'nome')) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" maxlength="150" value="<?= e(dado($dados, 'email')) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="perfil">Perfil:</label>
                <select id="perfil" name="perfil" required>
                    <option value="">Selecione...</option>
                    <?php foreach (Auth::$PERFIS as $chave => $nome): ?>
                        <option value="<?= e($chave) ?>"<?= selecionado(dado($dados, 'perfil'), $chave) ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="senha">Senha (mín. 8):</label>
                <input type="password" id="senha" name="senha" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="senha_confirmacao">Confirmar senha:</label>
                <input type="password" id="senha_confirmacao" name="senha_confirmacao" minlength="8" required>
            </div>
        </div>
        <button class="btn-primary" type="submit">Cadastrar usuário</button>
    </form>
</section>
