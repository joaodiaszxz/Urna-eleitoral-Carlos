<section class="painel formulario">
<h1>Acesso administrativo</h1>
<p>Entre com uma conta que tenha perfil de administrador.</p>
<?php if (!empty($erro)): ?><div class="mensagem erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="?action=admin_autenticar">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
<label for="email">E-mail</label><input id="email" name="email" type="email" required autocomplete="username" maxlength="160">
<label for="senha">Senha</label><input id="senha" name="senha" type="password" required autocomplete="current-password">
<button class="botao" type="submit">Entrar</button>
</form><a href="?action=inicio">Voltar ao início</a>
</section>
