<?php
/**
 * Modelo de configuração do banco de dados.
 * Copie este arquivo para config/database.php e ajuste os valores.
 * O arquivo config/database.php NÃO vai para o GitHub (está no .gitignore).
 *
 * Ambiente local (php -S):   host 'localhost', user 'root', pass da sua instalação MySQL.
 * InfinityFree:              veja o README (host tipo sqlXXX.infinityfree.com, banco if0_XXXX_nome).
 */
return array(
    'driver'  => 'mysql',
    'host'    => 'localhost',
    'port'    => 3306,
    'name'    => 'terrasara',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
);
