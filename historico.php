<?php
require_once __DIR__ . "/vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AgendamentoService;

$paginaAtual = 'historico';
$clienteLogado = Auth::usuarioEhCliente() && !empty($_SESSION['id']);
$agendamentos = [];
$erro = null;

if ($clienteLogado) {
    try {
        $agendamentoService = new AgendamentoService();
        $agendamentos = $agendamentoService->listarPorCliente((int) $_SESSION['id']);
    } catch (Throwable $e) {
        Utils::registrarLog($e);
        $erro = "Não foi possível carregar seu histórico. Tente novamente mais tarde.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de agendamentos | Léo Barbas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="site-pages.css">
    <script src="account.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>

    <main class="intro-section">
        <div class="page-heading">
            <span class="eyebrow">ÁREA DO CLIENTE</span>
            <h1>Histórico de agendamentos</h1>
            <p>Acompanhe os horários e serviços reservados pela sua conta.</p>
        </div>

        <?php if (!$clienteLogado): ?>
            <section class="feature history-empty">
                <h2>Entre na sua conta</h2>
                <p>Faça login pelo ícone de perfil no topo para acessar seu histórico.</p>
            </section>
        <?php elseif ($erro !== null): ?>
            <p class="aviso" role="alert"><?= Utils::escaparHtml($erro) ?></p>
        <?php elseif (empty($agendamentos)): ?>
            <section class="feature history-empty">
                <h2>Nenhum agendamento encontrado</h2>
                <p>Quando você fizer uma reserva, ela aparecerá aqui.</p>
                <a class="cta" href="agenda.php">Agendar um horário</a>
            </section>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($agendamentos as $agendamento): ?>
                    <article class="history-item">
                        <h2><?= Utils::escaparHtml((string) $agendamento['servico_nome']) ?></h2>
                        <p><strong>Data:</strong> <?= Utils::escaparHtml(date('d/m/Y', strtotime((string) $agendamento['data']))) ?></p>
                        <p><strong>Horário:</strong> <?= Utils::escaparHtml(substr((string) $agendamento['horario'], 0, 5)) ?></p>
                        <p><strong>Valor:</strong> R$ <?= Utils::escaparHtml(number_format((float) $agendamento['servico_preco'], 2, ',', '.')) ?></p>
                        <p><strong>Status:</strong> <?= Utils::escaparHtml(ucfirst((string) $agendamento['status'])) ?></p>
                        <?php if (!empty($agendamento['observacao'])): ?>
                            <p><strong>Observação:</strong> <?= Utils::escaparHtml((string) $agendamento['observacao']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="cta" href="agenda.php">Fazer outro agendamento</a>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
