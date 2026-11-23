<?php
/**
 * Renderizador de Views: troca o uso de $_REQUEST do código antigo por variáveis locais.
 * View::render('produto/listar', array('produtos' => $lista));
 */
class View
{
    public static function render($arquivo, $dados = array(), $layout = 'main')
    {
        $conteudo = self::capturar($arquivo, $dados);

        if ($layout === null) {
            echo $conteudo;
            return;
        }

        $dados['conteudo'] = $conteudo;
        echo self::capturar('layout/' . $layout, $dados);
    }

    private static function capturar($__arquivo, $__dados)
    {
        extract($__dados, EXTR_SKIP);
        ob_start();
        include VIEW_PATH . '/' . $__arquivo . '.php';
        return ob_get_clean();
    }
}
