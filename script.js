const horarios = [
  "08:00", "08:30", "09:00", "09:30",
  "10:00", "10:30", "11:00", "11:30",
  "13:00", "13:30", "14:00", "14:30",
  "15:00", "15:30", "16:00", "16:30",
  "17:00", "17:30", "18:00"
];

const dataInput = document.getElementById("data");
const horarioInput = document.getElementById("horario");
const servicoInput = document.getElementById("servico");
const mensagem = document.getElementById("mensagem");
let servicos = [];
let agendamentos = [];

async function requisicao(acao, dados, metodo = "POST") {
  const opcoes = { method: metodo, headers: {} };
  let url = `api.php?acao=${encodeURIComponent(acao)}`;

  if (metodo === "GET") {
    if (dados) {
      url += `&${new URLSearchParams(dados)}`;
    }
  } else {
    opcoes.headers["Content-Type"] = "application/json";
    opcoes.body = JSON.stringify({ acao, ...dados });
  }

  const resposta = await fetch(url, opcoes);
  const resultado = await resposta.json();
  if (!resposta.ok) {
    throw new Error(resultado.erro || "Não foi possível concluir a operação.");
  }
  return resultado;
}

function mostrarMensagem(texto) {
  mensagem.textContent = texto;
}

function formatarPreco(preco) {
  return Number(preco).toFixed(2).replace(".", ",");
}

async function carregarServicos() {
  const resultado = await requisicao("servicos", null, "GET");
  servicos = resultado.servicos;
  servicoInput.replaceChildren(new Option("Selecione um serviço", ""));

  servicos.forEach(servico => {
    const opcao = new Option(
      `${servico.nome} - R$ ${formatarPreco(servico.preco)}`,
      servico.id
    );
    servicoInput.appendChild(opcao);
  });
}

async function carregarHorarios() {
  horarioInput.replaceChildren(new Option("Selecione um horário", ""));
  if (!dataInput.value) return;

  const resultado = await requisicao(
    "disponibilidade",
    { data: dataInput.value },
    "GET"
  );
  const ocupados = new Set(resultado.ocupados);
  const agora = new Date();
  const hoje = [
    agora.getFullYear(),
    String(agora.getMonth() + 1).padStart(2, "0"),
    String(agora.getDate()).padStart(2, "0")
  ].join("-");

  horarios.forEach(horario => {
    const opcao = new Option("", horario);
    const passou = dataInput.value < hoje ||
      (dataInput.value === hoje && horario <=
        `${String(agora.getHours()).padStart(2, "0")}:${String(agora.getMinutes()).padStart(2, "0")}`);

    if (ocupados.has(horario)) {
      opcao.textContent = `${horario} - Ocupado`;
      opcao.disabled = true;
    } else if (passou) {
      opcao.textContent = `${horario} - Indisponível`;
      opcao.disabled = true;
    } else {
      opcao.textContent = `${horario} - Disponível`;
    }
    horarioInput.appendChild(opcao);
  });
}

function dataHoje() {
  const hoje = new Date();
  return [
    hoje.getFullYear(),
    String(hoje.getMonth() + 1).padStart(2, "0"),
    String(hoje.getDate()).padStart(2, "0")
  ].join("-");
}

dataInput.min = dataHoje();

dataInput.addEventListener("change", async () => {
  try {
    await carregarHorarios();
    mostrarMensagem("");
  } catch (erro) {
    mostrarMensagem(erro.message);
  }
});

document.getElementById("formAgendamento").addEventListener("submit", async evento => {
  evento.preventDefault();
  mostrarMensagem("");

  try {
    const resultado = await requisicao("agendar", {
      nome: document.getElementById("nome").value.trim(),
      telefone: document.getElementById("telefone").value.trim(),
      servico_id: servicoInput.value,
      data: dataInput.value,
      horario: horarioInput.value
    });

    alert(resultado.mensagem);
    evento.currentTarget.reset();
    await carregarHorarios();
  } catch (erro) {
    mostrarMensagem(erro.message);
    if (erro.message.includes("reservado")) {
      await carregarHorarios();
    }
  }
});

document.getElementById("linkAdmin").addEventListener("click", async () => {
  try {
    const sessao = await requisicao("admin", null, "GET");
    if (!sessao.autenticado) {
      const senha = prompt("Digite a senha do administrador:");
      if (senha === null) return;
      await requisicao("login", { senha });
    }

    await abrirAdmin();
  } catch (erro) {
    mostrarMensagem(erro.message);
    alert(erro.message);
  }
});

async function abrirAdmin() {
  const resultado = await requisicao("agendamentos", null, "GET");
  agendamentos = resultado.agendamentos;
  document.getElementById("areaCliente").classList.add("hidden");
  document.getElementById("areaAdmin").classList.remove("hidden");
  document.getElementById("linkAdmin").classList.add("hidden");
  mostrarAgendamentos();
  await mostrarServicos();
}

document.getElementById("btnVoltar").addEventListener("click", async () => {
  try {
    await requisicao("logout", {});
    document.getElementById("areaAdmin").classList.add("hidden");
    document.getElementById("areaCliente").classList.remove("hidden");
    document.getElementById("linkAdmin").classList.remove("hidden");
  } catch (erro) {
    alert(erro.message);
  }
});

function mostrarAgendamentos() {
  const lista = document.getElementById("listaAgendamentos");
  lista.replaceChildren();

  if (agendamentos.length === 0) {
    lista.textContent = "Nenhum agendamento cadastrado.";
    return;
  }

  [...agendamentos]
    .sort((a, b) => (a.data + a.horario).localeCompare(b.data + b.horario))
    .forEach(agendamento => {
      const div = document.createElement("div");
      div.className = "agendamento";

      const info = document.createElement("div");
      info.textContent = `Cliente: ${agendamento.nome} | Telefone: ${agendamento.telefone}`;

      const detalhes = document.createElement("div");
      detalhes.textContent =
        `Serviço: ${agendamento.servico} | R$ ${formatarPreco(agendamento.preco)} | Data: ${agendamento.data} | Horário: ${agendamento.horario}`;

      const cancelar = document.createElement("button");
      cancelar.textContent = "Cancelar";
      cancelar.addEventListener("click", async () => {
        if (!confirm("Deseja cancelar este agendamento?")) return;
        try {
          const resultado = await requisicao("cancelar", { id: agendamento.id });
          agendamentos = resultado.agendamentos;
          mostrarAgendamentos();
          await carregarHorarios();
        } catch (erro) {
          alert(erro.message);
        }
      });

      div.append(info, detalhes, cancelar);
      lista.appendChild(div);
    });
}

document.getElementById("btnCadastrarServico").addEventListener("click", async () => {
  const nome = document.getElementById("novoServico").value.trim();
  const campoPreco = document.getElementById("precoServico").value;

  if (!nome || campoPreco === "") {
    alert("Informe o nome e um preço válido!");
    return;
  }

  try {
    await requisicao("adicionar_servico", { nome, preco: campoPreco });
    await carregarServicos();
    await mostrarServicos();
    document.getElementById("novoServico").value = "";
    document.getElementById("precoServico").value = "";
  } catch (erro) {
    alert(erro.message);
  }
});

async function mostrarServicos() {
  const resultado = await requisicao("servicos", null, "GET");
  servicos = resultado.servicos;
  const lista = document.getElementById("listaServicos");
  lista.replaceChildren();

  servicos.forEach(servico => {
    const div = document.createElement("div");
    div.className = "agendamento";

    const texto = document.createElement("span");
    texto.textContent = `${servico.nome} - R$ ${formatarPreco(servico.preco)}`;

    const botao = document.createElement("button");
    botao.textContent = "Excluir";
    botao.addEventListener("click", async () => {
      if (!confirm("Excluir este serviço?")) return;
      try {
        await requisicao("excluir_servico", { id: servico.id });
        await carregarServicos();
        await mostrarServicos();
      } catch (erro) {
        alert(erro.message);
      }
    });

    div.append(texto, botao);
    lista.appendChild(div);
  });
}

async function inicializar() {
  try {
    await carregarServicos();
    await carregarHorarios();
  } catch (erro) {
    mostrarMensagem(erro.message);
  }
}

inicializar();
