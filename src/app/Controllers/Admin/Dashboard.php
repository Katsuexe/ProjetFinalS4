<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserTypeModel;

class Dashboard extends BaseController
{
    protected UserModel          $userModel;
    protected UserTypeModel      $typeModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel    = new UserModel();
        $this->typeModel    = new UserTypeModel();
        helper(['auth', 'url', 'form']);
    }

    public function index(): string
    {
        $transactionModel = new \App\Models\TransactionModel();
        
        // Gains de l'opérateur : total des frais collectés par type d'opération
        $gains = $transactionModel->getGainsStats();
        $totalFees = array_sum(array_column($gains, 'total_fees'));

        // Situation des comptes clients (utilisateurs de type 'user' avec solde)
        $clientAccounts = $this->userModel->getClientAccounts();


        return view('admin/dashboard', [
            'title'          => 'Administration',
            'pageTitle'      => 'Tableau de bord',
            'stats'          => $this->userModel->getStats(),
            'recent_users'   => $this->userModel->getRecentWithType(8),
            'gains'          => $gains,
            'total_fees'     => $totalFees,
            'client_accounts'=> $clientAccounts,
        ]);
    }

    public function users(): string
    {
        $users = $this->userModel->withType()
                                 ->orderBy('users.created_at', 'DESC')
                                 ->findAll();

        return view('admin/users', [
            'title'     => 'Utilisateurs',
            'pageTitle' => 'Gestion des utilisateurs',
            'users'     => $users,
        ]);
    }

    public function userDetail(int $id): string
    {
        $user = $this->userModel->withType()->where('users.id', $id)->first();

        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Utilisateur #{$id} introuvable."
            );
        }

        return view('admin/user_detail', [
            'title'     => 'Détail utilisateur',
            'pageTitle' => 'Utilisateur : ' . $user['username'],
            'user'      => $user,
        ]);
    }

    public function profile(): string
    {
        return view('admin/profile', [
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

        $this->userModel->update(session('user_id'), [
            'username' => $this->request->getPost('username'),
        ]);

        session()->set('username', $this->request->getPost('username'));

        return redirect()->to('/admin/profile')->with('success', 'Profil mis à jour.');
    }

    public function createUser(): string
    {
        if (! has_any_permission(['users.manage', 'users.create'])) {
            return redirect()->to('/admin/users')->with('error', "Vous n'avez pas la permission de créer des utilisateurs.");
        }

        // Determine default user type (slug 'user') for simple creators
        $defaultType = $this->typeModel->findBySlug('user');
        $defaultTypeId = $defaultType['id'] ?? null;
        return view('admin/users/create', [
            'title'          => 'Nouvel utilisateur',
            'pageTitle'      => 'Créer un utilisateur',
            'types'          => $this->typeModel->findAll(),
            'defaultUserId'  => $defaultTypeId,
        ]);
    }

    public function storeUser()
    {
        if (! has_any_permission(['users.manage', 'users.create'])) {
            return redirect()->to('/admin/users')->with('error', "Vous n'avez pas la permission de créer des utilisateurs.");
        }

        $idType = (int) $this->request->getPost('id_type');

        // Validation dynamic selon le type d'utilisateur
        $simpleType = $this->typeModel->findBySlug('user');
        $simpleId   = $simpleType['id'] ?? null;
        $isSimple   = ($idType == $simpleId);
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'email'    => $isSimple ? 'permit_empty|valid_email|is_unique[users.email]' : 'required|valid_email|is_unique[users.email]',
            'phone'    => $isSimple ? 'required|min_length[10]|max_length[20]|is_unique[users.phone]' : 'permit_empty|min_length[10]|max_length[20]|is_unique[users.phone]',
            'password' => $isSimple ? 'permit_empty|min_length[8]' : 'required|min_length[8]',
            'password_confirm' => $isSimple ? 'permit_empty|matches[password]' : 'required|matches[password]',
            'id_type'  => 'required|is_natural_no_decimal',
        ];
        $messages = [
            'email' => ['is_unique' => 'Cet email est déjà utilisé.'],
            'phone' => ['is_unique' => 'Ce numéro de téléphone est déjà utilisé.'],
            'password_confirm' => ['matches' => 'Les mots de passe ne correspondent pas.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $idType = (int) $this->request->getPost('id_type');
        if (! has_permission('users.manage')) {
            $defaultType = $this->typeModel->findBySlug('user');
            $idType      = $defaultType['id'] ?? $idType;
        }

        // Si c'est un compte "user" normal, le téléphone doit être fourni (requis métier pour Mobile Money)
        if ($idType == 3 && empty($this->request->getPost('phone'))) {
            return redirect()->back()->withInput()->with('error', 'Le numéro de téléphone est obligatoire pour un compte utilisateur (Mobile Money).');
        }

        $this->userModel->insert([
            'username'  => $this->request->getPost('username'),
            'email'     => $this->request->getPost('email'),
            'phone'     => $this->request->getPost('phone') ?: null,
            'password'  => $this->userModel->hashPassword($this->request->getPost('password')),
            'id_type'   => $idType,
            'is_active' => 1,
        ]);

        return redirect()->to('/admin/users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function editUser(int $id): string
    {
        $user = $this->userModel->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Utilisateur #{$id} introuvable.");
        }

        return view('admin/users/edit', [
            'title'     => 'Modifier utilisateur',
            'pageTitle' => 'Modifier : ' . $user['username'],
            'user'      => $user,
            'types'     => $this->typeModel->findAll(),
        ]);
    }

    public function updateUser(int $id)
    {
        $user = $this->userModel->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Utilisateur #{$id} introuvable.");
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'id_type'  => 'required|is_natural_no_decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'id_type'  => (int) $this->request->getPost('id_type'),
        ];

        $newPassword = $this->request->getPost('password');
        if (! empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                return redirect()->back()->withInput()->with('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            }
            $data['password'] = $this->userModel->hashPassword($newPassword);
        }

        $this->userModel->update($id, $data);

        return redirect()->to('/admin/users/' . $id)->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function toggleUser(int $id)
    {
        if (! has_permission('users.manage')) {
            return redirect()->to('/admin/users')->with('error', "Vous n'avez pas la permission de modifier les utilisateurs.");
        }

        $user = $this->userModel->find($id);
        if ($user) {
            $this->userModel->update($id, ['is_active' => (int) ! $user['is_active']]);
            session()->setFlashdata('success', 'Statut mis à jour.');
        }
        return redirect()->to('/admin/users');
    }

    public function deleteUser(int $id)
    {
        if (! has_permission('users.delete')) {
            return redirect()->to('/admin/users')->with('error', "Vous n'avez pas la permission de supprimer les utilisateurs.");
        }

        $this->userModel->delete($id);
        return redirect()->to('/admin/users')->with('success', 'Utilisateur supprimé.');
    }
}
