<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var list<array<string, mixed>> $rooms
 * @var array<string, string> $sortOptions
 * @var string $sortBy
 * @var string $sortDirection
 */

$rooms ??= [];
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <?php
    $sortOptions      = $sortOptions ?? [];
    $sortBy           = $sortBy ?? 'room_number';
    $sortDirection    = $sortDirection ?? 'asc';

    $buildSortUrl = static function (string $column) use ($sortBy, $sortDirection): string {
        $direction = $sortBy === $column && $sortDirection === 'asc' ? 'desc' : 'asc';

        return admin_path('rooms') . '?' . http_build_query([
            'sort'      => $column,
            'direction' => $direction,
        ]);
    };
    ?>

    <div class="section-head">
        <div>
            <h2>Rooms</h2>
            <p>Manage room inventory, pricing, and current room status.</p>
        </div>

        <a href="<?= view_esc(admin_path('rooms/create')) ?>" class="btn btn-primary">Add Room</a>
    </div>

    <?php if ($rooms !== []): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($sortOptions as $column => $label): ?>
                            <?php
                            $isActiveColumn = $sortBy === $column;
                            $nextDirection  = $isActiveColumn && $sortDirection === 'asc' ? 'descending' : 'ascending';
                            ?>
                            <th>
                                <a
                                    href="<?= view_esc($buildSortUrl($column)) ?>"
                                    class="sort-link <?= $isActiveColumn ? 'active' : '' ?>"
                                    aria-label="<?= view_esc('Sort rooms by ' . $label . ' in ' . $nextDirection . ' order') ?>"
                                >
                                    <span><?= view_esc($label) ?></span>
                                    <?php if ($isActiveColumn): ?>
                                        <span class="sort-indicator"><?= view_esc(strtoupper($sortDirection)) ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                        <?php endforeach; ?>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><strong><?= view_esc($room['room_number']) ?></strong></td>
                            <td><?= view_esc(room_type_options()[$room['type']] ?? humanize_key($room['type'])) ?></td>
                            <td><?= view_esc((string) $room['capacity']) ?> guests</td>
                            <td><?= view_esc(format_money($room['price_per_night'])) ?></td>
                            <td><span class="<?= view_esc(status_badge_class($room['status'])) ?>"><?= view_esc(room_status_options()[$room['status']] ?? humanize_key($room['status'])) ?></span></td>
                            <td><?= view_esc($room['description'] ?: 'No description') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= view_esc(admin_path('rooms/' . $room['id'] . '/edit')) ?>" class="btn btn-secondary">Edit</a>
                                    <form action="<?= view_esc(admin_path('rooms/' . $room['id'] . '/delete')) ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this room?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No rooms available yet</h3>
            <p class="text-muted">Start by adding your first room to build the lodging inventory.</p>
            <a href="<?= view_esc(admin_path('rooms/create')) ?>" class="btn btn-primary">Create First Room</a>
        </div>
    <?php endif; ?>
<?php $this->endSection(); ?>
