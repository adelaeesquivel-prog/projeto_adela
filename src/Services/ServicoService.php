<?php
namespace LeoBarbas\Services;

use Exception;
use LeoBarbas\Database\ConexaoBD;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Servico;
use PDO;
use PDOException;

final class ServicoService
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, nome, descricao, preco, duracao FROM servico ORDER BY nome";

        try {
            $consulta = $this->conexao->query($sql);
            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar serviços");
        }
    }

    public function inserir(Servico $servico): void
    {
        $sql = "INSERT INTO servico (nome, descricao, preco, duracao)
                VALUES (:nome, :descricao, :preco, :duracao)";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $servico->getNome());
            $consulta->bindValue(":descricao", $servico->getDescricao());
            $consulta->bindValue(":preco", $servico->getPreco());
            $consulta->bindValue(":duracao", $servico->getDuracao());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao inserir serviço");
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id, nome, descricao, preco, duracao FROM servico WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar serviço por ID.");
        }
    }

    public function atualizar(Servico $servico): void
    {
        $sql = "UPDATE servico
                SET nome = :nome, descricao = :descricao, preco = :preco, duracao = :duracao
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $servico->getNome());
            $consulta->bindValue(":descricao", $servico->getDescricao());
            $consulta->bindValue(":preco", $servico->getPreco());
            $consulta->bindValue(":duracao", $servico->getDuracao());
            $consulta->bindValue(":id", $servico->getId());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar serviço");
        }
    }

    public function excluir(int $id): void
    {
        $sql = "DELETE FROM servico WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao excluir serviço");
        }
    }
}