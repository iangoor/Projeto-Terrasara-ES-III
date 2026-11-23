<?php
class Usuario
{
    private $id;
    private $nome;
    private $email;
    private $senhaHash;
    private $perfil;
    private $ativo = true;
    private $tentativasFalhas = 0;
    private $bloqueadoAte; // 'Y-m-d H:i:s' ou null

    public function getId()               { return $this->id; }
    public function getNome()             { return $this->nome; }
    public function getEmail()            { return $this->email; }
    public function getSenhaHash()        { return $this->senhaHash; }
    public function getPerfil()           { return $this->perfil; }
    public function isAtivo()             { return (bool) $this->ativo; }
    public function getTentativasFalhas() { return (int) $this->tentativasFalhas; }
    public function getBloqueadoAte()     { return $this->bloqueadoAte; }

    public function setId($v)               { $this->id = $v; }
    public function setNome($v)             { $this->nome = $v; }
    public function setEmail($v)            { $this->email = $v; }
    public function setSenhaHash($v)        { $this->senhaHash = $v; }
    public function setPerfil($v)           { $this->perfil = $v; }
    public function setAtivo($v)            { $this->ativo = (bool) $v; }
    public function setTentativasFalhas($v) { $this->tentativasFalhas = (int) $v; }
    public function setBloqueadoAte($v)     { $this->bloqueadoAte = $v; }

    /** Gera o hash seguro (bcrypt) de uma senha — password_hash existe desde o PHP 5.5. */
    public static function gerarHash($senha)
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    public function verificarSenha($senha)
    {
        return password_verify($senha, (string) $this->senhaHash);
    }

    /** $agora: timestamp Unix. */
    public function estaBloqueado($agora = null)
    {
        $agora = ($agora === null) ? time() : $agora;
        return $this->bloqueadoAte !== null && $this->bloqueadoAte !== ''
            && strtotime($this->bloqueadoAte) > $agora;
    }
}
