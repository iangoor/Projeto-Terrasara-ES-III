<?php
/**
 * Base dos DAOs. Recebe o PDO por parâmetro (injeção de dependência — facilita testes);
 * se nenhum for passado, usa a conexão Singleton.
 */
abstract class BaseDAO
{
    protected $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo ? $pdo : Conexao::getConexao();
    }

    /**
     * Executa $funcao dentro de uma transação (tudo ou nada). Se já houver uma transação
     * aberta (outro DAO a iniciou), apenas participa dela.
     */
    protected function transacao($funcao)
    {
        if ($this->pdo->inTransaction()) {
            return call_user_func($funcao);
        }
        $this->pdo->beginTransaction();
        $concluida = false;
        try {
            $resultado = call_user_func($funcao);
            $this->pdo->commit();
            $concluida = true;
            return $resultado;
        } finally {
            if (!$concluida && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    protected function consultar($sql, array $parametros = array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }

    protected function ultimoId()
    {
        return (int) $this->pdo->lastInsertId();
    }
}
