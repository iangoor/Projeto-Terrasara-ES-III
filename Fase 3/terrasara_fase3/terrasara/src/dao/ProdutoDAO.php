<?php
class ProdutoDAO extends BaseDAO
{
    const SELECT = 'SELECT p.*, f.nome AS fornecedor_nome
                    FROM produtos p LEFT JOIN fornecedores f ON f.id = p.fornecedor_id';

    public function salvar(Produto $p)
    {
        $this->consultar(
            'INSERT INTO produtos (descricao, preco, validade, precoCusto, fornecedor_id, quantEstoque, categoria, unidadeMedida, lucro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array($p->getDescricao(), $p->getPreco(), $p->getValidade(), $p->getPrecoCusto(), $p->getFornecedorId(),
                $p->getQuantEstoque(), $p->getCategoria(), $p->getUnidadeMedida(), $p->getLucro())
        );
        return $this->ultimoId();
    }

    /** Cadastra o produto e registra a entrada do estoque inicial na auditoria (RN11), em uma transação. */
    public function salvarComEstoqueInicial(Produto $p, $usuarioId)
    {
        $self = $this;
        return $this->transacao(function () use ($self, $p, $usuarioId) {
            $quantidade = (int) $p->getQuantEstoque();
            $p->setQuantEstoque(0);
            $id = $self->salvar($p);
            if ($quantidade > 0) {
                $mov = new MovimentacaoDAO($self->getPdo());
                $mov->registrar($id, $usuarioId, 'entrada', $quantidade, 'Estoque inicial do cadastro');
            }
            $p->setQuantEstoque($quantidade);
            return $id;
        });
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    /** Atualiza os dados do produto. O estoque NÃO muda aqui: use o módulo de Estoque (auditoria). */
    public function atualizar(Produto $p)
    {
        $this->consultar(
            'UPDATE produtos SET descricao=?, preco=?, validade=?, precoCusto=?, fornecedor_id=?,
             categoria=?, unidadeMedida=?, lucro=? WHERE id=?',
            array($p->getDescricao(), $p->getPreco(), $p->getValidade(), $p->getPrecoCusto(), $p->getFornecedorId(),
                $p->getCategoria(), $p->getUnidadeMedida(), $p->getLucro(), (int) $p->getId())
        );
    }


    /** Apaga o produto. Se nunca foi vendido, apaga junto o histórico de estoque dele.
     *  Se já foi vendido, a chave estrangeira bloqueia (PDOException 23000) e nada é apagado. */
    public function excluir($id)
    {
        $id = (int) $id;
        $this->transacao(function () use ($id) {
            $vendido = $this->consultar(
                'SELECT 1 FROM itens_pedido WHERE produto_id = ? LIMIT 1', array($id)
            )->fetch();
            if (!$vendido) {
                $this->consultar('DELETE FROM movimentacoes_estoque WHERE produto_id = ?', array($id));
            }
            $this->consultar('DELETE FROM produtos WHERE id = ?', array($id));
        });
    }

    public function listar($busca = '')
    {
        $busca = trim($busca);
        if ($busca === '') {
            $stmt = $this->consultar(self::SELECT . ' ORDER BY p.id');
        } else {
            $stmt = $this->consultar(
                self::SELECT . ' WHERE p.descricao LIKE ? OR p.id = ? ORDER BY p.id',
                array('%' . $busca . '%', (int) $busca)
            );
        }
        return $this->montarLista($stmt);
    }
    
    public function buscarPorId($id)
    {
        $stmt = $this->consultar(self::SELECT . ' WHERE p.id = ?', array((int) $id));
        $row = $stmt->fetch();
        return $row ? $this->montarProduto($row) : null;
    }

    /** Retorna array(id => Produto). */
    public function buscarPorIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return array();
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->consultar(self::SELECT . ' WHERE p.id IN (' . $marcas . ')', $ids);
        $resultado = array();
        foreach ($this->montarLista($stmt) as $p) {
            $resultado[(int) $p->getId()] = $p;
        }
        return $resultado;
    }

    /** Produtos que podem ser vendidos: com estoque e dentro da validade. */
    public function listarDisponiveisParaVenda($hoje)
    {
        $stmt = $this->consultar(
            self::SELECT . ' WHERE p.quantEstoque > 0 AND p.validade >= ? ORDER BY p.descricao',
            array($hoje)
        );
        return $this->montarLista($stmt);
    }

    /** Produtos com estoque que já venceram ou vencem em até $dias dias (RN09). */
    public function listarAlertas($hoje, $dias)
    {
        $limite = new DateTime($hoje);
        $limite->modify('+' . (int) $dias . ' days');
        $stmt = $this->consultar(
            self::SELECT . ' WHERE p.quantEstoque > 0 AND p.validade <= ? ORDER BY p.validade, p.descricao',
            array($limite->format('Y-m-d'))
        );
        return $this->montarLista($stmt);
    }

    private function montarLista(PDOStatement $stmt)
    {
        $produtos = array();
        while ($row = $stmt->fetch()) {
            $produtos[] = $this->montarProduto($row);
        }
        return $produtos;
    }

    private function montarProduto(array $row)
    {
        $p = new Produto();
        $p->setId((int) $row['id']);
        $p->setDescricao($row['descricao']);
        $p->setPreco((float) $row['preco']);
        $p->setValidade($row['validade']);
        $p->setPrecoCusto((float) $row['precoCusto']);
        $p->setFornecedorId($row['fornecedor_id'] === null ? null : (int) $row['fornecedor_id']);
        $p->setFornecedorNome(isset($row['fornecedor_nome']) ? $row['fornecedor_nome'] : null);
        $p->setQuantEstoque((int) $row['quantEstoque']);
        $p->setCategoria($row['categoria']);
        $p->setUnidadeMedida($row['unidadeMedida']);
        $p->setLucro((float) $row['lucro']);
        return $p;
    }
}
