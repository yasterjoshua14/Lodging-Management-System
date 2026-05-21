<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var list<array<string, mixed>> $rooms
 * @var bool $paymongoReady
 * @var array<string, mixed>|null $selectedRoom
 */

$rooms ??= [];
$paymongoReady ??= false;
$selectedRoom ??= null;
$selectedRoomNotes = old('notes', '');
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
                    <p>PayMongo keys are not configured yet, so rooms stay visible but checkout remains disabled until setup is finished.</p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="card card--stacked">
        <div class="section-head">
            <div>
                <h2>Available Rooms</h2>
                <p class="text-muted">Choose a room based on capacity, duration, and price, then continue straight to payment.</p>
            </div>

            <?php if ($selectedRoom !== null): ?>
                <a href="#selected-room" class="btn btn-primary">Review Selection</a>
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
                            <?php $isSelectedRoom = $selectedRoom !== null && (int) ($room['id'] ?? 0) === (int) ($selectedRoom['id'] ?? 0); ?>
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
                                    <div class="actions actions--compact">
                                        <?php if ($room['is_bookable'] ?? false): ?>
                                            <a href="<?= view_esc(tenant_path('myRooms') . '?selected_room=' . (int) $room['id']) ?>" class="btn <?= $isSelectedRoom ? 'btn-info' : 'btn-primary' ?> btn-compact">
                                                <?= $isSelectedRoom ? 'Selected' : 'Select' ?>
                                            </a>
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
                <p class="text-muted">Add rooms first so tenants can browse room status, duration, and pricing here.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($selectedRoom !== null): ?>
        <section class="card card--stacked" id="selected-room">
            <div class="list-head">
                <div>
                    <h2>Selected Room</h2>
                    <p>Review the room details, add optional notes, and continue to payment.</p>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <h3>Room</h3>
                    <div class="detail-value"><?= view_esc($selectedRoom['room_number']) ?></div>
                    <p><?= view_esc(room_type_options()[$selectedRoom['type']] ?? humanize_key($selectedRoom['type'])) ?></p>
                </div>

                <div class="detail-item">
                    <h3>Capacity</h3>
                    <div class="detail-value"><?= view_esc((string) $selectedRoom['capacity']) ?> guests</div>
                </div>

                <div class="detail-item">
                    <h3>Duration</h3>
                    <div class="detail-value"><?= view_esc(hour_duration_label($selectedRoom['pricing_hours'] ?? 1)) ?></div>
                </div>

                <div class="detail-item">
                    <h3>Total Amount</h3>
                    <div class="detail-value"><?= view_esc(format_money($selectedRoom['stay_total'])) ?></div>
                </div>

                <div class="detail-item full-span">
                    <h3>Description</h3>
                    <h4><?= view_esc($selectedRoom['description'] ?: 'No description provided for this room.') ?></h4>
                </div>
            </div>

            <form action="<?= view_esc(tenant_path('myRooms/book')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="room_id" value="<?= view_esc((string) $selectedRoom['id']) ?>">

                <div class="form-grid">
                    <div class="full-span">
                        <label for="notes">Booking Notes</label>
                        <textarea id="notes" name="notes" placeholder="Optional notes for the front desk"><?= view_esc($selectedRoomNotes) ?></textarea>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn btn-primary" <?= $paymongoReady ? '' : 'disabled' ?>>
                        Proceed to Payment
                    </button>
                    <a href="<?= view_esc(tenant_path('myRooms')) ?>" class="btn btn-danger">Cancel Selection</a>
                </div>
            </form>
        </section>
    <?php endif; ?>
<?php $this->endSection(); ?>
