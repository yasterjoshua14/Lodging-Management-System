<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var list<array<string, mixed>> $bookings
 */

$bookings ??= [];
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>My Bookings</h2>
            <p>Only bookings tied to your authenticated tenant profile are shown here.</p>
        </div>

    </div>

    <?php if ($bookings !== []): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Stay Dates</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <strong><?= view_esc($booking['room_number']) ?></strong><br>
                                <span class="text-muted"><?= view_esc(room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'])) ?></span>
                            </td>
                            <td><?= view_esc($booking['check_in']) ?> to <?= view_esc($booking['check_out']) ?></td>
                            <td><span class="<?= view_esc(status_badge_class($booking['status'])) ?>"><?= view_esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                            <td><?= view_esc(format_money($booking['total_amount'])) ?></td>
                            <td><?= view_esc($booking['notes'] ?: 'No notes') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No bookings yet</h3>
            <p class="text-muted">Once the lodging team creates a reservation for your tenant record, it will appear here.</p>
        </div>
    <?php endif; ?>
<?php $this->endSection(); ?>
