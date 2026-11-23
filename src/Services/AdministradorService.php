<?php
namespace LeoBarbas\Services;

use Exception;
use LeoBarbas\Database\ConexaoBD;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Administrador;
use PDO;
use PDOException;

final class AdministradorService
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, nome, email FROM administrador ORDER BY nome";

        try {
            $consulta = $this->conexao->query($sql);
            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar administradores");
        }
    }

    public function inserir(Administrador $administrador): void
    {
        $sql = "INSERT INTO administrador (nome, email, senha)
                VALUES (:nome, :email, :senha)";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $administrador->getNome());
            $consulta->bindValue(":email", $administrador->getEmail());
            $consulta->bindValue(":senha", $administrador->getSenha());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao inserir administrador");
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id, nome, email, senha FROM administrador WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar administrador por ID.");
        }
    }

    public function atualizar(Administrador $administrador): void
    {
        $sql = "UPDATE administrador
                SET nome = :nome, email = :email, senha = :senha
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $administrador->getNome());
            $consulta->bindValue(":email", $administrador->getEmail());
            $consulta->bindValue(":senha", $administrador->getSenha());
            $consulta->bindValue(":id", $administrador->getId());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar administrador");
        }
    }

    public function excluir(int $id): void
    {
        $sql = "DELETE FROM administrador WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao excluir administrador");
        }
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT id, nome, email, senha FROM administrador WHERE email = :email";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":email", $email);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar administrador por e-mail");
        }
    }
}