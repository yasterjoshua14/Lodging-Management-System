<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        if (! session()->get('is_logged_in')) {
            $expectedRole = $arguments[0] ?? null;

            return redirect()
                ->to(login_path_for_role($expectedRole))
                ->with('warning', 'Please sign in to continue.');
        }

        $allowedRoles = is_array($arguments) ? array_values(array_filter($arguments, 'is_string')) : [];
        $currentRole  = auth_role();

        if ($allowedRoles !== [] && ! in_array($currentRole, $allowedRoles, true)) {
            return redirect()
                ->to(auth_redirect_path())
                ->with('error', 'You do not have permission to access that area.');
        }

        if ($currentRole === 'customer' && auth_tenant_id() === null) {
            session()->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Your customer account is missing a tenant profile link. Please contact support.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
