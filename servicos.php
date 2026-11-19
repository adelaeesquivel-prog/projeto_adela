<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Léo Barbas | Serviços</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="site-pages.css">
  <script src="servicos.js" defer></script>
</head>
<body>
  <?php
  $paginaAtual = 'servicos';
  require __DIR__ . '/header.php';
  ?>

  <main class="services-main">
    <section class="page-heading">
      <span class="eyebrow">O QUE OFERECEMOS</span>
      <h1>Nossos serviços</h1>
      <p>Escolha o cuidado ideal para você. Confira abaixo os serviços e valores cadastrados.</p>
    </section>

    <section id="cardsServicos" class="services-grid" aria-live="polite">
      <p class="empty-services">Carregando serviços...</p>
    </section>

    <section class="booking-banner">
      <div>
        <h2>Encontrou o que procura?</h2>
        <p>Reserve agora o melhor horário para você.</p>
      </div>
      <a class="cta" href="agenda.php">Agendar agora</a>
    </section>
  </main>

  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
