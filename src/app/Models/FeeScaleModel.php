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
     * Retourne 0 si aucun barème n'est trouvé.
     */
    public function getApplicableFee(int $operationTypeId, float $amount): float
    {
        $scale = $this->where('operation_type_id', $operationTypeId)
                      ->where('min_amount <=', $amount)
                      ->where('max_amount >=', $amount)
                      ->first();
                      
        return $scale ? (float) $scale['fee_amount'] : 0.0;
    }
}
