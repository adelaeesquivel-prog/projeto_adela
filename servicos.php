<?php
require_once __DIR__ . "/vendor/autoload.php";

use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

$paginaAtual = 'servicos';
$servicos = [];

try {
    $servicoService = new ServicoService();
    $servicos = $servicoService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
}

// Caso a tabela servico ainda esteja vazia no banco, exibe os 6 cards padrão
if (empty($servicos)) {
    $servicos = [
        [
            "id" => 1,
            "nome" => "Corte de cabelo masculino",
            "descricao" => "Corte moderno ou tradicional com acabamento impecável.",
            "preco" => 30.00,
            "icone" => "✂"
        ],
        [
            "id" => 2,
            "nome" => "Barba completa",
            "descricao" => "Modelagem, alinhamento e hidratação para a barba.",
            "preco" => 25.00,
            "icone" => "✂"
        ],
        [
            "id" => 3,
            "nome" => "Corte + Barba",
            "descricao" => "Combo completo para renovar o visual em um só atendimento.",
            "preco" => 50.00,
            "icone" => "★"
        ],
        [
            "id" => 4,
            "nome" => "Sobrancelha",
            "descricao" => "Limpeza e alinhamento natural para valorizar o olhar.",
            "preco" => 15.00,
            "icone" => "✓"
        ],
        [
            "id" => 5,
            "nome" => "Corte infantil",
            "descricao" => "Atendimento paciente e cuidadoso para os pequenos.",
            "preco" => 28.00,
            "icone" => "✂"
        ],
        [
            "id" => 6,
            "nome" => "Acabamento / Pezinho",
            "descricao" => "Manutenção rápida nas laterais, nuca e contornos.",
            "preco" => 15.00,
            "icone" => "◷"
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços | Léo Barbas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="site-pages.css">
    <script src="account.js" defer></script>
    <script src="servicos.js" defer></script>
</head>
<body>
    <?php require __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="intro-section">
            <span class="eyebrow">NOSSOS SERVIÇOS</span>
            <h1>Escolha o cuidado ideal para o seu visual</h1>
            <p>Selecione um serviço abaixo para conferir o valor ou seguir para o agendamento</p>

            <div id="cardsServicos" class="feature-grid">
                <?php foreach ($servicos as $servico): ?>
                    <?php
                    $id = (string) ($servico["id"] ?? "");
                    $nome = (string) ($servico["nome"] ?? "");
                    $descricaoTexto = trim((string) ($servico["descricao"] ?? ""));
                    $descricao = $descricaoTexto !== "" ? $descricaoTexto : "Serviço pensado para valorizar seu visual.";
                    $preco = number_format((float) ($servico["preco"] ?? 0), 2, ",", ".");
                    $icone = (string) ($servico["icone"] ?? "✂");
                    ?>
                    <article class="feature service-card" data-id="<?= Utils::escaparHtml($id) ?>" data-nome="<?= Utils::escaparHtml($nome) ?>">
                        <span class="feature-icon"><?= Utils::escaparHtml($icone) ?></span>
                        <h3><?= Utils::escaparHtml($nome) ?></h3>
                        <p><?= Utils::escaparHtml($descricao) ?></p>
                        <p class="price"><strong>R$ <?= Utils::escaparHtml($preco) ?></strong></p>
                        <a class="cta" href="agenda.php?servico_id=<?= Utils::escaparHtml($id) ?>">Agendar</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>