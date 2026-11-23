<?php
/**
 * Base dos controllers: pequenos atalhos para não repetir código.
 */
abstract class Controller
{
    protected function render($view, $dados = array())
    {
        View::render($view, $dados);
    }

    protected function redirecionar($rota, $params = array())
    {
        header('Location: ' . rota($rota, $params));
        exit;
    }

    protected function ehPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function post($campo, $padrao = '')
    {
        return isset($_POST[$campo]) ? $_POST[$campo] : $padrao;
    }

    protected function get($campo, $padrao = '')
    {
        return isset($_GET[$campo]) ? $_GET[$campo] : $padrao;
    }

    protected function idDaUrl()
    {
        return isset($_GET['id']) ? (int) $_GET['id'] : 0;
    }

    protected function idDoPost()
    {
        return isset($_POST['id']) ? (int) $_POST['id'] : 0;
    }

    protected function sucesso($mensagem)
    {
        Flash::sucesso($mensagem);
    }

    protected function erro($mensagem)
    {
        Flash::erro($mensagem);
    }

    /** Mensagem de erro + volta para a listagem. */
    protected function naoEncontrado($mensagem, $rota)
    {
        Flash::erro($mensagem);
        $this->redirecionar($rota);
    }

    /** True quando a exceção é violação de chave estrangeira (registro em uso) — SQLSTATE 23000. */
    protected function emUso(PDOException $e)
    {
        return (string) $e->getCode() === '23000';
    }
}
