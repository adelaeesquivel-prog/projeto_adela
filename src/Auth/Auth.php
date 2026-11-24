<?php
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

        $_SESSION['id'] = $id;
        $_SESSION['nome'] = $nome;
        $_SESSION['tipo'] = $tipo;
    }

    public static function logout(): void
    {
        self::iniciarSessao();
        self::encerrarSessao();
        header("location:index.php?logout");
        exit;
    }

    public static function usuarioEhAdmin(): bool
    {
        self::iniciarSessao();

        return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
    }

    public static function usuarioEhCliente(): bool
    {
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
    }
}