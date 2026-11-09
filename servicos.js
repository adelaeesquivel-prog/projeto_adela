// Exibe os mesmos serviços cadastrados no sistema de agendamento.
const areaServicos = document.getElementById("cardsServicos");

const servicosPadrao = [
  { nome: "Corte de cabelo", preco: 30 },
  { nome: "Barba", preco: 20 },
  { nome: "Cabelo + Barba", preco: 45 },
  { nome: "Sobrancelha", preco: 10 }
];

function carregarServicosDaPagina() {
  let servicos = servicosPadrao;

  try {
    const dadosSalvos = localStorage.getItem("leo_servicos");
    if (dadosSalvos) {
      const lista = JSON.parse(dadosSalvos);
      if (Array.isArray(lista)) {
        servicos = lista;
      }
    }
  } catch (erro) {
    console.error("Não foi possível carregar os serviços.", erro);
  }

  areaServicos.innerHTML = "";

  if (servicos.length === 0) {
    areaServicos.innerHTML = '<p class="empty-services">Nenhum serviço cadastrado no momento.</p>';
    return;
  }

  servicos.forEach(servico => {
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
}

carregarServicosDaPagina();
