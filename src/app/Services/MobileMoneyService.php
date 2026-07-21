<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserBalanceModel;
use App\Models\TransactionModel;
use App\Models\OperationTypeModel;
use App\Models\FeeScaleModel;
use App\Models\FeeCreditModel;

class MobileMoneyService
{
    protected $db;
    protected $userModel;
    protected $balanceModel;
    protected $transactionModel;
    protected $opTypeModel;
    protected $feeScaleModel;
    protected $feeCreditModel;

    public function __construct()
    {
        $this->db               = \Config\Database::connect();
        $this->userModel        = new UserModel();
        $this->balanceModel     = new UserBalanceModel();
        $this->transactionModel = new TransactionModel();
        $this->opTypeModel      = new OperationTypeModel();
        $this->feeScaleModel    = new FeeScaleModel();
        $this->feeCreditModel   = new FeeCreditModel();
    }

    /**
     * Effectue un dépôt sur le compte de l'utilisateur.
     * @return array ['success' => bool, 'message' => string]
     */
    public function deposit(int $userId, float $amount): array
    {
        if ($amount <= 0) {
            throw new \Exception("Le montant du dépôt doit être supérieur à 0.");
        }

        try {
            $this->db->transException(true)->transStart();

            $opTypeId = $this->opTypeModel->getIdBySlug('deposit');
            if (!$opTypeId) {
                throw new \Exception("Type d'opération 'deposit' introuvable.");
            }

            // Historique d'abord : le solde est ensuite DÉRIVÉ de cette ligne,
            // jamais l'inverse (voir UserBalanceModel::recomputeFromLedger).
            $this->transactionModel->insert([
                'user_id'           => $userId,
                'recipient_id'      => null,
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            // Recalcul du solde à partir du grand livre (source de vérité),
            // plutôt qu'un "balance + X" qui ne fait que déplacer un compteur.
            $this->balanceModel->recomputeFromLedger($userId);

            $this->db->transComplete();
            return ['success' => true, 'message' => 'Dépôt effectué avec succès.'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Effectue un retrait depuis le compte de l'utilisateur (avec calcul des frais et consommation de crédits).
     * @return array ['success' => bool, 'message' => string, 'fee' => float, 'credit_consumed' => float]
     */
    public function withdraw(int $userId, float $amount): array
    {
        if ($amount <= 0) {
            throw new \Exception("Le montant du retrait doit être supérieur à 0.");
        }

        try {
            $this->db->transException(true)->transStart();

            $opTypeId = $this->opTypeModel->getIdBySlug('withdraw');
            if (!$opTypeId) {
                throw new \Exception("Type d'opération 'withdraw' introuvable.");
            }

            $calculator = new FeeCalculatorService();
            $fee = $calculator->scaleFee($opTypeId, $amount);

            // Consommer les crédits les plus anciens en premier
            $credits = $this->feeCreditModel->where('user_id', $userId)
                                            ->where('amount_remaining >', 0)
                                            ->orderBy('created_at', 'ASC')
                                            ->findAll();

            $consumed = 0.0;
            foreach ($credits as $credit) {
                if ($consumed >= $fee) break;
                $take = min($credit['amount_remaining'], $fee - $consumed);
                $this->feeCreditModel->update($credit['id'], ['amount_remaining' => $credit['amount_remaining'] - $take]);
                $consumed += $take;
            }

            $realFee = $fee - $consumed; // Ce qui reste réellement à payer par l'utilisateur
            $totalToDeduct = $amount + $realFee;

            // Vérifier le solde
            $balanceRow = $this->balanceModel->where('id_user', $userId)->first();
            if (!$balanceRow || $balanceRow['balance'] < $totalToDeduct) {
                throw new \Exception('Solde insuffisant (incluant ' . $realFee . ' Ar de frais).');
            }

            // Historique d'abord, solde recalculé ensuite depuis ce grand livre.
            $this->transactionModel->insert([
                'user_id'           => $userId,
                'recipient_id'      => null,
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => $realFee, // Frais réellement payés, après consommation des crédits éventuels
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->balanceModel->recomputeFromLedger($userId);

            $this->db->transComplete();

            $msg = "Retrait de {$amount} Ar effectué. Frais payés: {$realFee} Ar.";
            if ($consumed > 0) {
                $msg .= " (Frais offerts grâce à vos transferts: {$consumed} Ar)";
            }

            return [
                'success' => true, 
                'message' => $msg,
                'fee'     => $realFee,
                'credit_consumed' => $consumed
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param string $senderPhone
     * @param string $recipientPhone
     * @param float  $amount
     * @param bool   $includeWithdrawFee
     * @return array ['success' => bool, 'transaction_id' => int, 'breakdown' => array]
     */
    public function transfer(string $senderPhone, string $recipientPhone, float $amount, bool $includeWithdrawFee = true): array
    {
        if ($amount <= 0) {
            throw new \RuntimeException("Le montant du transfert doit être supérieur à 0.");
        }

        if ($senderPhone === $recipientPhone) {
            throw new \RuntimeException("Vous ne pouvez pas transférer de l'argent vers votre propre numéro.");
        }

        $calculator = new FeeCalculatorService();
        $resolution = $calculator->resolveOperator($recipientPhone);

        if ($resolution['type'] === 'unknown') {
            throw new \RuntimeException('Opération impossible.'); // Message générique de sécurité
        }

        $breakdown = $calculator->computeTransfer($amount, $resolution['external_operator_id'], $includeWithdrawFee);

        $this->db->transStart();

        $sender = $this->userModel->where('phone', $senderPhone)->first();
        if (!$sender) {
            throw new \RuntimeException('Opération impossible.');
        }

        $balanceRow = $this->balanceModel->where('id_user', $sender['id'])->first();
        if (!$balanceRow || $balanceRow['balance'] < $breakdown['total_debit']) {
            throw new \RuntimeException('Solde insuffisant pour ce transfert.');
        }

        $opTypeId = $this->opTypeModel->getIdBySlug('transfer');
        $transactionId = null;

        if ($resolution['type'] === 'internal') {
            $recipient = $this->userModel->where('phone', $recipientPhone)->first();
            if (!$recipient) {
                throw new \RuntimeException('Opération impossible.');
            }

            $transactionId = $this->transactionModel->insert([
                'user_id'           => $sender['id'],
                'recipient_id'      => $recipient['id'],
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => $breakdown['transfer_fee'],
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            // décision 4A : crédit de frais de retrait pour le DESTINATAIRE INTERNE uniquement
            if ($includeWithdrawFee && $breakdown['withdraw_fee_eq'] > 0) {
                $this->feeCreditModel->insert([
                    'user_id'               => $recipient['id'],
                    'amount_remaining'      => $breakdown['withdraw_fee_eq'],
                    'source_transaction_id' => $transactionId,
                    'created_at'            => date('Y-m-d H:i:s'),
                ]);
            }
        } else {
            // externe (décision 1A) : pas de compte destinataire, juste une trace
            $transactionId = $this->transactionModel->insert([
                'user_id'               => $sender['id'],
                'recipient_id'          => null,
                'operation_type_id'     => $opTypeId,
                'amount'                => $amount,
                'fee_amount'            => $breakdown['transfer_fee'],
                'external_operator_id'  => $resolution['external_operator_id'],
                'external_phone'        => $recipientPhone,
                'commission_amount'     => $breakdown['commission'],
                'envoye'                => 0, // décision 3B
                'created_at'            => date('Y-m-d H:i:s'),
            ]);
        }

        // Recalcul des soldes impliqués depuis le grand livre (jamais de
        // +/- direct sur user_balances.balance) : expéditeur toujours, et
        // destinataire interne s'il existe.
        $this->balanceModel->recomputeFromLedger($sender['id']);
        if ($resolution['type'] === 'internal') {
            $this->balanceModel->recomputeFromLedger($recipient['id']);
        }

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Erreur lors du transfert.');
        }

        return [
            'success'        => true,
            'message'        => 'Transfert effectué avec succès.',
            'transaction_id' => $transactionId, 
            'breakdown'      => $breakdown
        ];
    }

    /**
     * Envoi groupé dynamique : Chaque destinataire a son propre montant ET
     * son propre choix d'inclure ou non les frais de retrait équivalents
     * (décision 4A appliquée individuellement, pas globalement).
     * $recipientsData : array of ['phone' => '...', 'amount' => float, 'include_withdraw_fee' => bool]
     */
    public function transferMultiple(string $senderPhone, array $recipientsData): array
    {
        if (empty($recipientsData)) {
            throw new \RuntimeException("Aucun destinataire sélectionné.");
        }

        $this->db->transStart(); // tout ou rien

        $results = [];
        foreach ($recipientsData as $data) {
            if (empty($data['phone']) || empty($data['amount'])) continue;
            
            // On réutilise la logique de transfert classique pour chaque ligne
            $results[] = $this->transfer($senderPhone, $data['phone'], (float) $data['amount'], (bool) ($data['include_withdraw_fee'] ?? true));
        }

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Opération impossible pour un ou plusieurs destinataires. Annulation.');
        }

        return [
            'success' => true,
            'message' => 'Transferts groupés effectués avec succès.',
            'results' => $results
        ];
    }
}
