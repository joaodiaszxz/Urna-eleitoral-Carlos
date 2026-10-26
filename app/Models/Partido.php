<?php
namespace App\Models;
use App\Core\Database;

final class Partido {
    public static function todos(): array {
        return Database::conectar()->query('SELECT * FROM partidos ORDER BY nome')->fetchAll();
    }

    public static function porId(int $id): ?array {
        $stmt = Database::conectar()->prepare('SELECT * FROM partidos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function salvar(?int $id, string $nome, string $sigla, int $numero): int {
        $pdo = Database::conectar();
        if ($id !== null) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('SELECT id FROM partidos WHERE id = :id');
                $stmt->execute(['id' => $id]);
                if (!$stmt->fetch()) {
                    throw new \RuntimeException('Partido não encontrado.');
                }
                $stmt = $pdo->prepare(
                    "SELECT e.id, e.status FROM candidatos c
                     JOIN eleicoes e ON e.id = c.eleicao_id
                     WHERE c.partido_id = :id ORDER BY e.id FOR UPDATE"
                );
                $stmt->execute(['id' => $id]);
                foreach ($stmt->fetchAll() as $eleicao) {
                    if ($eleicao['status'] !== 'RASCUNHO') {
                        throw new \RuntimeException('Partidos vinculados a eleições iniciadas não podem ser alterados.');
                    }
                }
                $stmt = $pdo->prepare('UPDATE partidos SET nome = :nome, sigla = :sigla, numero = :numero WHERE id = :id');
                $stmt->execute(['nome' => $nome, 'sigla' => $sigla, 'numero' => $numero, 'id' => $id]);
                $pdo->commit();
                return $id;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO partidos (nome, sigla, numero) VALUES (:nome, :sigla, :numero)');
        $stmt->execute(['nome' => $nome, 'sigla' => $sigla, 'numero' => $numero]);
        return (int)$pdo->lastInsertId();
    }
}
