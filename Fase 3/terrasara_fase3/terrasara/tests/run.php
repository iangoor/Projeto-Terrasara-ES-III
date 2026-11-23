<?php
/**
 * Executor dos testes.   Uso:  php tests/run.php            (todos)
 *                              php tests/run.php Strategy   (só classes cujo nome contém "Strategy")
 * Saída: uma linha por teste + resumo. Código de saída 0 = tudo passou; 1 = houve falha.
 */
require_once dirname(__FILE__) . '/bootstrap.php';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "Habilite a extensão pdo_sqlite no php.ini (extension=pdo_sqlite) para rodar os testes.\n");
    exit(2);
}

$filtro = isset($argv[1]) ? $argv[1] : '';
$arquivos = glob(dirname(__FILE__) . '/Casos/*Test.php');
sort($arquivos);
foreach ($arquivos as $arq) {
    require_once $arq;
}

$total = 0;
$falhas = array();
$inicio = microtime(true);
echo "TerraSara — suíte de testes (PHP " . PHP_VERSION . ")\n";
echo str_repeat('=', 70) . "\n";

foreach (get_declared_classes() as $classe) {
    if (!is_subclass_of($classe, 'TesteBase') || ($filtro !== '' && stripos($classe, $filtro) === false)) {
        continue;
    }
    echo "\n" . $classe . "\n";
    foreach (get_class_methods($classe) as $metodo) {
        if (strpos($metodo, 'test') !== 0) {
            continue;
        }
        $total++;
        $t = new $classe();
        try {
            $t->setUp();
            $t->$metodo();
            echo "  [OK]    " . $metodo . "\n";
        } catch (Exception $e) {
            $falhas[] = $classe . '::' . $metodo . ' — ' . $e->getMessage();
            echo "  [FALHA] " . $metodo . "\n          " . $e->getMessage() . "\n";
        } catch (Throwable $e) { // PHP 7+: erros fatais capturáveis (TypeError etc.)
            $falhas[] = $classe . '::' . $metodo . ' — ' . $e->getMessage();
            echo "  [ERRO]  " . $metodo . "\n          " . get_class($e) . ': ' . $e->getMessage() . "\n";
        }
        $t->tearDown();
    }
}

$passou = $total - count($falhas);
echo "\n" . str_repeat('=', 70) . "\n";
printf("Testes: %d | Passaram: %d | Falharam: %d | Tempo: %.2fs\n", $total, $passou, count($falhas), microtime(true) - $inicio);
echo count($falhas) === 0 ? "RESULTADO: SUCESSO\n" : "RESULTADO: FALHA\n";
exit(count($falhas) === 0 ? 0 : 1);
