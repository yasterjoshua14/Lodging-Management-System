<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>My Account</h2>
            <p>Update the personal details linked to your tenant profile and tenant portal login.</p>
        </div>
    </div>

    <form action="/myAccount" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <section class="detail-grid">
            <div class="detail-item">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= esc(old('full_name', $tenant['full_name'])) ?>" required>
            </div>

            <div class="detail-item">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= esc(old('email', $tenant['email'] ?: auth_user()['email'])) ?>" required>
            </div>

            <div class="detail-item">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?= esc(old('phone', $tenant['phone'])) ?>" required>
            </div>

            <div class="detail-item">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= esc(old('address', $tenant['address'] ?? '')) ?>" placeholder="Street, city, state/province, postal code">
            </div>

            <div class="detail-item">
                <label for="id_type">ID Type</label>
                <input type="text" id="id_type" name="id_type" value="<?= esc(old('id_type', $tenant['id_type'] ?? '')) ?>" placeholder="Passport, National ID, Driver's License">
                <p>(Optional) government or travel document category.</p>
            </div>

            <div class="detail-item">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?= esc(old('id_number', $tenant['id_number'] ?? '')) ?>" placeholder="Enter your reference number">
                <p>(Optional) ID reference number stored with your profile.</p>
            </div>

            <div class="detail-item">
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= esc(old('emergency_contact_name', $tenant['emergency_contact_name'] ?? '')) ?>">
                <p>(Optional) Person to contact if the lodging team cannot reach you.</p>
            </div>

            <div class="detail-item">
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= esc(old('emergency_contact_phone', $tenant['emergency_contact_phone'] ?? '')) ?>">
                <p>(Optional) Phone number for your emergency contact.</p>
            </div>

            <div class="detail-item full-span">
                <label for="id_document">ID Upload</label>
                <input type="file" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png">
                <p>(Optional) Upload a PDF, JPG, or PNG copy of your government or travel ID.</p>
                <?php if (! empty($tenant['id_document_path'])): ?>
                    <p>Current file on record: <?= esc(basename((string) $tenant['id_document_path'])) ?></p>
                <?php endif; ?>
            </div>

            <article class="detail-item">
                <h3>Tenant ID</h3>
                <div class="detail-value">#<?= esc((string) $tenant['id']) ?></div>
                <p>Your tenant ID cannot be change.</p>
            </article>
        </section>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
<?= $this->endSection() ?>
