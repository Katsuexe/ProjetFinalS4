<?php

namespace App\Services;

use App\Models\FeeScaleModel;
use App\Models\ExternalOperatorModel;
use App\Models\ExternalOperatorPrefixModel;
use App\Models\OperatorPrefixModel;

class FeeCalculatorService
{
    protected FeeScaleModel $feeScales;
    protected ExternalOperatorModel $externalOperators;
    protected ExternalOperatorPrefixModel $externalPrefixes;
    protected OperatorPrefixModel $ownPrefixes;

    public function __construct()
    {
        $this->feeScales         = new FeeScaleModel();
        $this->externalOperators = new ExternalOperatorModel();
        $this->externalPrefixes  = new ExternalOperatorPrefixModel();
        $this->ownPrefixes       = new OperatorPrefixModel();
    }

    /**
     * Décision 1A : résout un numéro en "interne" (nous), "externe" (opérateur concurrent) ou "inconnu".
     * @return array{type:string, external_operator_id:?int}
     */
    public function resolveOperator(string $phone): array
    {
        //get prefix
        $prefix = substr(preg_replace('/\D/', '', $phone), 0, 3);
        
        //prefix poiur même opérateur (self)
        if ($this->ownPrefixes->where('prefix', $prefix)->first()) {
            return ['type' => 'internal', 'external_operator_id' => null];
        }

        // prefix pour opérateur externe 
        $externalPrefix = $this->externalPrefixes->where('prefix', $prefix)->first();
        if ($externalPrefix) {
            return ['type' => 'external', 'external_operator_id' => $externalPrefix['external_operator_id']];
        }

        return ['type' => 'unknown', 'external_operator_id' => null];
    }



    /** Frais barème par tranche (dépôt/retrait/transfert interne — adapté au vrai schéma DB) */
    public function scaleFee(int $operationTypeId, float $amount): float
    {
        return $this->feeScales->getApplicableFee($operationTypeId, $amount);
    }


    /**
     * Décision 2A : commission externe additive, décision 4A : frais de retrait "inclus" optionnel.
     * Retourne le détail complet pour affichage ET pour débit réel — même structure des 2 côtés.
     */
    public function computeTransfer(float $amount, ?int $externalOperatorId, bool $includeWithdrawFee, ?float $pourcentagepromo): array
    {
        $transferTypeId = 3; // id du type "transfert" dans operation_types
        $withdrawTypeId = 2; // id du type "retrait"

        $transferFee = $this->scaleFee($transferTypeId, $amount);

        $commission = 0.0;
        if ($externalOperatorId !== null) {
            $operator   = $this->externalOperators->find($externalOperatorId);
            $commission = $amount * ((float)$operator['commission_pourcentage'] / 100);
        }

        $withdrawFeeEq = $includeWithdrawFee ? $this->scaleFee($withdrawTypeId, $amount) : 0.0;

        return [
            'amount'          => $amount,
            'transfer_fee'    => $transferFee,
            'commission'      => $commission,
            'withdraw_fee_eq' => $withdrawFeeEq,
            'total_debit'     => $amount + $transferFee + $commission + $withdrawFeeEq,
            'amount_received' => $amount, // toujours le montant plein (décision 1A/4A)
        ];
    }
}
