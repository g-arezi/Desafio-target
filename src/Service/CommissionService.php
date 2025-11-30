<?php

namespace App\Service;

/**
 * Service responsável pelo cálculo de comissões a partir de dados de vendas.
 * Regras:
 * - vendas < 100.00 -> 0%
 * - 100.00 <= vendas < 500.00 -> 1%
 * - vendas >= 500.00 -> 5%
 */
class CommissionService
{
    /**
     * Calcula comissões a partir do array decodificado do JSON.
     * Retorna um array com o total de comissão por vendedor e detalhes por venda.
     *
     * @param array $data
     * @return array
     */
    public function calculateFromArray(array $data): array
    {
        $result = [];

        $sales = $data['vendas'] ?? [];

        foreach ($sales as $sale) {
            $vendedor = $sale['vendedor'];
            $valor = floatval($sale['valor']);

            // determina a porcentagem de comissão
            if ($valor < 100.0) {
                $percent = 0.0;
            } elseif ($valor < 500.0) {
                $percent = 0.01;
            } else {
                $percent = 0.05;
            }

            $comissao = $valor * $percent;

            if (!isset($result[$vendedor])) {
                $result[$vendedor] = [
                    'total_comissao' => 0.0,
                    'vendas' => []
                ];
            }

            $result[$vendedor]['total_comissao'] += $comissao;
            $result[$vendedor]['vendas'][] = [
                'valor' => $valor,
                'percentual' => $percent,
                'comissao' => $comissao
            ];
        }

        return $result;
    }

    /**
     * Lê um arquivo JSON e calcula comissões.
     *
     * @param string $path
     * @return array
     */
    public function calculateFromFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new \RuntimeException('Arquivo de vendas não encontrado: ' . $path);
        }

        $json = json_decode(file_get_contents($path), true);
        return $this->calculateFromArray($json);
    }
}
