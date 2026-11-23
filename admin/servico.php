<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

Auth::exigirAdmin();

$servicos = [];
$mensagemErro = "";

try {
    $servicoService = new ServicoService();
    $servicos = $servicoService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível carregar a lista de serviços.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Serviços</h1>
        <nav>
            <a href="index.php">Painel</a>
            <a href="servico-insere.php">Novo serviço</a>
            <a href="../index.php">Área pública</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php elseif (empty($servicos)): ?>
            <p>Nenhum serviço cadastrado.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Preço</th>
                        <th>Duração</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicos as $servico): ?>
                        <tr>
                            <td><?= Utils::escaparHtml($servico["id"]) ?></td>
                            <td><?= Utils::escaparHtml($servico["nome"]) ?></td>
                            <td><?= Utils::escaparHtml((string) ($servico["descricao"] ?? "")) ?></td>
                            <td>R$ <?= Utils::escaparHtml(number_format((float) $servico["preco"], 2, ",", ".")) ?></td>
                            <td><?= Utils::escaparHtml($servico["duracao"]) ?></td>
                            <td>
                                <a href="servico-atualiza.php?id=<?= Utils::escaparHtml($servico["id"]) ?>">Editar</a>
                                |
                                <a href="servico-exclui.php?id=<?= Utils::escaparHtml($servico["id"]) ?>" onclick="return confirm('Deseja realmente excluir este serviço?');">Excluir</a>
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