<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
class AdminFilter implements FilterInterface {
    public function before(RequestInterface $request, $arguments = null) {
        if (! session()->get('isLoggedIn'))
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        $perms = session()->get('permissions') ?? [];
        if (! in_array('admin.panel', $perms, true))
            return redirect()->to('/user/dashboard')->with('error', 'Accès réservé aux administrateurs.');
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
