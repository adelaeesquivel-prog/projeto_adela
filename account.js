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
        <input id="contaTelefone" type="tel" maxlength="30" autocomplete="tel">
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
    <h2>Olá, ${admin ? "Administrador" : conta.nome}</h2>
    ${admin ? "<p>Acesso administrativo ativo.</p>" : `<p>${conta.email}</p><p>${conta.telefone}</p>`}
    ${admin ? '<button class="account-primary" type="button" id="abrirPainel">Abrir painel administrativo</button>' : '<button class="account-primary" type="button" id="irAgendamento">Fazer agendamento</button><button class="account-secondary" type="button" id="irHistorico">Ver histórico de agendamentos</button>'}
    <button class="account-secondary" type="button" id="logoutConta">Sair da conta</button>
  `;
  const modal = criarModal(conteudo);

  const painel = modal.querySelector("#abrirPainel");
  if (painel) painel.addEventListener("click", () => {
    window.location.href = "agenda.php?admin=1";
  });

  const agendamento = modal.querySelector("#irAgendamento");
  if (agendamento) agendamento.addEventListener("click", () => {
    window.location.href = "agenda.php";
  });

  const historico = modal.querySelector("#irHistorico");
  if (historico) historico.addEventListener("click", () => {
    window.location.href = "historico.php";
  });

  modal.querySelector("#logoutConta").addEventListener("click", async () => {
    await requisicaoConta("logout", {});
    modal.remove();
    atualizarConta();
  });
}

async function atualizarConta() {
  if (!loginIcon) return;
  try {
    const conta = await requisicaoConta("conta", null, "GET");
    const rotulo = loginIcon.querySelector(".login-label");
    loginIcon.classList.toggle("logged", conta.autenticado);
    loginIcon.title = conta.autenticado ? `Conta: ${conta.nome}` : "Registrar ou entrar";
    loginIcon.dataset.logado = conta.autenticado ? "true" : "false";
    if (rotulo) rotulo.textContent = conta.autenticado ? conta.nome : "Registrar";
    loginIcon.onclick = conta.autenticado
      ? () => mostrarContaLogada(conta)
      : () => mostrarFormulario("cadastrar");
  } catch (erro) {
    const rotulo = loginIcon.querySelector(".login-label");
    if (rotulo) rotulo.textContent = "Registrar";
    loginIcon.onclick = () => mostrarFormulario("cadastrar");
  }
}

if (loginIcon) {
  loginIcon.onclick = () => mostrarFormulario("cadastrar");
  atualizarConta();
}