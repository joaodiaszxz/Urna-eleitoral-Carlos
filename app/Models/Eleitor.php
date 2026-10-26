<?php
namespace App\Models;
use App\Core\Database;

final class Eleitor {
    public static function porIdentificador(string $identificador): ?array {
        $pdo = Database::conectar();
        $stmt = $pdo->prepare('SELECT * FROM eleitores WHERE identificador = :identificador LIMIT 1');
        $stmt->execute(['identificador' => $identificador]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function marcarComoVotou(int $id, int $eleicaoId): void {
        $pdo = Database::conectar();
        $stmt = $pdo->prepare('UPDATE eleitores SET votou = 1, updated_at = NOW() WHERE id = :id AND eleicao_id = :eleicao AND votou = 0');
        $stmt->execute(['id' => $id, 'eleicao' => $eleicaoId]);
        if ($stmt->rowCount() !== 1) {
            throw new \RuntimeException('Eleitor já votou ou não está habilitado para esta eleição.');
        }
    }
}
