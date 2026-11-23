<?php
$linhas = array();
$idsPost = (isset($dados['produto_id']) && is_array($dados['produto_id'])) ? $dados['produto_id'] : array();
$qtdPost = (isset($dados['quantidade']) && is_array($dados['quantidade'])) ? $dados['quantidade'] : array();
foreach ($idsPost as $i => $id) {
    $linhas[] = array('produto' => is_scalar($id) ? $id : '', 'qtd' => (isset($qtdPost[$i]) && is_scalar($qtdPost[$i])) ? $qtdPost[$i] : '1');
}
if (empty($linhas)) {
    $linhas[] = array('produto' => '', 'qtd' => '1');
}
$clienteSel = dado($dados, 'cliente_id');
?>
<section class="form-container form-largo">
    <h1>Nova venda</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form action="<?= e(rota('venda/novo')) ?>" method="post" id="form-venda">
        <?= Csrf::campo() ?>

        <fieldset>
            <legend>Cliente</legend>
            <div class="form-group">
                <label for="cliente_id">Cliente já cadastrado:</label>
                <select id="cliente_id" name="cliente_id">
                    <option value="">— Novo cliente (preencha abaixo) —</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= (int) $c->getId() ?>"<?= selecionado($clienteSel, $c->getId()) ?>>
                            <?= e($c->getNome()) ?> · <?= e(telefone_br($c->getTelefone())) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="novo-cliente">
                <div class="form-group">
                    <label for="novo_nome">Nome:</label>
                    <input type="text" id="novo_nome" name="novo_nome" maxlength="120" value="<?= e(dado($dados, 'novo_nome')) ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="novo_telefone">Telefone (com DDD):</label>
                        <input type="text" id="novo_telefone" name="novo_telefone" maxlength="20" value="<?= e(dado($dados, 'novo_telefone')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="novo_email">E-mail:</label>
                        <input type="email" id="novo_email" name="novo_email" maxlength="150" value="<?= e(dado($dados, 'novo_email')) ?>">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Produtos</legend>
            <div id="itens">
                <?php foreach ($linhas as $l): ?>
                <div class="item-linha">
                    <select name="produto_id[]" class="sel-produto">
                        <option value="">Selecione o produto...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= (int) $p->getId() ?>" data-preco="<?= e($p->getPreco()) ?>"
                                    data-estoque="<?= (int) $p->getQuantEstoque() ?>"<?= selecionado($l['produto'], $p->getId()) ?>>
                                <?= e($p->getDescricao()) ?> — <?= e(moeda($p->getPreco())) ?> (estoque: <?= (int) $p->getQuantEstoque() ?> <?= e($p->getUnidadeMedida()) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantidade[]" class="inp-qtd" min="1" step="1" value="<?= e($l['qtd']) ?>" aria-label="Quantidade">
                    <span class="sub">R$ 0,00</span>
                    <button type="button" class="btn-danger btn-remover" title="Remover item">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" id="add-item">+ Adicionar item</button>
            <?php if (empty($produtos)): ?>
                <p class="dica">Não há produtos disponíveis para venda (sem estoque ou vencidos).</p>
            <?php endif; ?>
            <div class="total-box">Total: <output id="total">R$ 0,00</output></div>
        </fieldset>

        <div class="form-row">
            <div class="form-group">
                <label for="forma_pagamento">Forma de pagamento:</label>
                <select id="forma_pagamento" name="forma_pagamento" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($formas as $chave => $nome): ?>
                        <option value="<?= e($chave) ?>"<?= selecionado(dado($dados, 'forma_pagamento'), $chave) ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="observacao">Observações / personalização do pedido:</label>
            <textarea id="observacao" name="observacao" maxlength="500" rows="3"
                      placeholder="Ex.: embalagem para presente, sem sacola plástica..."><?= e(dado($dados, 'observacao')) ?></textarea>
        </div>

        <p class="dica">O pedido ficará como <strong>Pendente de Pagamento</strong> até a confirmação do pagamento.</p>
        <button class="btn-primary" type="submit">Registrar venda</button>
        <a href="<?= e(rota('venda/listar')) ?>" class="btn-primary btn-link">Voltar</a>
    </form>
</section>

<script>
    (function () {
        var container = document.getElementById('itens');
        var totalEl = document.getElementById('total');
        var clienteSel = document.getElementById('cliente_id');
        var novoCliente = document.getElementById('novo-cliente');

        function moeda(v) {
            return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function recalcular() {
            var total = 0;
            var linhas = container.getElementsByClassName('item-linha');
            for (var i = 0; i < linhas.length; i++) {
                var sel = linhas[i].getElementsByTagName('select')[0];
                var qtd = parseInt(linhas[i].getElementsByTagName('input')[0].value, 10);
                var opt = sel.options[sel.selectedIndex];
                var preco = parseFloat(opt.getAttribute('data-preco'));
                var sub = (isFinite(preco) && isFinite(qtd) && qtd > 0) ? Math.round(preco * qtd * 100) / 100 : 0;
                linhas[i].getElementsByClassName('sub')[0].textContent = moeda(sub);
                total += sub;
            }
            totalEl.textContent = moeda(Math.round(total * 100) / 100);
        }
        function alternarCliente() {
            novoCliente.style.display = clienteSel.value === '' ? '' : 'none';
        }

        document.getElementById('add-item').addEventListener('click', function () {
            var linhas = container.getElementsByClassName('item-linha');
            var nova = linhas[0].cloneNode(true);
            nova.getElementsByTagName('select')[0].selectedIndex = 0;
            nova.getElementsByTagName('input')[0].value = '1';
            container.appendChild(nova);
            recalcular();
        });
        container.addEventListener('click', function (ev) {
            if (ev.target.className.indexOf('btn-remover') !== -1) {
                var linhas = container.getElementsByClassName('item-linha');
                if (linhas.length > 1) {
                    container.removeChild(ev.target.parentNode);
                    recalcular();
                }
            }
        });
        container.addEventListener('change', recalcular);
        container.addEventListener('input', recalcular);
        clienteSel.addEventListener('change', alternarCliente);
        alternarCliente();
        recalcular();
    })();
</script>
