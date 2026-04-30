<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var string $portalLabel
 * @var string $loginUrl
 * @var string $requestUrl
 * @var string $requestCopy
 * @var bool $allowSms
 */
$oldIdentifier = old('identifier');
$hasIdentifier = trim((string) $oldIdentifier) !== '';
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2><?= view_esc($portalLabel) ?> password recovery</h2>
            <p><?= view_esc($requestCopy) ?></p>
        </div>
    </div>

    <form action="<?= view_esc($requestUrl) ?>" method="post" class="grid">
        <?= csrf_field() ?>

        <div>
            <label for="identifier">Email or phone number</label>
            <input
                type="text"
                id="identifier"
                name="identifier"
                value="<?= view_esc($oldIdentifier) ?>"
                placeholder="Email address or phone number"
                autocomplete="username"
                required
            >
        </div>

        <fieldset class="choice-field" id="otp-channel-field" <?= $hasIdentifier ? '' : 'hidden' ?>>
            <legend>Send OTP by</legend>
            <label class="choice-option">
                <input type="radio" name="channel" value="email" <?= old('channel', 'email') === 'email' ? 'checked' : '' ?>>
                <span>Email</span>
            </label>
            <?php if ($allowSms): ?>
                <label class="choice-option">
                    <input type="radio" name="channel" value="sms" <?= old('channel') === 'sms' ? 'checked' : '' ?>>
                    <span>SMS</span>
                </label>
            <?php endif; ?>
        </fieldset>

        <button type="submit" class="btn btn-primary" id="otp-submit-button" <?= $hasIdentifier ? '' : 'hidden' ?>>Send OTP</button>
    </form>

    <p class="auth-switch"><a class="link-inline" href="<?= view_esc($loginUrl) ?>">Back to login</a>.</p>

    <script>
        (function () {
            const identifierInput = document.getElementById('identifier');
            const channelField = document.getElementById('otp-channel-field');
            const submitButton = document.getElementById('otp-submit-button');

            if (!identifierInput || !channelField || !submitButton) {
                return;
            }

            const toggleChannelField = function () {
                const shouldHideRecoveryOptions = identifierInput.value.trim() === '';

                channelField.hidden = shouldHideRecoveryOptions;
                submitButton.hidden = shouldHideRecoveryOptions;
            };

            identifierInput.addEventListener('input', toggleChannelField);
            toggleChannelField();
        })();
    </script>
<?php $this->endSection(); ?>
