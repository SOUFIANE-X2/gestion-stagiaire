<?php
$maxSlot = 1;
foreach ($slotStats as $slot) {
    $maxSlot = max($maxSlot, (int) $slot['total']);
}
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Documents</p>
        <h2>Rapports</h2>
        <p class="muted">Générez des rapports par filière ou exportez vos données.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('reports/absences.csv')) ?>"><?= icon('download') ?> Absences CSV</a>
        <a class="btn btn-primary" href="<?= e(url('reports/stagiaires.csv')) ?>"><?= icon('download') ?> Stagiaires CSV</a>
    </div>
</section>

<div class="stat-grid">
    <article class="card stat">
        <span class="chip chip-primary"><?= icon('users') ?></span>
        <div>
            <p class="stat-label">Stagiaires</p>
            <p class="stat-value"><?= $totalStagiaires ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-amber"><?= icon('calendar-x') ?></span>
        <div>
            <p class="stat-label">Absences</p>
            <p class="stat-value"><?= $stats['total'] ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-teal"><?= icon('check-circle') ?></span>
        <div>
            <p class="stat-label">Justification</p>
            <p class="stat-value"><?= e(percent($stats['justified'], $stats['total'])) ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-violet"><?= icon('chart') ?></span>
        <div>
            <p class="stat-label">Moyenne générale</p>
            <p class="stat-value"><?= e(note_fr($averageNote)) ?></p>
        </div>
    </article>
</div>

<div class="grid grid-2">
    <section class="card">
        <div class="card-head">
            <div>
                <h3>Rapports par filière</h3>
                <p class="muted">Fiche imprimable avec effectifs, notes et absences.</p>
            </div>
        </div>

        <?php if ($filieres === []): ?>
            <div class="empty-state">
                <?= icon('file') ?>
                <p>Aucune filière disponible.</p>
            </div>
        <?php else: ?>
            <ul class="report-list">
                <?php foreach ($filieres as $filiere): ?>
                    <li>
                        <span class="code-chip"><?= e($filiere['code']) ?></span>
                        <span class="report-copy">
                            <strong><?= e($filiere['label']) ?></strong>
                            <small><?= (int) $filiere['stagiaire_count'] ?> stagiaire<?= (int) $filiere['stagiaire_count'] > 1 ? 's' : '' ?> · <?= (int) $filiere['group_count'] ?> groupe<?= (int) $filiere['group_count'] > 1 ? 's' : '' ?> · moyenne <?= e(note_fr($filiere['average_note'])) ?>/20</small>
                        </span>
                        <a class="btn btn-ghost btn-sm" href="<?= e(url('reports/filieres/' . $filiere['id'])) ?>"><?= icon('printer') ?> Ouvrir</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Absences par créneau</h3>
                <p class="muted">Répartition sur l’ensemble de l’année.</p>
            </div>
        </div>

        <ul class="bar-list">
            <?php foreach ($slotStats as $slot): ?>
                <?php $width = max(2, ((int) $slot['total'] / $maxSlot) * 100); ?>
                <li>
                    <div class="bar-list-head">
                        <span class="bar-list-label"><?= e(slot_label((int) $slot['slot'])) ?></span>
                        <span class="bar-list-value"><?= (int) $slot['total'] ?> absence<?= (int) $slot['total'] > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="bar-track"><span style="width: <?= e((string) round($width, 1)) ?>%"></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<div class="grid grid-2">
    <section class="card action-card">
        <span class="chip chip-primary"><?= icon('download') ?></span>
        <div>
            <h3>Exporter les stagiaires</h3>
            <p class="muted">Fichier CSV compatible Excel : identité, groupe, filière, note, statut et compteur d’absences.</p>
            <a class="btn btn-primary btn-sm" href="<?= e(url('reports/stagiaires.csv')) ?>">Télécharger le CSV</a>
        </div>
    </section>

    <section class="card action-card">
        <span class="chip chip-amber"><?= icon('download') ?></span>
        <div>
            <h3>Exporter les absences</h3>
            <p class="muted">Toutes les absences avec créneau, statut de justification et motifs.</p>
            <a class="btn btn-primary btn-sm" href="<?= e(url('reports/absences.csv')) ?>">Télécharger le CSV</a>
        </div>
    </section>
</div>

<p class="print-hint"><?= icon('printer') ?> Astuce : ouvrez un rapport de filière puis utilisez « Imprimer » pour l’enregistrer en PDF depuis votre navigateur.</p>
