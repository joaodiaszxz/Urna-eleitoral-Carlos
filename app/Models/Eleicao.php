<?php
namespace App\Models;
use App\Core\Database;

final class Eleicao {
    public static function ativa(): ?array {
        $pdo = Database::conectar();
        $stmt = $pdo->query("SELECT * FROM eleicoes WHERE status = 'EM_ANDAMENTO' ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function todas(): array {
        return Database::conectar()->query('SELECT * FROM eleicoes ORDER BY id DESC')->fetchAll();
    }

    public static function porId(int $id): ?array {
        $stmt = Database::conectar()->prepare('SELECT * FROM eleicoes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function salvar(?int $id, string $nome, string $descricao, string $status): int {
        if (!in_array($status, ['RASCUNHO', 'EM_ANDAMENTO', 'ENCERRADA'], true)) {
            throw new \InvalidArgumentException('Status de eleição inválido.');
        }

        $pdo = Database::conectar();
        $pdo->beginTransaction();
        try {
            $atual = null;
            if ($id !== null) {
                $stmt = $pdo->prepare('SELECT * FROM eleicoes WHERE id = :id FOR UPDATE');
                $stmt->execute(['id' => $id]);
                $atual = $stmt->fetch();
                if (!$atual) {
                    throw new \RuntimeException('Eleição não encontrada.');
                }
                if ($atual['status'] === 'ENCERRADA' && $status !== 'ENCERRADA') {
                    throw new \RuntimeException('Uma eleição encerrada não pode ser reaberta.');
                }
            }

            if ($status === 'EM_ANDAMENTO') {
                $stmt = $pdo->prepare("SELECT id FROM eleicoes WHERE status = 'EM_ANDAMENTO' AND id <> :id FOR UPDATE");
                $stmt->execute(['id' => $id ?? 0]);
                if ($stmt->fetch()) {
                    throw new \RuntimeException('Já existe outra eleição em andamento.');
                }
            }

            if ($atual !== null) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM votos WHERE eleicao_id = :id');
                $stmt->execute(['id' => $id]);
                $temVotos = (int)$stmt->fetchColumn() > 0;
                if ($temVotos && ($nome !== $atual['nome'] || $descricao !== (string)($atual['descricao'] ?? '') || ($status !== $atual['status'] && $status !== 'ENCERRADA'))) {
                    throw new \RuntimeException('Esta eleição já tem votos; só é permitido encerrá-la.');
                }

                $stmt = $pdo->prepare(
                    'UPDATE eleicoes
                     SET nome = :nome, descricao = :descricao, status = :status,
                         data_inicio = CASE WHEN :inicio_status = \'EM_ANDAMENTO\' AND data_inicio IS NULL THEN NOW() ELSE data_inicio END,
                         data_fim = CASE WHEN :fim_status = \'ENCERRADA\' THEN COALESCE(data_fim, NOW()) ELSE NULL END
                     WHERE id = :id'
                );
                $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao !== '' ? $descricao : null,
                    'status' => $status,
                    'inicio_status' => $status,
                    'fim_status' => $status,
                    'id' => $id,
                ]);
                $salvoId = $id;
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO eleicoes (nome, descricao, status, data_inicio, data_fim)
                     VALUES (:nome, :descricao, :status,
                         CASE WHEN :inicio_status = \'EM_ANDAMENTO\' THEN NOW() ELSE NULL END,
                         CASE WHEN :fim_status = \'ENCERRADA\' THEN NOW() ELSE NULL END)'
                );
                $stmt->execute([
                    'nome' => $nome,
                    'descricao' => $descricao !== '' ? $descricao : null,
                    'status' => $status,
                    'inicio_status' => $status,
                    'fim_status' => $status,
                ]);
                $salvoId = (int)$pdo->lastInsertId();
            }

            $pdo->commit();
            return $salvoId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
