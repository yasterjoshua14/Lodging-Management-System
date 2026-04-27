<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <?php
    $sortOptions      = $sortOptions ?? [];
    $sortBy           = $sortBy ?? 'room_number';
    $sortDirection    = $sortDirection ?? 'asc';
    $currentSortLabel = $sortOptions[$sortBy] ?? 'Room No.';
    $isDefaultSort    = $sortBy === 'room_number' && $sortDirection === 'asc';

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

        <a href="<?= esc(admin_path('rooms/create')) ?>" class="btn btn-primary">Add Room</a>
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
                                    href="<?= esc($buildSortUrl($column)) ?>"
                                    class="sort-link <?= $isActiveColumn ? 'active' : '' ?>"
                                    aria-label="<?= esc('Sort rooms by ' . $label . ' in ' . $nextDirection . ' order') ?>"
                                >
                                    <span><?= esc($label) ?></span>
                                    <?php if ($isActiveColumn): ?>
                                        <span class="sort-indicator"><?= esc(strtoupper($sortDirection)) ?></span>
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
                            <td><strong><?= esc($room['room_number']) ?></strong></td>
                            <td><?= esc(room_type_options()[$room['type']] ?? humanize_key($room['type'])) ?></td>
                            <td><?= esc((string) $room['capacity']) ?> guests</td>
                            <td><?= esc(format_money($room['price_per_night'])) ?></td>
                            <td><span class="<?= esc(status_badge_class($room['status'])) ?>"><?= esc(room_status_options()[$room['status']] ?? humanize_key($room['status'])) ?></span></td>
                            <td><?= esc($room['description'] ?: 'No description') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= esc(admin_path('rooms/' . $room['id'] . '/edit')) ?>" class="btn btn-secondary">Edit</a>
                                    <form action="<?= esc(admin_path('rooms/' . $room['id'] . '/delete')) ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this room?');">
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
            <a href="<?= esc(admin_path('rooms/create')) ?>" class="btn btn-primary">Create First Room</a>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
