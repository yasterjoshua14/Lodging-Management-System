<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Admin login</h2>
        </div>
    </div>

    <form action="<?= esc(admin_path('login')) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Email" required>
        </div>

        <div>
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn-primary">Enter Admin Console</button>
    </form>

    <p class="auth-switch"><a class="link-inline" href="<?= esc(tenant_path('login')) ?>">Use the tenant login</a>.</p>
<?= $this->endSection() ?>
