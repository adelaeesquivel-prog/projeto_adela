const loginIcon = document.getElementById("loginIcon");

async function requisicaoConta(acao, dados = null, metodo = "POST") {
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
  if (!resposta.ok) throw new Error(resultado.erro || "Não foi possível concluir a operação.");
  return resultado;
}

function criarModal(conteudo) {
  const existente = document.querySelector(".account-overlay");
  if (existente) existente.remove();

  const overlay = document.createElement("div");
  overlay.className = "account-overlay";
  overlay.innerHTML = `
    <div class="account-modal" role="dialog" aria-modal="true" aria-label="Área de acesso">
      <button class="account-close" type="button" aria-label="Fechar">×</button>
      ${conteudo}
    </div>
  `;
  overlay.querySelector(".account-close").addEventListener("click", () => overlay.remove());
  overlay.addEventListener("click", evento => {
    if (evento.target === overlay) overlay.remove();
  });
  document.body.appendChild(overlay);
  return overlay;
}

function mascararEmail(email) {
  const partes = String(email || "").split("@");
  if (partes.length !== 2 || !partes[0] || !partes[1]) return "***";

  const mascararParte = parte => parte.length > 2
    ? `${parte[0]}***${parte.slice(-1)}`
    : `${parte[0]}***`;
  const dominio = partes[1].split(".");
  const nomeDominio = dominio.shift();
  return `${mascararParte(partes[0])}@${mascararParte(nomeDominio)}${dominio.length ? `.${dominio.join(".")}` : ""}`;
}

function mostrarFormulario(tipo = "entrar") {
  const registro = tipo === "cadastrar";
  const conteudo = `
    <h2>${registro ? "Criar conta" : "Entrar"}</h2>
    <div class="account-tabs" aria-label="Tipo de conta">
      <button type="button" class="account-tab ${tipo !== "admin" ? "active" : ""}" data-form="cliente">Cliente</button>
      <button type="button" class="account-tab ${tipo === "admin" ? "active" : ""}" data-form="admin">Administrador</button>
    </div>
    <form id="formConta">
      <div id="camposRegistro" ${registro ? "" : "hidden"}>
        <label for="contaNome">Nome</label>
        <input id="contaNome" type="text" maxlength="120" autocomplete="name">
        <label for="contaTelefone">Telefone</label>
        <input id="contaTelefone" type="tel" minlength="10" maxlength="11" pattern="[0-9]{10,11}" inputmode="numeric" placeholder="DDD + número" autocomplete="tel">
      </div>
      <div id="campoEmail">
        <label for="contaEmail">E-mail</label>
        <input id="contaEmail" type="email" required autocomplete="email">
      </div>
      <label for="contaSenha">Senha</label>
      <input id="contaSenha" type="password" required minlength="6" autocomplete="${registro ? "new-password" : "current-password"}">
      <p class="account-message" id="contaMensagem" role="status"></p>
      <button class="account-primary" type="submit">${registro ? "Criar conta" : "Entrar"}</button>
    </form>
    <button class="account-secondary" type="button" id="alternarConta" ${tipo === "admin" ? "hidden" : ""}>
      ${registro ? "Já tenho conta" : "Criar uma conta"}
    </button>
  `;

  const modal = criarModal(conteudo);
  const form = modal.querySelector("#formConta");
  const mensagem = modal.querySelector("#contaMensagem");
  const camposRegistro = modal.querySelector("#camposRegistro");
  const botaoEnvio = modal.querySelector(".account-primary");
  const alternar = modal.querySelector("#alternarConta");
  const campoTelefone = modal.querySelector("#contaTelefone");
  if (campoTelefone) {
    campoTelefone.addEventListener("input", () => {
      campoTelefone.value = campoTelefone.value.replace(/\D/g, "").slice(0, 11);
    });
  }

  let tipoAtual = tipo === "admin" ? "admin" : "cliente";
  let modoAtual = tipo === "cadastrar" ? "registrar" : "login";

  modal.querySelectorAll(".account-tab").forEach(tab => {
    tab.addEventListener("click", () => {
      tipoAtual = tab.dataset.form;
      modoAtual = "login";
      camposRegistro.hidden = true;
      modal.querySelectorAll(".account-tab").forEach(item => item.classList.remove("active"));
      tab.classList.add("active");
      botaoEnvio.textContent = "Entrar";
      if (alternar) alternar.hidden = tipoAtual === "admin";
      mensagem.textContent = "";
      modal.querySelector("h2").textContent = "Entrar";
    });
  });

  if (alternar) {
    alternar.addEventListener("click", () => {
      modoAtual = modoAtual === "login" ? "registrar" : "login";
      camposRegistro.hidden = modoAtual !== "registrar";
      alternar.textContent = modoAtual === "registrar" ? "Já tenho conta" : "Criar uma conta";
      botaoEnvio.textContent = modoAtual === "registrar" ? "Criar conta" : "Entrar";
      modal.querySelector("h2").textContent = modoAtual === "registrar" ? "Criar conta" : "Entrar";
      mensagem.textContent = "";
    });
  }

  form.addEventListener("submit", async evento => {
    evento.preventDefault();
    mensagem.textContent = "";

    try {
      if (modoAtual === "registrar") {
        await requisicaoConta("registrar", {
          nome: modal.querySelector("#contaNome").value.trim(),
          telefone: modal.querySelector("#contaTelefone").value.trim(),
          email: modal.querySelector("#contaEmail").value.trim(),
          senha: modal.querySelector("#contaSenha").value
        });
      } else {
        await requisicaoConta("login", {
          tipo: tipoAtual,
          email: modal.querySelector("#contaEmail").value.trim(),
          senha: modal.querySelector("#contaSenha").value
        });
      }
      modal.remove();
      atualizarConta();
    } catch (erro) {
      mensagem.textContent = erro.message;
    }
  });
}

async function mostrarContaLogada(conta) {
  const admin = conta.tipo === "admin";
  const conteudo = `
    <h2 id="tituloConta"></h2>
    ${admin ? "<p>Acesso administrativo ativo.</p>" : `
      <section class="account-details" aria-label="Dados da conta">
        <div class="account-detail">
          <span class="account-detail-label">E-mail</span>
          <p class="account-detail-value" id="emailConta"></p>
        </div>
        <div class="account-detail">
          <span class="account-detail-label">Telefone</span>
          <p class="account-detail-value" id="telefoneConta"></p>
        </div>
      </section>
    `}
    ${admin ? "" : `<button class="account-secondary account-history-button" type="button" id="verHistorico">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
        <path d="M7 3v4M17 3v4M3 10h18M8 14h3M8 17h6"></path>
      </svg>
      Histórico de agendamentos
    </button>`}
    ${admin ? '<button class="account-primary" type="button" id="abrirPainel">Abrir painel administrativo</button>' : '<button class="account-primary" type="button" id="irAgendamento">Fazer agendamento</button>'}
    <button class="account-secondary" type="button" id="logoutConta">Sair da conta</button>
  `;
  const modal = criarModal(conteudo);
  modal.querySelector("#tituloConta").textContent = `Olá, ${admin ? "Administrador" : conta.nome}`;
  if (!admin) {
    modal.querySelector("#emailConta").textContent = mascararEmail(conta.email);
    modal.querySelector("#telefoneConta").textContent = conta.telefone || "Não informado";
  }

  const historico = modal.querySelector("#verHistorico");
  if (historico) {
    historico.addEventListener("click", () => mostrarHistoricoAgendamentos(conta));
  }

  const painel = modal.querySelector("#abrirPainel");
  if (painel) painel.addEventListener("click", () => {
    window.location.href = "agenda.php?admin=1";
  });

  const agendamento = modal.querySelector("#irAgendamento");
  if (agendamento) agendamento.addEventListener("click", () => {
    window.location.href = "agenda.php";
  });

  modal.querySelector("#logoutConta").addEventListener("click", async () => {
    await requisicaoConta("logout", {});
    modal.remove();
    atualizarConta();
  });
}

async function mostrarHistoricoAgendamentos(conta) {
  const modal = criarModal(`
    <h2>Histórico de agendamentos</h2>
    <div class="account-history-list" id="historicoAgendamentos">
      <p class="account-message">Carregando histórico...</p>
    </div>
    <button class="account-secondary" type="button" id="voltarConta">Voltar à conta</button>
  `);
  modal.querySelector("#voltarConta").addEventListener("click", () => mostrarContaLogada(conta));

  const lista = modal.querySelector("#historicoAgendamentos");
  try {
    const resultado = await requisicaoConta("historico", null, "GET");
    lista.replaceChildren();

    if (!resultado.agendamentos.length) {
      const vazio = document.createElement("p");
      vazio.textContent = "Você ainda não tem agendamentos.";
      lista.appendChild(vazio);
      return;
    }

    resultado.agendamentos.forEach(agendamento => {
      const item = document.createElement("article");
      item.className = "account-history-item";

      const servico = document.createElement("h3");
      servico.textContent = agendamento.servico_nome || "Serviço indisponível";

      const dataPartes = String(agendamento.data || "").split("-");
      const data = dataPartes.length === 3
        ? `${dataPartes[2]}/${dataPartes[1]}/${dataPartes[0]}`
        : String(agendamento.data || "");
      const horario = String(agendamento.horario || "").slice(0, 5);
      const detalhes = document.createElement("p");
      detalhes.textContent = `${data} às ${horario}`;

      const status = document.createElement("p");
      status.className = "account-history-status";
      const rotulosStatus = {
        pendente: "Pendente",
        confirmado: "Confirmado",
        concluido: "Concluído",
        falta: "Falta",
        falta_justificada: "Falta com justificativa",
        cancelado: "Cancelado"
      };
      status.textContent = `Status: ${rotulosStatus[agendamento.status] || agendamento.status || "não informado"}`;

      item.append(servico, detalhes, status);
      if (agendamento.justificativa_falta) {
        const justificativa = document.createElement("p");
        justificativa.textContent = `Justificativa: ${agendamento.justificativa_falta}`;
        item.appendChild(justificativa);
      }
      lista.appendChild(item);
    });
  } catch (erro) {
    lista.replaceChildren();
    const mensagem = document.createElement("p");
    mensagem.className = "account-message";
    mensagem.textContent = erro.message;
    lista.appendChild(mensagem);
  }
}

async function atualizarConta() {
  if (!loginIcon) return;
  try {
    const conta = await requisicaoConta("conta", null, "GET");
    const rotulo = loginIcon.querySelector(".login-label");
    loginIcon.classList.toggle("logged", conta.autenticado);
    loginIcon.title = conta.autenticado ? `Conta: ${conta.nome}` : "Registrar ou entrar";
    loginIcon.dataset.logado = conta.autenticado ? "true" : "false";
    loginIcon.setAttribute("aria-label", conta.autenticado ? "Abrir minha conta" : "Registrar ou entrar");
    if (rotulo) rotulo.textContent = conta.autenticado ? conta.nome : "Registrar";
    loginIcon.onclick = conta.autenticado
      ? () => mostrarContaLogada(conta)
      : () => mostrarFormulario("cadastrar");
  } catch (erro) {
    const rotulo = loginIcon.querySelector(".login-label");
    loginIcon.setAttribute("aria-label", "Registrar ou entrar");
    if (rotulo) rotulo.textContent = "Registrar";
    loginIcon.onclick = () => mostrarFormulario("cadastrar");
  }
}

if (loginIcon) {
  loginIcon.onclick = () => mostrarFormulario("cadastrar");
  atualizarConta();
}