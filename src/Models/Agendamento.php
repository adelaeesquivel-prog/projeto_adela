<?php
namespace LeoBarbas\Models;

final class Agendamento
{
    private ?int $id;
    private string $data;
    private string $horario;
    private ?string $observacao;
    private ?string $justificativaFalta;
    private string $status;
    private ?string $dataCriacao;
    private int $clienteId;
    private ?int $adminId;
    private int $servicoId;

    public function __construct(
        string $data,
        string $horario,
        int $clienteId,
        int $servicoId,
        string $status = 'pendente',
        ?string $observacao = null,
        ?string $dataCriacao = null,
        ?int $adminId = null,
        ?int $id = null,
        ?string $justificativaFalta = null
    ) {
        $this->data = $data;
        $this->horario = $horario;
        $this->clienteId = $clienteId;
        $this->servicoId = $servicoId;
        $this->status = $status;
        $this->observacao = $observacao;
        $this->justificativaFalta = $justificativaFalta;
        $this->dataCriacao = $dataCriacao;
        $this->adminId = $adminId;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getHorario(): string
    {
        return $this->horario;
    }

    public function getObservacao(): ?string
    {
        return $this->observacao;
    }

    public function getJustificativaFalta(): ?string
    {
        return $this->justificativaFalta;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDataCriacao(): ?string
    {
        return $this->dataCriacao;
    }

    public function getClienteId(): int
    {
        return $this->clienteId;
    }

    public function getAdminId(): ?int
    {
        return $this->adminId;
    }

    public function getServicoId(): int
    {
        return $this->servicoId;
    }
}