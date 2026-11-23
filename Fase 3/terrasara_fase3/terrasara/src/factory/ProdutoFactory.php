<?php
require_once dirname(dirname(__FILE__)) . '/model/Produto.php';
require_once dirname(dirname(__FILE__)) . '/strategy/CalculoPrecoContext.php';
require_once dirname(dirname(__FILE__)) . '/strategy/LucroProdutoStrategy.php';

/**
 * PADRÃO GoF: FACTORY — valida os dados do formulário e cria o Produto já com o lucro calculado
 * (Strategy). Lança InvalidArgumentException com mensagem amigável quando algo está errado.
 */
class ProdutoFactory
{
    const UNIDADES = 'Quilo,Grama,Unidade';
    const CATEGORIAS = 'Perecíveis,Congelados,Secos';
    const MARGEM_MINIMA = 35; // RN02: 35% sobre o custo

    public static function unidades()
    {
        return explode(',', self::UNIDADES);
    }

    public static function categorias()
    {
        return explode(',', self::CATEGORIAS);
    }

    public static function criarContexto()
    {
        return new CalculoPrecoContext(new LucroProdutoStrategy());
    }

    /** Converte "12,50" ou "12.50" em float; retorna null se não for número. */
    public static function numero($valor)
    {
        $v = str_replace(',', '.', trim((string) $valor));
        return is_numeric($v) ? (float) $v : null;
    }

    public static function criarDeArray(array $dados, CalculoPrecoContext $contexto = null)
    {
        $contexto = $contexto ? $contexto : self::criarContexto();

        $rotulos = array(
            'descricao' => 'Descrição', 'precoCusto' => 'Preço de custo', 'preco' => 'Preço de venda',
            'validade' => 'Validade', 'fornecedor_id' => 'Fornecedor', 'quantEstoque' => 'Quantidade em estoque',
            'categoria' => 'Categoria', 'unidadeMedida' => 'Unidade de medida',
        );
        foreach ($rotulos as $campo => $rotulo) {
            if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                throw new InvalidArgumentException('Preencha o campo: ' . $rotulo . '.');
            }
        }

        $descricao = trim($dados['descricao']);
        if (tamanho_texto($descricao) > 255) {
            throw new InvalidArgumentException('A descrição deve ter no máximo 255 caracteres.');
        }
        if (!in_array($dados['unidadeMedida'], self::unidades(), true)) {
            throw new InvalidArgumentException('Unidade de medida inválida.');
        }
        if (!in_array($dados['categoria'], self::categorias(), true)) {
            throw new InvalidArgumentException('Categoria inválida.');
        }

        $custo = self::numero($dados['precoCusto']);
        $preco = self::numero($dados['preco']);
        if ($custo === null || $preco === null || $custo <= 0 || $preco <= 0) {
            throw new InvalidArgumentException('Preços devem ser números maiores que zero.');
        }
        if ($custo > 99999999.99 || $preco > 99999999.99) {
            throw new InvalidArgumentException('Preço acima do limite permitido.');
        }
        $custo = round($custo, 2);
        $preco = round($preco, 2);

        // RN02: margem mínima de lucro sobre o custo.
        $minimo = $contexto->precoSugerido($custo, self::MARGEM_MINIMA);
        if ($preco < $minimo) {
            throw new InvalidArgumentException(
                'O preço de venda deve ser de pelo menos ' . moeda($minimo)
                . ' (margem mínima de ' . self::MARGEM_MINIMA . '% sobre o custo).'
            );
        }

        $qtd = filter_var($dados['quantEstoque'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
        if ($qtd === false) {
            throw new InvalidArgumentException('Quantidade em estoque deve ser um número inteiro (0 ou mais).');
        }

        $validade = (string) $dados['validade'];
        $data = DateTime::createFromFormat('Y-m-d', $validade);
        if (!$data || $data->format('Y-m-d') !== $validade) {
            throw new InvalidArgumentException('Data de validade inválida.');
        }

        $fornecedorId = filter_var($dados['fornecedor_id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($fornecedorId === false) {
            throw new InvalidArgumentException('Selecione um fornecedor válido.');
        }

        $p = new Produto();
        $p->setDescricao($descricao);
        $p->setPrecoCusto($custo);
        $p->setPreco($preco);
        $p->setValidade($validade);
        $p->setFornecedorId($fornecedorId);
        $p->setQuantEstoque($qtd);
        $p->setCategoria($dados['categoria']);
        $p->setUnidadeMedida($dados['unidadeMedida']);
        $p->atualizarLucro($contexto); // Strategy

        return $p;
    }
}
