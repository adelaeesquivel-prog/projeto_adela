<?php

use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

require_once __DIR__ . "/vendor/autoload.php";

$servicos = [];
$erroServicos = null;
$servicoSelecionado = (int) ($_GET['servico_id'] ?? 0);

try {
    $servicoService = new ServicoService();
    $servicos = $servicoService->listarTodos();
} catch (Throwable $e) {
    Utils::registrarLog($e);
    $erroServicos = "Não foi possível carregar os serviços.";
}
?>



<?php $paginaAtual = 'agenda'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Léo Barbas | Agendamento</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="site-pages.css">
    <script src="agenda.js" defer></script>
    <script src="account.js" defer></script>
</head>
<body>
   <?php require __DIR__ . '/includes/header.php'; ?>

  <main class="container">
    <section class="card" id="areaCliente">
      <h2>Agende seu horário</h2>
      <form id="formAgendamento">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" required placeholder="Digite seu nome">

        <label for="telefone">Telefone</label>
        <input type="tel" id="telefone" required placeholder="(00) 00000-0000">

        <label for="servico">Escolha o serviço</label>
        <select id="servico" required>
          <option value="">Selecione um serviço</option>
          <?php foreach ($servicos as $servico): ?>
            <?php
            $id = (int) $servico['id'];
            $nome = (string) $servico['nome'];
            $preco = number_format((float) $servico['preco'], 2, ',', '.');
            ?>
            <option value="<?= Utils::escaparHtml($id) ?>" <?= $id === $servicoSelecionado ? 'selected' : '' ?>>
              <?= Utils::escaparHtml($nome) ?> - R$ <?= Utils::escaparHtml($preco) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($erroServicos !== null): ?>
          <p class="aviso" role="alert"><?= Utils::escaparHtml($erroServicos) ?></p>
        <?php endif; ?>

        <label for="data">Escolha a data</label>
        <input type="date" id="data" required>

        <label for="horario">Escolha o horário</label>
        <select id="horario" required>
          <option value="">Selecione uma data primeiro</option>
          <?php for ($hora = 8; $hora <= 17; $hora++): ?>
            <?php if ($hora !== 12): ?>
              <option value="<?= sprintf('%02d:00', $hora) ?>"><?= sprintf('%02d:00', $hora) ?></option>
              <?php if ($hora < 17): ?>
                <option value="<?= sprintf('%02d:30', $hora) ?>"><?= sprintf('%02d:30', $hora) ?></option>
              <?php endif; ?>
            <?php endif; ?>
          <?php endfor; ?>
        </select>

        <p class="aviso" id="mensagem" role="status"></p>
        <button type="submit" class="full">CONFIRMAR AGENDAMENTO</button>
      </form>
    </section>

    <p class="admin-link" id="linkAdmin">Área do administrador</p>

    <section class="card hidden" id="areaAdmin">
      <h2>Painel administrativo</h2>
      <button type="button" id="btnVoltar">Voltar ao site</button>

      <h3 class="subtitulo">Agendamentos</h3>
      <div id="listaAgendamentos"></div>
      <hr>

      <h3>Cadastro de serviços</h3>
      <label for="novoServico">Nome do serviço</label>
      <input id="novoServico" type="text" placeholder="Ex: Corte degradê">

      <label for="precoServico">Preço (R$)</label>
      <input id="precoServico" type="number" min="0" step="0.01" placeholder="30">

      <label for="duracaoServico">Duração</label>
      <input id="duracaoServico" type="text" placeholder="Ex: 30 min">

      <label for="descricaoServico">Descrição</label>
      <input id="descricaoServico" type="text" placeholder="Ex: Corte moderno com acabamento na navalha">

      <button type="button" id="btnCadastrarServico">Cadastrar serviço</button>

      <h3 class="subtitulo">Serviços cadastrados</h3>
      <div id="listaServicos"></div>
    </section>
  </main>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>