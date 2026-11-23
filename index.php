<?php
require_once __DIR__ . "/vendor/autoload.php";

use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

$paginaAtual = 'index';
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
    <title>Léo Barbas</title>
    <link rel="stylesheet" href="site-pages.css">
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>

    <main>
        <?php if ($erro !== null): ?>
            <p><?= Utils::escaparHtml($erro) ?></p>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>