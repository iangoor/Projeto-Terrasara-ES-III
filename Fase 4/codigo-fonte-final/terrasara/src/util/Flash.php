<?php
/**
 * Mensagens "flash": gravadas na sessão e exibidas uma única vez (após redirecionamento).
 */
class Flash
{
    const CHAVE = '_flash';

    public static function sucesso($mensagem)
    {
        self::definir('sucesso', $mensagem);
    }

    public static function erro($mensagem)
    {
        self::definir('erro', $mensagem);
    }

    public static function aviso($mensagem)
    {
        self::definir('aviso', $mensagem);
    }

    /** Alias de definir(). */
    public static function set($tipo, $mensagem)
    {
        self::definir($tipo, $mensagem);
    }

    public static function definir($tipo, $mensagem)
    {
        $_SESSION[self::CHAVE][] = array('tipo' => $tipo, 'msg' => $mensagem);
    }

    /** Devolve e apaga as mensagens pendentes. */
    public static function consumir()
    {
        $mensagens = isset($_SESSION[self::CHAVE]) ? $_SESSION[self::CHAVE] : array();
        unset($_SESSION[self::CHAVE]);
        return $mensagens;
    }
}
