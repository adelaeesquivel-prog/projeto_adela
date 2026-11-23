<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\ServicoService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? 0);

if ($id > 0) {
    try {
        $servicoService = new ServicoService();
        $servicoService->excluir($id);
    } catch (Throwable $e) {
        Utils::registrarLog($e);
    }
}

header("Location: servico.php");
exit;