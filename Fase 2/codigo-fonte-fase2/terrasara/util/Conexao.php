<?php
/**
 * PADRÃO GoF: SINGLETON
 * Garante que exista UMA única conexão MySQL (mysqli) durante a requisição.
 * Todos os DAOs pedem a conexão por Conexao::getConexao() e recebem sempre a mesma.
 */
class Conexao
{
    private static ?mysqli $instancia = null;

    // Construtor e clone privados: ninguém consegue criar outra Conexao com "new" ou "clone".
    private function __construct() {}
    private function __clone() {}

    public static function getConexao(): mysqli
    {
        if (self::$instancia === null) {

            if (!class_exists('mysqli')) {
                throw new RuntimeException(
                    'A extensão mysqli do PHP não está habilitada. ' .
                    'Abra o php.ini e remova o ";" da linha extension=mysqli, depois reinicie o servidor.'
                );
            }
            
            $host    = 'localhost';
            $usuario = 'root';
            $senha   = 'usbw';
            $banco   = 'terrasara2';

            try {
                self::$instancia = new mysqli($host, $usuario, $senha, $banco);
            } catch (mysqli_sql_exception $e) {
                throw new RuntimeException('Não foi possível conectar ao banco: ' . $e->getMessage());
            }

            self::$instancia->set_charset('utf8mb4');
        }

        return self::$instancia;
    }
}
