<?php
/**
 * Proteção CSRF: um token aleatório por sessão, enviado em campo oculto de todo
 * formulário POST e conferido no servidor com hash_equals (comparação segura).
 */
class Csrf
{
    const CHAVE = '_csrf_token';
    const CAMPO = '_csrf';

    public static function token()
    {
        if (empty($_SESSION[self::CHAVE])) {
            $_SESSION[self::CHAVE] = self::gerar();
        }
        return $_SESSION[self::CHAVE];
    }

    /** HTML do campo oculto para colocar dentro dos <form method="post">. */
    public static function campo()
    {
        return '<input type="hidden" name="' . self::CAMPO . '" value="' . e(self::token()) . '">';
    }

    public static function validar($token)
    {
        return is_string($token)
            && isset($_SESSION[self::CHAVE])
            && is_string($_SESSION[self::CHAVE])
            && hash_equals($_SESSION[self::CHAVE], $token);
    }

    public static function renovar()
    {
        $_SESSION[self::CHAVE] = self::gerar();
    }

    /** random_bytes só existe no PHP 7+; no 5.6 usamos openssl. */
    private static function gerar()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $forte = false;
            $bytes = openssl_random_pseudo_bytes(32, $forte);
            if ($bytes !== false && $forte) {
                return bin2hex($bytes);
            }
        }
        throw new AppException('Não há fonte segura de números aleatórios. Habilite a extensão openssl.');
    }
}
