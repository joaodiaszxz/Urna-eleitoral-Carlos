<?php
namespace App\Controllers;
use App\Core\View;
use App\Models\Eleicao;
use App\Models\Candidato;
use App\Models\Voto;

final class VotacaoController {
    private function exigeEleitor(): bool {
        if (empty($_SESSION['eleitor_id'])) { header('Location: ?action=login'); return false; }
        return true;
    }
    public function tela(): void {
        if (!$this->exigeEleitor()) return;
        $eleicao = Eleicao::ativa();
        if (!$eleicao || (int)$eleicao['id'] !== (int)$_SESSION['eleicao_id']) {
            View::render('erro', ['mensagem' => 'Não existe eleição em andamento para este eleitor.']); return;
        }
        $candidato = null; $erro = null;
        if (isset($_GET['numero']) && $_GET['numero'] !== '') {
            $numero = filter_var($_GET['numero'], FILTER_VALIDATE_INT);
            if ($numero === false || $numero < 0) $erro = 'Digite um número válido.';
            else $candidato = Candidato::porNumero((int)$numero, (int)$eleicao['id']);
            if (!$candidato && !$erro) $erro = 'Número não encontrado. Você pode corrigir ou votar nulo.';
        }
        $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        View::render('votar', ['candidato' => $candidato, 'erro' => $erro, 'eleicao' => $eleicao]);
    }
    public function confirmar(): void {
        if (!$this->exigeEleitor()) return;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            View::render('erro', ['mensagem' => 'Requisição inválida.']); return;
        }
        $tipo = (string)($_POST['tipo'] ?? 'VALIDO');
        $candidatoId = null;
        if ($tipo === 'VALIDO') {
            $numero = filter_var($_POST['numero'] ?? null, FILTER_VALIDATE_INT);
            $c = ($numero !== false) ? Candidato::porNumero((int)$numero, (int)$_SESSION['eleicao_id']) : null;
            if (!$c) { View::render('erro', ['mensagem' => 'Candidato inválido. Volte e corrija o número.']); return; }
            $candidatoId = (int)$c['id'];
        } elseif (!in_array($tipo, ['BRANCO', 'NULO'], true)) {
            View::render('erro', ['mensagem' => 'Tipo de voto inválido.']); return;
        }
        try {
            Voto::registrar((int)$_SESSION['eleitor_id'], (int)$_SESSION['eleicao_id'], $candidatoId, $tipo);
            unset($_SESSION['eleitor_id'], $_SESSION['eleitor_nome'], $_SESSION['eleicao_id']);
            View::render('computado');
        } catch (\Throwable $e) {
            View::render('erro', ['mensagem' => $e instanceof \RuntimeException ? $e->getMessage() : 'Não foi possível registrar o voto.']);
        }
    }
}
