<?php
/**
 * PADRÃO GoF: SINGLETON
 * Garante UMA única conexão PDO por requisição. Os DAOs pedem a conexão por
 * Conexao::getConexao() (ou recebem um PDO pelo construtor, o que facilita os testes).
 *
 * As credenciais ficam em config/database.php (fora do controle de versão).
 * Drivers: 'mysql' (produção) e 'sqlite' (apenas testes/demonstração).
 */
class Conexao
{
    private static $instancia = null;

    // Construtor e clone privados: ninguém cria outra Conexao com "new" ou "clone".
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getConexao()
    {
        if (self::$instancia === null) {
            self::$instancia = self::criar(self::carregarConfig());
        }
        return self::$instancia;
    }

    /** Permite injetar uma conexão (ou null para zerar). Usado nos testes. */
    public static function definir(PDO $pdo = null)
    {
        self::$instancia = $pdo;
    }

    public static function carregarConfig()
    {
        $arquivo = APP_ROOT . '/config/database.php';
        if (!is_file($arquivo)) {
            throw new AppException(
                'Arquivo config/database.php não encontrado. ' .
                'Copie config/database.example.php para config/database.php e ajuste os dados do banco.'
            );
        }

        $config = require $arquivo;
        $config = array_merge(array(
            'driver'      => 'mysql',
            'host'        => 'localhost',
            'port'        => 3306,
            'name'        => 'terrasara',
            'user'        => 'root',
            'pass'        => '',
            'charset'     => 'utf8mb4',
            'sqlite_path' => '',
        ), is_array($config) ? $config : array());

        // Variáveis de ambiente têm prioridade (úteis em testes/CI).
        foreach (array('driver', 'host', 'port', 'name', 'user', 'pass', 'sqlite_path') as $chave) {
            $valor = getenv('TERRASARA_DB_' . strtoupper($chave));
            if ($valor !== false && $valor !== '') {
                $config[$chave] = $valor;
            }
        }
        return $config;
    }

    /**
     * Cria uma conexão PDO a partir de um array de configuração.
     * $comBanco = false conecta só no servidor (usado pelo instalador para criar o banco).
     */
    public static function criar(array $config, $comBanco = true)
    {
        if (!extension_loaded('pdo')) {
            throw new AppException('A extensão PDO do PHP não está habilitada.');
        }

        $opcoes = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );

        if ($config['driver'] === 'sqlite') {
            if (!extension_loaded('pdo_sqlite')) {
                throw new AppException('A extensão pdo_sqlite não está habilitada.');
            }
            $caminho = $config['sqlite_path'] !== '' ? $config['sqlite_path'] : ':memory:';
            $pdo = new PDO('sqlite:' . $caminho, null, null, $opcoes);
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        }

        if (!extension_loaded('pdo_mysql')) {
            throw new AppException(
                'A extensão pdo_mysql do PHP não está habilitada. ' .
                'Abra o php.ini, remova o ";" da linha extension=pdo_mysql (ou extension=php_pdo_mysql.dll no Windows) e reinicie o servidor.'
            );
        }

        $dsn = 'mysql:host=' . $config['host'] . ';port=' . (int) $config['port'];
        if ($comBanco) {
            $dsn .= ';dbname=' . $config['name'];
        }
        $dsn .= ';charset=' . $config['charset'];

        $opcoes[PDO::ATTR_EMULATE_PREPARES] = false;

        try {
            return new PDO($dsn, $config['user'], $config['pass'], $opcoes);
        } catch (PDOException $e) {
            // Não expõe usuário/senha/host ao usuário final; o detalhe vai para o log.
            error_log('[Terrasara] Falha ao conectar no banco: ' . $e->getMessage());
            throw new AppException(
                'Não foi possível conectar ao banco de dados. Confira config/database.php ' .
                '(host, porta, usuário, senha) e se o MySQL está em execução.'
            );
        }
    }
}
