<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        return is_authenticated()
            ? redirect()->to(auth_redirect_path())
            : redirect()->to(tenant_path('login'));
    }

    public function admin(): RedirectResponse
    {
        if (! is_authenticated()) {
            return redirect()->to(admin_path('login'));
        }

        return redirect()->to(auth_redirect_path());
    }
}
