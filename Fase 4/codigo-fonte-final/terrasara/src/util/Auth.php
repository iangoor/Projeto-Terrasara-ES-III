<?php
/**
 * Sessão e autorização por perfil (RF001, RF005, RF006).
 */
class Auth
{
    const TIMEOUT = 1800; // 30 min de inatividade

    public static $PERFIS = array(
        'admin'      => 'Administradora',
        'vendedor'   => 'Vendedor',
        'estoquista' => 'Estoquista',
        'financeiro' => 'Gerente Financeiro',
        'cadastro'   => 'Analista de Cadastro',
    );

    public static function iniciarSessao()
    {
        if (session_id() !== '') {
            return;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_name('TERRASARASESS');
        // httponly = o JavaScript não lê o cookie de sessão.
        session_set_cookie_params(0, '/', '', $https, true);
        session_start();

        if (isset($_SESSION['usuario'], $_SESSION['ultimo_acesso'])
            && (time() - $_SESSION['ultimo_acesso']) > self::TIMEOUT) {
            self::limpar();
            Flash::aviso('Sua sessão expirou por inatividade. Entre novamente.');
        } elseif (isset($_SESSION['usuario'])) {
            $_SESSION['ultimo_acesso'] = time();
        }
    }

    public static function login(Usuario $usuario)
    {
        // Evita "session fixation": novo ID de sessão a cada login.
        session_regenerate_id(true);
        $_SESSION['usuario'] = array(
            'id'     => (int) $usuario->getId(),
            'nome'   => $usuario->getNome(),
            'email'  => $usuario->getEmail(),
            'perfil' => $usuario->getPerfil(),
        );
        $_SESSION['ultimo_acesso'] = time();
        Csrf::renovar();
    }

    public static function logout()
    {
        self::limpar();
        Flash::sucesso('Você saiu do sistema.');
    }

    private static function limpar()
    {
        unset($_SESSION['usuario'], $_SESSION['ultimo_acesso']);
        if (session_id() !== '') {
            session_regenerate_id(true);
        }
        Csrf::renovar();
    }

    public static function estaLogado()
    {
        return isset($_SESSION['usuario']);
    }

    public static function usuario()
    {
        return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
    }

    public static function id()
    {
        return isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : 0;
    }

    public static function nome()
    {
        return isset($_SESSION['usuario']['nome']) ? $_SESSION['usuario']['nome'] : '';
    }

    public static function perfil()
    {
        return isset($_SESSION['usuario']['perfil']) ? $_SESSION['usuario']['perfil'] : '';
    }

    public static function nomePerfil($perfil = null)
    {
        $p = ($perfil === null) ? self::perfil() : $perfil;
        return isset(self::$PERFIS[$p]) ? self::$PERFIS[$p] : '';
    }

    /**
     * $acesso: '*' (qualquer usuário logado) ou lista de perfis. A administradora acessa tudo.
     */
    public static function podeAcessar($acesso)
    {
        if (!self::estaLogado()) {
            return false;
        }
        if ($acesso === '*' || self::perfil() === 'admin') {
            return true;
        }
        return is_array($acesso) && in_array(self::perfil(), $acesso, true);
    }
}
