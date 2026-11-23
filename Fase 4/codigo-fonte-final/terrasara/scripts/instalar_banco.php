<?php
/**
 * Instalador do banco (roda no terminal/PowerShell, SEM precisar do cliente "mysql").
 *
 *   php scripts/instalar_banco.php                 cria o banco + tabelas + administrador
 *   php scripts/instalar_banco.php --demo          idem + dados de demonstracao (produtos, clientes...)
 *   php scripts/instalar_banco.php --migrar        converte o banco da versao anterior (so "produtos")
 *   php scripts/instalar_banco.php --recriar --demo  APAGA o banco e recria do zero
 *
 * Usa as credenciais de config/database.php. Compativel com PHP 5.6+.
 */
if (php_sapi_name() !== 'cli') {
    exit("Execute apenas pelo terminal.\n");
}

define('APP_ROOT', dirname(dirname(__FILE__)));
require_once APP_ROOT . '/src/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

$opcoes = array_slice($argv, 1);
$demo = in_array('--demo', $opcoes, true);
$migrar = in_array('--migrar', $opcoes, true);
$recriar = in_array('--recriar', $opcoes, true);

const ADMIN_EMAIL = 'admin@terrasara.com.br';
const ADMIN_SENHA = 'Terrasara@2026';

function executar_sql(PDO $pdo, $arquivo)
{
    $sql = file_get_contents($arquivo);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (preg_split('/;\s*(\r?\n|$)/', $sql) as $comando) {
        if (trim($comando) !== '') {
            $pdo->exec($comando);
        }
    }
}

try {
    $config = Conexao::carregarConfig();
    if ($config['driver'] !== 'mysql') {
        throw new Exception("Este instalador e para MySQL (driver atual: {$config['driver']}).");
    }
    $banco = $config['name'];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $banco)) {
        throw new Exception('Nome de banco invalido em config/database.php (use letras, numeros e _).');
    }

    echo "Conectando em {$config['host']}:{$config['port']} como {$config['user']}...\n";
    $servidor = Conexao::criar($config, false);

    if ($recriar) {
        echo "Apagando o banco '{$banco}'...\n";
        $servidor->exec("DROP DATABASE IF EXISTS `{$banco}`");
    }

    // Em hospedagem gratuita (InfinityFree) o banco ja existe e CREATE DATABASE e negado: ignoramos.
    try {
        $servidor->exec("CREATE DATABASE IF NOT EXISTS `{$banco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "Banco '{$banco}' pronto.\n";
    } catch (PDOException $e) {
        echo "Aviso: nao foi possivel criar o banco (sem permissao?). Usando o existente.\n";
    }

    $pdo = Conexao::criar($config, true);

    if ($migrar) {
        echo "Migrando tabela 'produtos' da versao anterior...\n";
        executar_sql($pdo, APP_ROOT . '/sql/migracao_v1_para_v2.sql');
    }

    echo "Criando tabelas...\n";
    executar_sql($pdo, APP_ROOT . '/sql/schema.sql');

    // Administrador inicial (a senha e gravada com password_hash).
    $dao = new UsuarioDAO($pdo);
    if ($dao->buscarPorEmail(ADMIN_EMAIL) === null) {
        $dao->salvar(UsuarioFactory::criarDeArray(array(
            'nome' => 'Administradora', 'email' => ADMIN_EMAIL, 'perfil' => 'admin',
            'senha' => ADMIN_SENHA, 'senha_confirmacao' => ADMIN_SENHA,
        )));
        echo "Administrador criado.\n";
    } else {
        echo "Administrador ja existe (mantido).\n";
    }

    if ($demo) {
        $tem = $pdo->query('SELECT COUNT(*) FROM fornecedores')->fetchColumn();
        if ((int) $tem > 0) {
            echo "Dados de demonstracao NAO importados: o banco ja possui fornecedores.\n";
        } else {
            executar_sql($pdo, APP_ROOT . '/sql/seed_demo.sql');
            echo "Dados de demonstracao importados.\n";
        }
    }

    echo "\nPRONTO!\n";
    echo "  Acesse:  http://localhost:8000   (php -S localhost:8000 -t public)\n";
    echo '  Login:   ' . ADMIN_EMAIL . "\n";
    echo '  Senha:   ' . ADMIN_SENHA . "   <- troque em 'Alterar senha'\n";
} catch (Exception $e) {
    fwrite(STDERR, 'ERRO: ' . $e->getMessage() . "\n");
    exit(1);
}
