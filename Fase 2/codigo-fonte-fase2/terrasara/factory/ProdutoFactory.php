<?php
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../strategy/CalculoPrecoContext.php';
require_once __DIR__ . '/../strategy/LucroProdutoStrategy.php';

class ProdutoFactory
{
    const UNIDADES   = ['Quilo', 'Grama', 'Unidade'];
    const CATEGORIAS = ['Perecíveis', 'Congelados', 'Secos'];

    public static function criarContexto(): CalculoPrecoContext
    {
        return new CalculoPrecoContext(new LucroProdutoStrategy());
    }

    public static function criarDeArray(array $dados): Produto
    {
        $obrigatorios = ['descricao', 'precoCusto', 'preco', 'validade',
                         'fornecedor', 'quantEstoque', 'categoria', 'unidadeMedida'];

        foreach ($obrigatorios as $campo) {
            if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                throw new InvalidArgumentException("Preencha o campo: $campo.");
            }
        }

        if (!in_array($dados['unidadeMedida'], self::UNIDADES, true)) {
            throw new InvalidArgumentException('Unidade de medida inválida.');
        }
        if (!in_array($dados['categoria'], self::CATEGORIAS, true)) {
            throw new InvalidArgumentException('Categoria inválida.');
        }
        if (!is_numeric($dados['preco']) || !is_numeric($dados['precoCusto'])
            || (float) $dados['preco'] <= 0 || (float) $dados['precoCusto'] <= 0) {
            throw new InvalidArgumentException('Preços devem ser números maiores que zero.');
        }
        if (filter_var($dados['quantEstoque'], FILTER_VALIDATE_INT) === false || (int) $dados['quantEstoque'] < 0) {
            throw new InvalidArgumentException('Quantidade em estoque deve ser um número inteiro (0 ou mais).');
        }
        $data = DateTime::createFromFormat('Y-m-d', $dados['validade']);
        if (!$data || $data->format('Y-m-d') !== $dados['validade']) {
            throw new InvalidArgumentException('Data de validade inválida.');
        }

        $custo = (float) $dados['precoCusto'];
        $preco = (float) $dados['preco'];

        $p = new Produto();
        $p->setDescricao(trim($dados['descricao']));
        $p->setPrecoCusto($custo);
        $p->setPreco($preco);
        $p->setValidade($dados['validade']);
        $p->setFornecedor(trim($dados['fornecedor']));
        $p->setQuantEstoque((int) $dados['quantEstoque']);
        $p->setCategoria($dados['categoria']);
        $p->setUnidadeMedida($dados['unidadeMedida']);
        $p->atualizarLucro(self::criarContexto());

        return $p;
    }
}
