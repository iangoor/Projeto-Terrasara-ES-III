<?php
/**
 * Funções auxiliares usadas principalmente nas Views.
 * Todas protegidas por function_exists para evitar "Cannot redeclare".
 */

if (!function_exists('e')) {
    /** Escapa texto para HTML (ENT_QUOTES + UTF-8 — o padrão do PHP 5.6 NÃO escapa aspas simples). */
    function e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('moeda')) {
    function moeda($valor)
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}

if (!function_exists('data_br')) {
    /** Converte 'Y-m-d' (ou 'Y-m-d H:i:s') em 'd/m/Y'. */
    function data_br($data)
    {
        if (!$data) {
            return '';
        }
        $d = DateTime::createFromFormat('Y-m-d', substr((string) $data, 0, 10));
        return $d ? $d->format('d/m/Y') : '';
    }
}

if (!function_exists('datahora_br')) {
    function datahora_br($data)
    {
        if (!$data) {
            return '';
        }
        $d = DateTime::createFromFormat('Y-m-d H:i:s', (string) $data);
        return $d ? $d->format('d/m/Y H:i') : data_br($data);
    }
}

if (!function_exists('telefone_br')) {
    function telefone_br($digitos)
    {
        $d = preg_replace('/\D+/', '', (string) $digitos);
        if (strlen($d) === 11) {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 5) . '-' . substr($d, 7);
        }
        if (strlen($d) === 10) {
            return '(' . substr($d, 0, 2) . ') ' . substr($d, 2, 4) . '-' . substr($d, 6);
        }
        return (string) $digitos;
    }
}

if (!function_exists('rota')) {
    /** Monta a URL de uma rota: rota('produto/editar', array('id' => 3)) */
    function rota($rota, $params = array())
    {
        $params = array_merge(array('r' => $rota), $params);
        return 'index.php?' . http_build_query($params, '', '&');
    }
}

if (!function_exists('tamanho_texto')) {
    function tamanho_texto($texto)
    {
        return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
    }
}

if (!function_exists('selecionado')) {
    function selecionado($atual, $valor)
    {
        return ((string) $atual === (string) $valor) ? ' selected' : '';
    }
}

if (!function_exists('dado')) {
    /** Lê $dados[$campo] com segurança (substitui o operador ?? que não existe no PHP 5.6). */
    function dado($dados, $campo, $padrao = '')
    {
        return (is_array($dados) && isset($dados[$campo])) ? $dados[$campo] : $padrao;
    }
}

if (!function_exists('cortar_texto')) {
    /** Corta em $max CARACTERES (não bytes), sem quebrar letras acentuadas. */
    function cortar_texto($texto, $max)
    {
        return function_exists('mb_substr') ? mb_substr($texto, 0, (int) $max, 'UTF-8') : substr($texto, 0, (int) $max);
    }
}
