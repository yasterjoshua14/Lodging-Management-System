<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Bookings</h2>
            <p>Track guest stays, date ranges, and booking status updates.</p>
        </div>

        <a href="<?= esc(admin_path('bookings/create')) ?>" class="btn btn-primary">Add Booking</a>
    </div>

    <?php if ($bookings !== []): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Room</th>
                        <th>Stay Dates</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= esc($booking['tenant_name']) ?></td>
                            <td>
                                <strong><?= esc($booking['room_number']) ?></strong><br>
                                <span class="text-muted"><?= esc(room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'])) ?></span>
                            </td>
                            <td><?= esc($booking['check_in']) ?> to <?= esc($booking['check_out']) ?></td>
                            <td><span class="<?= esc(status_badge_class($booking['status'])) ?>"><?= esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                            <td><?= esc(format_money($booking['total_amount'])) ?></td>
                            <td><?= esc($booking['notes'] ?: 'No notes') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= esc(admin_path('bookings/' . $booking['id'] . '/edit')) ?>" class="btn btn-secondary">Edit</a>
                                    <form action="<?= esc(admin_path('bookings/' . $booking['id'] . '/delete')) ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this booking?');">
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
            <h3>No bookings recorded yet</h3>
            <p class="text-muted">Create a booking after adding rooms and tenants.</p>
            <a href="<?= esc(admin_path('bookings/create')) ?>" class="btn btn-primary">Create First Booking</a>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
