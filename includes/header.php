<header class="site-header">
  <div class="site-header-top">
    <button class="login-icon" id="loginIcon" type="button" aria-label="Entrar ou abrir minha conta" title="Entrar ou minha conta">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0H5Z"/>
      </svg>
      <span class="login-label">Registrar</span>
    </button>
    <div class="site-brand">
      <a class="logo" href="home.php">✂ LÉO BARBAS</a>
      <p>Seu estilo começa aqui!</p>
    </div>
  </div>
  <nav class="site-nav" aria-label="Navegação principal">
    <a class="<?= ($paginaAtual ?? '') === 'home' ? 'active' : '' ?>" href="index.php">Home</a>
    <a class="<?= ($paginaAtual ?? '') === 'servicos' ? 'active' : '' ?>" href="servicos.php">Serviços</a>
    <a class="<?= ($paginaAtual ?? '') === 'agenda' ? 'active' : '' ?>" href="agenda.php">Agendamento</a>
  </nav>
</header>
