<?php
declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Models\Agendamento;
use LeoBarbas\Models\Cliente;
use LeoBarbas\Models\Servico;
use LeoBarbas\Services\AdministradorService;
use LeoBarbas\Services\AgendamentoService;
use LeoBarbas\Services\ClienteService;
use LeoBarbas\Services\ServicoService;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

header('Content-Type: application/json; charset=utf-8');

const HORARIOS = [
    '08:00', '08:30', '09:00', '09:30',
    '10:00', '10:30', '11:00', '11:30',
    '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30',
    '17:00',
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

function exigirAdminApi(): void
{
    if (!Auth::usuarioEhAdmin()) {
        responder(['erro' => 'Faça login como administrador para continuar.'], 401);
    }
}

function validarData(string $data): bool
{
    $dataFormatada = DateTime::createFromFormat('!Y-m-d', $data);
    return $dataFormatada !== false && $dataFormatada->format('Y-m-d') === $data;
}

$servicoService = new ServicoService();
$agendamentoService = new AgendamentoService();
$clienteService = new ClienteService();
$adminService = new AdministradorService();

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$acao = $_GET['acao'] ?? '';

try {
    if ($metodo === 'GET') {
        if ($acao === 'servicos') {
            responder(['servicos' => $servicoService->listarTodos()]);
        }

        if ($acao === 'disponibilidade') {
            $data = (string) ($_GET['data'] ?? '');
            if (!validarData($data)) {
                responder(['erro' => 'Escolha uma data válida.'], 400);
            }

            $ocupados = [];
            foreach ($agendamentoService->listarTodos() as $agendamento) {
                if (($agendamento['data'] ?? '') === $data && ($agendamento['status'] ?? '') !== 'cancelado') {
                    $ocupados[] = substr((string) $agendamento['horario'], 0, 5);
                }
            }
            responder(['ocupados' => $ocupados, 'horarios' => HORARIOS]);
        }

        if ($acao === 'admin') {
            responder(['autenticado' => Auth::usuarioEhAdmin()]);
        }

        if ($acao === 'conta') {
            if (Auth::usuarioEhAdmin()) {
                responder([
                    'autenticado' => true,
                    'tipo' => 'admin',
                    'nome' => $_SESSION['nome'] ?? 'Administrador',
                ]);
            }

            if (Auth::usuarioEhCliente() && !empty($_SESSION['id'])) {
                $cliente = $clienteService->buscarPorId((int) $_SESSION['id']);
                if ($cliente !== null) {
                    responder([
                        'autenticado' => true,
                        'tipo' => 'cliente',
                        'nome' => $cliente['nome'],
                        'email' => $cliente['email'],
                        'telefone' => $cliente['telefone'],
                    ]);
                }
            }

            responder(['autenticado' => false]);
        }

        if ($acao === 'agendamentos') {
            exigirAdminApi();
            responder(['agendamentos' => $agendamentoService->listarTodos()]);
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
        $tipo = (string) ($entrada['tipo'] ?? 'admin');
        $senha = $entrada['senha'] ?? null;

        if (!is_string($senha)) {
            responder(['erro' => 'Senha incorreta.'], 401);
        }

        if ($tipo === 'admin') {
            $email = mb_strtolower(trim((string) ($entrada['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                responder(['erro' => 'Informe um e-mail válido.'], 400);
            }

            $admin = $adminService->buscarPorEmail($email);
            if ($admin === null || !password_verify($senha, $admin['senha'])) {
                responder(['erro' => 'E-mail ou senha incorretos.'], 401);
            }

            session_regenerate_id(true);
            Auth::login((int) $admin['id'], (string) $admin['nome'], 'admin');
            responder(['autenticado' => true, 'tipo' => 'admin', 'nome' => $admin['nome']]);
        }

        if ($tipo === 'cliente') {
            $email = mb_strtolower(trim((string) ($entrada['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                responder(['erro' => 'Informe um e-mail válido.'], 400);
            }

            $cliente = $clienteService->buscarPorEmail($email);
            if ($cliente === null || !password_verify($senha, $cliente['senha'])) {
                responder(['erro' => 'E-mail ou senha incorretos.'], 401);
            }

            session_regenerate_id(true);
            Auth::login((int) $cliente['id'], (string) $cliente['nome'], 'cliente');
            responder(['autenticado' => true, 'tipo' => 'cliente', 'nome' => $cliente['nome']]);
        }

        responder(['erro' => 'Tipo de login inválido.'], 400);
    }

    if ($acao === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        session_destroy();
        responder(['autenticado' => false]);
    }

    if ($acao === 'registrar') {
        $nome = trim((string) ($entrada['nome'] ?? ''));
        $email = mb_strtolower(trim((string) ($entrada['email'] ?? '')));
        $telefone = trim((string) ($entrada['telefone'] ?? ''));
        $senha = $entrada['senha'] ?? null;

        if ($nome === '' || mb_strlen($nome) > 120) {
            responder(['erro' => 'Informe um nome válido.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder(['erro' => 'Informe um e-mail válido.'], 400);
        }
        if (mb_strlen($telefone) < 8 || mb_strlen($telefone) > 30) {
            responder(['erro' => 'Informe um telefone válido.'], 400);
        }
        if (!is_string($senha) || mb_strlen($senha) < 6 || mb_strlen($senha) > 100) {
            responder(['erro' => 'A senha deve ter entre 6 e 100 caracteres.'], 400);
        }

        if ($clienteService->buscarPorEmail($email) !== null) {
            responder(['erro' => 'Já existe uma conta com este e-mail.'], 409);
        }

        $cliente = new Cliente(
            $nome,
            $email,
            password_hash($senha, PASSWORD_DEFAULT),
            $telefone
        );
        $clienteService->inserir($cliente);

        $clienteCriado = $clienteService->buscarPorEmail($email);
        session_regenerate_id(true);
        if ($clienteCriado !== null) {
            Auth::login((int) $clienteCriado['id'], (string) $clienteCriado['nome'], 'cliente');
        }

        responder(['autenticado' => true, 'tipo' => 'cliente', 'nome' => $nome]);
    }

    if ($acao === 'agendar') {
        $nome = trim((string) ($entrada['nome'] ?? ''));
        $telefone = trim((string) ($entrada['telefone'] ?? ''));
        $servicoId = (int) ($entrada['servico_id'] ?? 0);
        $data = (string) ($entrada['data'] ?? '');
        $horario = (string) ($entrada['horario'] ?? '');
        $observacao = isset($entrada['observacao']) ? trim((string) $entrada['observacao']) : null;

        if ($nome === '' || mb_strlen($nome) > 120 || mb_strlen($telefone) < 8 || mb_strlen($telefone) > 30) {
            responder(['erro' => 'Informe seu nome e um telefone válido.'], 400);
        }
        if ($servicoId <= 0 || !validarData($data) || $data < date('Y-m-d') || !in_array($horario, HORARIOS, true)) {
            responder(['erro' => 'Escolha uma data e um horário válidos.'], 400);
        }
        if ($data === date('Y-m-d') && $horario <= date('H:i')) {
            responder(['erro' => 'Esse horário já passou. Escolha outro horário.'], 400);
        }

        $servico = $servicoService->buscarPorId($servicoId);
        if ($servico === null) {
            responder(['erro' => 'O serviço selecionado não está mais disponível. Atualize a página.'], 400);
        }

        foreach ($agendamentoService->listarTodos() as $item) {
            $horarioItem = substr((string) ($item['horario'] ?? ''), 0, 5);
            if (($item['data'] ?? '') === $data && $horarioItem === $horario && ($item['status'] ?? '') !== 'cancelado') {
                responder(['erro' => 'Esse horário acabou de ser reservado. Escolha outro.'], 409);
            }
        }

        $adminId = null;
        if (Auth::usuarioEhAdmin() && !empty($_SESSION['id'])) {
            $adminId = (int) $_SESSION['id'];
        } else {
            $administradores = $adminService->listarTodos();
            if (!empty($administradores)) {
                $adminId = (int) $administradores[0]['id'];
            }
        }

        if ($adminId === null || $adminId <= 0) {
            responder(['erro' => 'Não há administrador cadastrado para receber o agendamento.'], 503);
        }

        // Resolve cliente_id: usa o cliente logado ou vincula/cria pelo telefone/e-mail se necessário
        $clienteId = null;
        if (Auth::usuarioEhCliente() && !empty($_SESSION['id'])) {
            $clienteId = (int) $_SESSION['id'];
        } else {
            $emailCliente = mb_strtolower(trim((string) ($entrada['email'] ?? '')));
            if ($emailCliente !== '' && filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
                $existente = $clienteService->buscarPorEmail($emailCliente);
                if ($existente !== null) {
                    $clienteId = (int) $existente['id'];
                }
            }
        }

        if ($clienteId === null) {
            // Se não houver cliente cadastrado com e-mail, cria um registro básico para satisfazer a FK cliente_id
            $emailGerado = 'cliente_' . bin2hex(random_bytes(5)) . '@local.invalid';
            $senhaAleatoria = password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT);
            $clienteService->inserir(new Cliente($nome, $emailGerado, $senhaAleatoria, $telefone));
            $novoCliente = $clienteService->buscarPorEmail($emailGerado);
            if ($novoCliente === null) {
                responder(['erro' => 'Não foi possível registrar os dados do cliente.'], 500);
            }
            $clienteId = (int) $novoCliente['id'];
        }

        $agendamento = new Agendamento(
            $data,
            $horario . ':00',
            $clienteId,
            $servicoId,
            'pendente',
            $observacao !== '' ? $observacao : null,
            null,
            $adminId
        );
        $agendamentoService->inserir($agendamento);

        responder(['mensagem' => 'Agendamento realizado com sucesso.']);
    }

    exigirAdminApi();

    if ($acao === 'cancelar') {
        $id = (int) ($entrada['id'] ?? 0);
        if ($id <= 0 || $agendamentoService->buscarPorId($id) === null) {
            responder(['erro' => 'Agendamento não encontrado.'], 404);
        }

        $agendamentoService->excluir($id);
        responder(['agendamentos' => $agendamentoService->listarTodos()]);
    }

    if ($acao === 'adicionar_servico') {
        $nome = trim((string) ($entrada['nome'] ?? ''));
        $preco = $entrada['preco'] ?? null;
        $duracao = trim((string) ($entrada['duracao'] ?? '30 min'));
        $descricao = isset($entrada['descricao']) && trim((string) $entrada['descricao']) !== ''
            ? trim((string) $entrada['descricao'])
            : null;

        if ($nome === '' || mb_strlen($nome) > 80 || !is_numeric($preco) || (float) $preco < 0) {
            responder(['erro' => 'Informe um nome e um preço válido para o serviço.'], 400);
        }

        $servico = new Servico(
            $nome,
            round((float) $preco, 2),
            $duracao !== '' ? $duracao : '30 min',
            $descricao
        );
        $servicoService->inserir($servico);

        responder(['servicos' => $servicoService->listarTodos()]);
    }

    if ($acao === 'excluir_servico') {
        $id = (int) ($entrada['id'] ?? 0);
        if ($id <= 0 || $servicoService->buscarPorId($id) === null) {
            responder(['erro' => 'Serviço não encontrado.'], 404);
        }

        $servicoService->excluir($id);
        responder(['servicos' => $servicoService->listarTodos()]);
    }

    responder(['erro' => 'Ação não encontrada.'], 404);
} catch (Throwable $e) {
    responder(['erro' => $e->getMessage() ?: 'Erro interno no servidor.'], 500);
}