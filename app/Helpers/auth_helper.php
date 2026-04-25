<?php

if (! function_exists('auth_user')) {
    function auth_user(): ?array
    {
        $session = session();

        if (! $session->get('is_logged_in')) {
            return null;
        }

        $storedRole = $session->get('user_role');
        $role       = $storedRole === 'admin' ? 'admin' : ($storedRole === null ? null : 'tenant');

        if ($storedRole !== $role && $role !== null) {
            $session->set('user_role', $role);
        }

        return [
            'id'        => $session->get('user_id'),
            'name'      => $session->get('user_name'),
            'email'     => $session->get('user_email'),
            'role'      => $role,
            'tenant_id' => $session->get('tenant_id'),
        ];
    }
}

if (! function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return auth_user() !== null;
    }
}

if (! function_exists('auth_role')) {
    function auth_role(): ?string
    {
        return auth_user()['role'] ?? null;
    }
}

if (! function_exists('auth_tenant_id')) {
    function auth_tenant_id(): ?int
    {
        $tenantId = auth_user()['tenant_id'] ?? null;

        return $tenantId === null ? null : (int) $tenantId;
    }
}

if (! function_exists('has_role')) {
    function has_role(string ...$roles): bool
    {
        $role = auth_role();

        return $role !== null && in_array($role, $roles, true);
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return has_role('admin');
    }
}

if (! function_exists('is_tenant')) {
    function is_tenant(): bool
    {
        return has_role('tenant');
    }
}

if (! function_exists('dashboard_path_for_role')) {
    function dashboard_path_for_role(?string $role): string
    {
        return $role === 'admin' ? '/admin/dashboard' : '/dashboard';
    }
}

if (! function_exists('login_path_for_role')) {
    function login_path_for_role(?string $role): string
    {
        return $role === 'admin' ? '/admin/login' : '/login';
    }
}

if (! function_exists('auth_redirect_path')) {
    function auth_redirect_path(): string
    {
        return dashboard_path_for_role(auth_role());
    }
}
