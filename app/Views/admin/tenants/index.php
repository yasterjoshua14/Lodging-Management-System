<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var list<array<string, mixed>> $tenants
 */

$tenants ??= [];
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <?php $imageExtensions = ['jpg', 'jpeg', 'png']; ?>

    <div class="section-head">
        <div>
            <h2>Tenants</h2>
            <p>Maintain guest identity, contact, and emergency information.</p>
        </div>

        <a href="<?= view_esc(admin_path('tenants/create')) ?>" class="btn btn-primary">Add Tenant</a>
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
                        <?php
                        $idDocumentPath      = trim((string) ($tenant['id_document_path'] ?? ''));
                        $idDocumentUrl       = $idDocumentPath !== '' ? admin_path('tenants/' . $tenant['id'] . '/id-document') : null;
                        $idDocumentExtension = strtolower(pathinfo($idDocumentPath, PATHINFO_EXTENSION));
                        $hasImagePreview     = $idDocumentUrl !== null && in_array($idDocumentExtension, $imageExtensions, true);
                        ?>
                        <tr>
                            <td><strong><?= view_esc($tenant['full_name']) ?></strong></td>
                            <td>
                                <?= view_esc($tenant['phone']) ?><br>
                                <span class="text-muted"><?= view_esc($tenant['email'] ?: 'No email provided') ?></span>
                            </td>
                            <td>
                                <div class="document-cell">
                                    <div>
                                        <?= view_esc($tenant['id_type'] ?: 'N/A') ?><br>
                                        <span class="text-muted"><?= view_esc($tenant['id_number'] ?: 'No ID number') ?></span>
                                    </div>

                                    <?php if ($hasImagePreview): ?>
                                        <a href="<?= view_esc($idDocumentUrl) ?>" target="_blank" rel="noopener" class="document-thumb">
                                            <img src="<?= view_esc($idDocumentUrl) ?>" alt="<?= view_esc('Uploaded ID for ' . $tenant['full_name']) ?>">
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($idDocumentUrl !== null): ?>
                                        <a href="<?= view_esc($idDocumentUrl) ?>" target="_blank" rel="noopener" class="link-inline">Open uploaded ID</a>
                                    <?php else: ?>
                                        <span class="text-muted">No uploaded ID</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?= view_esc($tenant['emergency_contact_name'] ?: 'N/A') ?><br>
                                <span class="text-muted"><?= view_esc($tenant['emergency_contact_phone'] ?: 'No emergency phone') ?></span>
                            </td>
                            <td><?= view_esc($tenant['address'] ?: 'No address provided') ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= view_esc(admin_path('tenants/' . $tenant['id'] . '/edit')) ?>" class="btn btn-secondary">Edit</a>
                                    <form action="<?= view_esc(admin_path('tenants/' . $tenant['id'] . '/delete')) ?>" method="post" class="inline-form" onsubmit="return confirm('Delete this tenant record?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">Delete Data</button>
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
            <a href="<?= view_esc(admin_path('tenants/create')) ?>" class="btn btn-primary">Create First Tenant</a>
        </div>
    <?php endif; ?>
<?php $this->endSection(); ?>
