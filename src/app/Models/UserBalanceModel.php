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
     *
     * ⚠️ Ne PAS utiliser directement pour les opérations mobile money
     * (dépôt/retrait/transfert) : ce raccourci "+/-" ne fait que déplacer un
     * compteur, sans jamais être revérifié contre l'historique réel. Utiliser
     * recomputeFromLedger() à la place une fois la ligne `transactions`
     * insérée, pour que le solde stocké reste toujours dérivable et
     * cohérent avec le grand livre. Conservé ici seulement pour des cas
     * hors mobile money (ex: script d'admin ponctuel).
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
            // Plus de concaténation SQL brute ("balance + {$amount}", false) :
            // on lit la valeur, on calcule en PHP, on écrit une valeur figée.
            // Toujours passer par le Query Builder paramétré, jamais par du
            // SQL construit à la main, même quand la valeur est un float
            // "sûr" — un futur appelant pourrait un jour y passer une
            // chaîne non validée.
            $newBalance = (float) $row['balance'] + $amount;

            $this->where('id_user', $userId)
                 ->set(['balance' => $newBalance, 'updated_at' => date('Y-m-d H:i:s')])
                 ->update();
        }
    }

    /**
     * Recalcule le solde d'un utilisateur à partir du grand livre
     * (TransactionModel::computeBalanceForUser) et écrit ce résultat comme
     * nouvelle valeur de cache. C'est la méthode à appeler après CHAQUE
     * opération mobile money, à l'intérieur de la même transaction SQL que
     * l'insertion de la ligne `transactions`.
     *
     * Avantage sur un simple "+/-" : le solde stocké n'est jamais qu'une
     * PROJECTION de l'historique. Il ne peut donc jamais dériver
     * silencieusement de la réalité — en cas de doute, on peut toujours
     * relancer ce recalcul et comparer.
     */
    public function recomputeFromLedger(int $userId): float
    {
        $transactionModel = new TransactionModel();
        $newBalance = $transactionModel->computeBalanceForUser($userId);

        $row = $this->findByUser($userId);

        if ($row === null) {
            $this->insert([
                'id_user'    => $userId,
                'balance'    => $newBalance,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->where('id_user', $userId)
                 ->set(['balance' => $newBalance, 'updated_at' => date('Y-m-d H:i:s')])
                 ->update();
        }

        return $newBalance;
    }
}
