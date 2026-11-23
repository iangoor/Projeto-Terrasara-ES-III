<?php
require_once __DIR__ . '/../util/Conexao.php';
require_once __DIR__ . '/../model/Produto.php';

class ProdutoDAO
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function salvar(Produto $p): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO produtos2
             (descricao, preco, validade, precoCusto, fornecedor, quantEstoque, categoria, unidadeMedida, lucro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $descricao = $p->getDescricao();
        $preco     = $p->getPreco();
        $validade  = $p->getValidade();
        $custo     = $p->getPrecoCusto();
        $fornec    = $p->getFornecedor();
        $qtd       = $p->getQuantEstoque();
        $categoria = $p->getCategoria();
        $unidade   = $p->getUnidadeMedida();
        $lucro     = $p->getLucro();

        $stmt->bind_param('sdsdsissd',
            $descricao, $preco, $validade, $custo, $fornec, $qtd, $categoria, $unidade, $lucro);
        $stmt->execute();
        $stmt->close();
    }

    public function atualizar(Produto $p): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE produtos2 SET
             descricao=?, preco=?, validade=?, precoCusto=?, fornecedor=?,
             quantEstoque=?, categoria=?, unidadeMedida=?, lucro=?
             WHERE id=?"
        );

        $descricao = $p->getDescricao();
        $preco     = $p->getPreco();
        $validade  = $p->getValidade();
        $custo     = $p->getPrecoCusto();
        $fornec    = $p->getFornecedor();
        $qtd       = $p->getQuantEstoque();
        $categoria = $p->getCategoria();
        $unidade   = $p->getUnidadeMedida();
        $lucro     = $p->getLucro();
        $id        = (int) $p->getId();

        $stmt->bind_param('sdsdsissdi',
            $descricao, $preco, $validade, $custo, $fornec, $qtd, $categoria, $unidade, $lucro, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function excluir(int $id): void
    {
        $stmt = $this->conn->prepare("DELETE FROM produtos2 WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function listar(): array
    {
        $result   = $this->conn->query("SELECT * FROM produtos2 ORDER BY id");
        $produtos = [];

        while ($row = $result->fetch_assoc()) {
            $produtos[] = $this->montarProduto($row);
        }
        return $produtos;
    }

    public function buscarPorId(int $id): ?Produto
    {
        $stmt = $this->conn->prepare("SELECT * FROM produtos2 WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? $this->montarProduto($row) : null;
    }

    private function montarProduto(array $row): Produto
    {
        $p = new Produto();
        $p->setId($row['id']);
        $p->setDescricao($row['descricao']);
        $p->setPreco($row['preco']);
        $p->setValidade($row['validade']);
        $p->setPrecoCusto($row['precoCusto']);
        $p->setFornecedor($row['fornecedor']);
        $p->setQuantEstoque($row['quantEstoque']);
        $p->setCategoria($row['categoria']);
        $p->setUnidadeMedida($row['unidadeMedida']);
        $p->setLucro($row['lucro']);
        return $p;
    }
}
