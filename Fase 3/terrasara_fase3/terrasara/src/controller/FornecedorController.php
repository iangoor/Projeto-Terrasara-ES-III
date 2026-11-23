<?php
class FornecedorController extends Controller
{
    public function listar()
    {
        $this->render('fornecedor/listar', array('titulo' => 'Fornecedores', 'fornecedores' => (new FornecedorDAO())->listar()));
    }

    public function criar()
    {
        $erro = null;
        $dados = array();
        if ($this->ehPost()) {
            try {
                (new FornecedorDAO())->salvar(FornecedorFactory::criarDeArray($_POST));
                $this->sucesso('Fornecedor cadastrado com sucesso.');
                $this->redirecionar('fornecedor/listar');
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
                $dados = $_POST;
            }
        }
        $this->render('fornecedor/form', array('titulo' => 'Novo fornecedor', 'dados' => $dados, 'erro' => $erro, 'editando' => false));
    }

    public function editar()
    {
        $dao = new FornecedorDAO();
        if ($this->ehPost()) {
            $id = (int) $this->post('id');
            try {
                if ($dao->buscarPorId($id) === null) {
                    $this->naoEncontrado('Fornecedor não encontrado.', 'fornecedor/listar');
                }
                $f = FornecedorFactory::criarDeArray($_POST);
                $f->setId($id);
                $dao->atualizar($f);
                $this->sucesso('Fornecedor atualizado com sucesso.');
                $this->redirecionar('fornecedor/listar');
            } catch (InvalidArgumentException $e) {
                $dados = $_POST;
                $dados['id'] = $id;
                $this->render('fornecedor/form', array('titulo' => 'Editar fornecedor', 'dados' => $dados, 'erro' => $e->getMessage(), 'editando' => true));
                return;
            }
        }

        $f = $dao->buscarPorId($this->idDaUrl());
        if ($f === null) {
            $this->naoEncontrado('Fornecedor não encontrado.', 'fornecedor/listar');
        }
        $this->render('fornecedor/form', array('titulo' => 'Editar fornecedor', 'erro' => null, 'editando' => true, 'dados' => array(
            'id' => $f->getId(), 'nome' => $f->getNome(), 'telefone' => $f->getTelefone(),
            'email' => $f->getEmail(), 'certificado' => $f->isCertificado() ? '1' : '',
        )));
    }

    public function excluir()
    {
        try {
            (new FornecedorDAO())->excluir((int) $this->post('id'));
            $this->sucesso('Fornecedor excluído.');
        } catch (PDOException $e) {
            if (!$this->emUso($e)) {
                throw $e;
            }
            $this->erro('Este fornecedor possui produtos cadastrados e não pode ser excluído.');
        }
        $this->redirecionar('fornecedor/listar');
    }
}
