<?php
namespace LeoBarbas\Services;

use Exception;
use LeoBarbas\Database\ConexaoBD;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Cliente;
use PDO;
use PDOException;

final class ClienteService
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, nome, email, telefone FROM cliente ORDER BY nome";

        try {
            $consulta = $this->conexao->query($sql);
            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar clientes");
        }
    }

    public function inserir(Cliente $cliente): void
    {
        $sql = "INSERT INTO cliente (nome, email, senha, telefone)
                VALUES (:nome, :email, :senha, :telefone)";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $cliente->getNome());
            $consulta->bindValue(":email", $cliente->getEmail());
            $consulta->bindValue(":senha", $cliente->getSenha());
            $consulta->bindValue(":telefone", $cliente->getTelefone());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao inserir cliente");
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id, nome, email, senha, telefone FROM cliente WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar cliente por ID.");
        }
    }

    public function atualizar(Cliente $cliente): void
    {
        $sql = "UPDATE cliente
                SET nome = :nome, email = :email, senha = :senha, telefone = :telefone
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $cliente->getNome());
            $consulta->bindValue(":email", $cliente->getEmail());
            $consulta->bindValue(":senha", $cliente->getSenha());
            $consulta->bindValue(":telefone", $cliente->getTelefone());
            $consulta->bindValue(":id", $cliente->getId());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar cliente");
        }
    }

    public function excluir(int $id): void
    {
        $sql = "DELETE FROM cliente WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao excluir cliente");
        }
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT id, nome, email, senha, telefone FROM cliente WHERE email = :email";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":email", $email);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar cliente por e-mail");
        }
    }
}