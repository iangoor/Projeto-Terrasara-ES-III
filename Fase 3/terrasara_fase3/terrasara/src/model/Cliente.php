<?php
class Cliente
{
    private $id;
    private $nome;
    private $telefone;
    private $email;

    public function getId()       { return $this->id; }
    public function getNome()     { return $this->nome; }
    public function getTelefone() { return $this->telefone; }
    public function getEmail()    { return $this->email; }

    public function setId($v)       { $this->id = $v; }
    public function setNome($v)     { $this->nome = $v; }
    public function setTelefone($v) { $this->telefone = $v; }
    public function setEmail($v)    { $this->email = $v; }
}
