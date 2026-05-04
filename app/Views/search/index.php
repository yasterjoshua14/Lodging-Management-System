<?php
/**
 * @var \CodeIgniter\View\View $this
 * @var bool $hasQuery
 * @var int $resultCount
 * @var string $searchHint
 * @var string $searchQuery
 * @var list<array{title:string,description:string,items:list<array<string,string>>,emptyMessage:string}> $searchSections
 */

$hasQuery        ??= false;
$resultCount     ??= 0;
$searchHint      ??= '';
$searchQuery     ??= '';
$searchSections  ??= [];
$resultLabel      = $resultCount === 1 ? 'result' : 'results';
?>
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>
    <div class="section-head">
        <div>
            <h2><?= $hasQuery ? 'Search Results' : 'Search' ?></h2>
            <p>
                <?php if ($hasQuery): ?>
                    <?= view_esc($resultCount . ' ' . $resultLabel . ' for "' . $searchQuery . '".') ?>
                <?php else: ?>
                    <?= view_esc($searchHint) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <section class="card search-hero">
        <span class="eyebrow"><?= $hasQuery ? 'Current Query' : 'Search Tips' ?></span>
        <?php if ($hasQuery): ?>
            <h3><?= view_esc($searchQuery) ?></h3>
            <p class="text-muted"><?= view_esc($searchHint) ?></p>
        <?php else: ?>
            <h3>Use the navbar search to jump across the portal.</h3>
            <p class="text-muted">Try names, room numbers, statuses, dates, email addresses, or booking notes.</p>
        <?php endif; ?>
    </section>

    <section class="search-grid">
        <?php foreach ($searchSections as $section): ?>
            <?php if (! $hasQuery && $section['items'] === []): ?>
                <?php continue; ?>
            <?php endif; ?>
            <article class="card search-section">
                <div class="list-head">
                    <div>
                        <h2><?= view_esc($section['title']) ?></h2>
                        <p><?= view_esc($section['description']) ?></p>
                    </div>
                    <span class="search-section__count"><?= view_esc((string) count($section['items'])) ?></span>
                </div>

                <?php if ($section['items'] !== []): ?>
                    <div class="search-list">
                        <?php foreach ($section['items'] as $item): ?>
                            <a class="search-item" href="<?= view_esc($item['href']) ?>">
                                <span class="search-item__meta"><?= view_esc($item['meta']) ?></span>
                                <strong><?= view_esc($item['title']) ?></strong>
                                <p><?= view_esc($item['summary']) ?></p>
                                <?php if (($item['detail'] ?? '') !== ''): ?>
                                    <span class="search-item__detail"><?= view_esc($item['detail']) ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="search-empty">
                        <h3>No matches here</h3>
                        <p class="text-muted"><?= view_esc($section['emptyMessage']) ?></p>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php $this->endSection(); ?>
