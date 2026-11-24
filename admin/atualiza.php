<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Cliente;
use LeoBarbas\Services\ClienteService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$mensagemErro = "";
$mensagemSucesso = "";
$clienteAtual = null;

try {
    $clienteService = new ClienteService();

    if ($id <= 0) {
        header("Location: cliente.php");
        exit;
    }

    $clienteAtual = $clienteService->buscarPorId($id);
    if ($clienteAtual === null) {
        header("Location: cliente.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome = trim((string) ($_POST["nome"] ?? ""));
        $email = mb_strtolower(trim((string) ($_POST["email"] ?? "")));
        $senha = (string) ($_POST["senha"] ?? "");
        $telefone = trim((string) ($_POST["telefone"] ?? ""));

        if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefone === "") {
            $mensagemErro = "Preencha nome, e-mail válido e telefone.";
        } else {
            $existente = $clienteService->buscarPorEmail($email);
            if ($existente !== null && (int) $existente["id"] !== $id) {
                $mensagemErro = "Este e-mail já está em uso por outro cliente.";
            } else {
                $senhaHash = $senha !== ""
                    ? password_hash($senha, PASSWORD_DEFAULT)
                    : (string) $clienteAtual["senha"];

                $cliente = new Cliente($nome, $email, $senhaHash, $telefone, $id);

                $clienteService->atualizar($cliente);
                $clienteAtual = $clienteService->buscarPorId($id);
                $mensagemSucesso = "Cliente atualizado com sucesso!";
            }
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
    <title>Atualizar Cliente | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Atualizar Cliente</h1>
        <nav>
            <a href="cliente.php">Voltar para lista</a>
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

        <?php if ($clienteAtual !== null): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= Utils::escaparHtml($clienteAtual["id"]) ?>">

                <div>
                    <label for="nome">Nome:</label><br>
                    <input type="text" id="nome" name="nome" value="<?= Utils::escaparHtml($clienteAtual["nome"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="email">E-mail:</label><br>
                    <input type="email" id="email" name="email" value="<?= Utils::escaparHtml($clienteAtual["email"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="telefone">Telefone:</label><br>
                    <input type="text" id="telefone" name="telefone" value="<?= Utils::escaparHtml($clienteAtual["telefone"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="senha">Nova senha (deixe em branco para manter a atual):</label><br>
                    <input type="password" id="senha" name="senha">
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