<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
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
                        <th>Room No.</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Price / Night</th>
                        <th>Status</th>
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
