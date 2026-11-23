<?php
/**
 * Regra de autenticação (UC01): valida credenciais e bloqueia a conta após
 * várias tentativas inválidas seguidas (RF007).
 * Não mexe em sessão — isso é papel da classe Auth — por isso é fácil de testar.
 */
class AuthService
{
    const MAX_TENTATIVAS   = 5;
    const MINUTOS_BLOQUEIO = 15;
    const MSG_INVALIDO     = 'E-mail ou senha inválidos.';

    private $dao;

    public function __construct(UsuarioDAO $dao)
    {
        $this->dao = $dao;
    }

    /** @return Usuario */
    public function autenticar($email, $senha, $agora = null)
    {
        $agora = ($agora === null) ? time() : $agora;
        $email = strtolower(trim((string) $email));
        $senha = (string) $senha;

        if ($email === '' || $senha === '') {
            throw new AuthException('Informe o e-mail e a senha.');
        }

        $usuario = $this->dao->buscarPorEmail($email);

        // Mensagem genérica: não revela se o e-mail existe (evita enumeração de usuários).
        if ($usuario === null || !$usuario->isAtivo()) {
            throw new AuthException(self::MSG_INVALIDO);
        }

        if ($usuario->estaBloqueado($agora)) {
            $minutos = (int) ceil((strtotime($usuario->getBloqueadoAte()) - $agora) / 60);
            throw new AuthException(
                'Acesso temporariamente bloqueado por excesso de tentativas. Tente novamente em ' .
                max(1, $minutos) . ' minuto(s).'
            );
        }

        if (!$usuario->verificarSenha($senha)) {
            $tentativas = $usuario->getTentativasFalhas() + 1;
            $bloqueadoAte = null;
            if ($tentativas >= self::MAX_TENTATIVAS) {
                $bloqueadoAte = date('Y-m-d H:i:s', $agora + self::MINUTOS_BLOQUEIO * 60);
                $tentativas = 0;
            }
            $this->dao->registrarFalha($usuario->getId(), $tentativas, $bloqueadoAte);

            if ($bloqueadoAte !== null) {
                throw new AuthException(
                    'Muitas tentativas inválidas. Acesso bloqueado por ' . self::MINUTOS_BLOQUEIO . ' minutos.'
                );
            }
            throw new AuthException(self::MSG_INVALIDO);
        }

        $this->dao->zerarFalhas($usuario->getId());
        return $usuario;
    }
}
