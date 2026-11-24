<?php
<<<<<<< HEAD
namespace LeoBarbas\Auth;

final class Auth
{
    private function __construct()
    {
    }

    private static function iniciarSessao(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function encerrarSessao(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function exigirLogin(): void
    {
        self::iniciarSessao();

        if (!isset($_SESSION['id'])) {
            self::encerrarSessao();
            header("location:index.php?acesso_proibido");
            exit;
        }
    }

    public static function login(int $id, string $nome, string $tipo): void
    {
        self::iniciarSessao();

=======
declare(strict_types=1);

namespace LeoBarbas\Auth;

class Auth
{
    public static function login(int $id, string $nome, string $tipo): void
    {
>>>>>>> 1f8d3175f271e11e4fb682f50e57883d2b86f8bd
        $_SESSION['id'] = $id;
        $_SESSION['nome'] = $nome;
        $_SESSION['tipo'] = $tipo;
    }

    public static function logout(): void
    {
<<<<<<< HEAD
        self::iniciarSessao();
        self::encerrarSessao();
        header("location:index.php?logout");
        exit;
=======
        $_SESSION = [];
>>>>>>> 1f8d3175f271e11e4fb682f50e57883d2b86f8bd
    }

    public static function usuarioEhAdmin(): bool
    {
<<<<<<< HEAD
        self::iniciarSessao();

        return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
=======
        return ($_SESSION['tipo'] ?? '') === 'admin';
>>>>>>> 1f8d3175f271e11e4fb682f50e57883d2b86f8bd
    }

    public static function usuarioEhCliente(): bool
    {
<<<<<<< HEAD
        self::iniciarSessao();

        return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'cliente';
    }

    public static function exigirAdmin(): void
    {
        self::exigirLogin();

        if (!self::usuarioEhAdmin()) {
            header("location:index.php?nao_autorizado");
            exit;
        }
=======
        return ($_SESSION['tipo'] ?? '') === 'cliente';
>>>>>>> 1f8d3175f271e11e4fb682f50e57883d2b86f8bd
    }
}