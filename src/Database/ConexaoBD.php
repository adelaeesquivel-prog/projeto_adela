<?php
namespace LeoBarbas\Database;

use PDO;
use PDOException;
use Exception;

final class ConexaoBD
{
    private function __construct() {}

    private static PDO $conexao;
    private static string $servidor = "localhost";
    private static string $usuario = "root";
    private static string $senha = "senacpenha";
    private static string $banco = "leo_barbas";

    public static function getConexao(): PDO
    {
        if (!isset(self::$conexao)) {
            try {
                self::$conexao = new PDO(
                    "mysql:host=" . self::$servidor . ";port=3307;dbname=" . self::$banco . ";charset=utf8mb4",
                    self::$usuario,
                    self::$senha
);
                self::$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new Exception(
                    "Erro ao conectar ao banco de dados. Contate o suporte.",
                    0,
                    $e
                );
            }
        }

        return self::$conexao;
    }
}