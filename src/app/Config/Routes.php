<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Accueil → redirige selon rôle ou vers login
$routes->get('/', 'Home::index');

// ── Auth (filtre guest: redirige si déjà connecté) ─────────────
$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('login',    'Auth::login');
    $routes->post('login',   'Auth::loginProcess');
    $routes->get('auth/check-phone', 'Auth::checkPhone');

    // Inscription désactivée : route retirée pour empêcher l'accès public
    // $routes->get('register', 'Auth::register');
    // $routes->post('register','Auth::registerProcess');
});

$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);

// ── Admin (filtre admin: connecté + permission admin.panel) ─────
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/',                        'Admin\Dashboard::index');
    $routes->get('dashboard',                'Admin\Dashboard::index');
    $routes->get('users',                    'Admin\Dashboard::users');
    // PEDAGOGIE : 'users/create' est déclaré AVANT 'users/(:num)' pour la
    // lisibilité (ordre logique liste → création → détail), mais l'ordre
    // n'a ici aucun effet sur le routage : (:num) ne matche que des
    // chiffres, il ne peut donc jamais intercepter le mot 'create'.
    // Autorisation fine faite dans le contrôleur (has_any_permission),
    // pas ici, car la création est ouverte à 2 permissions différentes
    // (users.manage OU users.create — voir Admin\Dashboard::createUser()).
    $routes->get('users/create',             'Admin\Dashboard::createUser');
    $routes->post('users', 'Admin\Dashboard::storeUser');
    $routes->post('users/check-unique', 'Admin\Dashboard::checkUnique');
    $routes->get('users/(:num)',             'Admin\Dashboard::userDetail/$1');
    $routes->get('users/(:num)/edit',        'Admin\Dashboard::editUser/$1',   ['filter' => 'permission:users.manage']);
    $routes->post('users/(:num)',            'Admin\Dashboard::updateUser/$1', ['filter' => 'permission:users.manage']);
    $routes->get('users/(:num)/toggle',      'Admin\Dashboard::toggleUser/$1');
    $routes->get('users/(:num)/delete',      'Admin\Dashboard::deleteUser/$1');

    // Profil admin
    $routes->get('profile',                  'Admin\Dashboard::profile');
    $routes->post('profile',                 'Admin\Dashboard::updateProfile');

    // ── Types & Rôles ─────────────────────────────────────────────
    $routes->get('types',                 'Admin\Types::index',  ['filter' => 'permission:types.manage']);
    $routes->get('types/create',          'Admin\Types::create', ['filter' => 'permission:types.manage']);
    $routes->post('types',                'Admin\Types::store',  ['filter' => 'permission:types.manage']);
    $routes->get('types/(:num)/edit',     'Admin\Types::edit/$1',   ['filter' => 'permission:types.manage']);
    $routes->post('types/(:num)',         'Admin\Types::update/$1', ['filter' => 'permission:types.manage']);
    $routes->get('types/(:num)/delete',   'Admin\Types::delete/$1', ['filter' => 'permission:types.manage']);

    // ── Import / Export (démo CSV + Excel + PDF) ────
    $routes->get('import',                'Admin\ImportController::index');
    $routes->post('import',               'Admin\ImportController::import');
    $routes->get('import/template',       'Admin\ImportController::downloadTemplate');
    $routes->get('import/export-excel',   'Admin\ImportController::exportUsersExcel');
    $routes->post('import/import-excel',  'Admin\ImportController::importExcel');
    $routes->get('import/export-pdf',     'Admin\ImportController::exportReportPdf');

    // Opérateurs externes (ajout manuel pour ne pas oublier, on supposait que c'était via resource mais on l'a fait manuellement)
    $routes->get('external-operators', 'Admin\ExternalOperatorController::index');
    $routes->post('external-operators', 'Admin\ExternalOperatorController::create');
    $routes->get('external-operators/(:num)/edit', 'Admin\ExternalOperatorController::edit/$1');
    $routes->post('external-operators/(:num)', 'Admin\ExternalOperatorController::update/$1');
    $routes->get('external-operators/(:num)/delete', 'Admin\ExternalOperatorController::delete/$1');

    // ── Settings : préfixes opérateur + barèmes ─────────────────
    $routes->get('settings/prefixes',              'Admin\Settings::prefixes',     ['filter' => 'permission:types.manage']);
    $routes->post('settings/prefixes',             'Admin\Settings::storePrefix',  ['filter' => 'permission:types.manage']);
    $routes->get('settings/prefixes/(:num)/delete','Admin\Settings::deletePrefix/$1', ['filter' => 'permission:types.manage']);
    $routes->get('settings/fees',                  'Admin\Settings::fees',         ['filter' => 'permission:types.manage']);
    $routes->post('settings/fees',                 'Admin\Settings::storeFee',     ['filter' => 'permission:types.manage']);
    $routes->post('settings/fees/import-csv',      'Admin\Settings::importFeesCsv',['filter' => 'permission:types.manage']);
    $routes->get('settings/fees/export-pdf',       'Admin\Settings::exportFeesPdf',['filter' => 'permission:types.manage']);
    $routes->get('settings/fees/(:num)/delete',    'Admin\Settings::deleteFee/$1', ['filter' => 'permission:types.manage']);
});

// ── User (filtre auth: doit être connecté) ──────────────────────
$routes->group('user', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',         'User\Dashboard::index');
    $routes->get('dashboard', 'User\Dashboard::index');
    $routes->get('profile',   'User\Dashboard::profile');
    $routes->post('profile',  'User\Dashboard::updateProfile');
    $routes->get('password',  'User\Dashboard::password');
    $routes->post('password', 'User\Dashboard::updatePassword');

    $routes->get('wallet', 'User\Wallet::index', ['filter' => 'permission:wallet.view']);
    
    // Opérations Mobile Money
    $routes->get('operations/deposit',  'User\Operations::deposit');
    $routes->post('operations/deposit', 'User\Operations::deposit');
    $routes->get('operations/withdraw', 'User\Operations::withdraw');
    $routes->post('operations/withdraw','User\Operations::withdraw');
    $routes->get('operations/transfer', 'User\Operations::transfer');
    $routes->post('operations/transfer','User\Operations::transfer');
    $routes->get('operations/history',  'User\Operations::history');
});
