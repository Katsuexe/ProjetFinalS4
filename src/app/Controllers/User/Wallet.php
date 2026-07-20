<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserBalanceModel;

/**
 * ============================================================================
 *  User\Wallet — page "Mon solde" (démonstration de permission granulaire)
 * ============================================================================
 *
 * PEDAGOGIE : ce contrôleur n'existait pas alors que la route et le lien de
 * la sidebar (app/Views/layouts/user.php) le référençaient déjà -> erreur
 * "Controller or its method is not found". Toujours créer le CONTRÔLEUR en
 * même temps que sa ROUTE, sinon le lien casse dès qu'on clique dessus.
 *
 * La route associée (voir app/Config/Routes.php) est protégée par :
 *   ['filter' => 'permission:wallet.view']
 * Seuls les types d'utilisateurs ayant la permission 'wallet.view' dans
 * user_type_permissions (voir MainSeeder : le type "user") peuvent y accéder.
 * Un compte "admin" ou "moderator" qui n'a pas cette permission recevrait un
 * message d'erreur et serait redirigé (voir PermissionFilter::before()).
 * ============================================================================
 */
class Wallet extends BaseController
{
    protected UserBalanceModel $balanceModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->balanceModel = new UserBalanceModel();
        helper(['auth', 'url']);
    }

    // ─── GET /user/wallet ────────────────────────────────────────────────────
    public function index(): string
    {
        $row = $this->balanceModel->findByUser((int) session('user_id'));

        return view('user/wallet', [
            'title'       => 'Mon solde',
            'pageTitle'   => 'Mon solde',
            'balance'     => $row['balance'] ?? 0,
            'currency'    => $row['currency'] ?? 'EUR',
            'updated_at'  => $row ? date('d/m/Y H:i', strtotime($row['updated_at'])) : null,
        ]);
    }
}
