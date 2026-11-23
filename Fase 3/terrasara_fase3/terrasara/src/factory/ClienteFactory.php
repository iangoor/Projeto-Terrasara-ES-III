<?php
require_once dirname(dirname(__FILE__)) . '/model/Cliente.php';

/**
 * RN05: nome, telefone e e-mail são obrigatórios.
 */
class ClienteFactory
{
    public static function criarDeArray(array $dados)
    {
        $nome = isset($dados['nome']) ? trim($dados['nome']) : '';
        $email = isset($dados['email']) ? strtolower(trim($dados['email'])) : '';
        $telefone = isset($dados['telefone']) ? preg_replace('/\D/', '', $dados['telefone']) : '';

        if ($nome === '' || $email === '' || $telefone === '') {
            throw new InvalidArgumentException('Nome, telefone e e-mail são obrigatórios para o cadastro do cliente.');
        }
        if (tamanho_texto($nome) < 2 || tamanho_texto($nome) > 120) {
            throw new InvalidArgumentException('O nome deve ter entre 2 e 120 caracteres.');
        }
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            throw new InvalidArgumentException('Telefone inválido. Informe o DDD e o número, ex.: (11) 99999-9999.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        $c = new Cliente();
        $c->setNome($nome);
        $c->setTelefone($telefone);
        $c->setEmail($email);
        return $c;
    }
}
