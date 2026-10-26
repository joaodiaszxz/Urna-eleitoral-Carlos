<section class="painel formulario">
<h1>Identificação do eleitor</h1><p>Entre com uma credencial fictícia cadastrada para esta demonstração.</p>
<?php if (!empty($erro)): ?><div class="mensagem erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="?action=autenticar">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ??= bin2hex(random_bytes(32)), ENT_QUOTES, 'UTF-8') ?>">
<label for="identificador">Identificador fictício</label><input id="identificador" name="identificador" required autocomplete="username" maxlength="50">
<label for="senha">Senha de demonstração</label><input id="senha" type="password" name="senha" required autocomplete="current-password">
<button class="botao" type="submit">Validar e continuar</button>
</form><a href="?action=inicio">Voltar ao início</a>
</section>
