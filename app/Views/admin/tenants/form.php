<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2><?= esc($heading) ?></h2>
            <p>Record the main tenant details required for lodging operations.</p>
        </div>

        <a href="<?= esc(admin_path('tenants')) ?>" class="btn btn-ghost">Back to Tenants</a>
    </div>

    <form action="<?= esc($action) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div>
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= esc(old('full_name', $tenant['full_name'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?= esc(old('phone', $tenant['phone'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= esc(old('email', $tenant['email'] ?? '')) ?>">
            </div>

            <div>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= esc(old('address', $tenant['address'] ?? '')) ?>">
            </div>

            <div>
                <label for="id_type">ID Type</label>
                <input type="text" id="id_type" name="id_type" value="<?= esc(old('id_type', $tenant['id_type'] ?? '')) ?>" placeholder="Passport, National ID, Driver's License">
            </div>

            <div>
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?= esc(old('id_number', $tenant['id_number'] ?? '')) ?>">
            </div>

            <div>
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= esc(old('emergency_contact_name', $tenant['emergency_contact_name'] ?? '')) ?>">
            </div>

            <div>
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= esc(old('emergency_contact_phone', $tenant['emergency_contact_phone'] ?? '')) ?>">
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Tenant</button>
            <a href="<?= esc(admin_path('tenants')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
<?= $this->endSection() ?>
