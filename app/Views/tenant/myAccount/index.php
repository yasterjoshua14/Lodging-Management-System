<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, mixed> $tenant
 */

$tenant            ??= [];
$currentTenantUser = auth_user() ?? [];
$tenantFirstName   = (string) ($tenant['first_name'] ?? '');
$tenantLastName    = (string) ($tenant['last_name'] ?? '');
$tenantEmail       = (string) (($tenant['email'] ?? '') ?: ($currentTenantUser['email'] ?? ''));
$tenantPhone       = (string) ($tenant['phone'] ?? '');
$tenantId          = (string) ($tenant['id'] ?? '');
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>My Account</h2>
        </div>
    </div>

    <form action="<?= view_esc(tenant_path('account')) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <section class="detail-grid">
            <div class="detail-item full-span">
                <label>Name</label>
                <div class="name-fields">
                    <input type="text" id="first_name" name="first_name" value="<?= view_esc(old('first_name', $tenantFirstName)) ?>" placeholder="First name" aria-label="First name" required>
                    <input type="text" id="last_name" name="last_name" value="<?= view_esc(old('last_name', $tenantLastName)) ?>" placeholder="Last name" aria-label="Last name" required>
                </div>
            </div>

            <div class="detail-item">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= view_esc(old('email', $tenantEmail)) ?>" required>
            </div>

            <div class="detail-item">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?= view_esc(old('phone', $tenantPhone)) ?>" required>
            </div>

            <div class="detail-item">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= view_esc(old('address', $tenant['address'] ?? '')) ?>" placeholder="Street, city, state/province, postal code">
                <p>(Optional) Your current address for communication purposes.</p>
            </div>

            <div class="detail-item">
                <label for="id_type">ID Type</label>
                <input type="text" id="id_type" name="id_type" value="<?= view_esc(old('id_type', $tenant['id_type'] ?? '')) ?>" placeholder="Passport, National ID, Driver's License">
                <p>(Optional) government or travel document category.</p>
            </div>

            <div class="detail-item">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?= view_esc(old('id_number', $tenant['id_number'] ?? '')) ?>" placeholder="Enter your reference number">
                <p>(Optional) ID reference number stored with your profile.</p>
            </div>

            <div class="detail-item">
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= view_esc(old('emergency_contact_name', $tenant['emergency_contact_name'] ?? '')) ?>">
                <p>(Optional) Person to contact if the lodging team cannot reach you.</p>
            </div>

            <div class="detail-item">
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= view_esc(old('emergency_contact_phone', $tenant['emergency_contact_phone'] ?? '')) ?>">
                <p>(Optional) Phone number for your emergency contact.</p>
            </div>

            <div class="detail-item full-span">
                <label for="id_document">ID Upload</label>
                <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png">
                <p>(Optional) Upload a PDF, JPG, or PNG copy of your government or travel ID.</p>
                <?php if (! empty($tenant['id_document_path'])): ?>
                    <p>Current file on record: <?= view_esc(basename((string) $tenant['id_document_path'])) ?></p>
                <?php endif; ?>
            </div>

            <article class="detail-item">
                <h3>Tenant ID</h3>
                <div class="detail-value">#<?= view_esc($tenantId) ?></div>
            </article>
        </section>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
<?php $this->endSection(); ?>
