<?php
namespace App\Models;
use App\Core\Database;

final class Admin {
    public static function autenticar(string $email, string $senha): ?array {
        $stmt = Database::conectar()->prepare(
            'SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        if (!$usuario || $usuario['perfil'] !== 'ADMIN' || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }
        return ['id' => (int)$usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email']];
    }
}
