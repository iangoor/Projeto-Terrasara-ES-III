<?php
/**
 * Gestão de usuários (somente administradora). Sem envio de e-mail, a "recuperação de senha"
 * (RF004) é feita pela administradora, que redefine a senha do usuário.
 */
class UsuarioController extends Controller
{
    public function index()
    {
        $this->render('usuario/index', array(
            'titulo' => 'Usuários', 'usuarios' => (new UsuarioDAO())->listar(),
            'dados' => array(), 'erro' => null,
        ));
    }

    public function criar()
    {
        $dao = new UsuarioDAO();
        try {
            $dao->salvar(UsuarioFactory::criarDeArray($_POST));
            $this->sucesso('Usuário cadastrado com sucesso.');
            $this->redirecionar('usuario/index');
        } catch (InvalidArgumentException $e) {
            $dados = $_POST;
            unset($dados['senha'], $dados['senha_confirmacao']);
            $this->render('usuario/index', array(
                'titulo' => 'Usuários', 'usuarios' => $dao->listar(), 'dados' => $dados, 'erro' => $e->getMessage(),
            ));
        }
    }

    public function redefinir()
    {
        $dao = new UsuarioDAO();
        $id = (int) $this->post('id');
        try {
            if ($dao->buscarPorId($id) === null) {
                throw new InvalidArgumentException('Usuário não encontrado.');
            }
            UsuarioFactory::validarSenha((string) $this->post('senha'), (string) $this->post('senha_confirmacao'));
            $dao->atualizarSenha($id, Usuario::gerarHash($this->post('senha')));
            $this->sucesso('Senha redefinida e conta desbloqueada.');
        } catch (InvalidArgumentException $e) {
            $this->erro($e->getMessage());
        }
        $this->redirecionar('usuario/index');
    }

    public function alternar()
    {
        $dao = new UsuarioDAO();
        $id = (int) $this->post('id');
        $u = $dao->buscarPorId($id);
        if ($u === null) {
            $this->erro('Usuário não encontrado.');
        } elseif ($id === Auth::id()) {
            $this->erro('Você não pode desativar o seu próprio usuário.');
        } else {
            $dao->definirAtivo($id, !$u->isAtivo());
            $this->sucesso($u->isAtivo() ? 'Usuário desativado.' : 'Usuário reativado.');
        }
        $this->redirecionar('usuario/index');
    }
}
