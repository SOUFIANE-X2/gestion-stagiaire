<?php
/**
 * @var array $result
 */
$queryBase = url(current_path());
$page = (int) ($result['page'] ?? 1);
$pages = (int) ($result['pages'] ?? 1);
$total = (int) ($result['total'] ?? 0);
$window = 2;
$start = max(1, $page - $window);
$end = min($pages, $page + $window);
?>
<div class="table-footer">
    <p class="muted"><?= $total ?> résultat<?= $total > 1 ? 's' : '' ?><?= $pages > 1 ? ' · page ' . $page . ' sur ' . $pages : '' ?></p>

    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a class="page-btn" href="<?= e($queryBase . query_string(['page' => $page - 1])) ?>" aria-label="Page précédente"><?= icon('chevron-left') ?></a>
            <?php else: ?>
                <span class="page-btn is-disabled" aria-hidden="true"><?= icon('chevron-left') ?></span>
            <?php endif; ?>

            <?php if ($start > 1): ?>
                <a class="page-btn" href="<?= e($queryBase . query_string(['page' => 1])) ?>">1</a>
                <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="page-btn is-current" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a class="page-btn" href="<?= e($queryBase . query_string(['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                <a class="page-btn" href="<?= e($queryBase . query_string(['page' => $pages])) ?>"><?= $pages ?></a>
            <?php endif; ?>

            <?php if ($page < $pages): ?>
                <a class="page-btn" href="<?= e($queryBase . query_string(['page' => $page + 1])) ?>" aria-label="Page suivante"><?= icon('chevron-right') ?></a>
            <?php else: ?>
                <span class="page-btn is-disabled" aria-hidden="true"><?= icon('chevron-right') ?></span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
