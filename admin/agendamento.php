<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AgendamentoService;

Auth::exigirAdmin();

$agendamentos = [];
$mensagemErro = "";
$rotulosStatus = [
    "pendente" => "Pendente",
    "confirmado" => "Confirmado",
    "concluido" => "Concluído",
    "falta" => "Falta",
    "falta_justificada" => "Falta com justificativa",
    "cancelado" => "Cancelado"
];

try {
    $agendamentoService = new AgendamentoService();
    $agendamentos = $agendamentoService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível carregar a lista de agendamentos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Agendamentos</h1>
        <nav>
            <a href="index.php">Painel</a>
            <a href="agendamento-insere.php">Novo agendamento</a>
            <a href="../index.php">Área pública</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php elseif (empty($agendamentos)): ?>
            <p>Nenhum agendamento cadastrado.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <th>Observação</th>
                        <th>Justificativa</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $item): ?>
                        <tr>
                            <td><?= Utils::escaparHtml($item["id"]) ?></td>
                            <td><?= Utils::escaparHtml($item["data"]) ?></td>
                            <td><?= Utils::escaparHtml($item["horario"]) ?></td>
                            <td><?= Utils::escaparHtml($item["cliente_nome"] ?? "Cliente #{$item['cliente_id']}") ?></td>
                            <td><?= Utils::escaparHtml($item["servico_nome"] ?? "Serviço #{$item['servico_id']}") ?></td>
                            <td><?= Utils::escaparHtml($rotulosStatus[$item["status"]] ?? $item["status"]) ?></td>
                            <td><?= Utils::escaparHtml((string) ($item["observacao"] ?? "")) ?></td>
                            <td><?= Utils::escaparHtml((string) ($item["justificativa_falta"] ?? "")) ?></td>
                            <td>
                                <a href="agendamento-atualiza.php?id=<?= Utils::escaparHtml($item["id"]) ?>">Editar</a>
                                |
                                <a href="agendamento-exclui.php?id=<?= Utils::escaparHtml($item["id"]) ?>" onclick="return confirm('Deseja realmente excluir este agendamento?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <footer>
        <p>Léo Barbas</p>
    </footer>
</body>
</html>