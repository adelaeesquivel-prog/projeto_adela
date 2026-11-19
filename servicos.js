const areaServicos = document.getElementById("cardsServicos");

async function carregarServicosDaPagina() {
  try {
    const resposta = await fetch("api.php?acao=servicos");
    const resultado = await resposta.json();
    if (!resposta.ok) {
      throw new Error(resultado.erro || "Não foi possível carregar os serviços.");
    }

    areaServicos.replaceChildren();
    if (resultado.servicos.length === 0) {
      const aviso = document.createElement("p");
      aviso.className = "empty-services";
      aviso.textContent = "Nenhum serviço cadastrado no momento.";
      areaServicos.appendChild(aviso);
      return;
    }

    resultado.servicos.forEach(servico => {
      const card = document.createElement("article");
      card.className = "service-card";

      const nome = document.createElement("h2");
      nome.textContent = servico.nome;

      const descricao = document.createElement("p");
      descricao.textContent = "Cuidado e atenção para renovar seu visual.";

      const preco = document.createElement("p");
      preco.className = "price";
      preco.textContent = "R$ " + Number(servico.preco).toFixed(2).replace(".", ",");

      card.append(nome, descricao, preco);
      areaServicos.appendChild(card);
    });
  } catch (erro) {
    areaServicos.replaceChildren();
    const aviso = document.createElement("p");
    aviso.className = "empty-services";
    aviso.textContent = erro.message;
    areaServicos.appendChild(aviso);
  }
}

carregarServicosDaPagina();
