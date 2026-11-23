<?php $produtos = $_REQUEST['produtos'] ?? []; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos Cadastrados - TerraSara</title>
    <link rel="stylesheet" href="view/style.css">
</head>
<body>

<header class="top-header">
    <div class="logo">
        <img src="view/logo.png" alt="TerraSara Orgânicos & Naturais">
        <span>TerraSara Orgânicos &amp; Naturais</span>
    </div>
    <div class="contato">Contato: (11) 99999-9999</div>
</header>

<section class="table-container">
    <div class="titulo-area">
        <h1>Produtos Cadastrados</h1>
        <div class="acoes-topo">
            <a href="index.php?action=criar" class="btn-add">Adicionar Produto +</a>
            <form action="index.php" method="GET" class="buscar-form">
                <input type="hidden" name="action" value="buscarId">
                <input type="number" name="id" placeholder="ID" min="1">
                <button type="submit" class="btn-add">Buscar</button>
            </form>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Preço Venda</th>
                <th>Preço Custo</th>
                <th>Lucro</th>
                <th>Estoque</th>
                <th>Validade</th>
                <th>Categoria</th>
                <th>Fornecedor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= (int) $p->getId() ?></td>
                <td><strong><?= htmlspecialchars($p->getDescricao()) ?></strong></td>
                <td class="preco">R$ <?= number_format($p->getPreco(), 2, ',', '.') ?></td>
                <td>R$ <?= number_format($p->getPrecoCusto(), 2, ',', '.') ?></td>
                <td>R$ <?= number_format($p->getLucro(), 2, ',', '.') ?></td>
                <td class="estoque">
                    <?= (int) $p->getQuantEstoque() ?> <?= htmlspecialchars($p->getUnidadeMedida()) ?>
                </td>
                <td><?= date('d/m/Y', strtotime($p->getValidade())) ?></td>
                <td><?= htmlspecialchars($p->getCategoria()) ?></td>
                <td><?= htmlspecialchars($p->getFornecedor()) ?></td>
                <td>
                    <a href="index.php?action=editar&id=<?= (int) $p->getId() ?>" class="btn-success">Editar</a>
                    <a href="index.php?action=excluir&id=<?= (int) $p->getId() ?>" class="btn-danger"
                       onclick="return confirm('Tem certeza que deseja excluir este produto?');">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($produtos)): ?>
            <tr><td colspan="10" class="vazio">Nenhum produto cadastrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<footer class="rodape">
    Rua Batista Renzi, 95 - Centro, Suzano - SP
</footer>

</body>
</html>