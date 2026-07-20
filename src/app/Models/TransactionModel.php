<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'user_id', 'recipient_id', 'operation_type_id', 'amount', 'fee_amount', 'created_at'
    ];
    
    // Les timestamps sont gérés manuellement dans le service pour s'assurer que ça matche bien (ou pas)
    protected $useTimestamps = false; 

    /**
     * Récupère l'historique complet d'un utilisateur (dépôts, retraits, transferts envoyés/reçus).
     */
    public function getUserHistory(int $userId): array
    {
        return $this->select('transactions.*, op.name as op_name, r.phone as recipient_phone, u.phone as sender_phone')
            ->join('operation_types op', 'op.id = transactions.operation_type_id')
            ->join('users u', 'u.id = transactions.user_id')
            ->join('users r', 'r.id = transactions.recipient_id', 'left')
            ->groupStart()
                ->where('transactions.user_id', $userId)
                ->orWhere('transactions.recipient_id', $userId)
            ->groupEnd()
            ->orderBy('transactions.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Récupère les N dernières transactions d'un utilisateur.
     */
    public function getRecentForUser(int $userId, int $limit = 5): array
    {
        return $this->select('transactions.amount, transactions.fee_amount, transactions.created_at, op.name AS op_name')
            ->join('operation_types op', 'op.id = transactions.operation_type_id')
            ->groupStart()
                ->where('transactions.user_id', $userId)
                ->orWhere('transactions.recipient_id', $userId)
            ->groupEnd()
            ->orderBy('transactions.created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Calcule les gains de l'opérateur par type d'opération.
     */
    public function getGainsStats(): array
    {
        return $this->select('operation_types.name AS op_name, operation_types.slug, COUNT(transactions.id) AS nb_tx, SUM(transactions.amount) AS total_amount, SUM(transactions.fee_amount) AS total_fees')
            ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
            ->groupBy('operation_types.id')
            ->orderBy('operation_types.slug')
            ->findAll();
    }
    public function getGainsStatsV2(): array
    {
        // Gains internes (dépôt, retrait, transfert interne) : frais barème uniquement
        $internal = $this->select('operation_types.name AS libelle, SUM(transactions.fee_amount) as total_frais')
            ->join('operation_types', 'operation_types.id = transactions.operation_type_id')
            ->where('transactions.external_operator_id', null)
            ->groupBy('operation_types.id')
            ->findAll();

        // Gains externes : frais barème + commission, groupés PAR OPÉRATEUR EXTERNE
        $external = $this->select('external_operators.nom,
                SUM(transactions.fee_amount) as total_frais,
                SUM(transactions.commission_amount) as total_commission')
            ->join('external_operators', 'external_operators.id = transactions.external_operator_id')
            ->where('transactions.external_operator_id !=', null)
            ->groupBy('external_operators.id')
            ->findAll();

        return ['internal' => $internal, 'external' => $external];
    }
}

