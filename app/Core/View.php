<?php
namespace App\Core;
final class View {
    public static function render(string $nome, array $dados = []): void {
        extract($dados, EXTR_SKIP);
        $arquivo = __DIR__ . '/../Views/' . $nome . '.php';
        require __DIR__ . '/../Views/layout.php';
    }
}
