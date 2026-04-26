<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TenantModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class AdminTenantsController extends BaseController
{
    public function index(): string
    {
        $tenantModel = new TenantModel();

        return view('admin/tenants/index', [
            'title'   => 'Tenants',
            'tenants' => $tenantModel->orderBy('full_name', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/tenants/form', [
            'title'   => 'Add Tenant',
            'tenant'  => null,
            'action'  => '/admin/tenants',
            'heading' => 'Add Tenant',
        ]);
    }

    public function store(): RedirectResponse
    {
        $data = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        (new TenantModel())->insert($data);

        return redirect()->to('/admin/tenants')->with('success', 'Tenant created successfully.');
    }

    public function edit(int $id): string
    {
        $tenant = $this->findTenantOrFail($id);

        return view('admin/tenants/form', [
            'title'   => 'Edit Tenant',
            'tenant'  => $tenant,
            'action'  => '/admin/tenants/' . $id,
            'heading' => 'Edit Tenant',
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findTenantOrFail($id);
        $data = $this->getValidatedData();

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        (new TenantModel())->update($id, $data);

        return redirect()->to('/admin/tenants')->with('success', 'Tenant updated successfully.');
    }

    public function delete(int $id): RedirectResponse
    {
        $tenant = $this->findTenantOrFail($id);
        $database = \Config\Database::connect();
        $database->transStart();

        (new BookingModel())->where('tenant_id', $tenant['id'])->delete();
        (new UserModel())->where('tenant_id', $tenant['id'])->delete();
        (new TenantModel())->delete($id);

        $database->transComplete();

        if (! $database->transStatus()) {
            return redirect()->to('/admin/tenants')->with('error', 'Unable to delete the tenant record right now.');
        }

        $idDocumentPath = trim((string) ($tenant['id_document_path'] ?? ''));
        if ($idDocumentPath !== '') {
            $this->deleteStoredIdDocument($idDocumentPath);
        }

        return redirect()->to('/admin/tenants')->with('success', 'Tenant and related records deleted successfully.');
    }

    private function getValidatedData()
    {
        $rules = [
            'full_name'               => 'required|min_length[3]|max_length[120]',
            'email'                   => 'permit_empty|valid_email|max_length[120]',
            'phone'                   => 'required|max_length[30]',
            'id_type'                 => 'permit_empty|max_length[50]',
            'id_number'               => 'permit_empty|max_length[50]',
            'address'                 => 'permit_empty|max_length[255]',
            'emergency_contact_name'  => 'permit_empty|max_length[120]',
            'emergency_contact_phone' => 'permit_empty|max_length[30]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return [
            'full_name'               => trim((string) $this->request->getPost('full_name')),
            'email'                   => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'                   => trim((string) $this->request->getPost('phone')),
            'id_type'                 => trim((string) $this->request->getPost('id_type')),
            'id_number'               => trim((string) $this->request->getPost('id_number')),
            'address'                 => trim((string) $this->request->getPost('address')),
            'emergency_contact_name'  => trim((string) $this->request->getPost('emergency_contact_name')),
            'emergency_contact_phone' => trim((string) $this->request->getPost('emergency_contact_phone')),
        ];
    }

    private function findTenantOrFail(int $id): array
    {
        $tenant = (new TenantModel())->find($id);

        if ($tenant === null) {
            throw PageNotFoundException::forPageNotFound('Tenant not found.');
        }

        return $tenant;
    }

    private function deleteStoredIdDocument(string $relativePath): void
    {
        $fullPath = WRITEPATH . ltrim($relativePath, '\\/');

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
