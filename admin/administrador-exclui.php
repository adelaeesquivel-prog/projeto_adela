<?php
require_once __DIR__ . "/../vendor/autoload.php";

use LeoBarbas\Auth\Auth;
use LeoBarbas\Helpers\Utils;
use LeoBarbas\Services\AdministradorService;

Auth::exigirAdmin();

$id = (int) ($_GET["id"] ?? 0);

if ($id > 0) {
    try {
        $administradorService = new AdministradorService();
        $administradorService->excluir($id);
    } catch (Throwable $e) {
        Utils::registrarLog($e);
    }
}

header("Location: administrador.php");
exit;