<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ClienteService;

Auth::exigirAdmin();

$clientes = [];
$mensagemErro = "";

try {
    $clienteService = new ClienteService();
    $clientes = $clienteService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível carregar a lista de clientes.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Clientes</h1>
        <nav>
            <a href="index.php">Painel</a>
            <a href="cliente-insere.php">Novo cliente</a>
            <a href="../index.php">Área pública</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php elseif (empty($clientes)): ?>
            <p>Nenhum cliente cadastrado.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= Utils::escaparHtml($cliente["id"]) ?></td>
                            <td><?= Utils::escaparHtml($cliente["nome"]) ?></td>
                            <td><?= Utils::escaparHtml($cliente["email"]) ?></td>
                            <td><?= Utils::escaparHtml($cliente["telefone"]) ?></td>
                            <td>
                                <a href="cliente-atualiza.php?id=<?= Utils::escaparHtml($cliente["id"]) ?>">Editar</a>
                                |
                                <a href="cliente-exclui.php?id=<?= Utils::escaparHtml($cliente["id"]) ?>" onclick="return confirm('Deseja realmente excluir este cliente?');">Excluir</a>
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