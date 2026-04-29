<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var array<string, mixed>|null $booking
 * @var list<array<string, mixed>> $rooms
 * @var list<array<string, mixed>> $tenants
 * @var string $action
 * @var string $heading
 */

$booking ??= null;
$rooms    ??= [];
$tenants  ??= [];
$action   ??= admin_path('bookings');
$heading  ??= 'Booking';
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2><?= view_esc($heading) ?></h2>
            <p>Link a tenant to a room and define the stay schedule.</p>
        </div>

        <a href="<?= view_esc(admin_path('bookings')) ?>" class="btn btn-ghost">Back to Bookings</a>
    </div>

    <form action="<?= view_esc($action) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div>
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id" required>
                    <option value="">Select room</option>
                    <?php foreach ($rooms as $room): ?>
                        <?php $roomId = (string) $room['id']; ?>
                        <option value="<?= view_esc($roomId) ?>" <?= old('room_id', isset($booking['room_id']) ? (string) $booking['room_id'] : '') === $roomId ? 'selected' : '' ?>>
                            <?= view_esc($room['room_number']) ?> - <?= view_esc(room_type_options()[$room['type']] ?? humanize_key($room['type'])) ?> (<?= view_esc(room_status_options()[$room['status']] ?? humanize_key($room['status'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="tenant_id">Tenant</label>
                <select id="tenant_id" name="tenant_id" required>
                    <option value="">Select tenant</option>
                    <?php foreach ($tenants as $tenant): ?>
                        <?php $tenantId = (string) $tenant['id']; ?>
                        <option value="<?= view_esc($tenantId) ?>" <?= old('tenant_id', isset($booking['tenant_id']) ? (string) $booking['tenant_id'] : '') === $tenantId ? 'selected' : '' ?>>
                            <?= view_esc($tenant['full_name']) ?> - <?= view_esc($tenant['phone']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="check_in">Check-in Date</label>
                <input type="date" id="check_in" name="check_in" value="<?= view_esc(old('check_in', $booking['check_in'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="check_out">Check-out Date</label>
                <input type="date" id="check_out" name="check_out" value="<?= view_esc(old('check_out', $booking['check_out'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="total_amount">Total Amount</label>
                <input type="number" id="total_amount" name="total_amount" min="0" step="0.01" value="<?= view_esc(old('total_amount', $booking['total_amount'] ?? '0.00')) ?>" required>
            </div>

            <div>
                <label for="status">Booking Status</label>
                <select id="status" name="status" required>
                    <?php foreach (booking_status_options() as $value => $label): ?>
                        <option value="<?= view_esc($value) ?>" <?= old('status', $booking['status'] ?? 'pending') === $value ? 'selected' : '' ?>><?= view_esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="full-span">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" placeholder="Optional booking remarks"><?= view_esc(old('notes', $booking['notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Booking</button>
            <a href="<?= view_esc(admin_path('bookings')) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
<?php $this->endSection(); ?>
