<?php

namespace App\Service;

/**
 * Service responsável por realizar movimentações de estoque.
 * Cada movimentação gera um identificador único e atualiza o arquivo de estoque.
 */
class InventoryService
{
    private string $stockPath;
    private string $movementsPath;

    public function __construct(string $dataDir = __DIR__ . '/../../data')
    {
        $this->stockPath = rtrim($dataDir, DIRECTORY_SEPARATOR) . '/estoque.json';
        $this->movementsPath = rtrim($dataDir, DIRECTORY_SEPARATOR) . '/movements.json';

        if (!file_exists($this->movementsPath)) {
            file_put_contents($this->movementsPath, json_encode([]));
        }
    }

    /**
     * Carrega o estoque atual do arquivo.
     * @return array
     */
    private function loadStock(): array
    {
        if (!file_exists($this->stockPath)) {
            throw new \RuntimeException('Arquivo de estoque não encontrado: ' . $this->stockPath);
        }
        $json = json_decode(file_get_contents($this->stockPath), true);
        return $json['estoque'] ?? [];
    }

    /**
     * Salva o estoque de volta no arquivo.
     * @param array $stock
     * @return void
     */
    private function saveStock(array $stock): void
    {
        $payload = ['estoque' => array_values($stock)];
        file_put_contents($this->stockPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Realiza uma movimentação no estoque.
     * @param int $codigoProduto
     * @param int $quantidade (positivo = entrada, negativo = saída)
     * @param string $descricao
     * @return array Detalhes da movimentação e quantidade final
     */
    public function performMovement(int $codigoProduto, int $quantidade, string $descricao): array
    {
        $stock = $this->loadStock();

        $foundKey = null;
        foreach ($stock as $k => $item) {
            if ((int)$item['codigoProduto'] === $codigoProduto) {
                $foundKey = $k;
                break;
            }
        }

        if ($foundKey === null) {
            throw new \InvalidArgumentException('Produto não encontrado: ' . $codigoProduto);
        }

        $current = (int)$stock[$foundKey]['estoque'];
        $final = $current + $quantidade;

        if ($final < 0) {
            throw new \RuntimeException('Movimentação inválida: estoque ficaria negativo.');
        }

        // atualiza estoque
        $stock[$foundKey]['estoque'] = $final;
        $this->saveStock($stock);

        // registra movimentação
        $movement = [
            'id' => uniqid('mov_', true),
            'codigoProduto' => $codigoProduto,
            'descricao' => $descricao,
            'quantidade' => $quantidade,
            'final_estoque' => $final,
            'data' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        $this->appendMovement($movement);

        return $movement;
    }

    private function appendMovement(array $movement): void
    {
        $content = json_decode(file_get_contents($this->movementsPath), true) ?: [];
        $content[] = $movement;
        file_put_contents($this->movementsPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Retorna o estoque atual (array de produtos)
     * @return array
     */
    public function getStock(): array
    {
        return $this->loadStock();
    }

    /**
     * Adiciona um novo produto ao estoque.
     * @param int $codigoProduto
     * @param string $descricaoProduto
     * @param int $quantidade
     * @return array O produto inserido
     */
    public function addProduct(int $codigoProduto, string $descricaoProduto, int $quantidade): array
    {
        $stock = $this->loadStock();

        // Verifica se já existe produto com o mesmo código
        foreach ($stock as $item) {
            if ((int)$item['codigoProduto'] === $codigoProduto) {
                throw new \InvalidArgumentException('Já existe um produto com o código: ' . $codigoProduto);
            }
        }

        $product = [
            'codigoProduto' => $codigoProduto,
            'descricaoProduto' => $descricaoProduto,
            'estoque' => (int)$quantidade
        ];

        $stock[] = $product;
        $this->saveStock($stock);

        return $product;
    }

    /**
     * Remove um produto do estoque pelo código.
     * @param int $codigoProduto
     * @return array Produto removido
     * @throws \InvalidArgumentException se não existir
     */
    public function removeProduct(int $codigoProduto): array
    {
        $stock = $this->loadStock();

        $foundKey = null;
        foreach ($stock as $k => $item) {
            if ((int)$item['codigoProduto'] === $codigoProduto) {
                $foundKey = $k;
                break;
            }
        }

        if ($foundKey === null) {
            throw new \InvalidArgumentException('Produto não encontrado: ' . $codigoProduto);
        }

        $removed = $stock[$foundKey];

        // remove do array e salva
        array_splice($stock, $foundKey, 1);
        $this->saveStock($stock);

        return $removed;
    }
}
