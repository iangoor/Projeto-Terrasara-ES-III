<?php
/**
 * Cria (ou redefine a senha de) um usuário administrador pela linha de comando.
 *   php scripts/criar_admin.php email@exemplo.com "Nome" "SenhaForte123"
 */
if (php_sapi_name() !== 'cli') {
    exit("Execute apenas pelo terminal.\n");
}
require_once dirname(dirname(__FILE__)) . '/src/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

if ($argc < 4) {
    exit("Uso: php scripts/criar_admin.php email nome senha\n");
}
list(, $email, $nome, $senha) = $argv;

try {
    UsuarioFactory::validarSenha($senha, $senha);
    $dao = new UsuarioDAO();
    $existente = $dao->buscarPorEmail($email);
    if ($existente) {
        $dao->atualizarSenha($existente->getId(), Usuario::gerarHash($senha));
        echo "Senha redefinida para {$email}.\n";
    } else {
        $dao->salvar(UsuarioFactory::criarDeArray(array(
            'nome' => $nome, 'email' => $email, 'perfil' => 'admin',
            'senha' => $senha, 'senha_confirmacao' => $senha,
        )));
        echo "Administrador {$email} criado.\n";
    }
} catch (Exception $e) {
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
