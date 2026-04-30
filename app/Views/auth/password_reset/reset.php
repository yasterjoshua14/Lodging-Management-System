<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var string $loginUrl
 * @var string $resetUrl
 */
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>Create new password</h2>
            <p>Use a password that is at least 8 characters long.</p>
        </div>
    </div>

    <form action="<?= view_esc($resetUrl) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" placeholder="Minimum 8 characters" autocomplete="new-password" required>
        </div>

        <div>
            <label for="password_confirm">Confirm New Password</label>
            <input type="password" id="password_confirm" name="password_confirm" placeholder="Repeat your new password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn-primary">Save New Password</button>
    </form>

    <p class="auth-switch"><a class="link-inline" href="<?= view_esc($loginUrl) ?>">Back to login</a>.</p>
<?php $this->endSection(); ?>
