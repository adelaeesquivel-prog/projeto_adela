const cardsServicos = document.querySelectorAll("#cardsServicos .service-card");

function formatarPreco(preco) {
  return Number(preco).toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function atualizarPrecosServicos(conta = window.contaAtual) {
  const clienteLogado = conta && conta.autenticado && conta.tipo === "cliente";
  const aviso = document.getElementById("discountNotice");

  cardsServicos.forEach(card => {
    const preco = Number(card.dataset.preco);
    const campoPreco = card.querySelector(".price");
    campoPreco.replaceChildren();

    if (clienteLogado) {
      const precoOriginal = document.createElement("span");
      precoOriginal.className = "price-original";
      precoOriginal.textContent = `R$ ${formatarPreco(preco)}`;

      const precoComDesconto = document.createElement("strong");
      precoComDesconto.className = "price-discounted";
      precoComDesconto.textContent = `R$ ${formatarPreco(Math.round(preco * 0.95 * 100) / 100)}`;

      const identificacaoDesconto = document.createElement("span");
      identificacaoDesconto.className = "price-discount-label";
      identificacaoDesconto.textContent = "5% de desconto";
      campoPreco.append(precoOriginal, precoComDesconto, identificacaoDesconto);
      return;
    }

    const precoRegular = document.createElement("strong");
    precoRegular.textContent = `R$ ${formatarPreco(preco)}`;
    campoPreco.appendChild(precoRegular);
  });

  if (aviso) {
    aviso.textContent = clienteLogado
      ? "Desconto de 5% aplicado aos preços para cliente logado."
      : "Faça login como cliente para aproveitar 5% de desconto.";
  }
}

document.addEventListener("conta:atualizada", evento => {
  atualizarPrecosServicos(evento.detail);
});
atualizarPrecosServicos();

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