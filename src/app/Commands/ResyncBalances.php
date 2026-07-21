<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserBalanceModel;
use App\Models\TransactionModel;

/**
 * Recalcule user_balances.balance pour CHAQUE utilisateur à partir du
 * grand livre `transactions` (TransactionModel::computeBalanceForUser),
 * et écrase la valeur en cache si elle diverge.
 *
 * À lancer :
 *   - après un `db:seed` qui aurait (mal) écrit des soldes directement,
 *   - après un import/migration de données,
 *   - ou simplement en cas de doute sur la cohérence des soldes.
 *
 * Usage : php spark balances:resync
 */
class ResyncBalances extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'balances:resync';
    protected $description = 'Recalcule tous les soldes (user_balances) depuis le grand livre des transactions.';

    public function run(array $params)
    {
        $balanceModel     = new UserBalanceModel();
        $transactionModel = new TransactionModel();

        $rows = $balanceModel->findAll();

        if (empty($rows)) {
            CLI::write('Aucune ligne dans user_balances.', 'yellow');
            return;
        }

        $fixed = 0;

        foreach ($rows as $row) {
            $userId  = (int) $row['id_user'];
            $stored  = (float) $row['balance'];
            $correct = $transactionModel->computeBalanceForUser($userId);

            if (abs($stored - $correct) > 0.001) {
                $balanceModel->update($row['id'], [
                    'balance'    => $correct,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                CLI::write(
                    "user_id={$userId} : {$stored} Ar -> {$correct} Ar (écart de " . round($stored - $correct, 2) . " Ar corrigé)",
                    'red'
                );
                $fixed++;
            } else {
                CLI::write("user_id={$userId} : OK ({$stored} Ar)", 'green');
            }
        }

        CLI::write($fixed > 0 ? "{$fixed} solde(s) corrigé(s)." : 'Tous les soldes étaient déjà cohérents.', 'yellow');
    }
}
