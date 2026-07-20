<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model pour la table user_balances.
 *
 * Toutes les requêtes passent par le Query Builder de CI4,
 * compatible avec tous les drivers (MySQL, Postgre, SQLite3, SQLSRV, OCI8).
 */
class UserBalanceModel extends Model
{
    protected $table            = 'user_balances';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;   // Pas de created_at sur cette table
    protected $updatedField     = 'updated_at';

    protected $allowedFields = ['id_user', 'balance', 'currency', 'updated_at'];

    // ── Requêtes métier ───────────────────────────────────────────────────────

    /**
     * Retrouve le solde d'un utilisateur.
     */
    public function findByUser(int $userId): ?array
    {
        return $this->where('id_user', $userId)->first();
    }

    /**
     * Crédite ou débite le solde d'un utilisateur.
     * Crée l'entrée si elle n'existe pas encore.
     */
    public function adjustBalance(int $userId, float $amount): void
    {
        $row = $this->findByUser($userId);

        if ($row === null) {
            $this->insert([
                'id_user'    => $userId,
                'balance'    => $amount,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->where('id_user', $userId)
                 ->set('balance', "balance + {$amount}", false)
                 ->set('updated_at', date('Y-m-d H:i:s'))
                 ->update();
        }
    }
}
