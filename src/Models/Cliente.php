<?php
namespace LeoBarbas\Models;

final class Cliente
{
    private ?int $id;
    private string $nome;
    private string $email;
    private string $senha;
    private string $telefone;

    public function __construct(
        string $nome,
        string $email,
        string $senha,
        string $telefone,
        ?int $id = null
    ) {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->telefone = $telefone;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }
}