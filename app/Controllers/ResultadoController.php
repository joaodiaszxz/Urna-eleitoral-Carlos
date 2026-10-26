<?php
namespace App\Controllers;
use App\Core\Database;
use App\Core\View;
use App\Models\Eleicao;
use App\Models\Voto;

final class ResultadoController {
    public function mostrar(): void {
        $pdo = Database::conectar();
        $eleicao = Eleicao::ativa();
        if (!$eleicao) {
            $stmt = $pdo->query('SELECT * FROM eleicoes ORDER BY id DESC LIMIT 1');
            $eleicao = $stmt->fetch();
        }
        if (!$eleicao) { View::render('resultado', ['eleicao' => null, 'linhas' => []]); return; }
        $totais = Voto::apuracao((int)$eleicao['id']);
        $linhas = [];
        foreach ($totais as $item) {
            $nome = ucfirst(strtolower($item['tipo']));
            if ($item['tipo'] === 'VALIDO' && $item['candidato_id']) {
                $s = $pdo->prepare('SELECT nome, numero FROM candidatos WHERE id = :id');
                $s->execute(['id' => $item['candidato_id']]); $c = $s->fetch();
                $nome = $c ? $c['numero'] . ' — ' . $c['nome'] : 'Candidato';
            }
            $linhas[] = ['nome' => $nome, 'tipo' => $item['tipo'], 'total' => (int)$item['total']];
        }
        $total = array_sum(array_column($linhas, 'total'));
        foreach ($linhas as &$linha) $linha['percentual'] = $total ? round(100 * $linha['total'] / $total, 2) : 0;
        View::render('resultado', ['eleicao' => $eleicao, 'linhas' => $linhas, 'total' => $total]);
    }
}
