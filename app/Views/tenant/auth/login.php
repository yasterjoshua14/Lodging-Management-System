<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Tenant login</h2>
            <p>Sign in to view your bookings, stay history, and tenant account details.</p>
        </div>
    </div>

    <form action="/login" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="Enter your email" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary">Log In</button>
    </form>

    <p class="auth-switch">Need an account? <a class="link-inline" href="/register">Create Account</a>.</p>
<?= $this->endSection() ?>
