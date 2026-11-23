<?php
require_once __DIR__ . '/controller/ProdutoController.php';

$controller = new ProdutoController();
$action     = $_GET['action'] ?? 'listar';

try {
    switch ($action) {
        case 'criar':    $controller->criar();    break;
        case 'editar':   $controller->editar();   break;
        case 'excluir':  $controller->excluir();  break;
        case 'buscarId': $controller->buscarId(); break;
        case 'listar':
        default:         $controller->listar();   break;
    }
} catch (Throwable $e) {
    $controller->mostrarErro($e->getMessage());
}
