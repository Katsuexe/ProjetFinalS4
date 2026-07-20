<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
class AuthFilter implements FilterInterface {
    public function before(RequestInterface $request, $arguments = null) {
        if (! session()->get('isLoggedIn'))
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
