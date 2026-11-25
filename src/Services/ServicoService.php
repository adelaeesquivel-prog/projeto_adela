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
    private const SERVICOS_PADRAO = [
        [
            "nome" => "Corte de cabelo masculino",
            "descricao" => "Corte moderno ou tradicional com acabamento impecável.",
            "preco" => 30.00,
            "duracao" => "30 min",
        ],
        [
            "nome" => "Barba completa",
            "descricao" => "Modelagem, alinhamento e hidratação para a barba.",
            "preco" => 25.00,
            "duracao" => "30 min",
        ],
        [
            "nome" => "Corte + Barba",
            "descricao" => "Combo completo para renovar o visual em um só atendimento.",
            "preco" => 50.00,
            "duracao" => "60 min",
        ],
        [
            "nome" => "Sobrancelha",
            "descricao" => "Limpeza e alinhamento natural para valorizar o olhar.",
            "preco" => 15.00,
            "duracao" => "15 min",
        ],
        [
            "nome" => "Corte infantil",
            "descricao" => "Atendimento paciente e cuidadoso para os pequenos.",
            "preco" => 28.00,
            "duracao" => "30 min",
        ],
        [
            "nome" => "Acabamento / Pezinho",
            "descricao" => "Manutenção rápida nas laterais, nuca e contornos.",
            "preco" => 15.00,
            "duracao" => "15 min",
        ],
    ];

    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = ConexaoBD::getConexao();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT id, nome, descricao, preco, duracao
        FROM servico
        ORDER BY nome";

        try {
            $consulta = $this->conexao->query($sql);
            $servicos = $consulta->fetchAll();

            if (empty($servicos)) {
                foreach (self::SERVICOS_PADRAO as $servico) {
                    $this->inserir(new Servico(
                        $servico["nome"],
                        $servico["preco"],
                        $servico["duracao"],
                        $servico["descricao"]
                    ));
                }

                $consulta = $this->conexao->query($sql);
                $servicos = $consulta->fetchAll();
            }

            return $servicos;
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
        $sql = "SELECT id, nome, descricao, preco FROM servico WHERE id = :id";

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
                SET nome = :nome, descricao = :descricao, preco = :preco
                WHERE id = :id";

        try {
            $consulta = $this->conexao->prepare($sql);
            $consulta->bindValue(":nome", $servico->getNome());
            $consulta->bindValue(":descricao", $servico->getDescricao());
            $consulta->bindValue(":preco", $servico->getPreco());
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