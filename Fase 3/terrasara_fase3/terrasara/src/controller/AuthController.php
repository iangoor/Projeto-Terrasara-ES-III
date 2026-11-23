<?php
class AuthController extends Controller
{
    public function login()
    {
        if (Auth::estaLogado()) {
            $this->redirecionar('produto/listar');
        }

        $erro = null;
        $email = '';
        if ($this->ehPost()) {
            $email = trim($this->post('email'));
            try {
                $servico = new AuthService(new UsuarioDAO());
                $usuario = $servico->autenticar($email, (string) $this->post('senha'));
                Auth::login($usuario);
                $this->redirecionar('produto/listar');
            } catch (AuthException $e) {
                $erro = $e->getMessage();
            }
        }
        View::render('auth/login', array('titulo' => 'Entrar', 'erro' => $erro, 'email' => $email));
    }

    public function logout()
    {
        Auth::logout(); // limpa os dados da sessão e deixa a mensagem "Você saiu do sistema."
        $this->redirecionar('auth/login');
    }

    /** Troca da própria senha. */
    public function senha()
    {
        $erro = null;
        if ($this->ehPost()) {
            try {
                $dao = new UsuarioDAO();
                $u = $dao->buscarPorId(Auth::id());
                if ($u === null || !$u->verificarSenha((string) $this->post('senha_atual'))) {
                    throw new InvalidArgumentException('A senha atual está incorreta.');
                }
                UsuarioFactory::validarSenha((string) $this->post('senha_nova'), (string) $this->post('senha_confirmacao'));
                $dao->atualizarSenha($u->getId(), Usuario::gerarHash($this->post('senha_nova')));
                $this->sucesso('Senha alterada com sucesso.');
                $this->redirecionar('produto/listar');
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
            }
        }
        $this->render('auth/senha', array('titulo' => 'Alterar senha', 'erro' => $erro));
    }
}
