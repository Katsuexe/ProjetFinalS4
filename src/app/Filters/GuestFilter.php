<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
class GuestFilter implements FilterInterface {
    public function before(RequestInterface $request, $arguments = null) {
        if (session()->get('isLoggedIn')) {
            $perms = session()->get('permissions') ?? [];
            return redirect()->to(in_array('admin.panel', $perms, true) ? '/admin/dashboard' : '/user/dashboard');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
