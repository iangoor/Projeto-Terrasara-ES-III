<section class="form-container">
    <h1><?= e($titulo) ?></h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form action="<?= e(rota($editando ? 'produto/editar' : 'produto/criar')) ?>" method="post">
        <?= Csrf::campo() ?>
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) dado($dados, 'id', 0) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" maxlength="255" value="<?= e(dado($dados, 'descricao')) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="precoCusto">Preço de custo:</label>
                <input type="number" id="precoCusto" step="0.01" min="0.01" name="precoCusto" value="<?= e(dado($dados, 'precoCusto')) ?>" required>
            </div>
            <div class="form-group">
                <label for="preco">Preço de venda:</label>
                <input type="number" id="preco" step="0.01" min="0.01" name="preco" value="<?= e(dado($dados, 'preco')) ?>" required>
                <small>Mínimo: custo + <?= (int) ProdutoFactory::MARGEM_MINIMA ?>% (<output id="preco-minimo">R$ 0,00</output>)</small>
            </div>
        </div>

        <div class="lucro-preview" aria-live="polite">
            <strong>Lucro por produto:</strong>
            <output id="lucro-previa">R$ 0,00</output>
            <small>Calculado automaticamente: preço de venda menos preço de custo.</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantEstoque">Quantidade em estoque:</label>
                <?php if ($editando): ?>
                    <input type="number" id="quantEstoque" value="<?= (int) dado($dados, 'quantEstoque', 0) ?>" disabled>
                    <small>Para alterar o estoque, use o módulo Estoque (fica registrado na auditoria).</small>
                <?php else: ?>
                    <input type="number" id="quantEstoque" min="0" step="1" name="quantEstoque" value="<?= e(dado($dados, 'quantEstoque', '0')) ?>" required>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="unidadeMedida">Unidade de medida:</label>
                <select id="unidadeMedida" name="unidadeMedida" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($unidades as $u): ?>
                        <option value="<?= e($u) ?>"<?= selecionado(dado($dados, 'unidadeMedida'), $u) ?>><?= e($u) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="validade">Validade:</label>
            <input type="date" id="validade" name="validade" value="<?= e(dado($dados, 'validade')) ?>" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecione...</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= e($cat) ?>"<?= selecionado(dado($dados, 'categoria'), $cat) ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="fornecedor_id">Fornecedor:</label>
            <select id="fornecedor_id" name="fornecedor_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= (int) $f->getId() ?>"<?= selecionado(dado($dados, 'fornecedor_id'), $f->getId()) ?>>
                        <?= e($f->getNome()) ?><?= $f->isCertificado() ? ' (certificado)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($fornecedores)): ?>
                <small>Cadastre um fornecedor antes de cadastrar produtos.</small>
            <?php endif; ?>
        </div>

        <button class="btn-primary" type="submit"><?= $editando ? 'Salvar' : 'Adicionar produto' ?></button>
        <a href="<?= e(rota('produto/listar')) ?>" class="btn-primary btn-link">Voltar</a>
    </form>
</section>

<script>
    (function () {
        var campoCusto = document.getElementById('precoCusto');
        var campoVenda = document.getElementById('preco');
        var lucro = document.getElementById('lucro-previa');
        var minimo = document.getElementById('preco-minimo');
        var margem = <?= (int) ProdutoFactory::MARGEM_MINIMA ?>;

        function moeda(v) {
            return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function atualizar() {
            var custo = parseFloat(campoCusto.value);
            var venda = parseFloat(campoVenda.value);
            minimo.textContent = isFinite(custo) ? moeda(Math.round(custo * (1 + margem / 100) * 100) / 100) : 'R$ 0,00';
            if (!isFinite(custo) || !isFinite(venda)) {
                lucro.textContent = 'R$ 0,00';
                lucro.className = '';
                return;
            }
            var l = Math.round((venda - custo) * 100) / 100;
            lucro.textContent = moeda(l);
            lucro.className = l < 0 ? 'lucro-negativo' : '';
        }
        campoCusto.addEventListener('input', atualizar);
        campoVenda.addEventListener('input', atualizar);
        atualizar();
    })();
</script>
