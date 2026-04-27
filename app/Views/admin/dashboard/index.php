<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <!-- Admin -->
    <div class="section-head">
        <div>
            <h2>Operations Dashboard</h2>
            <p>Quick visibility into rooms, tenants, and active stays.</p>
        </div>
    </div>

    <section class="grid stats-grid">
        <article class="card">
            <div class="stat-label">Total Rooms</div>
            <p class="stat-value"><?= esc((string) $stats['totalRooms']) ?></p>
            <div class="stat-note">All tracked room inventory</div>
        </article>

        <article class="card">
            <div class="stat-label">Available Rooms</div>
            <p class="stat-value"><?= esc((string) $stats['availableRooms']) ?></p>
            <div class="stat-note">Ready for incoming guests</div>
        </article>

        <article class="card">
            <div class="stat-label">Tenant Records</div>
            <p class="stat-value"><?= esc((string) $stats['totalTenants']) ?></p>
            <div class="stat-note">Stored guest profiles</div>
        </article>

        <article class="card">
            <div class="stat-label">Active Bookings</div>
            <p class="stat-value"><?= esc((string) $stats['activeBookings']) ?></p>
            <div class="stat-note">Pending and checked-in stays</div>
        </article>

        <article class="card">
            <div class="stat-label">Completed Stays</div>
            <p class="stat-value"><?= esc((string) $stats['completedStays']) ?></p>
            <div class="stat-note">Historical check-outs logged</div>
        </article>
    </section>

    <section class="split-grid">
        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Recent Bookings</h2>
                    <p>Latest check-in schedules and stay statuses.</p>
                </div>
                <a href="<?= esc(admin_path('bookings')) ?>" class="link-inline">View all</a>
            </div>

            <?php if ($recentBookings !== []): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Room</th>
                                <th>Stay</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?= esc($booking['tenant_name']) ?></td>
                                    <td>
                                        <strong><?= esc($booking['room_number']) ?></strong><br>
                                        <span class="text-muted"><?= esc(humanize_key($booking['room_type'])) ?></span>
                                    </td>
                                    <td><?= esc($booking['check_in']) ?> to <?= esc($booking['check_out']) ?></td>
                                    <td><span class="<?= esc(status_badge_class($booking['status'])) ?>"><?= esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                                    <td><?= esc(format_money($booking['total_amount'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No bookings yet</h3>
                    <p class="text-muted">Create the first booking to start tracking guest stays.</p>
                </div>
            <?php endif; ?>
        </article>

        <article class="card">
            <div class="list-head">
                <div>
                    <h2>Room Status</h2>
                    <p>Current distribution of room availability.</p>
                </div>
            </div>

            <div class="mini-list">
                <?php foreach ($roomsByStatus as $item): ?>
                    <div class="mini-item">
                        <div>
                            <strong><?= esc($item['label']) ?></strong>
                            <p class="text-muted">Rooms in this category</p>
                        </div>
                        <span class="badge badge-info"><?= esc((string) $item['count']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
<?= $this->endSection() ?>
