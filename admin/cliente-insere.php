<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Cliente;
use LeoBarbas\Services\ClienteService;

Auth::exigirAdmin();

$mensagemErro = "";
$mensagemSucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim((string) ($_POST["nome"] ?? ""));
    $email = mb_strtolower(trim((string) ($_POST["email"] ?? "")));
    $senha = (string) ($_POST["senha"] ?? "");
    $telefone = trim((string) ($_POST["telefone"] ?? ""));

    if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === "" || $telefone === "") {
        $mensagemErro = "Preencha nome, e-mail válido, senha e telefone.";
    } else {
        try {
            $clienteService = new ClienteService();
            $existente = $clienteService->buscarPorEmail($email);

            if ($existente !== null) {
                $mensagemErro = "Este e-mail já está cadastrado para outro cliente.";
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $cliente = new Cliente($nome, $email, $senhaHash, $telefone);

                $clienteService->inserir($cliente);
                $mensagemSucesso = "Cliente cadastrado com sucesso!";
            }
        } catch (Throwable $e) {
            Utils::registrarLog($e);
            $mensagemErro = "Não foi possível cadastrar o cliente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Novo Cliente</h1>
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

        <form method="post">
            <div>
                <label for="nome">Nome:</label><br>
                <input type="text" id="nome" name="nome" required>
            </div>
            <br>
            <div>
                <label for="email">E-mail:</label><br>
                <input type="email" id="email" name="email" required>
            </div>
            <br>
            <div>
                <label for="telefone">Telefone:</label><br>
                <input type="text" id="telefone" name="telefone" required>
            </div>
            <br>
            <div>
                <label for="senha">Senha:</label><br>
                <input type="password" id="senha" name="senha" required>
            </div>
            <br>
            <button type="submit">Salvar</button>
        </form>
    </main>

    <footer>
        <p>Léo Barbas</p>
    </footer>
</body>
</html>