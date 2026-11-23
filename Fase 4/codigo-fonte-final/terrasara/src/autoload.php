<?php
/**
 * Autoload simples + constantes de caminho.
 * Compatível com PHP 5.6, 7.x e 8.x. Não depende do Composer em produção.
 */
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('SRC_PATH')) {
    define('SRC_PATH', APP_ROOT . '/src');
}
if (!defined('VIEW_PATH')) {
    define('VIEW_PATH', SRC_PATH . '/view');
}

require_once SRC_PATH . '/util/helpers.php';

spl_autoload_register(function ($classe) {
    // Ignora classes com namespace (PHPUnit, etc.).
    if (strpos($classe, '\\') !== false) {
        return;
    }
    $pastas = array('controller', 'dao', 'model', 'factory', 'strategy', 'util');
    foreach ($pastas as $pasta) {
        $arquivo = SRC_PATH . '/' . $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});
