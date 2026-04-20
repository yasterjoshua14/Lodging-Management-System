<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2><?= esc($heading) ?></h2>
            <p>Capture room category, pricing, and availability details.</p>
        </div>

        <a href="/admin/rooms" class="btn btn-ghost">Back to Rooms</a>
    </div>

    <form action="<?= esc($action) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div>
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" value="<?= esc(old('room_number', $room['room_number'] ?? '')) ?>" required>
            </div>

            <div>
                <label for="type">Room Type</label>
                <select id="type" name="type" required>
                    <option value="">Select room type</option>
                    <?php foreach (room_type_options() as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= old('type', $room['type'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" min="1" value="<?= esc(old('capacity', $room['capacity'] ?? '1')) ?>" required>
            </div>

            <div>
                <label for="price_per_night">Price per Night</label>
                <input type="number" id="price_per_night" name="price_per_night" min="0" step="0.01" value="<?= esc(old('price_per_night', $room['price_per_night'] ?? '0.00')) ?>" required>
            </div>

            <div class="full-span">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <?php foreach (room_status_options() as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= old('status', $room['status'] ?? 'available') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="full-span">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Short room description or amenities"><?= esc(old('description', $room['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Save Room</button>
            <a href="/admin/rooms" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
<?= $this->endSection() ?>
