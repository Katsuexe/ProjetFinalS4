<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PermissionFilter
 *
 * Usage dans Routes.php :
 *   ->filter('permission:wallet.view')
 *   ->filter('permission:admin.panel,users.manage')  // plusieurs slugs (ET)
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        if (empty($arguments)) {
            return; // Pas de permission requise, on laisse passer
        }

        $userPerms = session()->get('permissions') ?? [];

        foreach ($arguments as $required) {
            if (! in_array($required, $userPerms, true)) {
                return redirect()->back()->with('error', "Vous n'avez pas la permission : {$required}");
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
