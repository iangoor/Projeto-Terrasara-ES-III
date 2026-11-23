<?php
/**
 * Inicialização da aplicação web (não usado pelos testes).
 */
require_once __DIR__ . '/autoload.php';

$appConfig = array();
if (is_file(APP_ROOT . '/config/app.php')) {
    $appConfig = require APP_ROOT . '/config/app.php';
}
$appConfig = array_merge(array('debug' => false, 'timezone' => 'America/Sao_Paulo'), $appConfig);

define('APP_DEBUG', (bool) $appConfig['debug']);
date_default_timezone_set($appConfig['timezone']);

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('default_charset', 'UTF-8');

/**
 * Qualquer exceção não tratada cai aqui: registra no log e mostra uma página amigável.
 * (Sem type hint de propósito: no PHP 5.6 é Exception, no 7+ pode ser Error.)
 */
set_exception_handler(function ($e) {
    error_log('[Terrasara] ' . get_class($e) . ': ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    $mensagem = 'Ocorreu um erro inesperado. Tente novamente em instantes.';
    if (APP_DEBUG || $e instanceof AppException) {
        $mensagem = $e->getMessage();
    }

    try {
        View::render('erro', array('titulo' => 'Erro', 'codigo' => 500, 'mensagem' => $mensagem));
    } catch (Exception $e2) {
        echo '<h1>Erro</h1><p>' . e($mensagem) . '</p>';
    }
});

Auth::iniciarSessao();
