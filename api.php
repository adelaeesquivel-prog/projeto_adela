<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

header('Content-Type: application/json; charset=utf-8');

const HORARIOS = [
    '08:00', '08:30', '09:00', '09:30',
    '10:00', '10:30', '11:00', '11:30',
    '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30',
    '17:00', '17:30', '18:00',
];

function responder(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function lerCorpo(): array
{
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder(['erro' => 'Envie os dados no formato JSON.'], 400);
    }

    return $dados;
}

function caminhoDados(): string
{
    $diretorio = getenv('LEO_DATA_DIR');
    if ($diretorio === false || $diretorio === '') {
        $diretorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'projeto_adela_dados';
    }

    if (!is_dir($diretorio) && !mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
        responder(['erro' => 'Não foi possível criar a pasta de dados do site.'], 500);
    }

    return $diretorio . DIRECTORY_SEPARATOR . 'dados.json';
}

function dadosPadrao(): array
{
    return [
        'servicos' => [
            ['id' => 'corte-cabelo', 'nome' => 'Corte de cabelo', 'preco' => 30],
            ['id' => 'barba', 'nome' => 'Barba', 'preco' => 20],
            ['id' => 'cabelo-barba', 'nome' => 'Cabelo + Barba', 'preco' => 45],
            ['id' => 'sobrancelha', 'nome' => 'Sobrancelha', 'preco' => 10],
        ],
        'agendamentos' => [],
    ];
}

function lerDados(): array
{
    $arquivo = caminhoDados();
    if (!is_file($arquivo)) {
        return dadosPadrao();
    }

    $dados = json_decode((string) file_get_contents($arquivo), true);
    if (
        !is_array($dados)
        || !isset($dados['servicos'], $dados['agendamentos'])
        || !is_array($dados['servicos'])
        || !is_array($dados['agendamentos'])
    ) {
        responder(['erro' => 'O arquivo de dados do site está inválido.'], 500);
    }

    return $dados;
}

function gravarDados(array $dados): void
{
    $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents(caminhoDados(), $json, LOCK_EX) === false) {
        responder(['erro' => 'Não foi possível salvar os dados. Verifique a permissão da pasta de dados.'], 500);
    }
}

function exigirAdmin(): void
{
    if (empty($_SESSION['admin'])) {
        responder(['erro' => 'Faça login como administrador para continuar.'], 401);
    }
}

function validarData(string $data): bool
{
    $dataFormatada = DateTime::createFromFormat('!Y-m-d', $data);
    return $dataFormatada !== false && $dataFormatada->format('Y-m-d') === $data;
}

$bloqueio = fopen(caminhoDados() . '.lock', 'c');
if ($bloqueio === false || !flock($bloqueio, LOCK_EX)) {
    responder(['erro' => 'Não foi possível acessar a pasta de dados.'], 500);
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$acao = $_GET['acao'] ?? '';

if ($metodo === 'GET') {
    $dados = lerDados();
    if ($acao === 'servicos') {
        responder(['servicos' => $dados['servicos']]);
    }

    if ($acao === 'disponibilidade') {
        $data = (string) ($_GET['data'] ?? '');
        if (!validarData($data)) {
            responder(['erro' => 'Escolha uma data válida.'], 400);
        }

        $ocupados = [];
        foreach ($dados['agendamentos'] as $agendamento) {
            if (($agendamento['data'] ?? '') === $data) {
                $ocupados[] = $agendamento['horario'];
            }
        }
        responder(['ocupados' => $ocupados, 'horarios' => HORARIOS]);
    }

    if ($acao === 'admin') {
        responder(['autenticado' => !empty($_SESSION['admin'])]);
    }

    if ($acao === 'agendamentos') {
        exigirAdmin();
        responder(['agendamentos' => $dados['agendamentos']]);
    }

    responder(['erro' => 'Ação não encontrada.'], 404);
}

if ($metodo !== 'POST') {
    header('Allow: GET, POST');
    responder(['erro' => 'Método não permitido.'], 405);
}

$entrada = lerCorpo();
$acao = (string) ($entrada['acao'] ?? '');

if ($acao === 'login') {
    $senhaAdmin = getenv('LEO_ADMIN_PASSWORD');
    if ($senhaAdmin === false || $senhaAdmin === '') {
        responder(['erro' => 'Configure a variável LEO_ADMIN_PASSWORD para habilitar o painel.'], 503);
    }
    if (!is_string($entrada['senha'] ?? null) || !hash_equals($senhaAdmin, $entrada['senha'])) {
        responder(['erro' => 'Senha incorreta.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    responder(['autenticado' => true]);
}

if ($acao === 'logout') {
    $_SESSION = [];
    session_regenerate_id(true);
    responder(['autenticado' => false]);
}

if ($acao === 'agendar') {
    $nome = trim((string) ($entrada['nome'] ?? ''));
    $telefone = trim((string) ($entrada['telefone'] ?? ''));
    $servicoId = (string) ($entrada['servico_id'] ?? '');
    $data = (string) ($entrada['data'] ?? '');
    $horario = (string) ($entrada['horario'] ?? '');

    if ($nome === '' || mb_strlen($nome) > 120 || mb_strlen($telefone) < 8 || mb_strlen($telefone) > 30) {
        responder(['erro' => 'Informe seu nome e um telefone válido.'], 400);
    }
    if (!validarData($data) || $data < date('Y-m-d') || !in_array($horario, HORARIOS, true)) {
        responder(['erro' => 'Escolha uma data e um horário válidos.'], 400);
    }
    if ($data === date('Y-m-d') && $horario <= date('H:i')) {
        responder(['erro' => 'Esse horário já passou. Escolha outro horário.'], 400);
    }

    $dados = lerDados();
    $servico = null;
    foreach ($dados['servicos'] as $item) {
        if (($item['id'] ?? '') === $servicoId) {
            $servico = $item;
            break;
        }
    }
    if ($servico === null) {
        responder(['erro' => 'O serviço selecionado não está mais disponível. Atualize a página.'], 400);
    }
    foreach ($dados['agendamentos'] as $item) {
        if ($item['data'] === $data && $item['horario'] === $horario) {
            responder(['erro' => 'Esse horário acabou de ser reservado. Escolha outro.'], 409);
        }
    }

    $dados['agendamentos'][] = [
        'id' => bin2hex(random_bytes(8)),
        'nome' => $nome,
        'telefone' => $telefone,
        'servico' => $servico['nome'],
        'preco' => (float) $servico['preco'],
        'data' => $data,
        'horario' => $horario,
    ];
    gravarDados($dados);
    responder(['mensagem' => 'Agendamento realizado com sucesso.']);
}

exigirAdmin();
$dados = lerDados();

if ($acao === 'cancelar') {
    $id = (string) ($entrada['id'] ?? '');
    $quantidadeAnterior = count($dados['agendamentos']);
    $dados['agendamentos'] = array_values(array_filter(
        $dados['agendamentos'],
        static fn (array $item): bool => ($item['id'] ?? '') !== $id
    ));
    if (count($dados['agendamentos']) === $quantidadeAnterior) {
        responder(['erro' => 'Agendamento não encontrado.'], 404);
    }
    gravarDados($dados);
    responder(['agendamentos' => $dados['agendamentos']]);
}

if ($acao === 'adicionar_servico') {
    $nome = trim((string) ($entrada['nome'] ?? ''));
    $preco = $entrada['preco'] ?? null;
    if ($nome === '' || mb_strlen($nome) > 80 || !is_numeric($preco) || (float) $preco < 0) {
        responder(['erro' => 'Informe um nome e um preço válido para o serviço.'], 400);
    }

    $dados['servicos'][] = [
        'id' => bin2hex(random_bytes(8)),
        'nome' => $nome,
        'preco' => round((float) $preco, 2),
    ];
    gravarDados($dados);
    responder(['servicos' => $dados['servicos']]);
}

if ($acao === 'excluir_servico') {
    $id = (string) ($entrada['id'] ?? '');
    $quantidadeAnterior = count($dados['servicos']);
    $dados['servicos'] = array_values(array_filter(
        $dados['servicos'],
        static fn (array $item): bool => ($item['id'] ?? '') !== $id
    ));
    if (count($dados['servicos']) === $quantidadeAnterior) {
        responder(['erro' => 'Serviço não encontrado.'], 404);
    }
    gravarDados($dados);
    responder(['servicos' => $dados['servicos']]);
}

responder(['erro' => 'Ação não encontrada.'], 404);
