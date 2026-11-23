<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Agendamento;
use LeoBarbas\Services\AgendamentoService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$mensagemErro = "";
$mensagemSucesso = "";
$agendamentoAtual = null;

$statusPermitidos = ["pendente", "confirmado", "concluido", "cancelado"];

try {
    $agendamentoService = new AgendamentoService();

    if ($id <= 0) {
        header("Location: agendamento.php");
        exit;
    }

    $agendamentoAtual = $agendamentoService->buscarPorId($id);
    if ($agendamentoAtual === null) {
        header("Location: agendamento.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = trim((string) ($_POST["data"] ?? ""));
        $horario = trim((string) ($_POST["horario"] ?? ""));
        $clienteId = (int) ($_POST["cliente_id"] ?? 0);
        $servicoId = (int) ($_POST["servico_id"] ?? 0);
        $status = trim((string) ($_POST["status"] ?? "pendente"));
        $observacaoTexto = trim((string) ($_POST["observacao"] ?? ""));
        $observacao = $observacaoTexto !== "" ? $observacaoTexto : null;
        $adminIdRaw = (int) ($_POST["admin_id"] ?? 0);
        $adminId = $adminIdRaw > 0 ? $adminIdRaw : null;

        if ($data === "" || $horario === "" || $clienteId <= 0 || $servicoId <= 0 || !in_array($status, $statusPermitidos, true)) {
            $mensagemErro = "Preencha data, horário, cliente ID, serviço ID e um status válido.";
        } else {
            $agendamento = new Agendamento(
                $data,
                $horario,
                $clienteId,
                $servicoId,
                $status,
                $observacao,
                $agendamentoAtual["data_criacao"] ?? null,
                $adminId,
                $id
            );

            $agendamentoService->atualizar($agendamento);
            $agendamentoAtual = $agendamentoService->buscarPorId($id);
            $mensagemSucesso = "Agendamento atualizado com sucesso!";
        }
    }
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível processar a atualização.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Agendamento | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Atualizar Agendamento</h1>
        <nav>
            <a href="agendamento.php">Voltar para lista</a>
            <a href="index.php">Painel</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php endif; ?>

        <?php if ($mensagemSucesso !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemSucesso) ?></p>
        <?php endif; ?>

        <?php if ($agendamentoAtual !== null): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= Utils::escaparHtml($agendamentoAtual["id"]) ?>">

                <div>
                    <label for="data">Data:</label><br>
                    <input type="date" id="data" name="data" value="<?= Utils::escaparHtml($agendamentoAtual["data"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="horario">Horário:</label><br>
                    <input type="time" id="horario" name="horario" value="<?= Utils::escaparHtml($agendamentoAtual["horario"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="cliente_id">ID do Cliente:</label><br>
                    <input type="number" id="cliente_id" name="cliente_id" min="1" value="<?= Utils::escaparHtml($agendamentoAtual["cliente_id"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="servico_id">ID do Serviço:</label><br>
                    <input type="number" id="servico_id" name="servico_id" min="1" value="<?= Utils::escaparHtml($agendamentoAtual["servico_id"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="status">Status:</label><br>
                    <select id="status" name="status">
                        <?php foreach ($statusPermitidos as $st): ?>
                            <option value="<?= Utils::escaparHtml($st) ?>" <?= ($agendamentoAtual["status"] === $st) ? "selected" : "" ?>>
                                <?= Utils::escaparHtml($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br>
                <div>
                    <label for="admin_id">ID do Admin (opcional):</label><br>
                    <input type="number" id="admin_id" name="admin_id" min="1" value="<?= Utils::escaparHtml((string) ($agendamentoAtual["admin_id"] ?? "")) ?>">
                </div>
                <br>
                <div>
                    <label for="observacao">Observação (opcional):</label><br>
                    <textarea id="observacao" name="observacao" rows="3"><?= Utils::escaparHtml((string) ($agendamentoAtual["observacao"] ?? "")) ?></textarea>
                </div>
                <br>
                <button type="submit">Atualizar</button>
            </form>
        <?php endif; ?>
    </main>

    <footer>
        <p>Léo Barbas</p>
    </footer>
</body>
</html>