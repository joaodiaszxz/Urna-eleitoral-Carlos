<section class="painel">
<h1>Área de votação</h1><p>Eleitor autenticado: <strong><?= htmlspecialchars($_SESSION['eleitor_nome'] ?? 'Eleitor', ENT_QUOTES, 'UTF-8') ?></strong></p>
<?php if (!empty($erro)): ?><div class="mensagem erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="get" class="numero-form">
<input type="hidden" name="action" value="votar"><label for="numero">Número do candidato</label>
<input id="numero" name="numero" inputmode="numeric" pattern="[0-9]*" maxlength="5" placeholder="Ex.: 10" value="<?= htmlspecialchars((string)($_GET['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<button class="botao" type="submit">Consultar número</button>
</form>
<?php if (!empty($candidato)): ?>
<div class="candidato"><div class="avatar" aria-hidden="true">★</div><h2><?= htmlspecialchars($candidato['nome'], ENT_QUOTES, 'UTF-8') ?></h2><p>Número <?= (int)$candidato['numero'] ?> · <?= htmlspecialchars($candidato['partido'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($candidato['sigla'], ENT_QUOTES, 'UTF-8') ?>)</p><p>Confirme somente se esta for a escolha desejada.</p>
<form method="post" action="?action=confirmar"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="tipo" value="VALIDO"><input type="hidden" name="numero" value="<?= (int)$candidato['numero'] ?>"><button class="botao confirmar" type="submit">Confirmar voto</button></form>
</div><?php endif; ?>
<div class="acoes">
<form method="post" action="?action=confirmar" onsubmit="return confirm('Confirma voto em branco?');"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="tipo" value="BRANCO"><button class="botao secundario" type="submit">Votar em branco</button></form>
<form method="post" action="?action=confirmar" onsubmit="return confirm('Confirma voto nulo?');"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="tipo" value="NULO"><button class="botao alerta" type="submit">Votar nulo</button></form>
</div><p><a href="?action=sair">Sair da sessão</a></p>
</section>
