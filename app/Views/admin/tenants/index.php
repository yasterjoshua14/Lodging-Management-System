<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="section-head">
        <div>
            <h2>Tenants</h2>
            <p>Maintain guest identity, contact, and emergency information.</p>
        </div>

        <a href="/admin/tenants/create" class="btn btn-primary">Add Tenant</a>
    </div>

    <?php if ($tenants !== []): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>ID Details</th>
                        <th>Emergency Contact</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                        <tr>
                            <td><strong><?= esc($tenant['full_name']) ?></strong></td>
                            <td>
                                <?= esc($tenant['phone']) ?><br>
                                <span class="text-muted"><?= esc($tenant['email'] ?: 'No email provided') ?></span>
                            </td>
                            <td>
                                <?= esc($tenant['id_type'] ?: 'N/A') ?><br>
                                <span class="text-muted"><?= esc($tenant['id_number'] ?: 'No ID number') ?></span>
                            </td>
                            <td>
                                <?= esc($tenant['emergency_contact_name'] ?: 'N/A') ?><br>
                                <span class="text-muted"><?= esc($tenant['emergency_contact_phone'] ?: 'No emergency phone') ?></span>
                            </td>
                            <td><?= esc($tenant['address'] ?: 'No address provided') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="/admin/tenants/<?= esc((string) $tenant['id']) ?>/edit" class="btn btn-secondary">Edit</a>
                                    <form action="/admin/tenants/<?= esc((string) $tenant['id']) ?>/delete" method="post" class="inline-form" onsubmit="return confirm('Delete this tenant record?');">
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
            <h3>No tenant records yet</h3>
            <p class="text-muted">Add your first tenant to start creating bookings.</p>
            <a href="/admin/tenants/create" class="btn btn-primary">Create First Tenant</a>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
