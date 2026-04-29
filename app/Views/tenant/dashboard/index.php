<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, int> $stats
 * @var array<string, mixed>|null $nextBooking
 * @var list<array<string, mixed>> $recentBookings
 * @var array<string, mixed>|null $tenant
 */

$stats ??= [
    'totalBookings'    => 0,
    'upcomingBookings' => 0,
    'activeStays'      => 0,
    'completedStays'   => 0,
];
$nextBooking    ??= null;
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
            <p>Track your stays, upcoming reservations, and account-linked lodging activity.</p>
        </div>
    </div>

    <section class="grid stats-grid">
        <article class="card">
            <div class="stat-label">Total Bookings</div>
            <p class="stat-value"><?= view_esc((string) $stats['totalBookings']) ?></p>
            <div class="stat-note">All reservations linked to your tenant account</div>
        </article>

        <article class="card">
            <div class="stat-label">Upcoming Stays</div>
            <p class="stat-value"><?= view_esc((string) $stats['upcomingBookings']) ?></p>
            <div class="stat-note">Reservations with a future check-in date</div>
        </article>

        <article class="card">
            <div class="stat-label">Active Stays</div>
            <p class="stat-value"><?= view_esc((string) $stats['activeStays']) ?></p>
            <div class="stat-note">Bookings currently in progress</div>
        </article>

        <article class="card">
            <div class="stat-label">Completed Stays</div>
            <p class="stat-value"><?= view_esc((string) $stats['completedStays']) ?></p>
            <div class="stat-note">Past stays already checked out</div>
        </article>
    </section>

    <section class="split-grid">
        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Next Booking</h2>
                    <p>Your nearest current or upcoming reservation.</p>
                </div>
            </div>

            <?php if ($nextBooking !== null): ?>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h3>Room</h3>
                        <div class="detail-value"><?= view_esc($nextBooking['room_number']) ?></div>
                        <p><?= view_esc(room_type_options()[$nextBooking['room_type']] ?? humanize_key($nextBooking['room_type'])) ?></p>
                    </div>

                    <div class="detail-item">
                        <h3>Status</h3>
                        <div class="detail-value">
                            <span class="<?= view_esc(status_badge_class($nextBooking['status'])) ?>">
                                <?= view_esc(booking_status_options()[$nextBooking['status']] ?? humanize_key($nextBooking['status'])) ?>
                            </span>
                        </div>
                        <p>Stay total: <?= view_esc(format_money($nextBooking['total_amount'])) ?></p>
                    </div>

                    <div class="detail-item full-span">
                        <h3>Schedule</h3>
                        <div class="detail-value"><?= view_esc($nextBooking['check_in']) ?> to <?= view_esc($nextBooking['check_out']) ?></div>
                        <p><?= view_esc($nextBooking['notes'] ?: 'No additional notes for this booking.') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No upcoming booking</h3>
                    <p class="text-muted">Your account does not have a current or future booking yet.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Profile Snapshot</h2>
                    <p>The tenant record attached to this tenant portal login.</p>
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
                <p>Your latest booking records.</p>
            </div>
            <a href="<?= view_esc(tenant_path('bookings')) ?>" class="link-inline">Open booking history</a>
        </div>

        <?php if ($recentBookings !== []): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Stay Dates</th>
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
                                <td><?= view_esc($booking['check_in']) ?> to <?= view_esc($booking['check_out']) ?></td>
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
