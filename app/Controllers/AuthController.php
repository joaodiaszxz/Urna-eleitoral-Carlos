<?php
namespace App\Controllers;
use App\Core\View;
use App\Models\Eleitor;

final class AuthController {
    public function formulario(): void { View::render('login', ['erro' => null]); }
    public function autenticar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?action=login'); exit; }
        $token = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            View::render('login', ['erro' => 'Sessão expirou. Atualize a página e tente novamente.']); return;
        }
        $identificador = trim((string)($_POST['identificador'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        if ($identificador === '' || $senha === '') {
            View::render('login', ['erro' => 'Preencha identificação e senha.']); return;
        }
        $eleitor = Eleitor::porIdentificador($identificador);
        if (!$eleitor || !password_verify($senha, $eleitor['senha_hash']) || $eleitor['status'] !== 'ATIVO') {
            View::render('login', ['erro' => 'Credenciais inválidas ou eleitor não habilitado.']); return;
        }
        if ((int)$eleitor['votou'] === 1) {
            View::render('login', ['erro' => 'Este eleitor já participou da votação.']); return;
        }
        session_regenerate_id(true);
        unset($_SESSION['admin_id'], $_SESSION['admin_nome']);
        $_SESSION['eleitor_id'] = (int)$eleitor['id'];
        $_SESSION['eleitor_nome'] = $eleitor['nome'];
        $_SESSION['eleicao_id'] = (int)$eleitor['eleicao_id'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: ?action=votar'); exit;
    }
    public function sair(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: ?action=inicio'); exit;
    }
}
