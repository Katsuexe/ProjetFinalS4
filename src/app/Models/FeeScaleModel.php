<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeScaleModel extends Model
{
    protected $table            = 'fee_scales';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'operation_type_id', 'min_amount', 'max_amount', 'fee_amount'
    ];

    /**
     * Trouve les frais applicables pour un type d'opération et un montant donné.
     *
     * Si le montant tombe dans une tranche définie, on prend son frais.
     * Sinon (montant au-delà du plus grand `max_amount` du barème, ou en
     * dessous du plus petit `min_amount`), on ne retourne JAMAIS 0 Ar
     * silencieusement — un montant hors barème n'est pas gratuit, c'est
     * juste un barème incomplet. On applique le frais de la tranche la
     * plus proche (la plus haute pour un dépassement par le haut, la plus
     * basse pour un dépassement par le bas), qui reste l'estimation la
     * moins fausse tant qu'aucune tranche explicite n'existe.
     */
    public function getApplicableFee(int $operationTypeId, float $amount): float
    {
        $scale = $this->where('operation_type_id', $operationTypeId)
                      ->where('min_amount <=', $amount)
                      ->where('max_amount >=', $amount)
                      ->first();

        if ($scale) {
            return (float) $scale['fee_amount'];
        }

        // Montant au-delà de la plus grande tranche connue → frais de la
        // tranche la plus haute (barème ouvert vers le haut).
        $highest = $this->where('operation_type_id', $operationTypeId)
                        ->orderBy('max_amount', 'DESC')
                        ->first();

        if ($highest && $amount > (float) $highest['max_amount']) {
            return (float) $highest['fee_amount'];
        }

        // Montant en dessous de la plus petite tranche connue → frais de
        // la tranche la plus basse.
        $lowest = $this->where('operation_type_id', $operationTypeId)
                       ->orderBy('min_amount', 'ASC')
                       ->first();

        if ($lowest && $amount < (float) $lowest['min_amount']) {
            return (float) $lowest['fee_amount'];
        }

        // Aucun barème du tout pour ce type d'opération.
        return 0.0;
    }
}
