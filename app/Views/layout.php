<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Simulador Didático de Urna</title><link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topo"><a href="?action=inicio" class="marca">SIMULADOR DE URNA</a><span>Projeto exclusivamente educacional</span><?php if (!empty($_SESSION['admin_id'])): ?><a class="topo-link" href="?action=admin">Administração</a><?php endif; ?></header>
<main class="container">
<?php require $arquivo; ?>
</main>
<footer>Dados, candidatos e resultados fictícios. Não representa uma urna oficial.</footer>
</body></html>
