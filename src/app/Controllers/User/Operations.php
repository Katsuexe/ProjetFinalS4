<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Services\MobileMoneyService;
use App\Models\TransactionModel;
use App\Models\UserBalanceModel;

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

    // ─── DEPOT ──────────────────────────────────────────────────────────────
    public function deposit()
    {
        if ($this->request->getMethod() === 'POST') {
            $amount = (float) $this->request->getPost('amount');
            
            $result = $this->mobileMoneyService->deposit((int) $this->userId, $amount);
            
            if ($result['success']) {
                return redirect()->to('user/operations/history')->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
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
            
            $result = $this->mobileMoneyService->withdraw((int) $this->userId, $amount);
            
            if ($result['success']) {
                return redirect()->to('user/operations/history')->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
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
            $amount         = (float) $this->request->getPost('amount');
            $recipientPhone = $this->request->getPost('recipient_phone');
            
            if (empty($recipientPhone)) {
                return redirect()->back()->with('error', 'Informations de transfert invalides.');
            }
            
            $result = $this->mobileMoneyService->transfer((int) $this->userId, $recipientPhone, $amount);
            
            if ($result['success']) {
                return redirect()->to('user/operations/history')->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }

        return view('user/operations/transfer', [
            'title' => 'Transférer de l\'argent'
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
}
