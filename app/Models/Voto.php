<?php
namespace App\Models;
use App\Core\Database;
use PDO;

final class Voto {
    public static function registrar(int $eleitorId, int $eleicaoId, ?int $candidatoId, string $tipo): void {
        $pdo = Database::conectar();
        try {
            $pdo->beginTransaction();
            // Lock do eleitor evita duas confirmações concorrentes para a mesma pessoa.
            $lock = $pdo->prepare('SELECT id, votou, status, eleicao_id FROM eleitores WHERE id = :id FOR UPDATE');
            $lock->execute(['id' => $eleitorId]);
            $eleitor = $lock->fetch();
            if (!$eleitor || $eleitor['status'] !== 'ATIVO' || (int)$eleitor['votou'] === 1 || (int)$eleitor['eleicao_id'] !== $eleicaoId) {
                throw new \RuntimeException('Eleitor não habilitado ou já votou.');
            }
            $check = $pdo->prepare("SELECT id FROM eleicoes WHERE id = :id AND status = 'EM_ANDAMENTO' FOR UPDATE");
            $check->execute(['id' => $eleicaoId]);
            if (!$check->fetch()) throw new \RuntimeException('A eleição não está em andamento.');
            $stmt = $pdo->prepare('INSERT INTO votos (eleicao_id, eleitor_id, candidato_id, tipo) VALUES (:eleicao, :eleitor, :candidato, :tipo)');
            $stmt->execute(['eleicao' => $eleicaoId, 'eleitor' => $eleitorId, 'candidato' => $candidatoId, 'tipo' => $tipo]);
            $upd = $pdo->prepare('UPDATE eleitores SET votou = 1, updated_at = NOW() WHERE id = :id');
            $upd->execute(['id' => $eleitorId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
    public static function apuracao(int $eleicaoId): array {
        $pdo = Database::conectar();
        $stmt = $pdo->prepare("SELECT tipo, candidato_id, COUNT(*) AS total FROM votos WHERE eleicao_id = :id GROUP BY tipo, candidato_id");
        $stmt->execute(['id' => $eleicaoId]);
        return $stmt->fetchAll();
    }
}
