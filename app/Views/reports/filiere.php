<?php
$appName = (string) config('app.name', 'INARAIN');
$passed = 0;
$below = 0;
foreach ($stagiaires as $stagiaire) {
    if ((float) $stagiaire['note'] >= (float) config('academic.pass_mark', 10)) {
        $passed++;
    } else {
        $below++;
    }
}
?>
<header class="report-head">
    <div class="report-brand">
        <span class="brand-mark" aria-hidden="true">IN</span>
        <div>
            <strong><?= e($appName) ?></strong>
            <small><?= e(config('app.tagline', '')) ?></small>
        </div>
    </div>
    <div class="report-meta">
        <p>Rapport de filière</p>
        <strong><?= e(date_fr(date('Y-m-d'))) ?></strong>
    </div>
</header>

<section class="report-title">
    <p class="eyebrow">Filière <?= e($filiere['code']) ?></p>
    <h1><?= e($filiere['label']) ?></h1>
    <p>État des effectifs, des moyennes et de l’assiduité.</p>
</section>

<div class="report-stats">
    <div>
        <span>Stagiaires</span>
        <strong><?= count($stagiaires) ?></strong>
    </div>
    <div>
        <span>Groupes</span>
        <strong><?= count($groups) ?></strong>
    </div>
    <div>
        <span>Moyenne</span>
        <strong><?= e(note_fr($average)) ?>/20</strong>
    </div>
    <div>
        <span>Absences</span>
        <strong><?= $totalAbsences ?></strong>
    </div>
    <div>
        <span>À justifier</span>
        <strong><?= $openAbsences ?></strong>
    </div>
</div>

<section class="report-section">
    <h2>Groupes</h2>
    <?php if ($groups === []): ?>
        <p class="muted">Aucun groupe rattaché à cette filière.</p>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Année</th>
                    <th class="num">Effectif</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= e($group['code']) ?></td>
                        <td><?= e($group['label']) ?></td>
                        <td><?= e($group['academic_year']) ?></td>
                        <td class="num"><?= (int) $group['stagiaire_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="report-section">
    <h2>Stagiaires</h2>
    <?php if ($stagiaires === []): ?>
        <p class="muted">Aucun stagiaire dans cette filière.</p>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom complet</th>
                    <th>Groupe</th>
                    <th class="num">Note</th>
                    <th class="num">Absences</th>
                    <th class="num">À justifier</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stagiaires as $stagiaire): ?>
                    <tr>
                        <td><?= e($stagiaire['code']) ?></td>
                        <td><?= e($stagiaire['last_name'] . ' ' . $stagiaire['first_name']) ?></td>
                        <td><?= e($stagiaire['group_code']) ?></td>
                        <td class="num"><?= e(note_fr($stagiaire['note'])) ?></td>
                        <td class="num"><?= (int) $stagiaire['absence_total'] ?></td>
                        <td class="num"><?= (int) $stagiaire['absence_open'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="report-section">
    <h2>Synthèse</h2>
    <div class="report-summary">
        <p><strong><?= $passed ?></strong> stagiaire<?= $passed > 1 ? 's' : '' ?> au-dessus de la moyenne (<?= e(note_fr(config('academic.pass_mark', 10))) ?>/20).</p>
        <p><strong><?= $below ?></strong> stagiaire<?= $below > 1 ? 's' : '' ?> en dessous de la moyenne.</p>
        <p><strong><?= e(percent($openAbsences, max(1, $totalAbsences))) ?></strong> des absences restent à justifier.</p>
    </div>
</section>

<footer class="report-foot">
    <span><?= e($appName) ?> · Rapport généré le <?= e(date('d/m/Y à H:i')) ?></span>
    <span>Signature de l’administration : ______________________</span>
</footer>
