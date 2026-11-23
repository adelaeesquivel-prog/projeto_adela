<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Models\Servico;
use LeoBarbas\Services\ServicoService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$mensagemErro = "";
$mensagemSucesso = "";
$servicoAtual = null;

try {
    $servicoService = new ServicoService();

    if ($id <= 0) {
        header("Location: servico.php");
        exit;
    }

    $servicoAtual = $servicoService->buscarPorId($id);
    if ($servicoAtual === null) {
        header("Location: servico.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome = trim((string) ($_POST["nome"] ?? ""));
        $precoTexto = str_replace(",", ".", trim((string) ($_POST["preco"] ?? "0")));
        $preco = (float) $precoTexto;
        $duracao = trim((string) ($_POST["duracao"] ?? ""));
        $descricaoTexto = trim((string) ($_POST["descricao"] ?? ""));
        $descricao = $descricaoTexto !== "" ? $descricaoTexto : null;

        if ($nome === "" || $preco < 0 || $duracao === "") {
            $mensagemErro = "Preencha nome, preço válido e duração.";
        } else {
            $servico = new Servico($nome, $preco, $duracao, $descricao, $id);

            $servicoService->atualizar($servico);
            $servicoAtual = $servicoService->buscarPorId($id);
            $mensagemSucesso = "Serviço atualizado com sucesso!";
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
    <title>Atualizar Serviço | Admin — Léo Barbas</title>
    <link rel="stylesheet" href="../site-pages.css">
</head>
<body>
    <header>
        <h1>Atualizar Serviço</h1>
        <nav>
            <a href="servico.php">Voltar para lista</a>
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

        <?php if ($servicoAtual !== null): ?>
            <form method="post">
                <input type="hidden" name="id" value="<?= Utils::escaparHtml($servicoAtual["id"]) ?>">

                <div>
                    <label for="nome">Nome:</label><br>
                    <input type="text" id="nome" name="nome" value="<?= Utils::escaparHtml($servicoAtual["nome"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="preco">Preço:</label><br>
                    <input type="text" id="preco" name="preco" value="<?= Utils::escaparHtml((string) $servicoAtual["preco"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="duracao">Duração:</label><br>
                    <input type="text" id="duracao" name="duracao" value="<?= Utils::escaparHtml($servicoAtual["duracao"]) ?>" required>
                </div>
                <br>
                <div>
                    <label for="descricao">Descrição (opcional):</label><br>
                    <textarea id="descricao" name="descricao" rows="3"><?= Utils::escaparHtml((string) ($servicoAtual["descricao"] ?? "")) ?></textarea>
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