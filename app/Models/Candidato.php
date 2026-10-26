<?php
namespace App\Models;
use App\Core\Database;

final class Candidato {
    public static function porNumero(int $numero, int $eleicaoId): ?array {
        $pdo = Database::conectar();
        $stmt = $pdo->prepare('SELECT c.id, c.nome, c.numero, c.cargo, p.nome AS partido, p.sigla FROM candidatos c JOIN partidos p ON p.id = c.partido_id WHERE c.numero = :numero AND c.eleicao_id = :eleicao LIMIT 1');
        $stmt->execute(['numero' => $numero, 'eleicao' => $eleicaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function todos(): array {
        return Database::conectar()->query(
            'SELECT c.*, p.nome AS partido_nome, e.nome AS eleicao_nome, e.status AS eleicao_status
             FROM candidatos c
             JOIN partidos p ON p.id = c.partido_id
             JOIN eleicoes e ON e.id = c.eleicao_id
             ORDER BY e.id DESC, c.numero'
        )->fetchAll();
    }

    public static function porId(int $id): ?array {
        $stmt = Database::conectar()->prepare('SELECT * FROM candidatos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function salvar(?int $id, string $nome, int $numero, string $cargo, int $partidoId, int $eleicaoId): int {
        $pdo = Database::conectar();
        $pdo->beginTransaction();
        try {
            $eleicaoAtualId = null;
            if ($id !== null) {
                $stmt = $pdo->prepare('SELECT eleicao_id FROM candidatos WHERE id = :id FOR UPDATE');
                $stmt->execute(['id' => $id]);
                $eleicaoAtualId = $stmt->fetchColumn();
                if ($eleicaoAtualId === false) {
                    throw new \RuntimeException('Candidato não encontrado.');
                }
            }

            $idsEleicao = array_values(array_unique(array_filter(
                [$eleicaoAtualId === null ? null : (int)$eleicaoAtualId, $eleicaoId],
                static fn(?int $value): bool => $value !== null
            )));
            sort($idsEleicao);
            $placeholders = implode(', ', array_fill(0, count($idsEleicao), '?'));
            $stmt = $pdo->prepare("SELECT id, status FROM eleicoes WHERE id IN ({$placeholders}) ORDER BY id FOR UPDATE");
            $stmt->execute($idsEleicao);
            $statusPorId = [];
            foreach ($stmt->fetchAll() as $eleicao) {
                $statusPorId[(int)$eleicao['id']] = $eleicao['status'];
            }
            if (!isset($statusPorId[$eleicaoId])) {
                throw new \RuntimeException('Selecione uma eleição existente.');
            }
            if ($statusPorId[$eleicaoId] !== 'RASCUNHO' ||
                ($eleicaoAtualId !== null && $statusPorId[(int)$eleicaoAtualId] !== 'RASCUNHO')) {
                throw new \RuntimeException('Candidatos só podem ser alterados enquanto a eleição vinculada estiver em rascunho.');
            }

            if ($id !== null) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM votos WHERE candidato_id = :id');
                $stmt->execute(['id' => $id]);
                if ((int)$stmt->fetchColumn() > 0) {
                    throw new \RuntimeException('Candidatos com votos registrados não podem ser alterados.');
                }
            }

            $stmt = $pdo->prepare('SELECT id FROM partidos WHERE id = :id');
            $stmt->execute(['id' => $partidoId]);
            if (!$stmt->fetch()) {
                throw new \RuntimeException('Selecione um partido existente.');
            }

            if ($id !== null) {
                $stmt = $pdo->prepare(
                    'UPDATE candidatos SET nome = :nome, numero = :numero, cargo = :cargo,
                     partido_id = :partido, eleicao_id = :eleicao WHERE id = :id'
                );
                $stmt->execute([
                    'nome' => $nome,
                    'numero' => $numero,
                    'cargo' => $cargo,
                    'partido' => $partidoId,
                    'eleicao' => $eleicaoId,
                    'id' => $id,
                ]);
                $salvoId = $id;
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO candidatos (nome, numero, cargo, partido_id, eleicao_id)
                     VALUES (:nome, :numero, :cargo, :partido, :eleicao)'
                );
                $stmt->execute([
                    'nome' => $nome,
                    'numero' => $numero,
                    'cargo' => $cargo,
                    'partido' => $partidoId,
                    'eleicao' => $eleicaoId,
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
