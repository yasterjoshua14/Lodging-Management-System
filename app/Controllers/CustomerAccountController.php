<?php

namespace App\Controllers;

use App\Models\TenantModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class CustomerAccountController extends BaseController
{
    public function index(): string
    {
        $tenant = (new TenantModel())->find(auth_tenant_id());

        if ($tenant === null) {
            throw PageNotFoundException::forPageNotFound('Customer account not found.');
        }

        return view('customer/account', [
            'title'  => 'My Account',
            'tenant' => $tenant,
        ]);
    }
}
