<?php
namespace LeoBarbas\Helpers;

use InvalidArgumentException;

class Validacoes
{
    private function __construct() {}

    public static function validarNome(string $nome): void
    {
        if (trim($nome) === '') {
            throw new InvalidArgumentException("Nome não pode ser vazio.");
        }
    }

    public static function validarEmail(string $email): void
    {
        if (trim($email) === '') {
            throw new InvalidArgumentException("Email não pode ser vazio.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email inválido.");
        }
    }

    public static function validarSenha(string $senha): void
    {
        if ($senha === '') {
            throw new InvalidArgumentException("Senha não pode ser vazia.");
        }

        if (mb_strlen($senha) < 5) {
            throw new InvalidArgumentException("Senha deve ter no mínimo 5 caracteres.");
        }
    }

    public static function validarServico(int $servicoId): void
    {
        if ($servicoId <= 0) {
            throw new InvalidArgumentException("Selecione um serviço válido.");
        }
    }

    public static function validarData(string $data): void
    {
        if (trim($data) === '') {
            throw new InvalidArgumentException("Data não pode ser vazia.");
        }
    }

    public static function validarHorario(string $horario): void
    {
        if (trim($horario) === '') {
            throw new InvalidArgumentException("Horário não pode ser vazio.");
        }
    }
}