<?php
namespace LeoBarbas\Models;

final class Servico
{
    private ?int $id;
    private string $nome;
    private ?string $descricao;
    private float $preco;
    private string $duracao;

    public function __construct(
        string $nome,
        float $preco,
        string $duracao,
        ?string $descricao = null,
        ?int $id = null
    ) {
        $this->nome = $nome;
        $this->preco = $preco;
        $this->duracao = $duracao;
        $this->descricao = $descricao;
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

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getDuracao(): string
    {
        return $this->duracao;
    }
}