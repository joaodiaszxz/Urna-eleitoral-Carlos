<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/View.php';
require_once __DIR__ . '/../app/Models/Eleitor.php';
require_once __DIR__ . '/../app/Models/Candidato.php';
require_once __DIR__ . '/../app/Models/Voto.php';
require_once __DIR__ . '/../app/Models/Eleicao.php';
require_once __DIR__ . '/../app/Models/Partido.php';
require_once __DIR__ . '/../app/Models/Admin.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/VotacaoController.php';
require_once __DIR__ . '/../app/Controllers/ResultadoController.php';

use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\VotacaoController;
use App\Controllers\ResultadoController;
use App\Core\View;

$action = $_GET['action'] ?? 'inicio';
$auth = new AuthController();
$admin = new AdminController();
$votacao = new VotacaoController();
$resultado = new ResultadoController();

$rotas = [
    'inicio' => fn() => View::render('inicio'),
    'login' => fn() => $auth->formulario(),
    'autenticar' => fn() => $auth->autenticar(),
    'sair' => fn() => $auth->sair(),
    'admin_login' => fn() => $admin->formularioLogin(),
    'admin_autenticar' => fn() => $admin->autenticar(),
    'admin_sair' => fn() => $admin->sair(),
    'admin' => fn() => $admin->painel(),
    'admin_eleicao_salvar' => fn() => $admin->salvarEleicao(),
    'admin_partido_salvar' => fn() => $admin->salvarPartido(),
    'admin_candidato_salvar' => fn() => $admin->salvarCandidato(),
    'votar' => fn() => $votacao->tela(),
    'confirmar' => fn() => $votacao->confirmar(),
    'resultado' => fn() => $resultado->mostrar(),
];

if (!isset($rotas[$action])) {
    http_response_code(404);
    View::render('erro', ['mensagem' => 'Página não encontrada.']);
    exit;
}
try {
    $rotas[$action]();
} catch (Throwable $e) {
    error_log((string)$e);
    http_response_code(500);
    View::render('erro', ['mensagem' => 'Ocorreu um erro. Verifique a configuração e os logs do servidor.']);
}
