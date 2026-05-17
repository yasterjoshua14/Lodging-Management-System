<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var list<array<string, mixed>> $rooms
 * @var bool $paymongoReady
 * @var array{check_in: string, check_out: string, guests: string, notes: string, selected_room_id: int, submitted: bool} $search
 * @var array<string, string> $searchErrors
 * @var array<string, mixed>|null $selectedRoom
 * @var int $stayNights
 */

$rooms ??= [];
$paymongoReady  ??= false;
$search         ??= [
    'check_in'         => '',
    'check_out'        => '',
    'guests'           => '',
    'notes'            => '',
    'selected_room_id' => 0,
    'submitted'        => false,
];
$searchErrors ??= [];
$selectedRoom ??= null;
$stayNights   ??= 0;
$normalizedSearchNotes = str_replace(["\r", "\n"], ' ', $search['notes']);
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2>Rooms</h2>
        </div>
    </div>

    <?php if (! $paymongoReady): ?>
        <section class="card card--stacked">
            <div class="list-head">
                <div>
                    <h2>Online Payment Setup Needed</h2>
                    <p>PayMongo keys are not configured yet, so the room list stays visible but checkout buttons remain disabled until setup is finished.</p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($search['submitted'] && $searchErrors === []): ?>
        <section class="card card--stacked">
            <div class="section-head">
                <div>
                    <h2>Rooms</h2>
                </div>

                <?php if ($selectedRoom !== null): ?>
                    <a href="#selected-room" class="btn btn-secondary">View Added Room</a>
                <?php endif; ?>
            </div>

            <?php if ($rooms !== []): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span class="sort-link active">
                                        <span>Room No.</span>
                                        <span class="sort-indicator">ASC</span>
                                    </span>
                                </th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
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
                                    <td><?= view_esc(hour_duration_label($room['pricing_hours'] ?? 1)) ?></td>
                                    <td>
                                        <span class="<?= view_esc(status_badge_class($room['display_status'] ?? 'available')) ?>">
                                            <?= view_esc(room_status_options()[$room['display_status'] ?? 'available'] ?? 'Available') ?>
                                        </span>
                                    </td>
                                    <td><?= view_esc($room['description'] ?: 'No description') ?></td>
                                    <td>
                                        <?php $isSelectedRoom = (int) ($room['id'] ?? 0) === (int) $search['selected_room_id']; ?>
                                        <div class="actions">
                                            <?php if ($room['is_bookable'] ?? false): ?>
                                                <form action="<?= view_esc(tenant_path('myRooms')) ?>" method="get" class="inline-form">
                                                    <input type="hidden" name="room_id" value="<?= view_esc((string) $room['id']) ?>">
                                                    <input type="hidden" name="selected_room" value="<?= view_esc((string) $room['id']) ?>">
                                                    <input type="hidden" name="check_in" value="<?= view_esc($search['check_in']) ?>">
                                                    <input type="hidden" name="check_out" value="<?= view_esc($search['check_out']) ?>">
                                                    <input type="hidden" name="guests" value="<?= view_esc($search['guests']) ?>">
                                                    <input type="hidden" name="notes" value="<?= view_esc($normalizedSearchNotes) ?>">
                                                    <button type="submit" class="btn <?= $isSelectedRoom ? 'btn-secondary' : 'btn-primary' ?>">
                                                        <?= $isSelectedRoom ? 'Added' : 'Add' ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Unavailable</span>
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
                    <h3>No rooms available yet</h3>
                    <p class="text-muted">Add rooms first so tenants can browse room status and pricing here.</p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($selectedRoom !== null): ?>
        <section class="card card--stacked" id="selected-room">
            <div class="list-head">
                <div>
                    <h2>Selected Room</h2>
                    <p>Review the added room details, stay schedule, and total before you continue to payment.</p>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <h3>Room</h3>
                    <div class="detail-value"><?= view_esc($selectedRoom['room_number']) ?></div>
                    <p><?= view_esc(room_type_options()[$selectedRoom['type']] ?? humanize_key($selectedRoom['type'])) ?></p>
                </div>

                <div class="detail-item">
                    <h3>Guests</h3>
                    <div class="detail-value"><?= view_esc((string) $selectedRoom['capacity']) ?> guests</div>
                </div>

                <div class="detail-item">
                    <h3>Stay Dates</h3>
                    <div class="detail-value"><?= view_esc($search['check_in']) ?> to <?= view_esc($search['check_out']) ?></div>
                    <p><?= view_esc((string) $stayNights) ?> night<?= $stayNights === 1 ? '' : 's' ?></p>
                </div>

                <div class="detail-item">
                    <h3>Total Amount</h3>
                    <div class="detail-value"><?= view_esc(format_money($selectedRoom['stay_total'])) ?></div>
                    <p><?= view_esc(format_money($selectedRoom['price_per_night'])) ?> per night</p>
                </div>

                <div class="detail-item full-span">
                    <h3>Description</h3>
                    <p><?= view_esc($selectedRoom['description'] ?: 'No description provided for this room.') ?></p>
                </div>
            </div>

            <div class="button-row">
                <form action="<?= view_esc(tenant_path('myRooms/book')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="room_id" value="<?= view_esc((string) $selectedRoom['id']) ?>">
                    <input type="hidden" name="check_in" value="<?= view_esc($search['check_in']) ?>">
                    <input type="hidden" name="check_out" value="<?= view_esc($search['check_out']) ?>">
                    <input type="hidden" name="guests" value="<?= view_esc($search['guests']) ?>">
                    <input type="hidden" name="notes" value="<?= view_esc($normalizedSearchNotes) ?>">
                    <button type="submit" class="btn btn-primary" <?= $paymongoReady ? '' : 'disabled' ?>>
                        <?= $paymongoReady ? 'Proceed to Payment' : 'Proceed to Payment' ?>
                    </button>
                </form>

                <a
                    href="<?= view_esc(tenant_path('myRooms') . '?' . http_build_query(array_filter([
                        'check_in' => $search['check_in'],
                        'check_out' => $search['check_out'],
                        'guests' => $search['guests'],
                        'notes' => $normalizedSearchNotes,
                    ], static fn (string $value): bool => $value !== ''))) ?>"
                    class="btn btn-secondary"
                >
                    Cancel Selection
                </a>
            </div>
        </section>
    <?php endif; ?>
<?php $this->endSection(); ?>
