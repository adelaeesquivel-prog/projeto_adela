<?php
require_once __DIR__ . "/vendor/autoload.php";

use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

$paginaAtual = 'home';
$servicos = [];
$erro = null;

try {
    $servicoService = new ServicoService();
    $servicos = $servicoService->listarTodos();
} catch (Throwable $e) {
    $erro = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Léo Barbas | Início</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="site-pages.css">
    <script src="account.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>

    <main>
        <?php if ($erro !== null): ?>
            <p><?= Utils::escaparHtml($erro) ?></p>
        <?php endif; ?>

        <section class="hero">
            <div class="hero-content">
                <span class="eyebrow">BARBEARIA • ESTILO • PERSONALIDADE</span>
                <h1>Seu visual em boas mãos.</h1>
                <p>Um bom corte faz a diferença. Venha cuidar do seu estilo com a equipe Léo Barbas.</p>
                <a class="cta" href="agenda.php">Agende seu horário</a>
                <a class="secondary-link" href="servicos.php">Conheça nossos serviços</a>
            </div>
        </section>

        <section class="intro-section">
            <span class="eyebrow">BEM-VINDO À LÉO BARBAS</span>
            <h2>Mais que um corte, uma experiência.</h2>
            <p>Preparamos um espaço para você renovar o visual, cuidar da barba e sair daqui com a autoestima em dia.</p>
            <div class="feature-grid">
                <article class="feature">
                    <span class="feature-icon">✂</span>
                    <h3>Estilo e cuidado</h3>
                    <p>Serviços pensados para valorizar seu visual.</p>
                </article>
                <article class="feature">
                    <span class="feature-icon">◷</span>
                    <h3>Agendamento fácil</h3>
                    <p>Escolha o serviço, a data e o horário pela internet.</p>
                </article>
                <article class="feature">
                    <span class="feature-icon">✓</span>
                    <h3>Mais organização</h3>
                    <p>Os horários ocupados ficam indisponíveis para evitar conflitos.</p>
                </article>
            </div>
        </section>

        <section class="booking-banner">
            <div>
                <h2>Pronto para renovar o visual?</h2>
                <p>Escolha seu serviço e reserve seu horário.</p>
            </div>
            <a class="cta" href="agenda.php">Fazer agendamento</a>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>