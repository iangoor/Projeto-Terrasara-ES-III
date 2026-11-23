<?php
/**
 * Tabela de rotas: 'modulo/acao' => array(Controller, método, acesso, métodos HTTP)
 *   acesso: 'publico' | '*' (qualquer usuário logado) | array de perfis (a administradora acessa tudo)
 *   Perfis: admin, vendedor, estoquista, financeiro, cadastro
 * Ações que alteram dados são sempre POST (com token CSRF).
 */
return array(
    // Autenticação (UC01, UC03)
    'auth/login'  => array('AuthController', 'login',  'publico', 'GET|POST'),
    'auth/logout' => array('AuthController', 'logout', '*',       'POST'),
    'auth/senha'  => array('AuthController', 'senha',  '*',       'GET|POST'),

    // Produtos (UC10, UC11)
    'produto/listar' => array('ProdutoController', 'listar',  '*',                    'GET'),
    'produto/criar'  => array('ProdutoController', 'criar',   array('cadastro'),      'GET|POST'),
    'produto/editar' => array('ProdutoController', 'editar',  array('cadastro'),      'GET|POST'),
    'produto/excluir'=> array('ProdutoController', 'excluir', array('cadastro'),      'POST'),

    // Fornecedores (RN10)
    'fornecedor/listar'  => array('FornecedorController', 'listar',  array('cadastro', 'estoquista'), 'GET'),
    'fornecedor/criar'   => array('FornecedorController', 'criar',   array('cadastro'), 'GET|POST'),
    'fornecedor/editar'  => array('FornecedorController', 'editar',  array('cadastro'), 'GET|POST'),
    'fornecedor/excluir' => array('FornecedorController', 'excluir', array('cadastro'), 'POST'),

    // Clientes (UC12)
    'cliente/listar'  => array('ClienteController', 'listar',  array('vendedor', 'cadastro'), 'GET'),
    'cliente/criar'   => array('ClienteController', 'criar',   array('vendedor', 'cadastro'), 'GET|POST'),
    'cliente/editar'  => array('ClienteController', 'editar',  array('vendedor', 'cadastro'), 'GET|POST'),
    'cliente/excluir' => array('ClienteController', 'excluir', array('cadastro'),             'POST'),

    // Vendas (UC04)
    'venda/listar'    => array('VendaController', 'listar',    array('vendedor', 'financeiro'), 'GET'),
    'venda/novo'      => array('VendaController', 'novo',      array('vendedor'),               'GET|POST'),
    'venda/ver'       => array('VendaController', 'ver',       array('vendedor', 'financeiro'), 'GET'),
    'venda/confirmar' => array('VendaController', 'confirmar', array('vendedor', 'financeiro'), 'POST'),
    'venda/cancelar'  => array('VendaController', 'cancelar',  array('vendedor'),               'POST'),

    // Estoque (UC05, UC15, UC16)
    'estoque/index'    => array('EstoqueController', 'index',    array('estoquista', 'vendedor'), 'GET'),
    'estoque/registrar'=> array('EstoqueController', 'registrar', array('estoquista'),          'POST'),

    // Financeiro (UC07, UC08, UC09)
    'relatorio/financeiro' => array('RelatorioController', 'financeiro', array('financeiro'), 'GET'),

    // Usuários (somente administradora)
    'usuario/index'     => array('UsuarioController', 'index',     array(), 'GET'),
    'usuario/criar'     => array('UsuarioController', 'criar',     array(), 'POST'),
    'usuario/redefinir' => array('UsuarioController', 'redefinir', array(), 'POST'),
    'usuario/alternar'  => array('UsuarioController', 'alternar',  array(), 'POST'),
);
