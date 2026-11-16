<?php
$justificationRate = $absenceStats['total'] > 0
    ? ($absenceStats['justified'] / $absenceStats['total']) * 100
    : 0;
$maxFiliere = 1;
foreach ($byFiliere as $row) {
    $maxFiliere = max($maxFiliere, (int) $row['total']);
}
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Vue d’ensemble · <?= e(date_fr(date('Y-m-d'))) ?></p>
        <h2>Bonjour, <?= e((string) ($admin['first_name'] ?? 'Administrateur')) ?></h2>
        <p class="muted">Voici l’activité de votre centre pour aujourd’hui.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('reports')) ?>"><?= icon('file') ?> Rapports</a>
        <a class="btn btn-primary" href="<?= e(url('stagiaires/create')) ?>"><?= icon('plus') ?> Ajouter un stagiaire</a>
    </div>
</section>

<div class="kpi-grid">
    <article class="card kpi">
        <div class="kpi-top">
            <span class="chip chip-primary"><?= icon('users') ?></span>
            <span class="chip chip-soft">Actifs</span>
        </div>
        <p class="kpi-label">Stagiaires actifs</p>
        <p class="kpi-value"><?= $statusCounts['active'] ?></p>
        <p class="kpi-meta">sur <?= $totalStagiaires ?> inscrit<?= $totalStagiaires > 1 ? 's' : '' ?> au total</p>
    </article>

    <article class="card kpi">
        <div class="kpi-top">
            <span class="chip chip-amber"><?= icon('calendar-x') ?></span>
            <span class="chip chip-soft"><?= e(month_fr((int) date('n'))) ?></span>
        </div>
        <p class="kpi-label">Absences ce mois</p>
        <p class="kpi-value"><?= $absenceStats['month_total'] ?></p>
        <p class="kpi-meta"><?= $absenceStats['month_open'] ?> non justifiée<?= $absenceStats['month_open'] > 1 ? 's' : '' ?> · <?= $absenceStats['today'] ?> aujourd’hui</p>
    </article>

    <article class="card kpi">
        <div class="kpi-top">
            <span class="chip chip-teal"><?= icon('check-circle') ?></span>
            <span class="chip chip-soft"><?= $absenceStats['justified'] ?> / <?= $absenceStats['total'] ?></span>
        </div>
        <p class="kpi-label">Taux de justification</p>
        <p class="kpi-value"><?= e(percent($absenceStats['justified'], $absenceStats['total'])) ?></p>
        <div class="progress" role="img" aria-label="<?= e(percent($absenceStats['justified'], $absenceStats['total'])) ?> d’absences justifiées">
            <span style="width: <?= e((string) round($justificationRate, 1)) ?>%"></span>
        </div>
    </article>

    <article class="card kpi">
        <div class="kpi-top">
            <span class="chip chip-violet"><?= icon('chart') ?></span>
            <span class="chip chip-soft">/ 20</span>
        </div>
        <p class="kpi-label">Note moyenne</p>
        <p class="kpi-value"><?= e(note_fr($averageNote)) ?></p>
        <p class="kpi-meta">Moyenne générale de tous les stagiaires</p>
    </article>
</div>

<div class="grid grid-2">
    <section class="card">
        <div class="card-head">
            <div>
                <h3>Absences sur 6 mois</h3>
                <p class="muted">Volume mensuel et part non justifiée.</p>
            </div>
            <a class="text-link" href="<?= e(url('absences')) ?>">Tout voir <?= icon('chevron-right') ?></a>
        </div>

        <div class="chart" role="img" aria-label="Histogramme des absences des six derniers mois">
            <?php foreach ($trend as $point): ?>
                <?php
                $height = $maxTrend > 0 ? max(4, (int) round(($point['total'] / $maxTrend) * 100)) : 4;
                $openShare = $point['total'] > 0 ? ($point['open'] / $point['total']) * 100 : 0;
                ?>
                <div class="chart-col" title="<?= e($point['label']) ?> : <?= $point['total'] ?> absence(s), <?= $point['open'] ?> non justifiée(s)">
                    <span class="chart-value"><?= $point['total'] ?></span>
                    <div class="chart-track">
                        <div class="chart-bar" style="height: <?= $height ?>%">
                            <span class="chart-bar-open" style="height: <?= e((string) round($openShare, 1)) ?>%"></span>
                        </div>
                    </div>
                    <span class="chart-label"><?= e($point['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="legend">
            <span><i class="dot dot-primary"></i> Justifiées</span>
            <span><i class="dot dot-amber"></i> À justifier</span>
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Répartition par filière</h3>
                <p class="muted">Effectifs et moyenne de chaque filière.</p>
            </div>
            <a class="text-link" href="<?= e(url('filieres')) ?>">Gérer <?= icon('chevron-right') ?></a>
        </div>

        <?php if ($byFiliere === []): ?>
            <div class="empty-state">
                <?= icon('layers') ?>
                <p>Aucune filière enregistrée.</p>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('filieres')) ?>">Créer une filière</a>
            </div>
        <?php else: ?>
            <ul class="bar-list">
                <?php foreach ($byFiliere as $row): ?>
                    <?php $width = max(2, ((int) $row['total'] / $maxFiliere) * 100); ?>
                    <li>
                        <div class="bar-list-head">
                            <span class="bar-list-label"><?= e($row['label']) ?></span>
                            <span class="bar-list-value"><?= (int) $row['total'] ?> stagiaire<?= (int) $row['total'] > 1 ? 's' : '' ?> · <?= e(note_fr($row['average_note'])) ?>/20</span>
                        </div>
                        <div class="bar-track"><span style="width: <?= e((string) round($width, 1)) ?>%"></span></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<div class="grid grid-2">
    <section class="card">
        <div class="card-head">
            <div>
                <h3>Meilleures moyennes</h3>
                <p class="muted">Stagiaires actifs classés par note.</p>
            </div>
            <a class="text-link" href="<?= e(url('stagiaires') . '?sort=note') ?>">Classement <?= icon('chevron-right') ?></a>
        </div>

        <?php if ($top === []): ?>
            <div class="empty-state">
                <?= icon('users') ?>
                <p>Aucun stagiaire actif pour le moment.</p>
            </div>
        <?php else: ?>
            <ol class="rank-list">
                <?php foreach ($top as $index => $stagiaire): ?>
                    <li>
                        <span class="rank-number"><?= $index + 1 ?></span>
                        <span class="avatar avatar-sm"><?= e(initials($stagiaire['first_name'], $stagiaire['last_name'])) ?></span>
                        <span class="rank-copy">
                            <a href="<?= e(url('stagiaires/' . $stagiaire['id'])) ?>"><?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?></a>
                            <small><?= e($stagiaire['group_code']) ?> · <?= e($stagiaire['filiere_code']) ?></small>
                        </span>
                        <span class="note note-<?= e(note_tone($stagiaire['note'])) ?>"><?= e(note_fr($stagiaire['note'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Dernières absences</h3>
                <p class="muted">Les signalements les plus récents.</p>
            </div>
            <a class="text-link" href="<?= e(url('absences')) ?>">Tout voir <?= icon('chevron-right') ?></a>
        </div>

        <?php if ($recent === []): ?>
            <div class="empty-state">
                <?= icon('calendar') ?>
                <p>Aucune absence enregistrée.</p>
            </div>
        <?php else: ?>
            <ul class="activity-list">
                <?php foreach ($recent as $absence): ?>
                    <li>
                        <span class="activity-icon <?= (int) $absence['justified'] === 1 ? 'is-ok' : 'is-warn' ?>">
                            <?= icon((int) $absence['justified'] === 1 ? 'check' : 'clock') ?>
                        </span>
                        <span class="activity-copy">
                            <a href="<?= e(url('stagiaires/' . $absence['stagiaire_id'])) ?>"><?= e($absence['first_name'] . ' ' . $absence['last_name']) ?></a>
                            <small><?= e(date_fr($absence['date'])) ?> · <?= e(slot_label((int) $absence['slot'])) ?></small>
                        </span>
                        <span class="badge <?= (int) $absence['justified'] === 1 ? 'badge-success' : 'badge-warning' ?>">
                            <?= (int) $absence['justified'] === 1 ? 'Justifiée' : 'À justifier' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
