<?php
class EstoqueController extends Controller
{
    /** UC05/UC16 — consulta de estoque, alertas de validade e histórico de movimentações. */
    public function index()
    {
        $hoje = date('Y-m-d');
        $produtosDao = new ProdutoDAO();
        $this->render('estoque/index', array(
            'titulo' => 'Estoque', 'hoje' => $hoje,
            'alertas' => $produtosDao->listarAlertas($hoje, Produto::DIAS_ALERTA),
            'produtos' => $produtosDao->listar(),
            'movimentacoes' => (new MovimentacaoDAO())->listarRecentes(30),
            'podeMovimentar' => Router::permitido('estoque/registrar'),
        ));
    }

    /** UC15/UC16 — registrar entrada ou descarte (produto vencido, perda). */
    public function registrar()
    {
        $tipo = (string) $this->post('tipo');
        try {
            if (!in_array($tipo, array('entrada', 'descarte'), true)) {
                throw new InvalidArgumentException('Escolha entrada ou descarte.');
            }
            $produtoId = (int) $this->post('produto_id');
            if ((new ProdutoDAO())->buscarPorId($produtoId) === null) {
                throw new InvalidArgumentException('Selecione um produto.');
            }
            (new MovimentacaoDAO())->registrar($produtoId, Auth::id(), $tipo,
                $this->post('quantidade'), (string) $this->post('motivo'));
            $this->sucesso('Movimentação registrada.');
        } catch (InvalidArgumentException $e) {
            $this->erro($e->getMessage());
        }
        $this->redirecionar('estoque/index');
    }
}
