<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    public function showTenantLogin(): string
    {
        return view('tenant/auth/login', [
            'title'       => 'Tenant Login',
            'authSurface' => 'tenant',
        ]);
    }

    public function showAdminLogin(): string
    {
        return view('admin/auth/login', [
            'title'       => 'Admin Login',
            'authSurface' => 'admin',
        ]);
    }

    public function loginTenant(): RedirectResponse
    {
        return $this->handleLogin('tenant');
    }

    public function loginAdmin(): RedirectResponse
    {
        return $this->handleLogin('admin');
    }

    public function showTenantRegister(): string
    {
        return view('tenant/auth/register', [
            'title'       => 'Create Tenant Account',
            'authSurface' => 'tenant',
        ]);
    }

    public function register(): RedirectResponse
    {
        $rules = [
            'full_name'        => 'required|min_length[3]|max_length[120]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'phone'            => 'required|max_length[30]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel   = new UserModel();
        $tenantModel = new TenantModel();
        $fullName    = trim((string) $this->request->getPost('full_name'));
        $email       = strtolower(trim((string) $this->request->getPost('email')));
        $phone       = trim((string) $this->request->getPost('phone'));
        $tenantId    = null;

        $tenant = $tenantModel->where('email', $email)->first();

        if ($tenant !== null) {
            if ($userModel->where('tenant_id', $tenant['id'])->first() !== null) {
                return redirect()->back()->withInput()->with('error', 'A portal account already exists for that tenant email.');
            }

            $tenantModel->update($tenant['id'], [
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone,
            ]);

            $tenantId = (int) $tenant['id'];
        } else {
            $tenantModel->insert([
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone,
            ]);

            $tenantId = (int) $tenantModel->getInsertID();
        }

        $userModel->insert([
            'full_name'     => $fullName,
            'email'         => $email,
            'role'          => 'tenant',
            'tenant_id'     => $tenantId,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $user = $userModel->find($userModel->getInsertID());

        return $this->loginUserAndRedirect($user, 'Your tenant account has been created successfully.');
    }

    public function logout(): RedirectResponse
    {
        helper('auth');

        $loginPath = login_path_for_role(auth_role());
        $this->session->destroy();

        return redirect()->to($loginPath)->with('success', 'You have been signed out.');
    }

    private function handleLogin(string $expectedRole): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $email     = strtolower(trim((string) $this->request->getPost('email')));
        $password  = (string) $this->request->getPost('password');
        $user      = $userModel->where('email', $email)->first();

        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }

        $userRole = $this->normalizeRole($user['role'] ?? null);

        if ($userRole !== $expectedRole) {
            return redirect()->back()->withInput()->with('error', $this->getPortalMismatchMessage($expectedRole));
        }

        if ($expectedRole === 'tenant' && empty($user['tenant_id'])) {
            return redirect()->back()->withInput()->with('error', 'Your tenant account is not linked to a tenant profile yet.');
        }

        if (($user['role'] ?? null) !== $userRole) {
            $userModel->update($user['id'], ['role' => $userRole]);
            $user['role'] = $userRole;
        }

        return $this->loginUserAndRedirect($user, 'Welcome back.');
    }

    private function loginUserAndRedirect(array $user, string $message): RedirectResponse
    {
        helper('auth');
        $normalizedRole = $this->normalizeRole($user['role'] ?? null);

        $this->session->regenerate(true);
        $this->session->set([
            'user_id'      => $user['id'],
            'user_name'    => $user['full_name'],
            'user_email'   => $user['email'],
            'user_role'    => $normalizedRole,
            'tenant_id'    => $user['tenant_id'] ?? null,
            'is_logged_in' => true,
        ]);

        return redirect()->to(dashboard_path_for_role($normalizedRole))->with('success', $message);
    }

    private function getPortalMismatchMessage(string $expectedRole): string
    {
        if ($expectedRole === 'admin') {
            return 'This account does not have admin access. Please use the tenant login page.';
        }

        return 'This account belongs to the admin app. Please use the admin login page.';
    }

    private function normalizeRole(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        return $role === 'admin' ? 'admin' : 'tenant';
    }
}
