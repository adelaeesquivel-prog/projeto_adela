<?php
namespace LeoBarbas\Services;

use Exception;
use LeoBarbas\Database\ConexaoBD;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Agendamento;
use PDO;
use PDOException;

final class AgendamentoService
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, data, horario, observacao, status, data_criacao, cliente_id, admin_id, servico_id
                FROM agendamento
                ORDER BY data DESC, horario DESC";

        try {
            $consulta = $this->conexao->query($sql);
            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao listar agendamentos");
        }
    }

    public function inserir(Agendamento $agendamento): void
    {
        $sql = "INSERT INTO agendamento (data, horario, observacao, status, cliente_id, admin_id, servico_id)
                VALUES (:data, :horario, :observacao, :status, :cliente_id, :admin_id, :servico_id)";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":data", $agendamento->getData());
            $consulta->bindValue(":horario", $agendamento->getHorario());
            $consulta->bindValue(":observacao", $agendamento->getObservacao());
            $consulta->bindValue(":status", $agendamento->getStatus());
            $consulta->bindValue(":cliente_id", $agendamento->getClienteId());
            $consulta->bindValue(":admin_id", $agendamento->getAdminId());
            $consulta->bindValue(":servico_id", $agendamento->getServicoId());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao inserir agendamento");
        }
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT id, data, horario, observacao, status, data_criacao, cliente_id, admin_id, servico_id
                FROM agendamento
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
            return $consulta->fetch() ?: null;
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao buscar agendamento por ID.");
        }
    }

    public function atualizar(Agendamento $agendamento): void
    {
        $sql = "UPDATE agendamento
                SET data = :data, horario = :horario, observacao = :observacao, status = :status,
                    cliente_id = :cliente_id, admin_id = :admin_id, servico_id = :servico_id
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":data", $agendamento->getData());
            $consulta->bindValue(":horario", $agendamento->getHorario());
            $consulta->bindValue(":observacao", $agendamento->getObservacao());
            $consulta->bindValue(":status", $agendamento->getStatus());
            $consulta->bindValue(":cliente_id", $agendamento->getClienteId());
            $consulta->bindValue(":admin_id", $agendamento->getAdminId());
            $consulta->bindValue(":servico_id", $agendamento->getServicoId());
            $consulta->bindValue(":id", $agendamento->getId());
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao atualizar agendamento");
        }
    }

    public function excluir(int $id): void
    {
        $sql = "DELETE FROM agendamento WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":id", $id);
            $consulta->execute();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Erro ao excluir agendamento");
        }
    }
}