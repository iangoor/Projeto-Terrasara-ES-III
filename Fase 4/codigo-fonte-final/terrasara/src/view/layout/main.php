<?php
$usuario = Auth::usuario();
$menu = array(
    'produto/listar'       => 'Produtos',
    'fornecedor/listar'    => 'Fornecedores',
    'cliente/listar'       => 'Clientes',
    'venda/listar'         => 'Vendas',
    'estoque/index'        => 'Estoque',
    'relatorio/financeiro' => 'Relatórios',
    'usuario/index'        => 'Usuários',
);
$rotaAtual = isset($_GET['r']) ? (string) $_GET['r'] : '';
$moduloAtual = substr($rotaAtual, 0, strpos($rotaAtual . '/', '/'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($titulo) ? $titulo : 'Sistema') ?> - TerraSara</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="top-header">
    <a class="logo" href="index.php">
        <img src="assets/logo.png" alt="TerraSara Orgânicos &amp; Naturais">
        <span>TerraSara Orgânicos &amp; Naturais</span>
    </a>
    <?php if ($usuario): ?>
        <div class="usuario-box">
            <span><?= e($usuario['nome']) ?> <small>(<?= e(Auth::nomePerfil($usuario['perfil'])) ?>)</small></span>
            <a href="<?= e(rota('auth/senha')) ?>" class="link-claro">Alterar senha</a>
            <form action="<?= e(rota('auth/logout')) ?>" method="post" class="form-inline"
                  onsubmit="return confirm('Deseja realmente sair do sistema?');">
                <?= Csrf::campo() ?>
                <button type="submit" class="btn-sair">Sair</button>
            </form>
        </div>
    <?php endif; ?>
</header>

<?php if ($usuario): ?>
<nav class="menu">
    <?php foreach ($menu as $destino => $rotulo): ?>
        <?php if (Router::permitido($destino)): ?>
            <a href="<?= e(rota($destino)) ?>"
               class="<?= (substr($destino, 0, strpos($destino, '/')) === $moduloAtual) ? 'ativo' : '' ?>"><?= e($rotulo) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<main class="conteudo">
    <?php foreach (Flash::consumir() as $m): ?>
        <div class="alerta alerta-<?= e($m['tipo']) ?>"><?= e($m['msg']) ?></div>
    <?php endforeach; ?>
    <?= $conteudo ?>
</main>

<footer class="rodape">
    TerraSara Orgânicos &amp; Naturais &middot; Rua Batista Renzi, 95 - Centro, Suzano - SP &middot; Contato: (11) 99999-9999
</footer>

</body>
</html>
