<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var string $portalLabel
 * @var string $loginUrl
 * @var string $requestUrl
 * @var string $verifyUrl
 * @var string $resendUrl
 * @var string $maskedDestination
 * @var int $attemptsRemaining
 * @var bool $canResend
 * @var int $resendSeconds
 */
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>Enter OTP</h2>
            <p>We sent a 6-digit OTP to <?= view_esc($maskedDestination) ?>.</p>
        </div>
    </div>

    <form action="<?= view_esc($verifyUrl) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="otp">One-time password</label>
            <input
                type="text"
                id="otp"
                name="otp"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="6-digit code"
                autocomplete="one-time-code"
                class="otp-input"
                required
            >
            <p class="text-muted"><?= view_esc((string) $attemptsRemaining) ?> attempts remaining.</p>
        </div>

        <button type="submit" class="btn btn-primary">Verify OTP</button>
    </form>

    <div class="button-row recovery-actions">
        <form action="<?= view_esc($resendUrl) ?>" method="post" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-secondary" <?= $canResend ? '' : 'disabled' ?>>
                <?= $canResend ? 'Resend OTP' : 'Resend in ' . view_esc((string) $resendSeconds) . 's' ?>
            </button>
        </form>
        <a href="<?= view_esc($requestUrl) ?>" class="btn btn-ghost">Use another account</a>
    </div>

    <p class="auth-switch"><a class="link-inline" href="<?= view_esc($loginUrl) ?>">Back to login</a>.</p>
<?php $this->endSection(); ?>
