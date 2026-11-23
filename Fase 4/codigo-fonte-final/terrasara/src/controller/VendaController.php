<?php
class VendaController extends Controller
{
    public function listar()
    {
        $status = (string) $this->get('status');
        $this->render('venda/listar', array(
            'titulo' => 'Vendas', 'status' => $status, 'pedidos' => (new PedidoDAO())->listar($status),
        ));
    }

    /** UC04 — Realizar venda. */
    public function novo()
    {
        $produtosDao = new ProdutoDAO();
        $hoje = date('Y-m-d');
        $erro = null;
        $dados = array();

        if ($this->ehPost()) {
            try {
                $ids = (isset($_POST['produto_id']) && is_array($_POST['produto_id'])) ? $_POST['produto_id'] : array();
                $porId = $produtosDao->buscarPorIds(array_filter($ids, 'is_scalar'));
                $pedido = PedidoFactory::criarDeArray($_POST, $porId, Auth::id(), $hoje); // Factory

                $novoCliente = null;
                if (!$pedido->getClienteId()) { // RN05/RN06: cliente novo, cadastrado junto com a compra
                    $novoCliente = ClienteFactory::criarDeArray(array(
                        'nome' => $this->post('novo_nome'), 'telefone' => $this->post('novo_telefone'),
                        'email' => $this->post('novo_email'),
                    ));
                }
                $id = (new PedidoDAO())->salvar($pedido, $novoCliente);
                $this->sucesso('Pedido #' . $id . ' registrado. Aguardando confirmação do pagamento.');
                $this->redirecionar('venda/ver', array('id' => $id));
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
                $dados = $_POST;
            }
        }

        $this->render('venda/form', array(
            'titulo' => 'Nova venda', 'erro' => $erro, 'dados' => $dados,
            'produtos' => $produtosDao->listarDisponiveisParaVenda($hoje),
            'clientes' => (new ClienteDAO())->listar(),
            'formas' => Pedido::$FORMAS,
        ));
    }

    public function ver()
    {
        $pedido = (new PedidoDAO())->buscarPorId($this->idDaUrl());
        if ($pedido === null) {
            $this->naoEncontrado('Pedido não encontrado.', 'venda/listar');
        }
        $this->render('venda/ver', array('titulo' => 'Pedido #' . $pedido->getId(), 'pedido' => $pedido));
    }

    public function confirmar()
    {
        $id = (int) $this->post('id');
        try {
            (new PedidoDAO())->confirmarPagamento($id);
            $this->sucesso('Pagamento confirmado. Pedido concluído.');
        } catch (InvalidArgumentException $e) {
            $this->erro($e->getMessage());
        }
        $this->redirecionar('venda/ver', array('id' => $id));
    }

    public function cancelar()
    {
        $id = (int) $this->post('id');
        try {
            (new PedidoDAO())->cancelar($id, Auth::id());
            $this->sucesso('Pedido cancelado e itens devolvidos ao estoque.');
        } catch (InvalidArgumentException $e) {
            $this->erro($e->getMessage());
        }
        $this->redirecionar('venda/ver', array('id' => $id));
    }
}
