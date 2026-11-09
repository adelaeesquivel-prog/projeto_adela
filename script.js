

// DADOS SALVOS NO NAVEGADOR
 
let servicos = JSON.parse(

  localStorage.getItem("leo_servicos")

) || [

  { nome: "Corte de cabelo", preco: 30 },

  { nome: "Barba", preco: 20 },

  { nome: "Cabelo + Barba", preco: 45 },

  { nome: "Sobrancelha", preco: 10 }

];
 
let agendamentos = JSON.parse(

  localStorage.getItem("leo_agendamentos")

) || [];
 
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
 
function salvarDados() {

  localStorage.setItem("leo_servicos", JSON.stringify(servicos));

  localStorage.setItem("leo_agendamentos", JSON.stringify(agendamentos));

}
 
// CADASTRAR E MOSTRAR SERVIÇOS
 
function carregarServicos() {

  servicoInput.innerHTML =

    '<option value="">Selecione um serviço</option>';
 
  servicos.forEach((s, i) => {

    const option = document.createElement("option");

    option.value = i;

    option.textContent =

      `${s.nome} - R$ ${Number(s.preco).toFixed(2)}`;
 
    servicoInput.appendChild(option);

  });

}
 
// HORÁRIOS DISPONÍVEIS
 
function carregarHorarios() {

  horarioInput.innerHTML =

    '<option value="">Selecione um horário</option>';
 
  const data = dataInput.value;
 
  if (!data) return;
 
  const hoje = new Date();

  const hojeString =

    hoje.getFullYear() + "-" +

    String(hoje.getMonth() + 1).padStart(2, "0") + "-" +

    String(hoje.getDate()).padStart(2, "0");
 
  horarios.forEach(hora => {

    const ocupado = agendamentos.some(a =>

      a.data === data && a.horario === hora

    );
 
    const dataHora = new Date(data + "T" + hora + ":00");

    const passado = dataHora <= hoje;
 
    const option = document.createElement("option");

    option.value = hora;
 
    if (ocupado) {

      option.textContent = hora + " - Ocupado";

      option.disabled = true;

    } else if (data < hojeString ||

      (data === hojeString && passado)) {

      option.textContent = hora + " - Indisponível";

      option.disabled = true;

    } else {

      option.textContent = hora + " - Disponível";

    }
 
    horarioInput.appendChild(option);

  });

}
 
dataInput.min = (() => {

  const d = new Date();

  return d.getFullYear() + "-" +

    String(d.getMonth() + 1).padStart(2, "0") + "-" +

    String(d.getDate()).padStart(2, "0");

})();
 
dataInput.addEventListener("change", carregarHorarios);
 
// AGENDAMENTO
 
document.getElementById("formAgendamento")

.addEventListener("submit", function(e) {

  e.preventDefault();
 
  const nome = document.getElementById("nome").value.trim();

  const telefone = document.getElementById("telefone").value.trim();

  const servicoIndex = servicoInput.value;

  const data = dataInput.value;

  const horario = horarioInput.value;
 
  if (!nome || !telefone || servicoIndex === "" ||

      !data || !horario) {

    alert("Preencha todos os campos!");

    return;

  }
 
  const ocupado = agendamentos.some(a =>

    a.data === data && a.horario === horario

  );
 
  if (ocupado) {

    alert("Este horário já está ocupado!");

    carregarHorarios();

    return;

  }
 
  const servico = servicos[Number(servicoIndex)];
 
  agendamentos.push({

    id: Date.now(),

    nome,

    telefone,

    servico: servico.nome,

    preco: servico.preco,

    data,

    horario

  });
 
  salvarDados();
 
  alert("Agendamento realizado com sucesso!");
 
  this.reset();

  carregarHorarios();

});
 
// ADMINISTRADOR
 
document.getElementById("linkAdmin")

.addEventListener("click", abrirAdmin);
 
function abrirAdmin() {

  const senha = prompt("Digite a senha do administrador:");
 
  if (senha !== "1234") {

    alert("Senha incorreta!");

    return;

  }
 
  document.getElementById("areaCliente").classList.add("hidden");

  document.getElementById("areaAdmin").classList.remove("hidden");

  document.getElementById("linkAdmin").classList.add("hidden");
 
  mostrarAgendamentos();

  mostrarServicos();

}
 
document.getElementById("btnVoltar")

.addEventListener("click", sairAdmin);
 
function sairAdmin() {

  document.getElementById("areaAdmin").classList.add("hidden");

  document.getElementById("areaCliente").classList.remove("hidden");

  document.getElementById("linkAdmin").classList.remove("hidden");

}
 
// LISTA DE AGENDAMENTOS
 
function mostrarAgendamentos() {

  const lista = document.getElementById("listaAgendamentos");

  lista.innerHTML = "";
 
  if (agendamentos.length === 0) {

    lista.textContent = "Nenhum agendamento cadastrado.";

    return;

  }
 
  const ordenados = [...agendamentos].sort((a, b) =>

    (a.data + a.horario).localeCompare(b.data + b.horario)

  );
 
  ordenados.forEach(a => {

    const div = document.createElement("div");

    div.className = "agendamento";
 
    const info = document.createElement("div");

    info.textContent =

      `Cliente: ${a.nome} | Telefone: ${a.telefone}`;
 
    const detalhes = document.createElement("div");

    detalhes.textContent =

      `Serviço: ${a.servico} | R$ ${Number(a.preco).toFixed(2)} | Data: ${a.data} | Horário: ${a.horario}`;
 
    const cancelar = document.createElement("button");

    cancelar.textContent = "Cancelar";

    cancelar.onclick = () => cancelarAgendamento(a.id);
 
    div.append(info, detalhes, cancelar);

    lista.appendChild(div);

  });

}
 
function cancelarAgendamento(id) {

  if (!confirm("Deseja cancelar este agendamento?")) return;
 
  agendamentos = agendamentos.filter(a => a.id !== id);
 
  salvarDados();

  mostrarAgendamentos();

  carregarHorarios();

}
 
// CADASTRO DE SERVIÇOS
 
document.getElementById("btnCadastrarServico")

.addEventListener("click", cadastrarServico);
 
function cadastrarServico() {

  const nome = document.getElementById("novoServico").value.trim();

  const campoPreco = document.getElementById("precoServico").value;

  const preco = Number(campoPreco);
 
  if (!nome || campoPreco === "" ||

      !Number.isFinite(preco) || preco < 0) {

    alert("Informe o nome e um preço válido!");

    return;

  }
 
  servicos.push({ nome, preco });
 
  salvarDados();

  carregarServicos();

  mostrarServicos();
 
  document.getElementById("novoServico").value = "";

  document.getElementById("precoServico").value = "";
 
  alert("Serviço cadastrado!");

}
 
function mostrarServicos() {

  const lista = document.getElementById("listaServicos");

  lista.innerHTML = "";
 
  servicos.forEach((s, i) => {

    const div = document.createElement("div");

    div.className = "agendamento";
 
    const texto = document.createElement("span");

    texto.textContent =

      `${s.nome} - R$ ${Number(s.preco).toFixed(2)}`;
 
    const botao = document.createElement("button");

    botao.textContent = "Excluir";
 
    botao.onclick = () => {

      if (!confirm("Excluir este serviço?")) return;
 
      servicos.splice(i, 1);

      salvarDados();

      carregarServicos();

      mostrarServicos();

    };
 
    div.append(texto, botao);

    lista.appendChild(div);

  });

}
 
// INICIALIZAÇÃO
 
carregarServicos();

carregarHorarios();

 