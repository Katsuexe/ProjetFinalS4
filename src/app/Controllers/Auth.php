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
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $user = $this->userModel->authenticate(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        if (! $user) {
            return view('auth/login', [
                'title'  => 'Connexion',
                'errors' => ['email' => 'Email ou mot de passe incorrect.'],
            ]);
        }

        session()->set([
            'isLoggedIn'     => true,
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'email'          => $user['email'],
            'id_type'        => $user['id_type'],
            'type_slug'      => $user['type_slug'],
            'user_type_name' => $user['type_name'],
            'permissions'    => $user['permissions'],
        ]);

        if (in_array('admin.panel', $user['permissions'], true)) {
            return redirect()->to('/admin/dashboard');
        }

        return redirect()->to('/user/dashboard');
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
