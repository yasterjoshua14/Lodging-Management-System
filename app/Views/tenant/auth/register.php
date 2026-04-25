<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Create tenant account</h2>
            <p>Register a tenant portal account that links to your own tenant profile.</p>
        </div>
    </div>

    <form action="/register" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?= esc(old('full_name')) ?>" placeholder="John Doe" required>
        </div>

        <div>
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="guest@example.com" required>
        </div>

        <div>
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?= esc(old('phone')) ?>" placeholder="+63 917 123 4567" required>
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
        </div>

        <div>
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Tenant Account</button>
    </form>

    <p class="auth-switch">Already registered? <a class="link-inline" href="/login">Back to tenant login</a>.</p>
<?= $this->endSection() ?>
