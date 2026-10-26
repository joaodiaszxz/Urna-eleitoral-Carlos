<section class="painel"><h1>Apuração demonstrativa</h1>
<?php if (empty($eleicao)): ?><p>Nenhuma eleição cadastrada.</p><?php else: ?>
<p><strong><?= htmlspecialchars($eleicao['nome'], ENT_QUOTES, 'UTF-8') ?></strong> · Status: <?= htmlspecialchars($eleicao['status'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Total de votos registrados: <strong><?= (int)($total ?? 0) ?></strong></p>
<table><thead><tr><th>Opção</th><th>Tipo</th><th>Votos</th><th>Percentual</th></tr></thead><tbody>
<?php foreach (($linhas ?? []) as $linha): ?><tr><td><?= htmlspecialchars($linha['nome'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($linha['tipo'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$linha['total'] ?></td><td><?= number_format($linha['percentual'], 2, ',', '.') ?>%</td></tr><?php endforeach; ?>
</tbody></table><p class="aviso">Os resultados são fictícios e servem somente para fins didáticos.</p>
<?php endif; ?><a class="botao secundario" href="?action=inicio">Voltar</a></section>