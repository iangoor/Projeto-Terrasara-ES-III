<?php
class FornecedorDAO extends BaseDAO
{
    public function salvar(Fornecedor $f)
    {
        $this->consultar(
            'INSERT INTO fornecedores (nome, telefone, email, certificado) VALUES (?, ?, ?, ?)',
            array($f->getNome(), $f->getTelefone(), $f->getEmail(), $f->isCertificado() ? 1 : 0)
        );
        return $this->ultimoId();
    }

    public function atualizar(Fornecedor $f)
    {
        $this->consultar(
            'UPDATE fornecedores SET nome=?, telefone=?, email=?, certificado=? WHERE id=?',
            array($f->getNome(), $f->getTelefone(), $f->getEmail(), $f->isCertificado() ? 1 : 0, (int) $f->getId())
        );
    }

    /** Lança PDOException (chave estrangeira) se houver produtos deste fornecedor. */
    public function excluir($id)
    {
        $this->consultar('DELETE FROM fornecedores WHERE id = ?', array((int) $id));
    }

    /** Certificados primeiro (RN10), depois por nome. */
    public function listar()
    {
        $stmt = $this->consultar('SELECT * FROM fornecedores ORDER BY certificado DESC, nome');
        $lista = array();
        while ($row = $stmt->fetch()) {
            $lista[] = $this->montar($row);
        }
        return $lista;
    }

    public function buscarPorId($id)
    {
        $row = $this->consultar('SELECT * FROM fornecedores WHERE id = ?', array((int) $id))->fetch();
        return $row ? $this->montar($row) : null;
    }

    private function montar(array $row)
    {
        $f = new Fornecedor();
        $f->setId((int) $row['id']);
        $f->setNome($row['nome']);
        $f->setTelefone($row['telefone']);
        $f->setEmail($row['email']);
        $f->setCertificado($row['certificado']);
        return $f;
    }
}
