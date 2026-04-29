<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, mixed>|null $tenant
 * @var string $action
 * @var string $heading
 */

$tenant  ??= null;
$action  ??= admin_path('tenants');
$heading ??= 'Tenant';
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <?php
    $idDocumentPath      = trim((string) ($tenant['id_document_path'] ?? ''));
    $idDocumentUrl       = $idDocumentPath !== '' && isset($tenant['id']) ? admin_path('tenants/' . $tenant['id'] . '/id-document') : null;
    $deleteDocumentUrl   = $idDocumentPath !== '' && isset($tenant['id']) ? admin_path('tenants/' . $tenant['id'] . '/id-document/delete') : null;
    $idDocumentExtension = strtolower(pathinfo($idDocumentPath, PATHINFO_EXTENSION));
    $hasImagePreview     = $idDocumentUrl !== null && in_array($idDocumentExtension, ['jpg', 'jpeg', 'png'], true);
    ?>

    <div class="section-head">
        <div>
            <h2><?= view_esc($heading) ?></h2>
            <p>Record the main tenant details required for lodging operations.</p>
        </div>

        <a href="<?= view_esc(admin_path('tenants')) ?>" class="btn btn-ghost">Back to Tenants</a>
    </div>

    <?php if ($deleteDocumentUrl !== null): ?>
        <form id="delete-id-document-form" action="<?= view_esc($deleteDocumentUrl) ?>" method="post" class="inline-form">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>

    <form action="<?= view_esc($action) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div>
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= view_esc(old('full_name', $tenant['full_name'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?= view_esc(old('phone', $tenant['phone'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= view_esc(old('email', $tenant['email'] ?? '')) ?>">
            </div>

            <div>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= view_esc(old('address', $tenant['address'] ?? '')) ?>">
            </div>

            <div>
                <label for="id_type">ID Type</label>
                <input type="text" id="id_type" name="id_type" value="<?= view_esc(old('id_type', $tenant['id_type'] ?? '')) ?>" placeholder="Passport, National ID, Driver's License">
            </div>

            <div>
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?= view_esc(old('id_number', $tenant['id_number'] ?? '')) ?>">
            </div>

            <div>
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= view_esc(old('emergency_contact_name', $tenant['emergency_contact_name'] ?? '')) ?>">
            </div>

            <div>
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= view_esc(old('emergency_contact_phone', $tenant['emergency_contact_phone'] ?? '')) ?>">
            </div>

            <?php if ($idDocumentUrl !== null): ?>
                <div class="document-preview full-span">
                    <div class="document-preview-head">
                        <div>
                            <label>Uploaded ID Document</label>
                        </div>

                        <div class="actions">
                            <a href="<?= view_esc($idDocumentUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary">Open Document</a>
                            <button
                                type="submit"
                                form="delete-id-document-form"
                                formnovalidate
                                class="btn btn-danger"
                                onclick="return confirm('Delete this uploaded ID document?');"
                            >
                                Delete Document
                            </button>
                        </div>
                    </div>

                    <?php if ($hasImagePreview): ?>
                        <a href="<?= view_esc($idDocumentUrl) ?>" target="_blank" rel="noopener" class="document-preview-link">
                            <img src="<?= view_esc($idDocumentUrl) ?>" alt="<?= view_esc('Uploaded ID for ' . ($tenant['full_name'] ?? 'tenant')) ?>" class="document-preview-image">
                        </a>
                    <?php else: ?>
                        <div class="document-file-chip">
                            <?= view_esc(strtoupper($idDocumentExtension ?: 'FILE')) ?> on record: <?= view_esc(basename($idDocumentPath)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Tenant</button>
            <a href="<?= view_esc(admin_path('tenants')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
<?php $this->endSection(); ?>
