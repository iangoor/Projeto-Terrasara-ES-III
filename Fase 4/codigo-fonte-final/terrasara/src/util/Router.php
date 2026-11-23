<?php
/**
 * Roteador do front controller (public/index.php?r=modulo/acao).
 * Só executa rotas declaradas em config/routes.php (lista branca), confere
 * método HTTP, login, perfil e token CSRF (em todo POST).
 */
class Router
{
    private static $rotas = null;

    public static function rotas()
    {
        if (self::$rotas === null) {
            self::$rotas = require APP_ROOT . '/config/routes.php';
        }
        return self::$rotas;
    }

    public static function permitido($rota)
    {
        $rotas = self::rotas();
        if (!isset($rotas[$rota])) {
            return false;
        }
        return $rotas[$rota][2] === 'publico' || Auth::podeAcessar($rotas[$rota][2]);
    }

    /** Itens do menu principal já filtrados pelo perfil do usuário. */
    public static function menu()
    {
        $itens = array(
            array('Produtos', 'produto/listar'),
            array('Fornecedores', 'fornecedor/listar'),
            array('Clientes', 'cliente/listar'),
            array('Vendas', 'venda/listar'),
            array('Estoque', 'estoque/index'),
            array('Relatórios', 'relatorio/financeiro'),
            array('Usuários', 'usuario/index'),
        );
        $menu = array();
        foreach ($itens as $item) {
            if (self::permitido($item[1])) {
                $menu[] = $item;
            }
        }
        return $menu;
    }

    public static function despachar($rota)
    {
        $rotas  = self::rotas();
        $logado = Auth::estaLogado();
        $rota   = trim((string) $rota);

        if ($rota === '') {
            $rota = $logado ? 'produto/listar' : 'auth/login';
        }

        if (!isset($rotas[$rota])) {
            self::erro(404, 'Página não encontrada', 'O endereço que você acessou não existe.');
            return;
        }

        $classe = $rotas[$rota][0];
        $metodo = $rotas[$rota][1];
        $acesso = $rotas[$rota][2];
        $http   = explode('|', $rotas[$rota][3]);

        $metodoHttp = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (!in_array($metodoHttp, $http, true)) {
            self::erro(405, 'Ação não permitida', 'Esta ação não pode ser feita desta forma.');
            return;
        }

        if ($acesso !== 'publico') {
            if (!$logado) {
                header('Location: ' . rota('auth/login'));
                exit;
            }
            if (!Auth::podeAcessar($acesso)) {
                self::erro(403, 'Acesso negado', 'Seu perfil (' . Auth::nomePerfil() . ') não tem permissão para esta área.');
                return;
            }
        } elseif ($logado && $rota === 'auth/login') {
            header('Location: ' . rota('produto/listar'));
            exit;
        }

        if ($metodoHttp === 'POST') {
            $token = isset($_POST[Csrf::CAMPO]) ? $_POST[Csrf::CAMPO] : '';
            if (!Csrf::validar($token)) {
                self::erro(400, 'Requisição inválida', 'O formulário expirou ou é inválido. Volte, recarregue a página e tente de novo.');
                return;
            }
        }

        $controller = new $classe();
        $controller->$metodo();
    }

    public static function erro($codigo, $titulo, $mensagem)
    {
        http_response_code($codigo);
        View::render('erro', array('titulo' => $titulo, 'codigo' => $codigo, 'mensagem' => $mensagem));
    }
}
