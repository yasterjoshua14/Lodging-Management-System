<?php
/** @var bool $usePopupAlerts */

$usePopupAlerts = $usePopupAlerts ?? false;
$errors = session('errors');
$alerts = [];

$pushAlert = static function (string $type, mixed $message, array $items = []) use (&$alerts): void {
    $normalizedMessage = trim((string) $message);
    $normalizedItems = array_values(array_filter(
        array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        ),
        static fn (string $item): bool => $item !== ''
    ));

    if ($normalizedMessage === '' && $normalizedItems === []) {
        return;
    }

    $alerts[] = [
        'type' => $type,
        'message' => $normalizedMessage,
        'items' => $normalizedItems,
    ];
};

foreach (['success', 'warning', 'error'] as $type) {
    $pushAlert($type, session()->getFlashdata($type));
}

if (is_array($errors) && $errors !== []) {
    $pushAlert('error', 'Please review the following:', $errors);
}

$alertsJson = json_encode(
    $alerts,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<?php if ($alerts === []): ?>
    <?php return; ?>
<?php endif; ?>

<?php if ($usePopupAlerts): ?>
    <script type="application/json" data-alert-payload><?= $alertsJson !== false ? $alertsJson : '[]' ?></script>
<?php else: ?>
    <div class="alert-stack" aria-live="polite">
        <?php foreach ($alerts as $alert): ?>
            <?php
            $type = is_string($alert['type'] ?? null) ? $alert['type'] : 'error';
            $message = is_string($alert['message'] ?? null) ? $alert['message'] : '';
            $items = is_array($alert['items'] ?? null) ? $alert['items'] : [];
            ?>
            <div class="alert alert-<?= view_esc($type, 'attr') ?>">
                <?php if ($message !== ''): ?>
                    <div><?= view_esc($message) ?></div>
                <?php endif; ?>

                <?php if ($items !== []): ?>
                    <ul>
                        <?php foreach ($items as $item): ?>
                            <li><?= view_esc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
