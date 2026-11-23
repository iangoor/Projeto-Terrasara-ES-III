<?php
class ClienteDAO extends BaseDAO
{
    const MSG_DUPLICADO = 'Já existe um cliente cadastrado com este e-mail ou telefone.';

    public function salvar(Cliente $c)
    {
        if ($this->existeDuplicado($c->getEmail(), $c->getTelefone(), 0)) {
            throw new InvalidArgumentException(self::MSG_DUPLICADO);
        }
        $this->consultar(
            'INSERT INTO clientes (nome, telefone, email, criado_em) VALUES (?, ?, ?, ?)',
            array($c->getNome(), $c->getTelefone(), $c->getEmail(), date('Y-m-d H:i:s'))
        );
        return $this->ultimoId();
    }

    public function atualizar(Cliente $c)
    {
        if ($this->existeDuplicado($c->getEmail(), $c->getTelefone(), (int) $c->getId())) {
            throw new InvalidArgumentException(self::MSG_DUPLICADO);
        }
        $this->consultar(
            'UPDATE clientes SET nome=?, telefone=?, email=? WHERE id=?',
            array($c->getNome(), $c->getTelefone(), $c->getEmail(), (int) $c->getId())
        );
    }

    /** Lança PDOException (chave estrangeira) se o cliente tem pedidos. */
    public function excluir($id)
    {
        $this->consultar('DELETE FROM clientes WHERE id = ?', array((int) $id));
    }

    public function existeDuplicado($email, $telefone, $ignorarId)
    {
        $stmt = $this->consultar(
            'SELECT id FROM clientes WHERE (email = ? OR telefone = ?) AND id <> ?',
            array($email, $telefone, (int) $ignorarId)
        );
        return $stmt->fetch() !== false;
    }

    public function listar($busca = '')
    {
        $busca = trim($busca);
        if ($busca === '') {
            $stmt = $this->consultar('SELECT * FROM clientes ORDER BY nome');
        } elseif (ctype_digit($busca) && strlen($busca) <= 6) {
            // só número curto: busca pelo ID exato (telefones têm mais dígitos)
            $stmt = $this->consultar('SELECT * FROM clientes WHERE id = ?', array((int) $busca));
        } else {
            $digitos = preg_replace('/\D/', '', $busca);
            $stmt = $this->consultar(
                'SELECT * FROM clientes WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ? ORDER BY nome',
                array('%' . $busca . '%', '%' . $busca . '%', '%' . ($digitos !== '' ? $digitos : $busca) . '%')
            );
        }
        $lista = array();
        while ($row = $stmt->fetch()) {
            $lista[] = $this->montar($row);
        }
        return $lista;
    }

    public function buscarPorId($id)
    {
        $row = $this->consultar('SELECT * FROM clientes WHERE id = ?', array((int) $id))->fetch();
        return $row ? $this->montar($row) : null;
    }

    private function montar(array $row)
    {
        $c = new Cliente();
        $c->setId((int) $row['id']);
        $c->setNome($row['nome']);
        $c->setTelefone($row['telefone']);
        $c->setEmail($row['email']);
        return $c;
    }
}
