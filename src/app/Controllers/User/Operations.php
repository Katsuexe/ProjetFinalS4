<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Services\MobileMoneyService;
use App\Models\TransactionModel;
use App\Models\UserBalanceModel;
use App\Models\ExternalOperatorModel;
use App\Services\FeeCalculatorService;
use App\Libraries\PdfService;

class Operations extends BaseController
{
    protected $userId;
    protected $phone;
    protected $mobileMoneyService;
    protected $transactionModel;
    protected $balanceModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $session = session();
        $this->userId = $session->get('user_id');
        $this->phone = $session->get('phone');
        
        // Sécurité : uniquement accessible si connecté et possesseur d'un numéro de téléphone (client mobile money)
        if (!$this->userId || empty($this->phone)) {
            header('Location: ' . base_url('login'));
            exit;
        }
        
        $this->mobileMoneyService = new MobileMoneyService();
        $this->transactionModel   = new TransactionModel();
        $this->balanceModel       = new UserBalanceModel();
        
        helper(['form', 'url']);
    }

    // ─── AFFICHAGE DYNAMIQUE (VUE UNIQUE) ───────────────────────────────────
    public function formulaire($type = null)
    {
        $validTypes = ['depot', 'retrait', 'transfert', 'transfert_multiple'];
        
        if (!$type || !in_array($type, $validTypes)) {
            // Afficher l'écran de sélection si aucun type valide
            return view('user/operations/actions', [
                'title' => 'Que souhaitez-vous faire ?'
            ]);
        }

        $data = [
            'title' => 'Opération : ' . ucfirst(str_replace('_', ' ', $type)),
            'type'  => $type
        ];

        if ($type === 'transfert') {
            $extModel = new ExternalOperatorModel();
            $data['external_operators'] = $extModel->findAll();
        }

        return view('user/operations/formulaire', $data);
    }

    // ─── TRAITEMENT DYNAMIQUE (ACTION UNIQUE) ───────────────────────────────
    public function traiter($type)
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('user/operations/formulaire');
        }

        $amount = (float) $this->request->getPost('amount');
        
        switch ($type) {
            case 'depot':
                $result = $this->mobileMoneyService->deposit((int) $this->userId, $amount);
                break;
                
            case 'retrait':
                $result = $this->mobileMoneyService->withdraw((int) $this->userId, $amount);
                break;
                
            case 'transfert':
                $recipientPhone = $this->request->getPost('recipient_phone');
                $includeWithdrawFee = (bool) $this->request->getPost('include_withdraw_fee');
                $forceOperatorId = $this->request->getPost('force_operator_id');
                
                if (empty($recipientPhone)) {
                    return redirect()->back()->with('error', 'Informations de transfert invalides.');
                }
                
                // Si l'utilisateur a forcé un opérateur, on pourrait bypasser la détection automatique ici.
                // Pour l'instant, on laisse la détection automatique fonctionner car MobileMoneyService::transfer() appelle resolveOperator().
                // Si on voulait forcer, il faudrait modifier MobileMoneyService::transfer() pour accepter un ID forcé.
                // Pour respecter l'implémentation actuelle, on laisse tel quel (le dropdown sert surtout de fallback ou de preview).
                $result = $this->mobileMoneyService->transfer($this->phone, $recipientPhone, $amount, $includeWithdrawFee);
                if ($result['success']) {
                    session()->setFlashdata('breakdown', $result['breakdown']);
                }
                break;
                
            case 'transfert_multiple':
                $recipientPhones = $this->request->getPost('recipient_phones'); // Array
                $recipientAmounts = $this->request->getPost('recipient_amounts'); // Array
                $recipientIncludeWithdrawFees = $this->request->getPost('recipient_include_withdraw_fees'); // Array (1 = inclure, 0 = ne pas inclure), un par destinataire
                
                if (empty($recipientPhones) || !is_array($recipientPhones) || empty($recipientAmounts) || !is_array($recipientAmounts)) {
                    return redirect()->back()->with('error', 'Veuillez ajouter au moins un destinataire et un montant.');
                }
                
                $recipientsData = [];
                foreach ($recipientPhones as $i => $phone) {
                    $recipientsData[] = [
                        'phone'                => $phone,
                        'amount'               => (float) ($recipientAmounts[$i] ?? 0),
                        'include_withdraw_fee' => !empty($recipientIncludeWithdrawFees[$i]) && $recipientIncludeWithdrawFees[$i] !== '0',
                    ];
                }
                
                $result = $this->mobileMoneyService->transferMultiple($this->phone, $recipientsData);
                break;
                
            default:
                return redirect()->to('user/operations/formulaire')->with('error', 'Type d\'opération inconnu.');
        }
        
        if (isset($result) && $result['success']) {
            return redirect()->to('user/operations/history')->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message'] ?? 'Une erreur est survenue.');
        }
    }

    // ─── APERCU TRANSFERT (AJAX) ────────────────────────────────────────────
    public function previewTransfer()
    {
        //!! getpost sender phone

        $senderPhone = $this->request->getPost('sender_phone');
        $recipientPhone = $this->request->getPost('recipient_phone');
        $amount = (float) $this->request->getPost('amount');
        $includeWithdrawFee = $this->request->getPost('include_withdraw_fee') === 'true' || $this->request->getPost('include_withdraw_fee') === '1';
        $forceOperatorId = $this->request->getPost('force_operator_id');

        // if(isSameOperator($senderPhone,$recipientPhone)){
        //    //!! ajouter promo
        // }

        $calculator = new FeeCalculatorService();
        
        if (!empty($forceOperatorId)) {
            $resolution = ['type' => 'external', 'external_operator_id' => (int) $forceOperatorId];
        } else {
            $resolution = $calculator->resolveOperator($recipientPhone);
        }

        if ($resolution['type'] === 'unknown') {
            return $this->response->setJSON(['error' => 'Numéro invalide ou opérateur non reconnu.', 'csrf_hash' => csrf_hash()]);
        }

        $breakdown = $calculator->computeTransfer(
            $amount,
            $resolution['external_operator_id'],
            $includeWithdrawFee
        );

        $breakdown['is_external'] = $resolution['type'] === 'external';
        $breakdown['csrf_hash'] = csrf_hash();

        return $this->response->setJSON($breakdown);
    }

    public function previewWithdraw()
    {
        $amount = (float) $this->request->getPost('amount');
        
        $calculator = new FeeCalculatorService();
        // ID 2 pour withdraw dans operation_types en général (selon seed)
        $fee = $calculator->scaleFee(2, $amount);
        
        return $this->response->setJSON([
            'amount' => $amount,
            'fee' => $fee,
            'total_debit' => $amount + $fee,
            'csrf_hash' => csrf_hash()
        ]);
    }

    public function previewMultipleTransfer()
    {
        $phones = $this->request->getPost('phones');
        $amounts = $this->request->getPost('amounts');
        $includeWithdrawFees = $this->request->getPost('include_withdraw_fees'); // Array, un par destinataire
        
        if (empty($phones) || empty($amounts)) {
            return $this->response->setJSON(['error' => 'Données invalides.', 'csrf_hash' => csrf_hash()]);
        }

        $calculator = new FeeCalculatorService();
        $totalTransferFee = 0;
        $totalCommission = 0;
        $totalWithdrawFee = 0;
        $totalAmount = 0;
        $totalDebit = 0;

        foreach ($phones as $i => $phone) {
            $amt = (float) ($amounts[$i] ?? 0);
            if ($amt <= 0) continue;

            $includeFee = !empty($includeWithdrawFees[$i]) && $includeWithdrawFees[$i] !== '0';

            $resolution = $calculator->resolveOperator($phone);
            if ($resolution['type'] !== 'unknown') {
                $bd = $calculator->computeTransfer($amt, $resolution['external_operator_id'], $includeFee);
                $totalTransferFee += $bd['transfer_fee'];
                $totalCommission += $bd['commission'];
                $totalWithdrawFee += $bd['withdraw_fee_eq'];
                $totalAmount += $amt;
                $totalDebit += $bd['total_debit'];
            }
        }

        return $this->response->setJSON([
            'total_amount' => $totalAmount,
            'total_transfer_fee' => $totalTransferFee,
            'total_commission' => $totalCommission,
            'total_withdraw_fee' => $totalWithdrawFee,
            'total_debit' => $totalDebit,
            'csrf_hash' => csrf_hash()
        ]);
    }



    // ─── HISTORIQUE ─────────────────────────────────────────────────────────
    public function history()
    {
        $transactions = $this->transactionModel->getUserHistory((int) $this->userId);
        $balanceRow   = $this->balanceModel->where('id_user', $this->userId)->first();

        return view('user/operations/history', [
            'title'        => 'Historique et Solde',
            'transactions' => $transactions,
            'balance'      => $balanceRow ? $balanceRow['balance'] : 0,
            'userId'       => $this->userId,
        ]);
    }

    // ─── EXPORT PDF ─────────────────────────────────────────────────────────
    public function exportPdf()
    {
        $transactions = $this->transactionModel->getUserHistory((int) $this->userId);
        $user = (new \App\Models\UserModel())->find($this->userId);

        $data = [
            'title'        => 'Historique des Opérations',
            'transactions' => $transactions,
            'userId'       => $this->userId,
            'user'         => $user
        ];

        $pdfService = new PdfService();
        return $pdfService->renderView('user/pdf/history_pdf', $data, 'historique_operations.pdf');
    }
}