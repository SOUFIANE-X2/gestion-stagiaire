<?php
$hasFilters = $filters['search'] !== '' || $filters['filiere'] > 0 || $filters['state'] !== '' || $filters['month'] !== '';
$groupedStagiaires = [];
foreach ($stagiaires as $stagiaire) {
    $groupedStagiaires[$stagiaire['filiere_label']][] = $stagiaire;
}
$slots = config('academic.slots', []);
$today = date('Y-m-d');
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Assiduité</p>
        <h2>Absences</h2>
        <p class="muted"><?= (int) $result['total'] ?> absence<?= (int) $result['total'] > 1 ? 's' : '' ?> dans la vue actuelle.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('reports/absences.csv')) ?>"><?= icon('download') ?> Exporter CSV</a>
        <button class="btn btn-primary" type="button" data-open-dialog="absence-dialog"><?= icon('plus') ?> Enregistrer une absence</button>
    </div>
</section>

<div class="stat-grid">
    <article class="card stat">
        <span class="chip chip-primary"><?= icon('calendar-x') ?></span>
        <div>
            <p class="stat-label">Total</p>
            <p class="stat-value"><?= $stats['total'] ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-amber"><?= icon('clock') ?></span>
        <div>
            <p class="stat-label">À justifier</p>
            <p class="stat-value"><?= $stats['open'] ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-teal"><?= icon('check-circle') ?></span>
        <div>
            <p class="stat-label">Taux de justification</p>
            <p class="stat-value"><?= e(percent($stats['justified'], $stats['total'])) ?></p>
        </div>
    </article>
    <article class="card stat">
        <span class="chip chip-violet"><?= icon('chart') ?></span>
        <div>
            <p class="stat-label">Ce mois</p>
            <p class="stat-value"><?= $stats['month_total'] ?></p>
        </div>
    </article>
</div>

<section class="card filters-card">
    <form class="filters" method="get" action="<?= e(url('absences')) ?>">
        <div class="filter-field filter-grow">
            <label for="absence-search">Recherche</label>
            <div class="input-icon">
                <?= icon('search') ?>
                <input id="absence-search" type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Nom ou code du stagiaire…">
            </div>
        </div>

        <div class="filter-field">
            <label for="absence-filiere">Filière</label>
            <select id="absence-filiere" name="filiere">
                <option value="">Toutes les filières</option>
                <?php foreach ($filieres as $filiere): ?>
                    <option value="<?= (int) $filiere['id'] ?>"<?= (int) $filters['filiere'] === (int) $filiere['id'] ? ' selected' : '' ?>><?= e($filiere['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="absence-state">Statut</label>
            <select id="absence-state" name="state">
                <option value="">Tous les statuts</option>
                <option value="open"<?= $filters['state'] === 'open' ? ' selected' : '' ?>>À justifier</option>
                <option value="justified"<?= $filters['state'] === 'justified' ? ' selected' : '' ?>>Justifiées</option>
            </select>
        </div>

        <div class="filter-field">
            <label for="absence-month">Mois</label>
            <input id="absence-month" type="month" name="month" value="<?= e($filters['month']) ?>">
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Filtrer</button>
            <?php if ($hasFilters): ?>
                <a class="btn btn-ghost" href="<?= e(url('absences')) ?>">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card card-flush">
    <?php if ($result['rows'] === []): ?>
        <div class="empty-state empty-state-lg">
            <?= icon('check-circle') ?>
            <h3>Aucune absence à afficher</h3>
            <p><?= $hasFilters ? 'Aucun résultat pour ces filtres.' : 'Aucune absence n’a encore été enregistrée.' ?></p>
            <div class="empty-actions">
                <?php if ($hasFilters): ?>
                    <a class="btn btn-ghost" href="<?= e(url('absences')) ?>">Effacer les filtres</a>
                <?php endif; ?>
                <button class="btn btn-primary" type="button" data-open-dialog="absence-dialog"><?= icon('plus') ?> Enregistrer une absence</button>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Stagiaire</th>
                        <th scope="col">Groupe</th>
                        <th scope="col">Créneau</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Motif</th>
                        <th scope="col" class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $absence): ?>
                        <?php $isJustified = (int) $absence['justified'] === 1; ?>
                        <tr>
                            <td class="table-strong"><?= e(date_fr($absence['date'])) ?></td>
                            <td>
                                <div class="person">
                                    <span class="avatar avatar-sm"><?= e(initials($absence['first_name'], $absence['last_name'])) ?></span>
                                    <div class="person-copy">
                                        <a class="person-name" href="<?= e(url('stagiaires/' . $absence['stagiaire_id'])) ?>"><?= e($absence['first_name'] . ' ' . $absence['last_name']) ?></a>
                                        <span class="person-meta"><?= e($absence['stagiaire_code']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="table-strong"><?= e($absence['group_code']) ?></span>
                                <span class="table-sub"><?= e($absence['filiere_label']) ?></span>
                            </td>
                            <td><?= e(slot_label((int) $absence['slot'])) ?></td>
                            <td>
                                <span class="badge <?= $isJustified ? 'badge-success' : 'badge-warning' ?>"><?= $isJustified ? 'Justifiée' : 'À justifier' ?></span>
                            </td>
                            <td class="cell-note">
                                <?php if ($absence['reason']): ?>
                                    <span><?= e($absence['reason']) ?></span>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                                <?php if ($isJustified && $absence['justification']): ?>
                                    <span class="table-sub"><?= e($absence['justification']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <?php if (!$isJustified): ?>
                                    <button
                                        class="icon-btn icon-btn-sm"
                                        type="button"
                                        title="Justifier l’absence"
                                        aria-label="Justifier l’absence de <?= e($absence['first_name'] . ' ' . $absence['last_name']) ?>"
                                        data-justify-url="<?= e(url('absences/' . $absence['id'] . '/justify')) ?>"
                                        data-justify-name="<?= e($absence['first_name'] . ' ' . $absence['last_name']) ?>"
                                        data-justify-date="<?= e(date_fr($absence['date']) . ' · ' . slot_label((int) $absence['slot'])) ?>"
                                        data-open-dialog="justify-dialog"
                                    ><?= icon('check') ?></button>
                                <?php endif; ?>
                                <form action="<?= e(url('absences/' . $absence['id'] . '/delete')) ?>" method="post" data-confirm="Supprimer cette absence ? La note du stagiaire sera recalculée en conséquence.">
                                    <?= csrf_field() ?>
                                    <button class="icon-btn icon-btn-sm icon-btn-danger" type="submit" title="Supprimer" aria-label="Supprimer l’absence"><?= icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/../partials/pagination.php'; ?>
    <?php endif; ?>
</section>

<dialog class="dialog" id="absence-dialog" aria-labelledby="absence-dialog-title">
    <form class="dialog-form" action="<?= e(url('absences')) ?>" method="post">
        <?= csrf_field() ?>
        <header class="dialog-head">
            <div>
                <p class="eyebrow">Assiduité</p>
                <h3 id="absence-dialog-title">Enregistrer une absence</h3>
                <p class="muted">La note du stagiaire sera réduite de <?= e(note_fr(config('academic.absence_penalty', 0.25))) ?> point jusqu’à justification.</p>
            </div>
            <button class="icon-btn" type="button" data-close-dialog aria-label="Fermer"><?= icon('x') ?></button>
        </header>

        <div class="dialog-body form-stack">
            <div class="field">
                <label for="absence-stagiaire">Stagiaire <span class="required">*</span></label>
                <select id="absence-stagiaire" name="stagiaire_id" required>
                    <option value="">Sélectionner un stagiaire…</option>
                    <?php foreach ($groupedStagiaires as $filiereLabel => $filiereStagiaires): ?>
                        <optgroup label="<?= e($filiereLabel) ?>">
                            <?php foreach ($filiereStagiaires as $stagiaire): ?>
                                <option value="<?= (int) $stagiaire['id'] ?>"><?= e($stagiaire['last_name'] . ' ' . $stagiaire['first_name']) ?> — <?= e($stagiaire['group_code']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="absence-date">Date <span class="required">*</span></label>
                    <input id="absence-date" type="date" name="date" value="<?= e($today) ?>" max="<?= e($today) ?>" required>
                </div>

                <div class="field">
                    <label for="absence-slot">Créneau <span class="required">*</span></label>
                    <select id="absence-slot" name="slot" required>
                        <?php foreach ($slots as $slot => $label): ?>
                            <option value="<?= (int) $slot ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="absence-reason">Motif <span class="muted">(facultatif)</span></label>
                <input id="absence-reason" type="text" name="reason" maxlength="255" placeholder="Maladie, rendez-vous, retard…">
            </div>
        </div>

        <footer class="dialog-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Annuler</button>
            <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Enregistrer l’absence</button>
        </footer>
    </form>
</dialog>

<dialog class="dialog" id="justify-dialog" aria-labelledby="justify-dialog-title">
    <form class="dialog-form" method="post" data-justify-form>
        <?= csrf_field() ?>
        <header class="dialog-head">
            <div>
                <p class="eyebrow">Justification</p>
                <h3 id="justify-dialog-title">Justifier une absence</h3>
                <p class="muted"><strong data-justify-name></strong> · <span data-justify-date></span></p>
            </div>
            <button class="icon-btn" type="button" data-close-dialog aria-label="Fermer"><?= icon('x') ?></button>
        </header>

        <div class="dialog-body">
            <div class="field">
                <label for="justification">Justification <span class="muted">(facultatif)</span></label>
                <textarea id="justification" name="justification" rows="3" maxlength="255" placeholder="Certificat médical, convocation, justificatif signé…"></textarea>
                <p class="field-hint">La note du stagiaire sera augmentée de <?= e(note_fr(config('academic.absence_penalty', 0.25))) ?> point, dans la limite de 20.</p>
            </div>
        </div>

        <footer class="dialog-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Annuler</button>
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> Valider la justification</button>
        </footer>
    </form>
</dialog>
