<?php
$dados    = $_REQUEST['dados'] ?? [];
$erro     = $_REQUEST['erro'] ?? null;
$editando = $_REQUEST['editando'] ?? false;

function v(array $dados, string $campo): string {
    return htmlspecialchars((string) ($dados[$campo] ?? ''));
}
function sel(array $dados, string $campo, string $valor): string {
    return (($dados[$campo] ?? '') === $valor) ? 'selected' : '';
}

$titulo = $editando ? 'Editar Produto' : 'Adicionar Produto';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - TerraSara</title>
    <link rel="stylesheet" href="view/style.css">
</head>
<body>

<header class="top-header">
    <div class="logo">
        <img src="view/logo.png" alt="TerraSara Orgânicos & Naturais">
        <span>TerraSara Orgânicos &amp; Naturais</span>
    </div>
</header>

<section class="form-container">
    <h1><?= $titulo ?></h1>

    <?php if ($erro): ?>
        <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form action="index.php?action=<?= $editando ? 'editar' : 'criar' ?>" method="post">

        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= v($dados, 'id') ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Descrição:</label>
            <input type="text" name="descricao" value="<?= v($dados, 'descricao') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Preço de Custo:</label>
                <input type="number" id="precoCusto" step="0.01" min="0.01" name="precoCusto" value="<?= v($dados, 'precoCusto') ?>" required>
            </div>
            <div class="form-group">
                <label>Preço de Venda:</label>
                <input type="number" id="preco" step="0.01" min="0.01" name="preco" value="<?= v($dados, 'preco') ?>" required>
            </div>
        </div>

        <div class="lucro-preview" aria-live="polite">
            <strong>Lucro por produto:</strong>
            <output id="lucro-previa">R$ 0,00</output>
            <small>Calculado automaticamente: preço de venda menos preço de custo.</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantidade em Estoque:</label>
                <input type="number" min="0" step="1" name="quantEstoque" value="<?= v($dados, 'quantEstoque') ?>" required>
            </div>
            <div class="form-group">
                <label>Unidade de Medida:</label>
                <select name="unidadeMedida" required>
                    <option value="">Selecione...</option>
                    <option value="Quilo"   <?= sel($dados, 'unidadeMedida', 'Quilo') ?>>Quilo (kg)</option>
                    <option value="Grama"   <?= sel($dados, 'unidadeMedida', 'Grama') ?>>Grama (g)</option>
                    <option value="Unidade" <?= sel($dados, 'unidadeMedida', 'Unidade') ?>>Unidade</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Validade:</label>
            <input type="date" name="validade" value="<?= v($dados, 'validade') ?>" required>
        </div>

        <div class="form-group">
            <label>Categoria:</label>
            <select name="categoria" required>
                <option value="">Selecione...</option>
                <?php foreach (ProdutoFactory::CATEGORIAS as $cat): ?>
                    <option value="<?= $cat ?>" <?= sel($dados, 'categoria', $cat) ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Fornecedor:</label>
            <input type="text" name="fornecedor" value="<?= v($dados, 'fornecedor') ?>" required>
        </div>

        <button class="btn-primary" type="submit"><?= $editando ? 'Salvar' : 'Adicionar Produto' ?></button>
        <a href="index.php?action=listar" class="btn-primary" style="margin-left: 10px;">Voltar</a>
    </form>
</section>

<script>
    const campoCusto = document.getElementById('precoCusto');
    const campoVenda = document.getElementById('preco');
    const exibicaoLucro = document.getElementById('lucro-previa');
    const formatoMoeda = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    function atualizarLucro() {
        const custo = Number.parseFloat(campoCusto.value);
        const venda = Number.parseFloat(campoVenda.value);

        if (!Number.isFinite(custo) || !Number.isFinite(venda)) {
            exibicaoLucro.textContent = 'R$ 0,00';
            exibicaoLucro.classList.remove('lucro-negativo');
            return;
        }

        const lucro = Math.round((venda - custo) * 100) / 100;
        exibicaoLucro.textContent = formatoMoeda.format(lucro);
        exibicaoLucro.classList.toggle('lucro-negativo', lucro < 0);
    }

    campoCusto.addEventListener('input', atualizarLucro);
    campoVenda.addEventListener('input', atualizarLucro);
    atualizarLucro();
</script>
</body>
</html>