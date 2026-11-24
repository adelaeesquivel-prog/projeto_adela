const cardsServicos = document.querySelectorAll("#cardsServicos .service-card");

cardsServicos.forEach((card) => {
  card.style.cursor = "pointer";

  card.addEventListener("click", (evento) => {
    // Se clicou diretamente no botão/link "Agendar", deixa o link navegar normalmente
    if (evento.target.closest("a")) {
      return;
    }

    cardsServicos.forEach((item) => item.classList.remove("selecionado"));
    card.classList.add("selecionado");

    const servicoId = card.dataset.id;
    if (servicoId) {
      window.location.href = `agenda.php?servico_id=${encodeURIComponent(servicoId)}`;
    }
  });
});