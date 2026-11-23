<?php
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../dao/ProdutoDAO.php';
require_once __DIR__ . '/../factory/ProdutoFactory.php';

class ProdutoController
{
    public function listar(): void
    {
        $_REQUEST['produtos'] = (new ProdutoDAO())->listar();
        require __DIR__ . '/../view/produto_view.php';
    }

    public function criar(): void
    {
        $_REQUEST['dados']    = [];
        $_REQUEST['editando'] = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $produto = ProdutoFactory::criarDeArray($_POST);   // Factory + Strategy
                (new ProdutoDAO())->salvar($produto);              // DAO + Singleton
                header('Location: index.php?action=listar');
                exit;
            } catch (InvalidArgumentException $e) {
                $_REQUEST['erro']  = $e->getMessage();
                $_REQUEST['dados'] = $_POST;
            }
        }

        require __DIR__ . '/../view/produto_form.php';
    }

    public function editar(): void
    {
        $dao = new ProdutoDAO();
        $_REQUEST['editando'] = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            try {
                if ($dao->buscarPorId($id) === null) {
                    throw new InvalidArgumentException('Produto não encontrado.');
                }
                $produto = ProdutoFactory::criarDeArray($_POST);
                $produto->setId($id);
                $dao->atualizar($produto);
                header('Location: index.php?action=listar');
                exit;
            } catch (InvalidArgumentException $e) {
                $_REQUEST['erro']  = $e->getMessage();
                $_REQUEST['dados'] = $_POST;
                require __DIR__ . '/../view/produto_form.php';
                return;
            }
        }

        $produto = $dao->buscarPorId((int) ($_GET['id'] ?? 0));
        if ($produto === null) {
            $this->mostrarErro('Produto não encontrado!');
            return;
        }

        $_REQUEST['dados'] = [
            'id'            => $produto->getId(),
            'descricao'     => $produto->getDescricao(),
            'precoCusto'    => $produto->getPrecoCusto(),
            'preco'         => $produto->getPreco(),
            'quantEstoque'  => $produto->getQuantEstoque(),
            'unidadeMedida' => $produto->getUnidadeMedida(),
            'validade'      => $produto->getValidade(),
            'categoria'     => $produto->getCategoria(),
            'fornecedor'    => $produto->getFornecedor(),
        ];
        require __DIR__ . '/../view/produto_form.php';
    }

    public function buscarId(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0 && (new ProdutoDAO())->buscarPorId($id) !== null) {
            header('Location: index.php?action=editar&id=' . $id);
            exit;
        }
        $this->mostrarErro('Produto não encontrado!');
    }

    public function excluir(): void
    {
        (new ProdutoDAO())->excluir((int) ($_GET['id'] ?? 0));
        header('Location: index.php?action=listar');
        exit;
    }

    public function mostrarErro(string $mensagem): void
    {
        require __DIR__ . '/../view/erro_produto.php';
    }
}