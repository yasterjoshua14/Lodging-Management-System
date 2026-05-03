<?php
/**
 * @var \CodeIgniter\View\View $this
 */
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
            <h2>Admin login</h2>
    </div>

    <form action="<?= view_esc(admin_path('login')) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <input type="email" id="email" name="email" value="<?= view_esc(old('email')) ?>" placeholder="Email" required>
        </div>

        <div>
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>

    <button type="submit" class="btn btn-primary">Enter Admin Console</button>
    </form>

    <p class="auth-switch"><a class="link-inline" href="<?= view_esc(admin_path('forgot-password')) ?>">Forgot your password?</a></p>
    <p class="auth-switch"><a class="link-inline" href="<?= view_esc(tenant_path('login')) ?>">Use the tenant login</a>.</p>
<?php $this->endSection(); ?>
