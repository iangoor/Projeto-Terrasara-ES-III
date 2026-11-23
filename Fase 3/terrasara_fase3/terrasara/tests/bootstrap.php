<?php
/**
 * Bootstrap dos testes: carrega o autoload do sistema e fixa o fuso horário.
 * Os testes usam SQLite em memória (driver 'sqlite' da classe Conexao): nada toca o MySQL real.
 */
define('APP_ROOT', dirname(dirname(__FILE__)));
require_once APP_ROOT . '/src/autoload.php';
date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
require_once dirname(__FILE__) . '/TesteBase.php';
