<?php

namespace App\Service;

/**
 * Service para cálculo de juros por atraso. Considera uma multa de 2,5% ao dia.
 * Implementação usa capitalização composta diária.
 */
class InterestService
{
    private float $dailyRate = 0.025; // 2.5% ao dia

    /**
     * Calcula juros desde a data de vencimento até hoje.
     * Retorna array com dias em atraso, valor original, juros e valor final.
     *
     * @param float $valor
     * @param string $dueDate string no formato YYYY-MM-DD
     * @return array
     */
    public function calculate(float $valor, string $dueDate): array
    {
        $due = new \DateTime($dueDate);
        $today = new \DateTime();

        $interval = $due->diff($today);
        $days = (int)$interval->format('%r%a');

        if ($days <= 0) {
            return [
                'dias_atraso' => 0,
                'valor_original' => $valor,
                'juros' => 0.0,
                'valor_final' => $valor
            ];
        }

        // capitalização composta diária: valor_final = valor * (1 + rate)^days
        $valorFinal = $valor * pow(1 + $this->dailyRate, $days);
        $juros = $valorFinal - $valor;

        return [
            'dias_atraso' => $days,
            'valor_original' => $valor,
            'juros' => $juros,
            'valor_final' => $valorFinal
        ];
    }
}
