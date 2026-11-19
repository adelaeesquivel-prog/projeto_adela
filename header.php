<header class="site-header">
  <a class="logo" href="home.php">✂ LÉO BARBAS</a>
  <p>Seu estilo começa aqui!</p>
  <nav class="site-nav" aria-label="Navegação principal">
    <a class="<?= ($paginaAtual ?? '') === 'home' ? 'active' : '' ?>" href="home.php">Home</a>
    <a class="<?= ($paginaAtual ?? '') === 'servicos' ? 'active' : '' ?>" href="servicos.php">Serviços</a>
    <a class="<?= ($paginaAtual ?? '') === 'agenda' ? 'active' : '' ?>" href="agenda.php">Agendamento</a>
  </nav>
</header>
