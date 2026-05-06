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
            <p>Review your booking history, continue unpaid checkouts, or cancel unpaid room holds from one place.</p>
        </div>

        <a href="<?= view_esc(tenant_path('myRooms')) ?>" class="btn btn-primary">Browse Rooms</a>
    </div>

    <section class="card card--stacked">
        <div class="list-head">
            <div>
                <h2>Booking History</h2>
                <p>Track your secured stays and any unpaid room holds waiting for checkout completion.</p>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $paymentReference = trim((string) ($booking['payment_reference'] ?? ''));
                            $paymentPaidAt    = trim((string) ($booking['payment_paid_at'] ?? ''));
                            $checkoutUrl      = trim((string) ($booking['checkout_url'] ?? ''));
                            $isAwaitingPayment = ($booking['status'] ?? '') === 'awaiting_payment';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= view_esc($booking['room_number']) ?></strong><br>
                                    <span class="text-muted"><?= view_esc(room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'])) ?></span>
                                </td>
                                <td><?= view_esc($booking['check_in']) ?> to <?= view_esc($booking['check_out']) ?></td>
                                <td><span class="<?= view_esc(status_badge_class($booking['status'])) ?>"><?= view_esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                                <td>
                                    <strong><?= view_esc(format_money($booking['total_amount'])) ?></strong>
                                    <?php if ($paymentReference !== ''): ?>
                                        <br><span class="text-muted">Ref: <?= view_esc($paymentReference) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= view_esc($booking['notes'] ?: 'No notes') ?>
                                    <?php if ($paymentPaidAt !== ''): ?>
                                        <br><span class="text-muted">Paid at <?= view_esc($paymentPaidAt) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($isAwaitingPayment && $checkoutUrl !== ''): ?>
                                            <a href="<?= view_esc($checkoutUrl, 'attr') ?>" class="btn btn-primary">Continue Payment</a>
                                        <?php endif; ?>

                                        <?php if ($isAwaitingPayment): ?>
                                            <form action="<?= view_esc(tenant_path('myBookings/' . $booking['id'] . '/cancel')) ?>" method="post" class="inline-form" onsubmit="return confirm('Cancel this unpaid booking hold?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-secondary">Cancel Hold</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted"><?= $paymentReference !== '' ? 'Payment verified' : 'Reservation recorded' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No bookings yet</h3>
                <p class="text-muted">Open the Rooms page to search availability, choose a stay, and complete payment for your first booking.</p>
            </div>
        <?php endif; ?>
    </section>
<?php $this->endSection(); ?>
