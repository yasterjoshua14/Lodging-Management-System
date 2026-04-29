<?php $errors = session('errors'); ?>

<div class="alert-stack">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= view_esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning"><?= view_esc(session()->getFlashdata('warning')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= view_esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (is_array($errors) && $errors !== []): ?>
        <div class="alert alert-error">
            Please review the following:
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= view_esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
