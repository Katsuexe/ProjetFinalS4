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
        return view('auth/login', [
            'title' => 'Connexion',
            'mode'  => 'phone',
        ]);
    }

    // ─── POST /login ────────────────────────────────────────────────────────
    public function loginProcess()
    {
        $loginId   = trim($this->request->getPost('login_id') ?? '');
        $loginMode = $this->request->getPost('login_mode'); // 'admin' si formulaire admin

        // ── Mode admin (formulaire email/mot de passe) ───────────────────────
        if ($loginMode === 'admin') {
            return $this->handleAdminLogin($loginId);
        }

        // ── Numéro secret → afficher le formulaire admin ─────────────────────
        $secretNumber = getenv('app.adminSecretNumber');
        if ($secretNumber && $loginId === $secretNumber) {
            return view('auth/login', [
                'title' => 'Connexion opérateur',
                'mode'  => 'admin',
            ]);
        }

        // ── Connexion client par numéro de téléphone ─────────────────────────
        if (!preg_match('/^\d{10}$/', $loginId)) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'mode'   => 'phone',
                'errors' => ['login_id' => 'Veuillez entrer un numéro de téléphone valide (10 chiffres).'],
            ]);
        }

        return $this->handlePhoneLogin($loginId);
    }

    // ─── Connexion admin/modo par email + mot de passe ───────────────────────
    private function handleAdminLogin(string $email)
    {
        $password = $this->request->getPost('password');

        $rules = [
            'login_id' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', [
                'title'  => 'Connexion opérateur',
                'mode'   => 'admin',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $user = $this->userModel->authenticate($email, $password);

        if (! $user) {
            return view('auth/login', [
                'title'  => 'Connexion opérateur',
                'mode'   => 'admin',
                'errors' => ['login_id' => 'Email ou mot de passe incorrect.'],
            ]);
        }

        $this->startSession($user);

        if (in_array('admin.panel', $user['permissions'], true)) {
            return redirect()->to('/admin/dashboard');
        }

        return redirect()->to('/user/dashboard');
    }

    // ─── Connexion client par numéro de téléphone ────────────────────────────
    private function handlePhoneLogin(string $phone)
    {
        $prefix = substr($phone, 0, 3);
        $db     = \Config\Database::connect();

        // Vérifier que le préfixe est opéré
        $validPrefix = $db->table('operator_prefixes')->where('prefix', $prefix)->get()->getRow();

        if (! $validPrefix) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'mode'   => 'phone',
                'errors' => ['login_id' => 'Ce préfixe n\'est pas pris en charge (033, 034, 037, 038...).'],
            ]);
        }

        // Chercher ou créer l'utilisateur
        $user = $this->userModel->where('phone', $phone)->first();

        if (! $user) {
            $defaultType = $this->userTypeModel->findBySlug('user');

            $userId = $this->userModel->insert([
                'username'  => 'Client ' . $phone,
                'phone'     => $phone,
                'id_type'   => $defaultType['id'] ?? null,
                'is_active' => 1,
            ]);

            // Créer le solde initial (0 Ar)
            $db->table('user_balances')->insert([
                'id_user'    => $userId,
                'balance'    => 0,
                'currency'   => 'Ar',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $user = $this->userModel->find($userId);
        }

        // Enrichir avec type + permissions pour la session
        $userType = $this->userTypeModel->find($user['id_type']);
        $perms    = $db->table('user_type_permissions')
            ->select('permissions.slug')
            ->join('permissions', 'permissions.id = user_type_permissions.id_permission')
            ->where('id_type', $user['id_type'])
            ->get()->getResultArray();

        $user['type_slug']   = $userType['slug'] ?? '';
        $user['type_name']   = $userType['name'] ?? '';
        $user['permissions'] = array_column($perms, 'slug');

        $this->startSession($user);
        return redirect()->to('/user/dashboard');
    }

    // ─── Initialiser la session ──────────────────────────────────────────────
    private function startSession(array $user): void
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
            'email'            => 'required|valid_email',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password_confirm' => ['matches' => 'Les mots de passe ne correspondent pas.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return view('auth/register', [
                'title'  => 'Créer un compte',
                'errors' => $this->validator->getErrors(),
            ]);
        }

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
