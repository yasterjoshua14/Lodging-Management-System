<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Admin login</h2>
            <p>Sign in to the management console for rooms, tenants, and bookings.</p>
        </div>
    </div>

    <form action="/admin/login" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="email">Admin Email</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Enter your email" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary">Enter Admin Console</button>
    </form>

    <p class="auth-switch">Need the tenant portal instead? <a class="link-inline" href="/login">Use the tenant login</a>.</p>
<?= $this->endSection() ?>
