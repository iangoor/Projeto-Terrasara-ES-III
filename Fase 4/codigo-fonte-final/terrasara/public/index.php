<?php
/**
 * Front controller do Terrasara. Todas as páginas passam por aqui: index.php?r=modulo/acao
 * Rodar localmente:  php -S localhost:8000 -t public
 */
require_once __DIR__ . '/../src/bootstrap.php';

Router::despachar(isset($_GET['r']) ? (string) $_GET['r'] : '');
