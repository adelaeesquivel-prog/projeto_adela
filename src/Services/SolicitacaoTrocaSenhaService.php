<?php
namespace LeoBarbas\Services;

use Exception;
use LeoBarbas\Database\ConexaoBD;
use LeoBarbas\Helpers\Utils;
use PDO;
use PDOException;

final class SolicitacaoTrocaSenhaService
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function criar(int $clienteId, string $senhaNovaHash): bool
    {
        try {
            $this->conexao->beginTransaction();

            $cliente = $this->conexao->prepare("SELECT id FROM cliente WHERE id = :id FOR UPDATE");
            $cliente->bindValue(":id", $clienteId, PDO::PARAM_INT);
            $cliente->execute();
            if (!$cliente->fetch()) {
                $this->conexao->rollBack();
                return false;
            }

            $pendente = $this->conexao->prepare(
                "SELECT id FROM solicitacao_troca_senha
                 WHERE cliente_id = :cliente_id AND status = 'pendente'
                 LIMIT 1"
            );
            $pendente->bindValue(":cliente_id", $clienteId, PDO::PARAM_INT);
            $pendente->execute();
            if ($pendente->fetch()) {
                $this->conexao->rollBack();
                return false;
            }

            $inserir = $this->conexao->prepare(
                "INSERT INTO solicitacao_troca_senha (cliente_id, senha_nova_hash)
                 VALUES (:cliente_id, :senha_nova_hash)"
            );
            $inserir->bindValue(":cliente_id", $clienteId, PDO::PARAM_INT);
            $inserir->bindValue(":senha_nova_hash", $senhaNovaHash);
            $inserir->execute();
            $this->conexao->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            Utils::registrarLog($e);
            throw new Exception("Não foi possível registrar o pedido de troca de senha.");
        }
    }

    public function listarPendentes(): array
    {
        try {
            $consulta = $this->conexao->query(
                "SELECT s.id, s.criado_em, c.nome, c.email, c.telefone
                 FROM solicitacao_troca_senha s
                 INNER JOIN cliente c ON c.id = s.cliente_id
                 WHERE s.status = 'pendente'
                 ORDER BY s.criado_em ASC"
            );
            return $consulta->fetchAll();
        } catch (PDOException $e) {
            Utils::registrarLog($e);
            throw new Exception("Não foi possível carregar os pedidos de troca de senha.");
        }
    }

    public function resolver(int $solicitacaoId, int $administradorId, bool $aprovar): bool
    {
        try {
            $this->conexao->beginTransaction();
            $consulta = $this->conexao->prepare(
                "SELECT cliente_id, senha_nova_hash
                 FROM solicitacao_troca_senha
                 WHERE id = :id AND status = 'pendente'
                 FOR UPDATE"
            );
            $consulta->bindValue(":id", $solicitacaoId, PDO::PARAM_INT);
            $consulta->execute();
            $solicitacao = $consulta->fetch();
            if (!$solicitacao) {
                $this->conexao->rollBack();
                return false;
            }

            if ($aprovar) {
                $atualizarSenha = $this->conexao->prepare(
                    "UPDATE cliente SET senha = :senha WHERE id = :cliente_id"
                );
                $atualizarSenha->bindValue(":senha", $solicitacao["senha_nova_hash"]);
                $atualizarSenha->bindValue(":cliente_id", $solicitacao["cliente_id"], PDO::PARAM_INT);
                $atualizarSenha->execute();
            }

            $status = $aprovar ? "aprovada" : "recusada";
            $resolver = $this->conexao->prepare(
                "UPDATE solicitacao_troca_senha
                 SET status = :status, senha_nova_hash = NULL, administrador_id = :administrador_id,
                     resolvido_em = CURRENT_TIMESTAMP
                 WHERE id = :id AND status = 'pendente'"
            );
            $resolver->bindValue(":status", $status);
            $resolver->bindValue(":administrador_id", $administradorId, PDO::PARAM_INT);
            $resolver->bindValue(":id", $solicitacaoId, PDO::PARAM_INT);
            $resolver->execute();

            $this->conexao->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            Utils::registrarLog($e);
            throw new Exception("Não foi possível processar o pedido de troca de senha.");
        }
    }
}
