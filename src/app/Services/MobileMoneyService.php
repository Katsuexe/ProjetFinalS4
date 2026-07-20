<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserBalanceModel;
use App\Models\TransactionModel;
use App\Models\OperationTypeModel;
use App\Models\FeeScaleModel;

class MobileMoneyService
{
    protected $db;
    protected $userModel;
    protected $balanceModel;
    protected $transactionModel;
    protected $opTypeModel;
    protected $feeScaleModel;

    public function __construct()
    {
        $this->db               = \Config\Database::connect();
        $this->userModel        = new UserModel();
        $this->balanceModel     = new UserBalanceModel();
        $this->transactionModel = new TransactionModel();
        $this->opTypeModel      = new OperationTypeModel();
        $this->feeScaleModel    = new FeeScaleModel();
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

            // Mettre à jour le solde
            $this->balanceModel->where('id_user', $userId)
                               ->set('balance', 'balance + ' . $amount, false)
                               ->update();

            // Historique
            $this->transactionModel->insert([
                'user_id'           => $userId,
                'recipient_id'      => null,
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();
            return ['success' => true, 'message' => 'Dépôt effectué avec succès.'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Effectue un retrait depuis le compte de l'utilisateur (avec calcul des frais).
     * @return array ['success' => bool, 'message' => string, 'fee' => float]
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

            // Calcul des frais
            $fee = $this->feeScaleModel->getApplicableFee($opTypeId, $amount);
            $totalToDeduct = $amount + $fee;

            // Vérifier le solde
            $balanceRow = $this->balanceModel->where('id_user', $userId)->first();
            if (!$balanceRow || $balanceRow['balance'] < $totalToDeduct) {
                throw new \Exception('Solde insuffisant (incluant ' . $fee . ' Ar de frais).');
            }

            // Débiter le compte
            $this->balanceModel->where('id_user', $userId)
                               ->set('balance', 'balance - ' . $totalToDeduct, false)
                               ->update();

            // Historique
            $this->transactionModel->insert([
                'user_id'           => $userId,
                'recipient_id'      => null,
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => $fee,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();
            return [
                'success' => true, 
                'message' => "Retrait de {$amount} Ar effectué. Frais: {$fee} Ar.",
                'fee'     => $fee
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Effectue un transfert d'un utilisateur à un autre (avec calcul des frais pour l'expéditeur).
     * @return array ['success' => bool, 'message' => string, 'fee' => float]
     */
    public function transfer(int $senderId, string $recipientPhone, float $amount): array
    {
        if ($amount <= 0) {
            throw new \Exception("Le montant du transfert doit être supérieur à 0.");
        }

        try {
            $this->db->transException(true)->transStart();

            // S'assurer qu'on ne s'envoie pas à soi-même
            $sender = $this->userModel->find($senderId);
            if ($sender && $sender['phone'] === $recipientPhone) {
                throw new \Exception("Vous ne pouvez pas transférer de l'argent vers votre propre compte.");
            }

            // Chercher le destinataire
            $recipient = $this->userModel->where('phone', $recipientPhone)->first();
            if (!$recipient) {
                throw new \Exception("Numéro de destinataire introuvable.");
            }

            $opTypeId = $this->opTypeModel->getIdBySlug('transfer');
            if (!$opTypeId) {
                throw new \Exception("Type d'opération 'transfer' introuvable.");
            }

            // Calcul des frais
            $fee = $this->feeScaleModel->getApplicableFee($opTypeId, $amount);
            $totalToDeduct = $amount + $fee;

            // Vérifier le solde expéditeur
            $balanceRow = $this->balanceModel->where('id_user', $senderId)->first();
            if (!$balanceRow || $balanceRow['balance'] < $totalToDeduct) {
                throw new \Exception('Solde insuffisant (incluant ' . $fee . ' Ar de frais).');
            }

            // 1. Débiter l'envoyeur
            $this->balanceModel->where('id_user', $senderId)
                               ->set('balance', 'balance - ' . $totalToDeduct, false)
                               ->update();

            // 2. Créditer le destinataire
            $this->balanceModel->where('id_user', $recipient['id'])
                               ->set('balance', 'balance + ' . $amount, false)
                               ->update();

            // 3. Historique
            $this->transactionModel->insert([
                'user_id'           => $senderId,
                'recipient_id'      => $recipient['id'],
                'operation_type_id' => $opTypeId,
                'amount'            => $amount,
                'fee_amount'        => $fee,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();
            return [
                'success' => true, 
                'message' => "Transfert de {$amount} Ar vers {$recipientPhone} réussi. Frais: {$fee} Ar.",
                'fee'     => $fee
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
