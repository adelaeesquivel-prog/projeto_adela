<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AgendamentoService;
use LeoBarbas\Services\ClienteService;
use LeoBarbas\Services\ServicoService;

Auth::exigirAdmin();

$totalServicos = 0;
$totalClientes = 0;
$totalAgendamentos = 0;
$mensagemErro = "";

try {
    $servicoService = new ServicoService();
    $clienteService = new ClienteService();
    $agendamentoService = new AgendamentoService();

    $totalServicos = count($servicoService->listarTodos());
    $totalClientes = count($clienteService->listarTodos());
    $totalAgendamentos = count($agendamentoService->listarTodos());
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível carregar os dados do painel.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Painel Administrativo — Léo Barbas</h1>
        <p>Olá, <?= Utils::escaparHtml($_SESSION['nome'] ?? 'Administrador') ?></p>
        <nav>
            <a href="../index.php">Área pública</a>
            <a href="../agenda.php">Agenda</a>
            <a href="../servicos.php">Serviços</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php else: ?>
            <section>
                <p>Serviços cadastrados: <strong><?= Utils::escaparHtml($totalServicos) ?></strong></p>
                <p>Clientes cadastrados: <strong><?= Utils::escaparHtml($totalClientes) ?></strong></p>
                <p>Agendamentos registrados: <strong><?= Utils::escaparHtml($totalAgendamentos) ?></strong></p>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>Léo Barbas</p>
    </footer>
</body>
</html>