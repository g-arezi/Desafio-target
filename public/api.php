<?php
// Ponto de entrada para a API do sistema
require __DIR__ . '/../vendor/autoload.php';

use App\Service\CommissionService;
use App\Service\InventoryService;
use App\Service\InterestService;

$action = $_GET['action'] ?? null;

function renderPage(string $title, string $bodyHtml)
{
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="stylesheet" href="css/style.css">';
    echo '</head><body>';
    echo '<header class="site-header"><div class="container"><h1>' . htmlspecialchars($title) . '</h1></div></header>';
    echo '<main class="container">' . $bodyHtml . '<p class="muted"><a href="index.php">Voltar</a></p></main>';
    echo '</body></html>';
}
// Processamento das ações
try {
    if ($action === 'commissions') {
        $svc = new CommissionService();
        $res = $svc->calculateFromFile(__DIR__ . '/../data/vendas.json');

        $body = '';
        foreach ($res as $vendedor => $info) {
            $body .= '<section class="card">';
            $body .= '<h2>' . htmlspecialchars($vendedor) . '</h2>';
            $body .= '<p class="muted">Total comissão: <strong>R$ ' . number_format($info['total_comissao'], 2, ',', '.') . '</strong></p>';
            $body .= '<table class="table"><thead><tr><th>Valor</th><th>%</th><th>Comissão</th></tr></thead><tbody>';
            foreach ($info['vendas'] as $v) {
                $body .= '<tr>';
                $body .= '<td>R$ ' . number_format($v['valor'], 2, ',', '.') . '</td>';
                $body .= '<td>' . ($v['percentual'] * 100) . '%</td>';
                $body .= '<td>R$ ' . number_format($v['comissao'], 2, ',', '.') . '</td>';
                $body .= '</tr>';
            }
            $body .= '</tbody></table>';
            $body .= '</section>';
        }

        renderPage('Comissões por vendedor', $body);
        exit;
    }
 // Movimentação de estoque
    if ($action === 'inventory_move' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = isset($_POST['codigoProduto']) ? (int)$_POST['codigoProduto'] : null;
        $quant = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : null;
        $desc = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';

        $svc = new InventoryService(__DIR__ . '/../data');
        $movement = $svc->performMovement($codigo, $quant, $desc);

        $body = '<section class="card">';
        $body .= '<h2>Movimentação registrada</h2>';
        $body .= '<ul>';
        $body .= '<li>ID: ' . htmlspecialchars($movement['id']) . '</li>';
        $body .= '<li>Produto: ' . htmlspecialchars($movement['codigoProduto']) . '</li>';
        $body .= '<li>Quantidade: ' . htmlspecialchars($movement['quantidade']) . '</li>';
        $body .= '<li>Descrição: ' . htmlspecialchars($movement['descricao']) . '</li>';
        $body .= '<li>Estoque final: ' . htmlspecialchars($movement['final_estoque']) . '</li>';
        $body .= '</ul>';
        $body .= '</section>';

        renderPage('Movimentação', $body);
        exit;
    }
// Adição de produto ao estoque
    if ($action === 'stock_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = isset($_POST['codigoProduto']) ? (int)$_POST['codigoProduto'] : null;
        $descricao = isset($_POST['descricaoProduto']) ? trim($_POST['descricaoProduto']) : '';
        $quant = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 0;

        $svc = new InventoryService(__DIR__ . '/../data');
        $product = $svc->addProduct($codigo, $descricao, $quant);

        $body = '<section class="card">';
        $body .= '<h2>Produto adicionado</h2>';
        $body .= '<ul>';
        $body .= '<li>Código: ' . htmlspecialchars($product['codigoProduto']) . '</li>';
        $body .= '<li>Descrição: ' . htmlspecialchars($product['descricaoProduto']) . '</li>';
        $body .= '<li>Estoque inicial: ' . htmlspecialchars($product['estoque']) . '</li>';
        $body .= '</ul>';
        $body .= '</section>';

        renderPage('Estoque - Produto adicionado', $body);
        exit;
    }
    // Remoção de produto do estoque
    if ($action === 'stock_remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = isset($_POST['codigoProduto']) ? (int)$_POST['codigoProduto'] : null;

        $svc = new InventoryService(__DIR__ . '/../data');
        $svc->removeProduct($codigo);

        $body = '<section class="card">';
        $body .= '<h2>Produto removido</h2>';
        $body .= '<p>O produto de código ' . htmlspecialchars($codigo) . ' foi removido do estoque.</p>';
        $body .= '</section>';

        renderPage('Estoque - Produto removido', $body);
        exit;
    }
// Cálculo de juros
    if ($action === 'interest_calculate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0.0;
        $due = isset($_POST['due_date']) ? $_POST['due_date'] : null;

        $svc = new InterestService();
        $res = $svc->calculate($valor, $due);

        $body = '<section class="card">';
        $body .= '<h2>Resultado do cálculo de juros</h2>';
        $body .= '<table class="table"><tbody>';
        $body .= '<tr><th>Dias de atraso</th><td>' . htmlspecialchars($res['dias_atraso']) . '</td></tr>';
        $body .= '<tr><th>Valor original</th><td>R$ ' . number_format($res['valor_original'], 2, ',', '.') . '</td></tr>';
        $body .= '<tr><th>Juros acumulados</th><td>R$ ' . number_format($res['juros'], 2, ',', '.') . '</td></tr>';
        $body .= '<tr><th>Valor final</th><td>R$ ' . number_format($res['valor_final'], 2, ',', '.') . '</td></tr>';
        $body .= '</tbody></table>';
        $body .= '</section>';

        renderPage('Juros', $body);
        exit;
    }

    // default (pagina inicial)
    header('Location: index.php');
    exit;
// Tratamento de erros
} catch (\Throwable $e) {
    http_response_code(500);
    $body = '<section class="card"><h2>Erro</h2><pre>' . htmlspecialchars($e->getMessage()) . '</pre></section>';
    renderPage('Erro', $body);
}
