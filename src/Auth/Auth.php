<?php
declare(strict_types=1);

namespace LeoBarbas\Auth;

class Auth
{
    public static function login(int $id, string $nome, string $tipo): void
    {
        $_SESSION['id'] = $id;
        $_SESSION['nome'] = $nome;
        $_SESSION['tipo'] = $tipo;
    }

    public static function logout(): void
    {
        $_SESSION = [];
    }

    public static function usuarioEhAdmin(): bool
    {
        return ($_SESSION['tipo'] ?? '') === 'admin';
    }

    public static function usuarioEhCliente(): bool
    {
        return ($_SESSION['tipo'] ?? '') === 'cliente';
    }
}