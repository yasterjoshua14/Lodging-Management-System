<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class TenantAccountController extends BaseController
{
    public function index(): string
    {
        return view('tenant/myAccount/index', [
            'title'  => 'My Account',
            'tenant' => $this->findTenantOrFail(),
        ]);
    }

    public function update(): RedirectResponse
    {
        $tenant = $this->findTenantOrFail();
        $user   = $this->findLinkedUserOrFail();
        $data   = $this->getValidatedData($user);

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        $uploadedDocument = $this->storeIdDocument($tenant);
        if ($uploadedDocument instanceof RedirectResponse) {
            return $uploadedDocument;
        }

        $previousDocumentPath = $tenant['id_document_path'] ?? null;
        $nextDocumentPath     = $uploadedDocument['path'] ?? $previousDocumentPath;

        $tenantData = $data + [
            'id_document_path' => $nextDocumentPath,
        ];

        $database = \Config\Database::connect();
        $database->transStart();

        (new TenantModel())->update($tenant['id'], $tenantData);
        (new UserModel())->update($user['id'], [
            'full_name' => $tenantData['full_name'],
            'email'     => $tenantData['email'],
        ]);

        $database->transComplete();

        if (! $database->transStatus()) {
            if ($uploadedDocument !== null) {
                $this->deleteStoredIdDocument($uploadedDocument['path']);
            }

            return redirect()->back()->withInput()->with('error', 'Unable to save your account changes right now.');
        }

        if ($uploadedDocument !== null && $previousDocumentPath !== null && $previousDocumentPath !== $uploadedDocument['path']) {
            $this->deleteStoredIdDocument($previousDocumentPath);
        }

        $this->session->set([
            'user_name'  => $tenantData['full_name'],
            'user_email' => $tenantData['email'],
        ]);

        return redirect()->to('/myAccount')->with('success', 'Your account details have been updated.');
    }

    private function findTenantOrFail(): array
    {
        $tenantId = auth_tenant_id();
        if ($tenantId === null) {
            throw PageNotFoundException::forPageNotFound('Tenant account not found.');
        }

        $tenant = (new TenantModel())->find($tenantId);

        if ($tenant === null) {
            throw PageNotFoundException::forPageNotFound('Tenant account not found.');
        }

        return $tenant;
    }

    private function findLinkedUserOrFail(): array
    {
        $userId = auth_user()['id'] ?? null;
        if ($userId === null) {
            throw PageNotFoundException::forPageNotFound('Linked portal user not found.');
        }

        $user = (new UserModel())->find($userId);

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('Linked portal user not found.');
        }

        return $user;
    }

    private function getValidatedData(array $user)
    {
        $rules = [
            'full_name'               => 'required|min_length[3]|max_length[120]',
            'email'                   => 'required|valid_email|max_length[120]',
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

        $email = strtolower(trim((string) $this->request->getPost('email')));

        $existingUser = (new UserModel())
            ->where('email', $email)
            ->where('id !=', $user['id'])
            ->first();

        if ($existingUser !== null) {
            return redirect()->back()->withInput()->with('error', 'That email address is already being used by another account.');
        }

        return [
            'full_name'               => trim((string) $this->request->getPost('full_name')),
            'email'                   => $email,
            'phone'                   => trim((string) $this->request->getPost('phone')),
            'id_type'                 => trim((string) $this->request->getPost('id_type')),
            'id_number'               => trim((string) $this->request->getPost('id_number')),
            'address'                 => trim((string) $this->request->getPost('address')),
            'emergency_contact_name'  => trim((string) $this->request->getPost('emergency_contact_name')),
            'emergency_contact_phone' => trim((string) $this->request->getPost('emergency_contact_phone')),
        ];
    }

    private function storeIdDocument(array $tenant)
    {
        $file = $this->request->getFile('id_document');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (! $file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'The uploaded ID document could not be processed.');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension         = strtolower($file->getExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            return redirect()->back()->withInput()->with('error', 'Upload a PDF, JPG, or PNG file for your ID document.');
        }

        if ($file->getSizeByUnit('kb') > 5120) {
            return redirect()->back()->withInput()->with('error', 'The ID document must be 5 MB or smaller.');
        }

        $uploadDirectory = WRITEPATH . 'uploads/id-documents';
        if (! is_dir($uploadDirectory) && ! mkdir($uploadDirectory, 0777, true) && ! is_dir($uploadDirectory)) {
            return redirect()->back()->withInput()->with('error', 'Unable to prepare secure storage for the uploaded ID document.');
        }

        $storedName = 'tenant-' . $tenant['id'] . '-id-document.' . $extension;

        $file->move($uploadDirectory, $storedName, true);

        foreach ($allowedExtensions as $allowedExtension) {
            if ($allowedExtension === $extension) {
                continue;
            }

            $existingVariant = $uploadDirectory . DIRECTORY_SEPARATOR . 'tenant-' . $tenant['id'] . '-id-document.' . $allowedExtension;

            if (is_file($existingVariant)) {
                unlink($existingVariant);
            }
        }

        return [
            'path' => 'uploads/id-documents/' . $storedName,
        ];
    }

    private function deleteStoredIdDocument(string $relativePath): void
    {
        $fullPath = WRITEPATH . ltrim($relativePath, '\\/');

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
