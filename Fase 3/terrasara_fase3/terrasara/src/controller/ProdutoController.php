<?php
class ProdutoController extends Controller
{
    public function listar()
    {
        $busca = trim($this->get('q'));
        $produtos = (new ProdutoDAO())->listar($busca);
        $hoje = date('Y-m-d');

        $vencidos = 0;
        $proximos = 0;
        foreach ($produtos as $p) {
            if ((int) $p->getQuantEstoque() <= 0) {
                continue;
            }
            $status = $p->statusValidade($hoje);
            if ($status === Produto::VENCIDO) {
                $vencidos++;
            } elseif ($status === Produto::PROXIMO) {
                $proximos++;
            }
        }

        $this->render('produto/listar', array(
            'titulo' => 'Produtos', 'produtos' => $produtos, 'busca' => $busca,
            'hoje' => $hoje, 'vencidos' => $vencidos, 'proximos' => $proximos,
        ));
    }

    public function criar()
    {
        $erro = null;
        $dados = array();

        if ($this->ehPost()) {
            try {
                $produto = ProdutoFactory::criarDeArray($_POST);            // Factory + Strategy
                $this->exigirFornecedor($produto->getFornecedorId());
                (new ProdutoDAO())->salvarComEstoqueInicial($produto, Auth::id()); // DAO + Singleton
                $this->sucesso('Produto cadastrado com sucesso.');
                $this->redirecionar('produto/listar');
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
                $dados = $_POST;
            }
        }
        $this->formulario($dados, $erro, false);
    }

    public function editar()
    {
        $dao = new ProdutoDAO();
        $erro = null;

        if ($this->ehPost()) {
            $id = (int) $this->post('id');
            $atual = $dao->buscarPorId($id);
            if ($atual === null) {
                $this->naoEncontrado('Produto não encontrado.', 'produto/listar');
            }
            try {
                $dadosPost = $_POST;
                $dadosPost['quantEstoque'] = $atual->getQuantEstoque(); // estoque só muda pelo módulo de Estoque
                $produto = ProdutoFactory::criarDeArray($dadosPost);
                $produto->setId($id);
                $this->exigirFornecedor($produto->getFornecedorId());
                $dao->atualizar($produto);
                $this->sucesso('Produto atualizado com sucesso.');
                $this->redirecionar('produto/listar');
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
                $dadosPost['id'] = $id;
                $this->formulario($dadosPost, $erro, true);
                return;
            }
        }

        $produto = $dao->buscarPorId($this->idDaUrl());
        if ($produto === null) {
            $this->naoEncontrado('Produto não encontrado.', 'produto/listar');
        }
        $this->formulario(array(
            'id'            => $produto->getId(),
            'descricao'     => $produto->getDescricao(),
            'precoCusto'    => $produto->getPrecoCusto(),
            'preco'         => $produto->getPreco(),
            'quantEstoque'  => $produto->getQuantEstoque(),
            'unidadeMedida' => $produto->getUnidadeMedida(),
            'validade'      => $produto->getValidade(),
            'categoria'     => $produto->getCategoria(),
            'fornecedor_id' => $produto->getFornecedorId(),
        ), null, true);
    }

    public function excluir()
    {
        try {
            (new ProdutoDAO())->excluir((int) $this->post('id'));
            $this->sucesso('Produto excluído.');
        } catch (PDOException $e) {
            if (!$this->emUso($e)) {
                throw $e;
            }
            $this->erro('Este produto já foi vendido ou movimentado e não pode ser excluído.');
        }
        $this->redirecionar('produto/listar');
    }

    private function exigirFornecedor($id)
    {
        if ((new FornecedorDAO())->buscarPorId($id) === null) {
            throw new InvalidArgumentException('Fornecedor não encontrado.');
        }
    }

    private function formulario($dados, $erro, $editando)
    {
        $this->render('produto/form', array(
            'titulo' => $editando ? 'Editar produto' : 'Adicionar produto',
            'dados' => $dados, 'erro' => $erro, 'editando' => $editando,
            'fornecedores' => (new FornecedorDAO())->listar(),
            'unidades' => ProdutoFactory::unidades(), 'categorias' => ProdutoFactory::categorias(),
        ));
    }
}
