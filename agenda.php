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
    <script src="account.js?v=20261126-phone-mask-3" defer></script>
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
        <input type="tel" id="telefone" required minlength="14" maxlength="15" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}" inputmode="numeric" placeholder="(11) 99999-9999">

        <label for="servico">Escolha o serviço</label>
        <select id="servico" required></select>

        <label for="data">Escolha a data</label>
        <input type="date" id="data" required>

        <label for="horario">Escolha o horário</label>
        <select id="horario" required>
          <option value="" selected>Selecione uma data primeiro</option>
        </select>

        <p class="aviso" id="mensagem" role="status"></p>
        <button type="submit" class="full">CONFIRMAR AGENDAMENTO</button>
      </form>
    </section>

    <p class="admin-link" id="linkAdmin">Área do administrador</p>

    <section class="card hidden" id="areaAdmin">
      <h2>Painel administrativo</h2>
      <button type="button" id="btnVoltar">Voltar ao site</button>

      <section class="admin-password-change" aria-labelledby="tituloAlterarSenhaAdmin">
        <h3 id="tituloAlterarSenhaAdmin">Alterar minha senha</h3>
        <form id="formAlteracaoSenhaAdmin">
          <label for="senhaAtualAdmin">Senha atual</label>
          <input id="senhaAtualAdmin" type="password" required autocomplete="current-password">

          <label for="senhaNovaAdmin">Nova senha</label>
          <input id="senhaNovaAdmin" type="password" required minlength="6" maxlength="100" autocomplete="new-password">

          <label for="confirmarSenhaNovaAdmin">Confirme a nova senha</label>
          <input id="confirmarSenhaNovaAdmin" type="password" required minlength="6" maxlength="100" autocomplete="new-password">

          <p class="account-message" id="mensagemAlteracaoSenhaAdmin" role="status"></p>
          <button type="submit">Alterar senha</button>
        </form>
      </section>

      <div class="admin-notifications">
        <button type="button" class="admin-notifications-button" id="btnPedidosSenha" aria-expanded="false" aria-controls="pedidosSenha">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"></path>
          </svg>
          <span>Pedidos de troca de senha</span>
          <span class="admin-notification-count" id="contadorPedidosSenha">0</span>
        </button>
        <section class="admin-password-requests hidden" id="pedidosSenha" aria-label="Pedidos pendentes de troca de senha">
          <h3>Solicitações pendentes</h3>
          <div id="listaPedidosSenha" role="status"></div>
        </section>
      </div>

      <h3 class="subtitulo">Agendamentos</h3>
      <div id="listaAgendamentos"></div>
      <hr>

      <h3 class="subtitulo">Contas de clientes</h3>
      <p>Excluir uma conta também remove todos os agendamentos vinculados a ela.</p>
      <button type="button" id="btnAtualizarClientes">Atualizar lista de clientes</button>
      <div id="listaClientes" role="status"></div>
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