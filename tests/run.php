<?php
declare(strict_types=1);

$databaseName = getenv('DB_NAME') ?: 'simulador_urna';
if ($databaseName !== 'simulador_urna_test') {
    fwrite(STDERR, "Testes recusados: defina DB_NAME=simulador_urna_test para proteger o banco principal.\n");
    exit(2);
}

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/Admin.php';
require_once __DIR__ . '/../app/Models/Eleicao.php';
require_once __DIR__ . '/../app/Models/Partido.php';
require_once __DIR__ . '/../app/Models/Candidato.php';
require_once __DIR__ . '/../app/Models/Voto.php';

use App\Core\Database;
use App\Models\Admin;
use App\Models\Candidato;
use App\Models\Eleicao;
use App\Models\Partido;
use App\Models\Voto;

$pdo = Database::conectar();
$prefix = bin2hex(random_bytes(5));
$emailAdmin = "admin-{$prefix}@example.invalid";
$emailOperador = "operador-{$prefix}@example.invalid";
$eleicaoId = null;
$eleicaoExtraId = null;
$partidoId = null;
$candidatoId = null;
$eleitorIds = [];
$passed = 0;

$test = static function (string $name, callable $assertion) use (&$passed): void {
    $assertion();
    $passed++;
    echo "PASS: {$name}\n";
};
$expectRuntimeException = static function (callable $operation, string $message): void {
    try {
        $operation();
    } catch (RuntimeException $e) {
        if (str_contains($e->getMessage(), $message)) {
            return;
        }
        throw $e;
    }
    throw new RuntimeException("Esperava erro contendo: {$message}");
};

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Admin de teste', $emailAdmin, password_hash('SenhaTeste#2026', PASSWORD_DEFAULT), 'ADMIN']);
    $stmt->execute(['Operador de teste', $emailOperador, password_hash('SenhaTeste#2026', PASSWORD_DEFAULT), 'OPERADOR']);

    $test('login administrativo válido e recusa de perfis/senhas inválidos', static function () use ($emailAdmin, $emailOperador): void {
        if (Admin::autenticar($emailAdmin, 'SenhaTeste#2026') === null ||
            Admin::autenticar($emailAdmin, 'senha-incorreta') !== null ||
            Admin::autenticar($emailOperador, 'SenhaTeste#2026') !== null) {
            throw new RuntimeException('Validação de login/perfil administrativo falhou.');
        }
    });

    $nomeEleicao = "Eleição teste {$prefix}";
    $eleicaoId = Eleicao::salvar(null, $nomeEleicao, 'Descrição inicial', 'RASCUNHO');
    $test('criação e edição de eleição em rascunho', static function () use ($eleicaoId, $nomeEleicao): void {
        Eleicao::salvar($eleicaoId, $nomeEleicao, 'Descrição atualizada', 'RASCUNHO');
        $eleicao = Eleicao::porId($eleicaoId);
        if (!$eleicao || $eleicao['descricao'] !== 'Descrição atualizada' || $eleicao['status'] !== 'RASCUNHO') {
            throw new RuntimeException('A eleição não foi criada/editada corretamente.');
        }
    });

    $sigla = 'T' . strtoupper(substr($prefix, 0, 4));
    $numeroPartido = random_int(1000, 65000);
    $partidoId = Partido::salvar(null, "Partido {$prefix}", $sigla, $numeroPartido);
    $test('criação e edição de partido', static function () use ($partidoId, $sigla, $numeroPartido): void {
        Partido::salvar($partidoId, "Partido atualizado {$sigla}", $sigla, $numeroPartido);
        $partido = Partido::porId($partidoId);
        if (!$partido || $partido['nome'] !== "Partido atualizado {$sigla}") {
            throw new RuntimeException('O partido não foi criado/editado corretamente.');
        }
    });

    $candidatoId = Candidato::salvar(null, "Candidata {$prefix}", 987, 'Representante', $partidoId, $eleicaoId);
    $test('criação e edição de candidato em eleição de rascunho', static function () use ($candidatoId, $partidoId, $eleicaoId): void {
        Candidato::salvar($candidatoId, 'Candidata editada', 988, 'Representante', $partidoId, $eleicaoId);
        $candidato = Candidato::porNumero(988, $eleicaoId);
        if (!$candidato || $candidato['nome'] !== 'Candidata editada') {
            throw new RuntimeException('O candidato não foi criado/editado corretamente.');
        }
    });

    $eleicaoExtraId = Eleicao::salvar(null, "Eleição alternativa {$prefix}", '', 'RASCUNHO');
    Eleicao::salvar($eleicaoId, $nomeEleicao, 'Descrição atualizada', 'EM_ANDAMENTO');
    $test('ativação de eleição e bloqueio de edição da cédula', static function () use ($eleicaoId, $candidatoId, $partidoId): void {
        if ((int)(Eleicao::ativa()['id'] ?? 0) !== $eleicaoId) {
            throw new RuntimeException('A eleição ativa não foi localizada.');
        }
        try {
            Candidato::salvar($candidatoId, 'Candidata editada', 988, 'Representante', $partidoId, $eleicaoId);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'rascunho')) {
                return;
            }
            throw $e;
        }
        throw new RuntimeException('A edição de candidato foi aceita após iniciar a eleição.');
    });
    $test('bloqueio de mover candidato para outra eleição após início', static function () use ($candidatoId, $partidoId, $eleicaoExtraId, $expectRuntimeException): void {
        $expectRuntimeException(
            static fn() => Candidato::salvar($candidatoId, 'Candidata editada', 988, 'Representante', $partidoId, $eleicaoExtraId),
            'eleição vinculada'
        );
    });
    $test('bloqueio de edição de partido após início da eleição', static function () use ($partidoId, $sigla, $numeroPartido, $expectRuntimeException): void {
        $expectRuntimeException(
            static fn() => Partido::salvar($partidoId, "Partido atualizado {$sigla}", $sigla, $numeroPartido),
            'eleições iniciadas'
        );
    });

    foreach (['Eleitor de teste', 'Segundo eleitor de teste'] as $offset => $nome) {
        $stmt = $pdo->prepare(
            'INSERT INTO eleitores (identificador, nome, senha_hash, status, votou, eleicao_id)
             VALUES (?, ?, ?, \'ATIVO\', 0, ?)'
        );
        $stmt->execute(["{$prefix}-{$offset}", $nome, password_hash('SenhaTeste#2026', PASSWORD_DEFAULT), $eleicaoId]);
        $eleitorIds[] = (int)$pdo->lastInsertId();
    }

    Voto::registrar($eleitorIds[0], $eleicaoId, null, 'BRANCO');
    $test('registro de voto e bloqueio de segundo voto', static function () use ($pdo, $eleitorIds, $eleicaoId, $expectRuntimeException): void {
        $stmt = $pdo->prepare('SELECT votou FROM eleitores WHERE id = ?');
        $stmt->execute([$eleitorIds[0]]);
        if ((int)$stmt->fetchColumn() !== 1) {
            throw new RuntimeException('O eleitor não foi marcado como votante.');
        }
        $expectRuntimeException(static fn() => Voto::registrar($eleitorIds[0], $eleicaoId, null, 'BRANCO'), 'já votou');
    });

    Eleicao::salvar($eleicaoId, $nomeEleicao, 'Descrição atualizada', 'ENCERRADA');
    $test('encerramento irreversível e recusa de novos votos', static function () use ($eleicaoId, $eleitorIds, $expectRuntimeException): void {
        if (Eleicao::porId($eleicaoId)['status'] !== 'ENCERRADA') {
            throw new RuntimeException('O status não foi encerrado.');
        }
        $expectRuntimeException(static fn() => Eleicao::salvar($eleicaoId, 'Outro nome', 'Descrição atualizada', 'RASCUNHO'), 'não pode ser reaberta');
        $expectRuntimeException(static fn() => Voto::registrar($eleitorIds[1], $eleicaoId, null, 'BRANCO'), 'não está em andamento');
    });

    echo "Resultado: {$passed} testes aprovados.\n";
} finally {
    if ($eleicaoId !== null) {
        $stmt = $pdo->prepare('DELETE FROM votos WHERE eleicao_id = ?');
        $stmt->execute([$eleicaoId]);
        $stmt = $pdo->prepare('DELETE FROM eleitores WHERE eleicao_id = ? AND identificador LIKE ?');
        $stmt->execute([$eleicaoId, "{$prefix}-%"]);
        $stmt = $pdo->prepare('DELETE FROM candidatos WHERE eleicao_id = ?');
        $stmt->execute([$eleicaoId]);
        $stmt = $pdo->prepare('DELETE FROM eleicoes WHERE id = ?');
        $stmt->execute([$eleicaoId]);
    }
    if ($eleicaoExtraId !== null) {
        $stmt = $pdo->prepare('DELETE FROM eleicoes WHERE id = ?');
        $stmt->execute([$eleicaoExtraId]);
    }
    if ($partidoId !== null) {
        $stmt = $pdo->prepare('DELETE FROM partidos WHERE id = ?');
        $stmt->execute([$partidoId]);
    }
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE email IN (?, ?)');
    $stmt->execute([$emailAdmin, $emailOperador]);
}
