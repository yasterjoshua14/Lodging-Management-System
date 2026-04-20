<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>My Account</h2>
            <p>Your customer login is linked to this tenant record on the backend.</p>
        </div>

    </div>

    <section class="detail-grid">
        <article class="detail-item">
            <h3>Full Name</h3>
            <div class="detail-value"><?= esc($tenant['full_name']) ?></div>
            <p>Name stored in the tenant profile</p>
        </article>

        <article class="detail-item">
            <h3>Email Address</h3>
            <div class="detail-value"><?= esc($tenant['email'] ?: auth_user()['email']) ?></div>
            <p>Used for customer portal access</p>
        </article>

        <article class="detail-item">
            <h3>Phone Number</h3>
            <div class="detail-value"><?= esc($tenant['phone']) ?></div>
            <p>Main contact number on file</p>
        </article>

        <article class="detail-item">
            <h3>Address</h3>
            <div class="detail-value"><?= esc($tenant['address'] ?: 'No address on file') ?></div>
            <p>Saved tenant mailing address</p>
        </article>

        <article class="detail-item">
            <h3>ID Type</h3>
            <div class="detail-value"><?= esc($tenant['id_type'] ?: 'Not provided') ?></div>
            <p>Government or travel document category</p>
        </article>

        <article class="detail-item">
            <h3>ID Number</h3>
            <div class="detail-value"><?= esc($tenant['id_number'] ?: 'Not provided') ?></div>
            <p>Reference number stored by the lodging team</p>
        </article>

        <article class="detail-item">
            <h3>Emergency Contact</h3>
            <div class="detail-value"><?= esc($tenant['emergency_contact_name'] ?: 'Not provided') ?></div>
            <p><?= esc($tenant['emergency_contact_phone'] ?: 'No emergency phone on file') ?></p>
        </article>

        <article class="detail-item">
            <h3>Tenant ID</h3>
            <div class="detail-value">#<?= esc((string) $tenant['id']) ?></div>
            <p>Internal profile link used for customer data isolation</p>
        </article>
    </section>
<?= $this->endSection() ?>
