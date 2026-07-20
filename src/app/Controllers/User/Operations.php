<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class Operations extends BaseController
{
    protected $db;
    protected $userId;
    protected $phone;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        
        $session = session();
        $this->userId = $session->get('user_id');
        $this->phone = $session->get('phone');
        
        // Sécurité : uniquement accessible si connecté et possesseur d'un numéro de téléphone (client mobile money)
        if (!$this->userId || empty($this->phone)) {
            header('Location: ' . base_url('login'));
            exit;
        }
        
        helper(['form', 'url']);
    }

    // ─── DEPOT ──────────────────────────────────────────────────────────────
    public function deposit()
    {
        if ($this->request->getMethod() === 'POST') {
            $amount = (float) $this->request->getPost('amount');
            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Montant invalide.');
            }

            try {
                $this->db->transException(true)->transStart();

                // Trouver l'ID du type d'opération "deposit"
                $opType = $this->db->table('operation_types')->where('slug', 'deposit')->get()->getRow();
                
                // Mettre à jour le solde
                $this->db->table('user_balances')
                         ->where('id_user', $this->userId)
                         ->set('balance', 'balance + ' . $amount, false)
                         ->update();
                
                // Historique
                $this->db->table('transactions')->insert([
                    'user_id'           => $this->userId,
                    'recipient_id'      => null,
                    'operation_type_id' => $opType->id,
                    'amount'            => $amount,
                    'fee_amount'        => 0,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);

                $this->db->transComplete();
                return redirect()->to('user/operations/history')->with('success', 'Dépôt effectué avec succès.');

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erreur lors du dépôt.');
            }
        }

        return view('user/operations/deposit', [
            'title' => 'Faire un dépôt'
        ]);
    }

    // ─── RETRAIT ────────────────────────────────────────────────────────────
    public function withdraw()
    {
        if ($this->request->getMethod() === 'POST') {
            $amount = (float) $this->request->getPost('amount');
            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Montant invalide.');
            }

            try {
                $this->db->transException(true)->transStart();
                $opType = $this->db->table('operation_types')->where('slug', 'withdraw')->get()->getRow();
                
                // Calcul des frais
                $feeScale = $this->db->table('fee_scales')
                                     ->where('operation_type_id', $opType->id)
                                     ->where('min_amount <=', $amount)
                                     ->where('max_amount >=', $amount)
                                     ->get()->getRow();
                
                $fee = $feeScale ? $feeScale->fee_amount : 0;
                $totalToDeduct = $amount + $fee;

                // Vérifier le solde
                $balanceRow = $this->db->table('user_balances')->where('id_user', $this->userId)->get()->getRow();
                if (!$balanceRow || $balanceRow->balance < $totalToDeduct) {
                    throw new \Exception('Solde insuffisant (incluant ' . $fee . ' Ar de frais).');
                }

                // Mettre à jour le solde
                $this->db->table('user_balances')
                         ->where('id_user', $this->userId)
                         ->set('balance', 'balance - ' . $totalToDeduct, false)
                         ->update();

                // Historique
                $this->db->table('transactions')->insert([
                    'user_id'           => $this->userId,
                    'recipient_id'      => null,
                    'operation_type_id' => $opType->id,
                    'amount'            => $amount,
                    'fee_amount'        => $fee,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);

                $this->db->transComplete();
                return redirect()->to('user/operations/history')->with('success', "Retrait de {$amount} Ar effectué. Frais: {$fee} Ar.");

            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return view('user/operations/withdraw', [
            'title' => 'Faire un retrait'
        ]);
    }

    // ─── TRANSFERT ──────────────────────────────────────────────────────────
    public function transfer()
    {
        if ($this->request->getMethod() === 'POST') {
            $amount = (float) $this->request->getPost('amount');
            $recipientPhone = $this->request->getPost('recipient_phone');
            
            if ($amount <= 0 || empty($recipientPhone) || $recipientPhone === $this->phone) {
                return redirect()->back()->with('error', 'Informations de transfert invalides.');
            }

            try {
                $this->db->transException(true)->transStart();
                $opType = $this->db->table('operation_types')->where('slug', 'transfer')->get()->getRow();
                
                // Chercher le destinataire
                $recipient = $this->db->table('users')->where('phone', $recipientPhone)->get()->getRow();
                if (!$recipient) {
                    throw new \Exception('Numéro de destinataire introuvable.');
                }

                // Calcul des frais
                $feeScale = $this->db->table('fee_scales')
                                     ->where('operation_type_id', $opType->id)
                                     ->where('min_amount <=', $amount)
                                     ->where('max_amount >=', $amount)
                                     ->get()->getRow();
                
                $fee = $feeScale ? $feeScale->fee_amount : 0;
                $totalToDeduct = $amount + $fee;

                // Vérifier le solde
                $balanceRow = $this->db->table('user_balances')->where('id_user', $this->userId)->get()->getRow();
                if (!$balanceRow || $balanceRow->balance < $totalToDeduct) {
                    throw new \Exception('Solde insuffisant (incluant ' . $fee . ' Ar de frais).');
                }

                // 1. Débiter l'envoyeur
                $this->db->table('user_balances')
                         ->where('id_user', $this->userId)
                         ->set('balance', 'balance - ' . $totalToDeduct, false)
                         ->update();

                // 2. Créditer le destinataire
                $this->db->table('user_balances')
                         ->where('id_user', $recipient->id)
                         ->set('balance', 'balance + ' . $amount, false)
                         ->update();

                // 3. Historique
                $this->db->table('transactions')->insert([
                    'user_id'           => $this->userId,
                    'recipient_id'      => $recipient->id,
                    'operation_type_id' => $opType->id,
                    'amount'            => $amount,
                    'fee_amount'        => $fee,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);

                $this->db->transComplete();
                return redirect()->to('user/operations/history')->with('success', "Transfert de {$amount} Ar vers {$recipientPhone} réussi. Frais: {$fee} Ar.");

            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return view('user/operations/transfer', [
            'title' => 'Transférer de l\'argent'
        ]);
    }

    // ─── HISTORIQUE ─────────────────────────────────────────────────────────
    public function history()
    {
        $transactions = $this->db->table('transactions')
            ->select('transactions.*, op.name as op_name, r.phone as recipient_phone, u.phone as sender_phone')
            ->join('operation_types op', 'op.id = transactions.operation_type_id')
            ->join('users u', 'u.id = transactions.user_id')
            ->join('users r', 'r.id = transactions.recipient_id', 'left')
            ->where('transactions.user_id', $this->userId)
            ->orWhere('transactions.recipient_id', $this->userId)
            ->orderBy('transactions.created_at', 'DESC')
            ->get()->getResultArray();

        // Récupérer le solde actuel pour l'afficher en haut de l'historique
        $balance = $this->db->table('user_balances')->where('id_user', $this->userId)->get()->getRowArray();

        return view('user/operations/history', [
            'title'        => 'Historique et Solde',
            'transactions' => $transactions,
            'balance'      => $balance['balance'] ?? 0,
            'userId'       => $this->userId,
        ]);
    }
}
