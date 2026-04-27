<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Tenant login</h2>
        </div>
    </div>

    <form action="<?= esc(tenant_path('login')) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Email" required>
        </div>

        <div>
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn-primary">Log In</button>
    </form>

    <p class="auth-switch"><a class="link-inline" href="">Forgot your password? </a></p>
    <p class="auth-switch">Need an account? <a class="link-inline" href="<?= esc(tenant_path('register')) ?>">Create Account</a>.</p>
<?= $this->endSection() ?>
