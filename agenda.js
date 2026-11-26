let servicos = [];
const opcoesStatusAgendamento = [
  { valor: "pendente", texto: "Pendente" },
  { valor: "confirmado", texto: "Confirmado" },
  { valor: "concluido", texto: "Concluído" },
  { valor: "falta", texto: "Falta" },
  { valor: "falta_justificada", texto: "Falta com justificativa" },
  { valor: "cancelado", texto: "Cancelado" }
];

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

function formatarTelefone(valor) {
  const digitos = valor.replace(/\D/g, "").slice(0, 11);
  if (digitos.length <= 2) return digitos ? `(${digitos}` : "";

  const ddd = digitos.slice(0, 2);
  const numero = digitos.slice(2);
  const tamanhoPrefixo = digitos.length > 10 ? 5 : 4;
  if (numero.length <= tamanhoPrefixo) return `(${ddd}) ${numero}`;

  return `(${ddd}) ${numero.slice(0, tamanhoPrefixo)}-${numero.slice(tamanhoPrefixo)}`;
}

function atualizarPrecosOpcoesServico(conta = window.contaAtual) {
  const select = document.getElementById("servico");
  if (!select) return;

  const clienteLogado = conta && conta.autenticado && conta.tipo === "cliente";
  servicos.forEach(servico => {
    const option = Array.from(select.options).find(item => item.value === String(servico.id));
    if (!option) return;

    let preco = Number(servico.preco);
    let desconto = "";
    if (clienteLogado) {
      preco = Math.round(preco * 0.95 * 100) / 100;
      desconto = " (5% de desconto)";
    }
    option.textContent = `${servico.nome} - R$ ${preco.toFixed(2).replace(".", ",")}${desconto}`;
  });
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
  atualizarPrecosOpcoesServico();
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

    const controles = document.createElement("div");
    controles.className = "admin-appointment-controls";

    const seletorStatus = document.createElement("select");
    seletorStatus.setAttribute("aria-label", `Status de ${nomeCliente}`);
    const statusAtual = String(agendamento.status || "pendente");
    const opcoes = [...opcoesStatusAgendamento];
    if (!opcoes.some(opcao => opcao.valor === statusAtual)) {
      opcoes.push({ valor: statusAtual, texto: statusAtual });
    }
    opcoes.forEach(opcao => {
      const option = document.createElement("option");
      option.value = opcao.valor;
      option.textContent = opcao.texto;
      seletorStatus.appendChild(option);
    });
    seletorStatus.value = statusAtual;

    const justificativa = document.createElement("textarea");
    justificativa.maxLength = 1000;
    justificativa.placeholder = "Justificativa da falta";
    justificativa.setAttribute("aria-label", `Justificativa da falta de ${nomeCliente}`);
    justificativa.value = agendamento.justificativa_falta || "";
    justificativa.hidden = statusAtual !== "falta_justificada";
    justificativa.required = statusAtual === "falta_justificada";
    seletorStatus.addEventListener("change", () => {
      const exigeJustificativa = seletorStatus.value === "falta_justificada";
      justificativa.hidden = !exigeJustificativa;
      justificativa.required = exigeJustificativa;
    });

    const botaoSalvarStatus = document.createElement("button");
    botaoSalvarStatus.type = "button";
    botaoSalvarStatus.textContent = "Atualizar status";
    botaoSalvarStatus.addEventListener("click", async () => {
      if (seletorStatus.value === "falta_justificada" && !justificativa.value.trim()) {
        justificativa.focus();
        alert("Informe a justificativa da falta.");
        return;
      }

      try {
        await requisicao("atualizar_status", {
          id: agendamento.id,
          status: seletorStatus.value,
          justificativa_falta: justificativa.value.trim()
        });
        await mostrarAgendamentos();
      } catch (erro) {
        alert(erro.message);
      }
    });

    controles.append(seletorStatus, justificativa, botaoSalvarStatus);

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

    div.append(info, controles, botaoCancelar);
    lista.appendChild(div);
  });
}

async function mostrarClientes() {
  const lista = document.getElementById("listaClientes");
  lista.replaceChildren();

  let resultado;
  try {
    resultado = await requisicao("clientes", null, "GET");
  } catch (erro) {
    const mensagem = document.createElement("p");
    mensagem.className = "account-message";
    mensagem.textContent = `Não foi possível carregar os clientes: ${erro.message}`;
    lista.appendChild(mensagem);
    return;
  }

  if (!resultado.clientes.length) {
    const vazio = document.createElement("p");
    vazio.textContent = "Nenhuma conta de cliente cadastrada.";
    lista.appendChild(vazio);
    return;
  }

  resultado.clientes.forEach(cliente => {
    const item = document.createElement("article");
    item.className = "admin-client";

    const informacoes = document.createElement("div");
    informacoes.className = "admin-client-info";
    const nome = document.createElement("strong");
    nome.textContent = cliente.nome;
    const email = document.createElement("p");
    email.textContent = `E-mail: ${cliente.email}`;
    const telefone = document.createElement("p");
    telefone.textContent = `Telefone: ${cliente.telefone}`;
    const agendamentos = Number(cliente.total_agendamentos) || 0;
    const total = document.createElement("p");
    total.textContent = `Agendamentos vinculados: ${agendamentos}`;
    informacoes.append(nome, email, telefone, total);

    const botaoExcluir = document.createElement("button");
    botaoExcluir.type = "button";
    botaoExcluir.textContent = "Excluir conta";
    botaoExcluir.addEventListener("click", async () => {
      const impacto = agendamentos > 0
        ? ` e seus ${agendamentos} agendamento(s)`
        : "";
      if (!confirm(`Excluir a conta de ${cliente.nome}${impacto}? Esta ação não pode ser desfeita.`)) return;

      try {
        await requisicao("excluir_cliente", { id: cliente.id });
        await Promise.all([mostrarClientes(), mostrarAgendamentos(), carregarHorarios()]);
      } catch (erro) {
        alert(erro.message);
      }
    });

    item.append(informacoes, botaoExcluir);
    lista.appendChild(item);
  });
}

async function carregarPedidosSenha() {
  const contador = document.getElementById("contadorPedidosSenha");
  const lista = document.getElementById("listaPedidosSenha");
  const secao = document.getElementById("pedidosSenha");
  if (!contador || !lista || !secao) return;

  try {
    const resultado = await requisicao("pedidos_troca_senha", null, "GET");
    const solicitacoes = resultado.solicitacoes;
    contador.textContent = String(solicitacoes.length);
    contador.setAttribute("aria-label", `${solicitacoes.length} pedidos pendentes`);
    if (secao.classList.contains("hidden")) return;

    lista.replaceChildren();
    if (!solicitacoes.length) {
      const vazio = document.createElement("p");
      vazio.textContent = "Não há pedidos pendentes.";
      lista.appendChild(vazio);
      return;
    }

    solicitacoes.forEach(solicitacao => {
      const item = document.createElement("article");
      item.className = "password-request";

      const nome = document.createElement("p");
      nome.textContent = `Nome: ${solicitacao.nome}`;
      const email = document.createElement("p");
      email.textContent = `E-mail: ${solicitacao.email}`;
      const telefone = document.createElement("p");
      telefone.textContent = `Telefone: ${solicitacao.telefone}`;
      const data = document.createElement("p");
      data.className = "password-request-date";
      data.textContent = `Recebido em ${solicitacao.criado_em}`;
      const alerta = document.createElement("p");
      alerta.className = "password-request-warning";
      alerta.textContent = "Confirme a identidade do cliente pelo telefone cadastrado antes de aprovar.";

      const acoes = document.createElement("div");
      acoes.className = "password-request-actions";
      const aprovar = document.createElement("button");
      aprovar.type = "button";
      aprovar.textContent = "Aprovar troca";
      aprovar.addEventListener("click", async () => {
        if (!confirm("Confirme que verificou a identidade do cliente pelo telefone cadastrado. Aprovará a nova senha.")) return;
        try {
          await requisicao("aprovar_troca_senha", { id: solicitacao.id });
          await carregarPedidosSenha();
        } catch (erro) {
          alert(erro.message);
        }
      });

      const recusar = document.createElement("button");
      recusar.type = "button";
      recusar.textContent = "Recusar";
      recusar.addEventListener("click", async () => {
        if (!confirm("Deseja recusar este pedido de troca de senha?")) return;
        try {
          await requisicao("recusar_troca_senha", { id: solicitacao.id });
          await carregarPedidosSenha();
        } catch (erro) {
          alert(erro.message);
        }
      });

      acoes.append(aprovar, recusar);
      item.append(nome, email, telefone, data, alerta, acoes);
      lista.appendChild(item);
    });
  } catch (erro) {
    contador.textContent = "!";
    contador.setAttribute("aria-label", "Não foi possível carregar os pedidos");
    if (secao.classList.contains("hidden")) return;

    lista.replaceChildren();
    const mensagem = document.createElement("p");
    mensagem.className = "account-message";
    mensagem.textContent = erro.message;
    lista.appendChild(mensagem);
  }
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
  const botaoPedidosSenha = document.getElementById("btnPedidosSenha");
  const secaoPedidosSenha = document.getElementById("pedidosSenha");
  secaoPedidosSenha.classList.remove("hidden");
  botaoPedidosSenha.setAttribute("aria-expanded", "true");
  await carregarPedidosSenha();
  await mostrarAgendamentos();
  await mostrarClientes();
  await mostrarServicos();
}

function fecharAdmin() {
  document.getElementById("areaAdmin").classList.add("hidden");
  document.getElementById("areaCliente").classList.remove("hidden");
  document.getElementById("linkAdmin").classList.remove("hidden");
}

function configurarEventos() {
  document.addEventListener("conta:atualizada", evento => {
    atualizarPrecosOpcoesServico(evento.detail);
  });

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
      campoTelefone.value = formatarTelefone(campoTelefone.value);
    });
  }

  const formAgendamento = document.getElementById("formAgendamento");
  if (formAgendamento) {
    formAgendamento.addEventListener("submit", async evento => {
      evento.preventDefault();
      mostrarMensagem("");

      const dados = {
        nome: document.getElementById("nome").value.trim(),
        telefone: document.getElementById("telefone").value.replace(/\D/g, ""),
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

  const formAlteracaoSenhaAdmin = document.getElementById("formAlteracaoSenhaAdmin");
  if (formAlteracaoSenhaAdmin) {
    formAlteracaoSenhaAdmin.addEventListener("submit", async evento => {
      evento.preventDefault();
      const senhaAtual = document.getElementById("senhaAtualAdmin");
      const senhaNova = document.getElementById("senhaNovaAdmin");
      const confirmacao = document.getElementById("confirmarSenhaNovaAdmin");
      const mensagem = document.getElementById("mensagemAlteracaoSenhaAdmin");
      mensagem.textContent = "";

      if (senhaNova.value !== confirmacao.value) {
        mensagem.textContent = "As senhas não conferem.";
        confirmacao.focus();
        return;
      }

      try {
        const resultado = await requisicao("alterar_senha_admin", {
          senha_atual: senhaAtual.value,
          senha_nova: senhaNova.value
        });
        mensagem.textContent = resultado.mensagem;
        senhaAtual.value = "";
        senhaNova.value = "";
        confirmacao.value = "";
      } catch (erro) {
        mensagem.textContent = erro.message;
      }
    });
  }

  const btnAtualizarClientes = document.getElementById("btnAtualizarClientes");
  if (btnAtualizarClientes) {
    btnAtualizarClientes.addEventListener("click", async () => {
      try {
        await mostrarClientes();
      } catch (erro) {
        alert(erro.message);
      }
    });
  }

  const btnPedidosSenha = document.getElementById("btnPedidosSenha");
  if (btnPedidosSenha) {
    btnPedidosSenha.addEventListener("click", async () => {
      const secao = document.getElementById("pedidosSenha");
      const expandido = btnPedidosSenha.getAttribute("aria-expanded") === "true";
      secao.classList.toggle("hidden", expandido);
      btnPedidosSenha.setAttribute("aria-expanded", String(!expandido));
      if (!expandido) await carregarPedidosSenha();
    });

    window.setInterval(() => {
      const painel = document.getElementById("areaAdmin");
      if (painel && !painel.classList.contains("hidden")) carregarPedidosSenha();
    }, 30000);
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