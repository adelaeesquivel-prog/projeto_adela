<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AgendamentoService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? 0);

if ($id > 0) {
    try {
        $agendamentoService = new AgendamentoService();
        $agendamentoService->excluir($id);
    } catch (Throwable $e) {
        Utils::registrarLog($e);
    }
}

header("Location: agendamento.php");
exit;