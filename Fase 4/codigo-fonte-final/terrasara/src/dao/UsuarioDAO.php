<?php
class UsuarioDAO extends BaseDAO
{
    public function salvar(Usuario $u)
    {
        if ($this->buscarPorEmail($u->getEmail()) !== null) {
            throw new InvalidArgumentException('Já existe um usuário com este e-mail.');
        }
        $this->consultar(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, tentativas_falhas, bloqueado_ate, criado_em)
             VALUES (?, ?, ?, ?, 1, 0, NULL, ?)',
            array($u->getNome(), $u->getEmail(), $u->getSenhaHash(), $u->getPerfil(), date('Y-m-d H:i:s'))
        );
        return $this->ultimoId();
    }

    public function buscarPorEmail($email)
    {
        $row = $this->consultar('SELECT * FROM usuarios WHERE email = ?', array(strtolower(trim($email))))->fetch();
        return $row ? $this->montar($row) : null;
    }

    public function buscarPorId($id)
    {
        $row = $this->consultar('SELECT * FROM usuarios WHERE id = ?', array((int) $id))->fetch();
        return $row ? $this->montar($row) : null;
    }

    public function listar()
    {
        $stmt = $this->consultar('SELECT * FROM usuarios ORDER BY nome');
        $lista = array();
        while ($row = $stmt->fetch()) {
            $lista[] = $this->montar($row);
        }
        return $lista;
    }

    /** Troca a senha e desbloqueia a conta. */
    public function atualizarSenha($id, $novoHash)
    {
        $this->consultar(
            'UPDATE usuarios SET senha_hash = ?, tentativas_falhas = 0, bloqueado_ate = NULL WHERE id = ?',
            array($novoHash, (int) $id)
        );
    }

    public function registrarFalha($id, $tentativas, $bloqueadoAte)
    {
        $this->consultar(
            'UPDATE usuarios SET tentativas_falhas = ?, bloqueado_ate = ? WHERE id = ?',
            array((int) $tentativas, $bloqueadoAte, (int) $id)
        );
    }

    public function zerarFalhas($id)
    {
        $this->consultar('UPDATE usuarios SET tentativas_falhas = 0, bloqueado_ate = NULL WHERE id = ?', array((int) $id));
    }

    public function definirAtivo($id, $ativo)
    {
        $this->consultar('UPDATE usuarios SET ativo = ? WHERE id = ?', array($ativo ? 1 : 0, (int) $id));
    }

    private function montar(array $row)
    {
        $u = new Usuario();
        $u->setId((int) $row['id']);
        $u->setNome($row['nome']);
        $u->setEmail($row['email']);
        $u->setSenhaHash($row['senha_hash']);
        $u->setPerfil($row['perfil']);
        $u->setAtivo($row['ativo']);
        $u->setTentativasFalhas($row['tentativas_falhas']);
        $u->setBloqueadoAte($row['bloqueado_ate']);
        return $u;
    }
}
