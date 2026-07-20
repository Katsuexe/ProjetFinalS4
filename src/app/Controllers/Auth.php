<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserTypeModel;

class Auth extends BaseController
{
    protected UserModel     $userModel;
    protected UserTypeModel $userTypeModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel     = new UserModel();
        $this->userTypeModel = new UserTypeModel();
        helper(['auth', 'url', 'form']);
    }

    // ─── GET /login ─────────────────────────────────────────────────────────
    public function login(): string
    {
        return view('auth/login', ['title' => 'Connexion']);
    }

    // ─── POST /login ────────────────────────────────────────────────────────
    public function loginProcess()
    {
        $loginId = $this->request->getPost('login_id');
        $password = $this->request->getPost('password');

        // Détection si c'est un numéro de téléphone (seulement des chiffres, ex: 0331234567)
        if (preg_match('/^[0-9]{10}$/', $loginId)) {
            return $this->handlePhoneLogin($loginId);
        }

        // Sinon, c'est une connexion par email classique (Admin/Modo)
        $rules = [
            'login_id' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $user = $this->userModel->authenticate($loginId, $password);

        if (! $user) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'errors' => ['login_id' => 'Email ou mot de passe incorrect.'],
            ]);
        }

        $this->startSession($user);

        if (in_array('admin.panel', $user['permissions'], true)) {
            return redirect()->to('/admin/dashboard');
        }

        return redirect()->to('/user/dashboard');
    }

    private function handlePhoneLogin(string $phone)
    {
        // 1. Vérifier si le préfixe est valide
        $prefix = substr($phone, 0, 3);
        $db = \Config\Database::connect();
        $validPrefix = $db->table('operator_prefixes')->where('prefix', $prefix)->get()->getRow();

        if (! $validPrefix) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'errors' => ['login_id' => 'Le préfixe du numéro de téléphone n\'est pas supporté (ex: 033, 034).'],
            ]);
        }

        // 2. Trouver l'utilisateur ou le créer
        $user = $this->userModel->where('phone', $phone)->first();

        if (! $user) {
            $defaultType = $this->userTypeModel->findBySlug('user');
            
            // Création de l'utilisateur
            $userId = $this->userModel->insert([
                'username'  => 'Client ' . $phone,
                'phone'     => $phone,
                'id_type'   => $defaultType['id'] ?? null,
                'is_active' => 1,
            ]);
            
            // Création de la balance (0 Ar par défaut)
            $db->table('user_balances')->insert([
                'id_user'    => $userId,
                'balance'    => 0,
                'currency'   => 'Ar',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Recharger l'utilisateur (pour withType et autres join si on utilisait find)
            $user = $this->userModel->find($userId);
        }

        // 3. Charger manuellement les relations nécessaires pour la session (comme authenticate)
        $userType = $this->userTypeModel->find($user['id_type']);
        $perms = $db->table('user_type_permissions')
            ->select('permissions.slug')
            ->join('permissions', 'permissions.id = user_type_permissions.id_permission')
            ->where('id_type', $user['id_type'])
            ->get()->getResultArray();
            
        $user['type_slug'] = $userType['slug'] ?? '';
        $user['type_name'] = $userType['name'] ?? '';
        $user['permissions'] = array_column($perms, 'slug');

        $this->startSession($user);

        return redirect()->to('/user/dashboard');
    }

    private function startSession(array $user)
    {
        session()->set([
            'isLoggedIn'     => true,
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'email'          => $user['email'] ?? null,
            'phone'          => $user['phone'] ?? null,
            'id_type'        => $user['id_type'],
            'type_slug'      => $user['type_slug'],
            'user_type_name' => $user['type_name'],
            'permissions'    => $user['permissions'],
        ]);
        
        // Update last login
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
    }

    // ─── GET /register ──────────────────────────────────────────────────────
    public function register(): string
    {
        return view('auth/register', ['title' => 'Créer un compte']);
    }

    // ─── POST /register ─────────────────────────────────────────────────────
    public function registerProcess()
    {
        $rules = [
            'username'         => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'email'            => ['is_unique' => 'Cet email est déjà utilisé.'],
            'password_confirm' => ['matches'   => 'Les mots de passe ne correspondent pas.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return view('auth/register', [
                'title'  => 'Créer un compte',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        // Récupérer le type 'user' par défaut via le Model (Query Builder)
        $defaultType = $this->userTypeModel->findBySlug('user');

        $this->userModel->insert([
            'username'  => $this->request->getPost('username'),
            'email'     => $this->request->getPost('email'),
            'password'  => $this->userModel->hashPassword($this->request->getPost('password')),
            'id_type'   => $defaultType['id'] ?? null,
            'is_active' => 1,
        ]);

        return redirect()->to('/login')
                         ->with('success', 'Compte créé avec succès. Connectez-vous !');
    }

    // ─── GET /logout ────────────────────────────────────────────────────────
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Vous êtes déconnecté.');
    }
}
