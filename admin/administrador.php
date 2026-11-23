<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AdministradorService;

Auth::exigirAdmin();

$administradores = [];
$mensagemErro = "";

try {
    $administradorService = new AdministradorService();
    $administradores = $administradorService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $mensagemErro = "Não foi possível carregar a lista de administradores.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administradores | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>

<body>
    <header>
        <h1>Administradores</h1>
        <nav>
            <a href="index.php">Painel</a>
            <a href="administrador-insere.php">Novo administrador</a>
            <a href="../index.php">Área pública</a>
        </nav>
    </header>

    <main>
        <?php if ($mensagemErro !== ""): ?>
            <p><?= Utils::escaparHtml($mensagemErro) ?></p>
        <?php elseif (empty($administradores)): ?>
            <p>Nenhum administrador cadastrado.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($administradores as $admin): ?>
                        <tr>
                            <td><?= Utils::escaparHtml($admin['id']) ?></td>
                            <td><?= Utils::escaparHtml($admin['nome']) ?></td>
                            <td><?= Utils::escaparHtml($admin['email']) ?></td>
                            <td>
                                <a href="administrador-atualiza.php?id=<?= Utils::escaparHtml($admin['id']) ?>">Editar</a>
                                |
                                <a href="administrador-exclui.php?id=<?= Utils::escaparHtml($admin['id']) ?>" onclick="return confirm('Deseja realmente excluir este administrador?');">Excluir</a>
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