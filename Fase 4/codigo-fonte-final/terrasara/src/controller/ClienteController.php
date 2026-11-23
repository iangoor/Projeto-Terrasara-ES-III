<?php
class ClienteController extends Controller
{
    public function listar()
    {
        $busca = trim($this->get('q'));
        $this->render('cliente/listar', array(
            'titulo' => 'Clientes', 'busca' => $busca, 'clientes' => (new ClienteDAO())->listar($busca),
        ));
    }

    public function criar()
    {
        $erro = null;
        $dados = array();
        if ($this->ehPost()) {
            try {
                (new ClienteDAO())->salvar(ClienteFactory::criarDeArray($_POST));
                $this->sucesso('Cliente cadastrado com sucesso.');
                $this->redirecionar('cliente/listar');
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
                $dados = $_POST;
            }
        }
        $this->render('cliente/form', array('titulo' => 'Novo cliente', 'dados' => $dados, 'erro' => $erro, 'editando' => false));
    }

    public function editar()
    {
        $dao = new ClienteDAO();
        if ($this->ehPost()) {
            $id = (int) $this->post('id');
            try {
                if ($dao->buscarPorId($id) === null) {
                    $this->naoEncontrado('Cliente não encontrado.', 'cliente/listar');
                }
                $c = ClienteFactory::criarDeArray($_POST);
                $c->setId($id);
                $dao->atualizar($c);
                $this->sucesso('Cliente atualizado com sucesso.');
                $this->redirecionar('cliente/listar');
            } catch (InvalidArgumentException $e) {
                $dados = $_POST;
                $dados['id'] = $id;
                $this->render('cliente/form', array('titulo' => 'Editar cliente', 'dados' => $dados, 'erro' => $e->getMessage(), 'editando' => true));
                return;
            }
        }

        $c = $dao->buscarPorId($this->idDaUrl());
        if ($c === null) {
            $this->naoEncontrado('Cliente não encontrado.', 'cliente/listar');
        }
        $this->render('cliente/form', array('titulo' => 'Editar cliente', 'erro' => null, 'editando' => true, 'dados' => array(
            'id' => $c->getId(), 'nome' => $c->getNome(), 'telefone' => telefone_br($c->getTelefone()), 'email' => $c->getEmail(),
        )));
    }

    public function excluir()
    {
        try {
            (new ClienteDAO())->excluir((int) $this->post('id'));
            $this->sucesso('Cliente excluído.');
        } catch (PDOException $e) {
            if (!$this->emUso($e)) {
                throw $e;
            }
            $this->erro('Este cliente possui pedidos e não pode ser excluído.');
        }
        $this->redirecionar('cliente/listar');
    }
}
