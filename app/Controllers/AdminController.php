<?php
namespace App\Controllers;
use App\Core\View;
use App\Models\Admin;
use App\Models\Candidato;
use App\Models\Eleicao;
use App\Models\Partido;
use PDOException;

final class AdminController {
    public function formularioLogin(?string $erro = null): void {
        if (!empty($_SESSION['admin_id'])) {
            header('Location: ?action=admin');
            return;
        }
        $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        View::render('admin-login', ['erro' => $erro]);
    }

    public function autenticar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->csrfValido()) {
            http_response_code(400);
            $this->formularioLogin('Sessão expirou ou requisição inválida. Atualize a página e tente novamente.');
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        if ($email === '' || $senha === '') {
            $this->formularioLogin('Preencha e-mail e senha.');
            return;
        }

        $usuario = Admin::autenticar($email, $senha);
        if (!$usuario) {
            $this->formularioLogin('Credenciais inválidas ou usuário sem permissão administrativa.');
            return;
        }

        session_regenerate_id(true);
        unset($_SESSION['eleitor_id'], $_SESSION['eleitor_nome'], $_SESSION['eleicao_id']);
        $_SESSION['admin_id'] = $usuario['id'];
        $_SESSION['admin_nome'] = $usuario['nome'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: ?action=admin');
    }

    public function painel(): void {
        if (!$this->exigeAdmin()) {
            return;
        }
        $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        $eleicaoId = $this->idDaQuery('editar_eleicao');
        $partidoId = $this->idDaQuery('editar_partido');
        $candidatoId = $this->idDaQuery('editar_candidato');

        View::render('admin', [
            'eleicoes' => Eleicao::todas(),
            'partidos' => Partido::todos(),
            'candidatos' => Candidato::todos(),
            'eleicoesEditaveis' => array_values(array_filter(Eleicao::todas(), static fn(array $e): bool => $e['status'] === 'RASCUNHO')),
            'eleicaoEditando' => $eleicaoId ? Eleicao::porId($eleicaoId) : null,
            'partidoEditando' => $partidoId ? Partido::porId($partidoId) : null,
            'candidatoEditando' => $candidatoId ? Candidato::porId($candidatoId) : null,
            'mensagem' => $_SESSION['admin_mensagem'] ?? null,
            'mensagemErro' => $_SESSION['admin_erro'] ?? null,
        ]);
        unset($_SESSION['admin_mensagem'], $_SESSION['admin_erro']);
    }

    public function salvarEleicao(): void {
        if (!$this->autorizarPost()) {
            return;
        }
        try {
            $id = $this->idDoPost();
        } catch (\InvalidArgumentException $e) {
            $this->falha($e->getMessage());
            return;
        }
        $nome = trim((string)($_POST['nome'] ?? ''));
        $descricao = trim((string)($_POST['descricao'] ?? ''));
        $status = (string)($_POST['status'] ?? 'RASCUNHO');
        if ($nome === '' || mb_strlen($nome) > 150 || mb_strlen($descricao) > 5000) {
            $this->falha('Informe um nome de até 150 caracteres e uma descrição de até 5.000 caracteres.');
            return;
        }
        try {
            Eleicao::salvar($id, $nome, $descricao, $status);
            $this->sucesso('Eleição salva.');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $this->falha($e->getMessage());
            return;
        } catch (PDOException $e) {
            error_log((string)$e);
            $this->falha('Não foi possível salvar a eleição. Verifique os dados e tente novamente.');
            return;
        }
        $this->redirecionar();
    }

    public function salvarPartido(): void {
        if (!$this->autorizarPost()) {
            return;
        }
        try {
            $id = $this->idDoPost();
        } catch (\InvalidArgumentException $e) {
            $this->falha($e->getMessage());
            return;
        }
        $nome = trim((string)($_POST['nome'] ?? ''));
        $sigla = strtoupper(trim((string)($_POST['sigla'] ?? '')));
        $numero = filter_var($_POST['numero'] ?? null, FILTER_VALIDATE_INT);
        if ($nome === '' || mb_strlen($nome) > 120 || !preg_match('/^[A-Z0-9]{2,12}$/', $sigla) || $numero === false || $numero < 1 || $numero > 65535) {
            $this->falha('Informe nome, sigla de 2 a 12 caracteres (letras/números) e número entre 1 e 65.535.');
            return;
        }
        try {
            Partido::salvar($id, $nome, $sigla, $numero);
            $this->sucesso('Partido salvo.');
        } catch (\RuntimeException $e) {
            $this->falha($e->getMessage());
            return;
        } catch (PDOException $e) {
            error_log((string)$e);
            $this->falha('Não foi possível salvar o partido. Sigla e número devem ser únicos.');
            return;
        }
        $this->redirecionar();
    }

    public function salvarCandidato(): void {
        if (!$this->autorizarPost()) {
            return;
        }
        try {
            $id = $this->idDoPost();
        } catch (\InvalidArgumentException $e) {
            $this->falha($e->getMessage());
            return;
        }
        $nome = trim((string)($_POST['nome'] ?? ''));
        $cargo = trim((string)($_POST['cargo'] ?? ''));
        $numero = filter_var($_POST['numero'] ?? null, FILTER_VALIDATE_INT);
        $partidoId = filter_var($_POST['partido_id'] ?? null, FILTER_VALIDATE_INT);
        $eleicaoId = filter_var($_POST['eleicao_id'] ?? null, FILTER_VALIDATE_INT);
        if ($nome === '' || mb_strlen($nome) > 120 || $cargo === '' || mb_strlen($cargo) > 80 ||
            $numero === false || $numero < 0 || $numero > 65535 ||
            $partidoId === false || $partidoId < 1 || $eleicaoId === false || $eleicaoId < 1) {
            $this->falha('Preencha nome, cargo, número válido, partido e eleição.');
            return;
        }
        try {
            Candidato::salvar($id, $nome, $numero, $cargo, $partidoId, $eleicaoId);
            $this->sucesso('Candidato salvo.');
        } catch (\RuntimeException $e) {
            $this->falha($e->getMessage());
            return;
        } catch (PDOException $e) {
            error_log((string)$e);
            $this->falha('Não foi possível salvar o candidato. O número deve ser único dentro da eleição.');
            return;
        }
        $this->redirecionar();
    }

    public function sair(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->csrfValido()) {
            http_response_code(400);
            $this->falha('Sessão expirou ou requisição inválida. Atualize a página e tente novamente.');
            return;
        }
        unset($_SESSION['admin_id'], $_SESSION['admin_nome']);
        header('Location: ?action=inicio');
    }

    private function csrfValido(): bool {
        return isset($_SESSION['csrf'], $_POST['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
    }

    private function exigeAdmin(): bool {
        if (!empty($_SESSION['admin_id'])) {
            return true;
        }
        header('Location: ?action=admin_login');
        return false;
    }

    private function autorizarPost(): bool {
        if (!$this->exigeAdmin()) {
            return false;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->csrfValido()) {
            http_response_code(400);
            $this->falha('Sessão expirou ou requisição inválida. Atualize a página e tente novamente.');
            return false;
        }
        return true;
    }

    private function idDoPost(): ?int {
        if (!isset($_POST['id']) || $_POST['id'] === '') {
            return null;
        }
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new \InvalidArgumentException('Identificador inválido.');
        }
        return $id;
    }

    private function idDaQuery(string $campo): ?int {
        $id = filter_var($_GET[$campo] ?? null, FILTER_VALIDATE_INT);
        return $id !== false && $id > 0 ? $id : null;
    }

    private function sucesso(string $mensagem): void {
        $_SESSION['admin_mensagem'] = $mensagem;
        unset($_SESSION['admin_erro']);
    }

    private function falha(string $mensagem): void {
        $_SESSION['admin_erro'] = $mensagem;
        unset($_SESSION['admin_mensagem']);
        $this->redirecionar();
    }

    private function redirecionar(): void {
        header('Location: ?action=admin');
    }
}
