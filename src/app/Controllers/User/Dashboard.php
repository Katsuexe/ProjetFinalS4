<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserBalanceModel;

class Dashboard extends BaseController
{
    protected UserModel          $userModel;
    protected UserBalanceModel   $balanceModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel    = new UserModel();
        $this->balanceModel = new UserBalanceModel();
        helper(['auth', 'url', 'form']);
    }

    public function index(): string
    {
        $db  = \Config\Database::connect();
        $uid = (int) session('user_id');

        // Solde — affiché pour tous les clients Mobile Money
        $row = $db->table('user_balances')->where('id_user', $uid)->get()->getRowArray();

        // Dernières transactions (5)
        $recentTx = $db->table('transactions')
            ->select('transactions.amount, transactions.fee_amount, transactions.created_at, op.name AS op_name')
            ->join('operation_types op', 'op.id = transactions.operation_type_id')
            ->where('transactions.user_id', $uid)
            ->orWhere('transactions.recipient_id', $uid)
            ->orderBy('transactions.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('user/dashboard', [
            'title'              => 'Mon espace',
            'pageTitle'          => 'Tableau de bord',
            'balance'            => $row['balance'] ?? 0,
            'currency'           => $row['currency'] ?? 'Ar',
            'balance_updated_at' => $row ? date('d/m/Y H:i', strtotime($row['updated_at'])) : null,
            'recent_transactions'=> $recentTx,
        ]);
    }

    public function profile(): string
    {
        return view('user/profile', [
            'title'     => 'Mon profil',
            'pageTitle' => 'Mon profil',
            'user'      => $this->userModel->find(session('user_id')),
        ]);
    }

    public function updateProfile()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $newUsername = $this->request->getPost('username');

        if ($newUsername === session('username')) {
            return redirect()->to('/user/profile')->with('error', 'Aucune modification détectée.');
        }

        $this->userModel->update(session('user_id'), [
            'username' => $newUsername,
        ]);

        session()->set('username', $newUsername);

        return redirect()->to('/user/profile')->with('success', 'Profil mis à jour avec succès.');
    }

    public function password(): string
    {
        return view('user/password', [
            'title'     => 'Mot de passe',
            'pageTitle' => 'Changer le mot de passe',
        ]);
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Vérification du mot de passe actuel
        $user = $this->userModel->find(session('user_id'));
        if (! $user || ! password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $this->userModel->update(session('user_id'), [
            'password' => $this->userModel->hashPassword($this->request->getPost('password')),
        ]);

        return redirect()->to('/user/profile')->with('success', 'Mot de passe mis à jour avec succès.');
    }
}
