<?php
require_once dirname(dirname(__FILE__)) . '/model/Fornecedor.php';

class FornecedorFactory
{
    public static function criarDeArray(array $dados)
    {
        $nome = isset($dados['nome']) ? trim($dados['nome']) : '';
        $email = isset($dados['email']) ? strtolower(trim($dados['email'])) : '';
        $telefone = isset($dados['telefone']) ? preg_replace('/\D/', '', $dados['telefone']) : '';

        if ($nome === '') {
            throw new InvalidArgumentException('Informe o nome do fornecedor.');
        }
        if (tamanho_texto($nome) > 255) {
            throw new InvalidArgumentException('O nome deve ter no máximo 255 caracteres.');
        }
        if ($telefone !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
            throw new InvalidArgumentException('Telefone inválido. Informe o DDD e o número.');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        $f = new Fornecedor();
        $f->setNome($nome);
        $f->setTelefone($telefone);
        $f->setEmail($email);
        $f->setCertificado(!empty($dados['certificado'])); // RN10
        return $f;
    }
}
