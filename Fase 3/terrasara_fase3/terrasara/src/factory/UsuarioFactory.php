<?php
require_once dirname(dirname(__FILE__)) . '/model/Usuario.php';

class UsuarioFactory
{
    const SENHA_MINIMA = 8;

    /** Valida uma senha nova (e sua confirmação). */
    public static function validarSenha($senha, $confirmacao)
    {
        if (strlen($senha) < self::SENHA_MINIMA) {
            throw new InvalidArgumentException('A senha deve ter pelo menos ' . self::SENHA_MINIMA . ' caracteres.');
        }
        if ($senha !== $confirmacao) {
            throw new InvalidArgumentException('A confirmação da senha não confere.');
        }
    }

    public static function criarDeArray(array $dados)
    {
        $nome = isset($dados['nome']) ? trim($dados['nome']) : '';
        $email = isset($dados['email']) ? strtolower(trim($dados['email'])) : '';
        $perfil = isset($dados['perfil']) ? $dados['perfil'] : '';
        $senha = isset($dados['senha']) ? $dados['senha'] : '';
        $confirmacao = isset($dados['senha_confirmacao']) ? $dados['senha_confirmacao'] : '';

        if ($nome === '' || $email === '') {
            throw new InvalidArgumentException('Nome e e-mail são obrigatórios.');
        }
        if (tamanho_texto($nome) > 120) {
            throw new InvalidArgumentException('O nome deve ter no máximo 120 caracteres.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
        if (!array_key_exists($perfil, Auth::$PERFIS)) {
            throw new InvalidArgumentException('Perfil inválido.');
        }
        self::validarSenha($senha, $confirmacao);

        $u = new Usuario();
        $u->setNome($nome);
        $u->setEmail($email);
        $u->setPerfil($perfil);
        $u->setSenhaHash(Usuario::gerarHash($senha));
        return $u;
    }
}
