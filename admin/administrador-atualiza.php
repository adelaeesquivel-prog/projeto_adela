<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Administrador;
use LeoBarbas\Services\AdministradorService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$mensagemErro = "";
$mensagemSucesso = "";
$adminAtual = null;

try {
    $administradorService = new AdministradorService();

    if ($id <= 0) {
        header("Location: administrador.php");
        exit;
    }

    $adminAtual = $administradorService->buscarPorId($id);
    if ($adminAtual === null) {
        header("Location: administrador.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome = trim((string) ($_POST["nome"] ?? ""));
        $email = mb_strtolower(trim((string) ($_POST["email"] ?? "")));
        $senha = (string) ($_POST["senha"] ?? "");

        if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagemErro = "Preencha um nome e um e-mail válido.";
        } else {
            $existente = $administradorService->buscarPorEmail($email);
            if ($existente !== null && (int) $existente["id"] !== $id) {
                $mensagemErro = "Este e-mail já está em uso por outro administrador.";
            } else {
                $senhaHash = $senha !== ""
                    ? password_hash($senha, PASSWORD_DEFAULT)
                    : (string) $adminAtual["senha"];

                $administrador = new Administrador($nome, $email, $senhaHash);
                $administrador = new Administrador($nome, $email, $senhaHash, $id);

                $administradorService->atualizar($administrador);
                $adminAtual = $administradorService->buscarPorId($id);
                $mensagemSucesso = "Administrador atualizado com sucesso!";
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
    <title>Atualizar Administrador | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Atualizar Administrador</h1>
        <nav>
            <a href="administrador.php">Voltar para lista</a>
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

        <?php if ($adminAtual !== null): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= Utils::escaparHtml($adminAtual['id']) ?>">

                <div>
                    <label for="nome">Nome:</label><br>
                    <input type="text" id="nome" name="nome" value="<?= Utils::escaparHtml($adminAtual['nome']) ?>" required>
                </div>
                <br>
                <div>
                    <label for="email">E-mail:</label><br>
                    <input type="email" id="email" name="email" value="<?= Utils::escaparHtml($adminAtual['email']) ?>" required>
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