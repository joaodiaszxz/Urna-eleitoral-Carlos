<section class="painel">
<div class="admin-cabecalho"><div><h1>Administração</h1><p>Olá, <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?>.</p></div><form method="post" action="?action=admin_sair"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>"><button class="botao secundario" type="submit">Sair</button></form></div>
<?php if (!empty($mensagem)): ?><div class="mensagem sucesso" role="status"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if (!empty($mensagemErro)): ?><div class="mensagem erro" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<p class="aviso">Eleições encerradas não podem ser reabertas. Depois do primeiro voto, só é permitido encerrar a eleição; candidatos só podem ser alterados enquanto ela estiver em rascunho.</p>

<section class="admin-secao">
<h2>Eleições</h2>
<div class="tabela-rolagem"><table><thead><tr><th>Nome</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php foreach ($eleicoes as $item): ?><tr><td><?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></td><td><a href="?action=admin&amp;editar_eleicao=<?= (int)$item['id'] ?>">Editar</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<h3><?= $eleicaoEditando ? 'Editar eleição' : 'Cadastrar eleição' ?></h3>
<form method="post" action="?action=admin_eleicao_salvar" class="admin-form">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
<?php if ($eleicaoEditando): ?><input type="hidden" name="id" value="<?= (int)$eleicaoEditando['id'] ?>"><?php endif; ?>
<label for="eleicao-nome">Nome</label><input id="eleicao-nome" name="nome" required maxlength="150" value="<?= htmlspecialchars($eleicaoEditando['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="eleicao-descricao">Descrição</label><textarea id="eleicao-descricao" name="descricao" maxlength="5000"><?= htmlspecialchars($eleicaoEditando['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
<label for="eleicao-status">Status</label><select id="eleicao-status" name="status"><?php foreach (['RASCUNHO', 'EM_ANDAMENTO', 'ENCERRADA'] as $status): ?><option value="<?= $status ?>" <?= ($eleicaoEditando['status'] ?? 'RASCUNHO') === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select>
<button class="botao" type="submit">Salvar eleição</button><?php if ($eleicaoEditando): ?><a href="?action=admin">Cancelar</a><?php endif; ?>
</form>
</section>

<section class="admin-secao">
<h2>Partidos</h2>
<div class="tabela-rolagem"><table><thead><tr><th>Nome</th><th>Sigla</th><th>Número</th><th>Ação</th></tr></thead><tbody>
<?php foreach ($partidos as $item): ?><tr><td><?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($item['sigla'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$item['numero'] ?></td><td><a href="?action=admin&amp;editar_partido=<?= (int)$item['id'] ?>">Editar</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<h3><?= $partidoEditando ? 'Editar partido' : 'Cadastrar partido' ?></h3>
<form method="post" action="?action=admin_partido_salvar" class="admin-form">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
<?php if ($partidoEditando): ?><input type="hidden" name="id" value="<?= (int)$partidoEditando['id'] ?>"><?php endif; ?>
<label for="partido-nome">Nome</label><input id="partido-nome" name="nome" required maxlength="120" value="<?= htmlspecialchars($partidoEditando['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="partido-sigla">Sigla</label><input id="partido-sigla" name="sigla" required maxlength="12" pattern="[A-Za-z0-9]{2,12}" value="<?= htmlspecialchars($partidoEditando['sigla'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="partido-numero">Número</label><input id="partido-numero" name="numero" type="number" min="1" max="65535" required value="<?= htmlspecialchars((string)($partidoEditando['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<button class="botao" type="submit">Salvar partido</button><?php if ($partidoEditando): ?><a href="?action=admin">Cancelar</a><?php endif; ?>
</form>
</section>

<section class="admin-secao">
<h2>Candidatos</h2>
<div class="tabela-rolagem"><table><thead><tr><th>Nome</th><th>Número</th><th>Partido</th><th>Eleição</th><th>Ação</th></tr></thead><tbody>
<?php foreach ($candidatos as $item): ?><tr><td><?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$item['numero'] ?></td><td><?= htmlspecialchars($item['partido_nome'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($item['eleicao_nome'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($item['eleicao_status'], ENT_QUOTES, 'UTF-8') ?>)</td><td><?php if ($item['eleicao_status'] === 'RASCUNHO'): ?><a href="?action=admin&amp;editar_candidato=<?= (int)$item['id'] ?>">Editar</a><?php else: ?>Bloqueado<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<h3><?= $candidatoEditando ? 'Editar candidato' : 'Cadastrar candidato' ?></h3>
<?php if (!$eleicoesEditaveis): ?><p>Crie uma eleição em rascunho para cadastrar candidatos.</p><?php elseif (!$partidos): ?><p>Cadastre um partido antes de cadastrar candidatos.</p><?php else: ?>
<form method="post" action="?action=admin_candidato_salvar" class="admin-form">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
<?php if ($candidatoEditando): ?><input type="hidden" name="id" value="<?= (int)$candidatoEditando['id'] ?>"><?php endif; ?>
<label for="candidato-nome">Nome</label><input id="candidato-nome" name="nome" required maxlength="120" value="<?= htmlspecialchars($candidatoEditando['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="candidato-numero">Número na urna</label><input id="candidato-numero" name="numero" type="number" min="0" max="65535" required value="<?= htmlspecialchars((string)($candidatoEditando['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label for="candidato-cargo">Cargo</label><input id="candidato-cargo" name="cargo" required maxlength="80" value="<?= htmlspecialchars($candidatoEditando['cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="candidato-partido">Partido</label><select id="candidato-partido" name="partido_id" required><?php foreach ($partidos as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (int)($candidatoEditando['partido_id'] ?? 0) === (int)$item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($item['sigla'], ENT_QUOTES, 'UTF-8') ?>)</option><?php endforeach; ?></select>
<label for="candidato-eleicao">Eleição (rascunho)</label><select id="candidato-eleicao" name="eleicao_id" required><?php foreach ($eleicoesEditaveis as $item): ?><option value="<?= (int)$item['id'] ?>" <?= (int)($candidatoEditando['eleicao_id'] ?? 0) === (int)$item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
<button class="botao" type="submit">Salvar candidato</button><?php if ($candidatoEditando): ?><a href="?action=admin">Cancelar</a><?php endif; ?>
</form>
<?php endif; ?>
</section>
<a href="?action=resultado">Ver apuração</a>
</section>
