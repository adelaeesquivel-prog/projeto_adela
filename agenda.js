let servicos = [];

async function requisicao(acao, dados = null, metodo = "POST") {
  let url = `api.php?acao=${encodeURIComponent(acao)}`;
  const opcoes = { method: metodo, headers: {} };

  if (metodo === "GET") {
    if (dados) url += `&${new URLSearchParams(dados)}`;
  } else {
    opcoes.headers["Content-Type"] = "application/json";
    opcoes.body = JSON.stringify({ acao, ...dados });
  }

  const resposta = await fetch(url, opcoes);
  const resultado = await resposta.json();
  if (!resposta.ok) throw new Error(resultado.erro || "Erro na requisição.");
  return resultado;
}

function mostrarMensagem(texto) {
  const aviso = document.getElementById("mensagem");
  if (aviso) aviso.textContent = texto;
}

async function carregarServicos() {
  const select = document.getElementById("servico");
  if (!select) return;

  const resultado = await requisicao("servicos", null, "GET");
  servicos = resultado.servicos;
  select.replaceChildren();

  if (!servicos.length) {
    const option = document.createElement("option");
    option.value = "";
    option.textContent = "Nenhum serviço disponível";
    option.disabled = true;
    option.selected = true;
    select.appendChild(option);
    return;
  }

  const placeholder = document.createElement("option");
  placeholder.value = "";
  placeholder.textContent = "Selecione um serviço";
  placeholder.disabled = true;
  placeholder.selected = true;
  select.appendChild(placeholder);

  const params = new URLSearchParams(window.location.search);
  const servicoIdUrl = params.get("servico_id");

  servicos.forEach(servico => {
    const option = document.createElement("option");
    option.value = servico.id;
    option.textContent = `${servico.nome} - R$ ${Number(servico.preco).toFixed(2).replace(".", ",")}`;
    if (servicoIdUrl && String(servico.id) === servicoIdUrl) {
      option.selected = true;
    }
    select.appendChild(option);
  });
}

async function carregarHorarios() {
  const campoData = document.getElementById("data");
  const selectHorario = document.getElementById("horario");
  if (!campoData || !selectHorario) return;

  const data = campoData.value;
  selectHorario.replaceChildren();

  if (!data) {
    const option = document.createElement("option");
    option.value = "";
    option.textContent = "Selecione uma data primeiro";
    option.disabled = true;
    option.selected = true;
    selectHorario.appendChild(option);
    return;
  }

  try {
    const { horarios, ocupados } = await requisicao("disponibilidade", { data }, "GET");
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = "Selecione um horário";
    placeholder.disabled = true;
    placeholder.selected = true;
    selectHorario.appendChild(placeholder);

    let horariosDisponiveis = 0;
    horarios.forEach(hora => {
      const option = document.createElement("option");
      option.value = hora;
      const ocupado = ocupados.includes(hora);
      option.textContent = ocupado ? `${hora} (Indisponível)` : hora;
      option.disabled = ocupado;
      if (!ocupado) horariosDisponiveis++;
      selectHorario.appendChild(option);
    });
    if (horariosDisponiveis === 0) {
      placeholder.textContent = "Não há horários disponíveis nesta data";
    }
  } catch (erro) {
    const option = document.createElement("option");
    option.value = "";
    option.textContent = "Não foi possível carregar os horários";
    option.disabled = true;
    option.selected = true;
    selectHorario.appendChild(option);
    mostrarMensagem(erro.message);
  }
}

async function mostrarAgendamentos() {
  const resultado = await requisicao("agendamentos", null, "GET");
  const lista = document.getElementById("listaAgendamentos");
  lista.replaceChildren();

  if (!resultado.agendamentos.length) {
    const vazio = document.createElement("p");
    vazio.textContent = "Nenhum agendamento encontrado.";
    lista.appendChild(vazio);
    return;
  }

  resultado.agendamentos.forEach(agendamento => {
    const div = document.createElement("div");
    div.className = "agendamento";

    const info = document.createElement("span");
    const horaCurta = String(agendamento.horario || "").slice(0, 5);
    const nomeServico = agendamento.servico_nome || agendamento.servico || `Serviço #${agendamento.servico_id}`;
    const nomeCliente = agendamento.cliente_nome || agendamento.nome || `Cliente #${agendamento.cliente_id}`;
    info.textContent = `${agendamento.data} às ${horaCurta} - ${nomeCliente} (${nomeServico})`;

    const botaoCancelar = document.createElement("button");
    botaoCancelar.type = "button";
    botaoCancelar.textContent = "Cancelar";
    botaoCancelar.addEventListener("click", async () => {
      try {
        await requisicao("cancelar", { id: agendamento.id });
        await mostrarAgendamentos();
        await carregarHorarios();
      } catch (erro) {
        alert(erro.message);
      }
    });

    div.append(info, botaoCancelar);
    lista.appendChild(div);
  });
}

async function mostrarServicos() {
  const resultado = await requisicao("servicos", null, "GET");
  servicos = resultado.servicos;
  const lista = document.getElementById("listaServicos");
  lista.replaceChildren();

  if (!servicos.length) {
    const vazio = document.createElement("p");
    vazio.textContent = "Nenhum serviço cadastrado.";
    lista.appendChild(vazio);
    return;
  }

  servicos.forEach(servico => {
    const div = document.createElement("div");
    div.className = "agendamento";

    const inputNome = document.createElement("input");
    inputNome.type = "text";
    inputNome.placeholder = "Nome";
    inputNome.value = servico.nome;

    const inputPreco = document.createElement("input");
    inputPreco.type = "number";
    inputPreco.min = "0";
    inputPreco.step = "0.01";
    inputPreco.placeholder = "Preço";
    inputPreco.value = servico.preco;

    const inputDuracao = document.createElement("input");
    inputDuracao.type = "text";
    inputDuracao.placeholder = "Duração";
    inputDuracao.value = servico.duracao || "30 min";

    const inputDescricao = document.createElement("input");
    inputDescricao.type = "text";
    inputDescricao.placeholder = "Descrição";
    inputDescricao.value = servico.descricao || "";

    const botaoAtualizar = document.createElement("button");
    botaoAtualizar.type = "button";
    botaoAtualizar.textContent = "Atualizar";
    botaoAtualizar.addEventListener("click", async () => {
      const nome = inputNome.value.trim();
      const preco = inputPreco.value;
      const duracao = inputDuracao.value.trim() || "30 min";
      const descricao = inputDescricao.value.trim();
      if (!nome || preco === "") {
        alert("Informe o nome e o preço do serviço.");
        return;
      }

      try {
        await requisicao("atualizar_servico", {
          id: servico.id,
          nome,
          preco,
          duracao,
          descricao
        });
        await carregarServicos();
        await mostrarServicos();
        alert("Serviço atualizado com sucesso.");
      } catch (erro) {
        alert(erro.message);
      }
    });

    const botaoExcluir = document.createElement("button");
    botaoExcluir.type = "button";
    botaoExcluir.textContent = "Excluir";
    botaoExcluir.addEventListener("click", async () => {
      if (!confirm("Excluir este serviço?")) return;
      try {
        await requisicao("excluir_servico", { id: servico.id });
        await carregarServicos();
        await mostrarServicos();
      } catch (erro) {
        alert(erro.message);
      }
    });

    div.append(inputNome, inputPreco, inputDuracao, inputDescricao, botaoAtualizar, botaoExcluir);
    lista.appendChild(div);
  });
}

async function abrirAdmin() {
  document.getElementById("areaCliente").classList.add("hidden");
  document.getElementById("linkAdmin").classList.add("hidden");
  document.getElementById("areaAdmin").classList.remove("hidden");
  await mostrarAgendamentos();
  await mostrarServicos();
}

function fecharAdmin() {
  document.getElementById("areaAdmin").classList.add("hidden");
  document.getElementById("areaCliente").classList.remove("hidden");
  document.getElementById("linkAdmin").classList.remove("hidden");
}

function configurarEventos() {
  const campoData = document.getElementById("data");
  if (campoData) {
    const hoje = new Date();
    campoData.min = [
      hoje.getFullYear(),
      String(hoje.getMonth() + 1).padStart(2, "0"),
      String(hoje.getDate()).padStart(2, "0")
    ].join("-");
    campoData.addEventListener("change", carregarHorarios);
  }

  const campoTelefone = document.getElementById("telefone");
  if (campoTelefone) {
    campoTelefone.addEventListener("input", () => {
      campoTelefone.value = campoTelefone.value.replace(/\D/g, "").slice(0, 11);
    });
  }

  const formAgendamento = document.getElementById("formAgendamento");
  if (formAgendamento) {
    formAgendamento.addEventListener("submit", async evento => {
      evento.preventDefault();
      mostrarMensagem("");

      const dados = {
        nome: document.getElementById("nome").value.trim(),
        telefone: document.getElementById("telefone").value.trim(),
        servico_id: Number(document.getElementById("servico").value),
        data: document.getElementById("data").value,
        horario: document.getElementById("horario").value
      };

      try {
        const resultado = await requisicao("agendar", dados);
        mostrarMensagem(resultado.mensagem || "Agendamento confirmado.");
        formAgendamento.reset();
        await carregarHorarios();
      } catch (erro) {
        mostrarMensagem(erro.message);
      }
    });
  }

  const linkAdmin = document.getElementById("linkAdmin");
  if (linkAdmin) {
    linkAdmin.addEventListener("click", async () => {
      try {
        const sessao = await requisicao("admin", null, "GET");
        if (!sessao.autenticado) {
          if (typeof mostrarFormulario === "function") {
            mostrarFormulario("admin");
          } else {
            alert("Faça login como administrador primeiro.");
          }
          return;
        }
        await abrirAdmin();
      } catch (erro) {
        alert(erro.message);
      }
    });
  }

  const btnVoltar = document.getElementById("btnVoltar");
  if (btnVoltar) {
    btnVoltar.addEventListener("click", fecharAdmin);
  }

  const btnCadastrarServico = document.getElementById("btnCadastrarServico");
  if (btnCadastrarServico) {
    btnCadastrarServico.addEventListener("click", async () => {
      const inputNome = document.getElementById("novoServico");
      const inputPreco = document.getElementById("precoServico");
      const inputDuracao = document.getElementById("duracaoServico");
      const inputDescricao = document.getElementById("descricaoServico");

      const nome = inputNome.value.trim();
      const preco = inputPreco.value;
      const duracao = inputDuracao ? inputDuracao.value.trim() : "";
      const descricao = inputDescricao ? inputDescricao.value.trim() : "";

      if (!nome || preco === "") {
        alert("Preencha o nome e o preço do serviço.");
        return;
      }

      try {
        await requisicao("adicionar_servico", { nome, preco, duracao, descricao });
        inputNome.value = "";
        inputPreco.value = "";
        if (inputDuracao) inputDuracao.value = "";
        if (inputDescricao) inputDescricao.value = "";
        await carregarServicos();
        await mostrarServicos();
      } catch (erro) {
        alert(erro.message);
      }
    });
  }
}

async function inicializar() {
  configurarEventos();
  try {
    await carregarServicos();
  } catch (erro) {
    mostrarMensagem(erro.message);
  }
  await carregarHorarios();

  const params = new URLSearchParams(window.location.search);
  if (params.get("admin") === "1") {
    try {
      const sessao = await requisicao("admin", null, "GET");
      if (sessao.autenticado) {
        await abrirAdmin();
      }
    } catch (erro) {
      mostrarMensagem(erro.message);
    }
  }
}

document.addEventListener("DOMContentLoaded", inicializar);