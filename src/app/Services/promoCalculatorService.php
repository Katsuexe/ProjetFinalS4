<?php

namespace App\Services;

use App\Models\FeeScaleModel;
use App\Models\ExternalOperatorModel;
use App\Models\ExternalOperatorPrefixModel;
use App\Models\OperatorPrefixModel;

class FeeCalculatorService
{
    protected PromoModel $promo;
    protected OperatorPrefixModel $ownPrefixes;


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


        public function isOwnOperator(string $phone1, string $phone2 ):boolean
    {
        $result= false;

        resolveOperator($S)

        return $result; 
    }

    public function scalePromo(float $pourcentagepromo, float $amount): float
    {

    }





}