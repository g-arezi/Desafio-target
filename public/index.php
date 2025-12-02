<?php
require __DIR__ . '/../vendor/autoload.php';

// Leitura rápida dos dados para popular formulários
$estoque = json_decode(file_get_contents(__DIR__ . '/../data/estoque.json'), true)['estoque'] ?? [];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Desafio - Comissões, Estoque e Juros</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">

        <h1>Desafio</h1>
        <div class="muted">Comissões • Movimentação de Estoque • Juros</div>
    </div>
</header>
<main class="container">
    <div class="grid">
        <section class="card">
            <h2>Comissões</h2>
            <p class="muted">Calcular comissões a partir do arquivo de vendas.</p>
            <form action="api.php" method="get">
                <input type="hidden" name="action" value="commissions">
                <button class="btn btn-primary" type="submit">Ver comissões</button>
            </form>
        </section>


        <section class="card">
            <h2>Movimentação de Estoque</h2>
            <form action="api.php?action=inventory_move" method="post">
                <label>Produto</label>
                <select name="codigoProduto" required>
                    <?php foreach ($estoque as $p): ?>
                        <option value="<?= htmlspecialchars($p['codigoProduto']) ?>"><?= htmlspecialchars($p['codigoProduto'] . ' - ' . $p['descricaoProduto']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Quantidade (negativo = saída)</label>
                <input type="number" name="quantidade" required>

                <label>Descrição</label>
                <input type="text" name="descricao" required>

                <button class="btn btn-primary" type="submit">Registrar</button>
            </form>
        </section>

        <section class="card">
            <h2>Cálculo de Juros</h2>
            <form action="api.php?action=interest_calculate" method="post">
                <label>Valor (R$)</label>
                <input type="number" step="0.01" name="valor" required>

                <label>Data de vencimento</label>
                <input type="date" name="due_date" required>

                <button class="btn btn-primary" type="submit">Calcular</button>
            </form>
        </section>

                <section class="card">
            <h2>Estoque</h2>
            <p class="muted">Visualize os produtos em estoque e adicione novos itens.</p>
            <table class="table">
                <thead>
                    <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($estoque as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['codigoProduto']) ?></td>
                        <td><?= htmlspecialchars($p['descricaoProduto']) ?></td>
                        <td><?= htmlspecialchars($p['estoque']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            
        </section>
        <section class="card">
            <h2>Adicionar Produto</h2>
            <form action="api.php?action=stock_add" method="post">
                <label>Código do Produto</label>
                <input type="number" name="codigoProduto" required>

                <label>Descrição do Produto</label>
                <input type="text" name="descricaoProduto" required>

                <label>Quantidade Inicial</label>
                <input type="number" name="estoque" required>

                <button class="btn btn-primary" type="submit">Adicionar Produto</button>
            </form>
        </section>
        <section class="card">
            <h2>Remover Produto</h2>
            <form action="api.php?action=stock_remove" method="post">
                <label>Produto</label>
                <select name="codigoProduto" required>
                    <?php foreach ($estoque as $p): ?>
                        <option value="<?= htmlspecialchars($p['codigoProduto']) ?>"><?= htmlspecialchars($p['codigoProduto'] . ' - ' . $p['descricaoProduto']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-primary" type="submit">Remover Produto</button>
            </form>
        </section>

    </div>

    <div class="footer">Simples, limpo e responsivo</div>
</main>

</body>
</html>
