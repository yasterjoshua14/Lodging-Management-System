<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, int> $stats
 * @var array<string, mixed>|null $currentBooking
 * @var list<array<string, mixed>> $recentBookings
 * @var array<string, mixed>|null $tenant
 */

$stats ??= [
    'totalBookings'     => 0,
    'pendingBookings'   => 0,
    'activeBookings'    => 0,
    'completedBookings' => 0,
];
$currentBooking ??= null;
$recentBookings ??= [];
$tenant         ??= null;

$currentTenantUser = auth_user() ?? [];
$tenantName        = (string) (($tenant['full_name'] ?? '') ?: ($currentTenantUser['name'] ?? 'Tenant'));
$tenantEmail       = (string) (($tenant['email'] ?? '') ?: ($currentTenantUser['email'] ?? 'No email address on file'));
$tenantPhone       = (string) (($tenant['phone'] ?? '') ?: 'No phone number on file');
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>My Dashboard</h2>
        </div>
    </div>

    <section class="grid stats-grid">
        <article class="card">
            <div class="stat-label">Total Bookings</div>
            <p class="stat-value"><?= view_esc((string) $stats['totalBookings']) ?></p>
        </article>

        <article class="card">
            <div class="stat-label">Pending Bookings</div>
            <p class="stat-value"><?= view_esc((string) $stats['pendingBookings']) ?></p>
        </article>

        <article class="card">
            <div class="stat-label">Checked-In Bookings</div>
            <p class="stat-value"><?= view_esc((string) $stats['activeBookings']) ?></p>
        </article>

        <article class="card">
            <div class="stat-label">Completed Bookings</div>
            <p class="stat-value"><?= view_esc((string) $stats['completedBookings']) ?></p>
        </article>
    </section>

    <section class="split-grid">
        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Current Booking</h2>
                </div>
            </div>

            <?php if ($currentBooking !== null): ?>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h3>Room</h3>
                        <div class="detail-value"><?= view_esc($currentBooking['room_number']) ?></div>
                        <p><?= view_esc(room_type_options()[$currentBooking['room_type']] ?? humanize_key($currentBooking['room_type'])) ?></p>
                    </div>

                    <div class="detail-item">
                        <h3>Status</h3>
                        <div class="detail-value">
                            <span class="<?= view_esc(status_badge_class($currentBooking['status'])) ?>">
                                <?= view_esc(booking_status_options()[$currentBooking['status']] ?? humanize_key($currentBooking['status'])) ?>
                            </span>
                        </div>
                        <p>Total: <?= view_esc(format_money($currentBooking['total_amount'])) ?></p>
                    </div>

                    <div class="detail-item">
                        <h3>Duration</h3>
                        <div class="detail-value"><?= view_esc(hour_duration_label($currentBooking['pricing_hours'] ?? 1)) ?></div>
                        <p>Booked <?= view_esc(format_datetime($currentBooking['created_at'] ?? null)) ?></p>
                    </div>

                    <div class="detail-item full-span">
                        <h3>Notes</h3>
                        <p><?= view_esc($currentBooking['notes'] ?: 'No additional notes for this booking.') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No active booking</h3>
                    <p class="text-muted">Your account does not have a pending or checked-in booking yet.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Profile Snapshot</h2>
                </div>
            </div>

            <div class="mini-list">
                <div class="mini-item">
                    <div>
                        <strong><?= view_esc($tenantName) ?></strong>
                        <p class="text-muted">Tenant name</p>
                    </div>
                </div>

                <div class="mini-item">
                    <div>
                        <strong><?= view_esc($tenantEmail) ?></strong>
                        <p class="text-muted">Email address</p>
                    </div>
                </div>

                <div class="mini-item">
                    <div>
                        <strong><?= view_esc($tenantPhone) ?></strong>
                        <p class="text-muted">Primary contact</p>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="card">
        <div class="list-head">
            <div>
                <h2>Recent Activity</h2>
            </div>
            <a href="<?= view_esc(tenant_path('myBookings')) ?>" class="link-inline">Open booking history</a>
        </div>

        <?php if ($recentBookings !== []): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Booking Details</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong><?= view_esc($booking['room_number']) ?></strong><br>
                                    <span class="text-muted"><?= view_esc(room_type_options()[$booking['room_type']] ?? humanize_key($booking['room_type'])) ?></span>
                                </td>
                                <td>
                                    <strong><?= view_esc(hour_duration_label($booking['pricing_hours'] ?? 1)) ?></strong><br>
                                    <span class="text-muted">Booked <?= view_esc(format_datetime($booking['created_at'] ?? null)) ?></span>
                                </td>
                                <td><span class="<?= view_esc(status_badge_class($booking['status'])) ?>"><?= view_esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                                <td><?= view_esc(format_money($booking['total_amount'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No booking activity yet</h3>
                <p class="text-muted">Your booking history will appear here once reservations are linked to your tenant account.</p>
            </div>
        <?php endif; ?>
    </section>
<?php $this->endSection(); ?>
