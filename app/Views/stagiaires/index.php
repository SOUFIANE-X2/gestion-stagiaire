<?php
$statusLabels = ['active' => 'Actif', 'graduated' => 'Diplômé', 'archived' => 'Archivé'];
$statusBadges = ['active' => 'success', 'graduated' => 'info', 'archived' => 'muted'];
$hasFilters = $filters['search'] !== '' || $filters['filiere'] > 0 || $filters['group'] > 0 || $filters['status'] !== '';
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Gestion</p>
        <h2>Stagiaires</h2>
        <p class="muted"><?= (int) $result['total'] ?> stagiaire<?= (int) $result['total'] > 1 ? 's' : '' ?> correspondant à votre recherche.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('reports/stagiaires.csv')) ?>"><?= icon('download') ?> Exporter CSV</a>
        <a class="btn btn-primary" href="<?= e(url('stagiaires/create')) ?>"><?= icon('plus') ?> Nouveau stagiaire</a>
    </div>
</section>

<section class="card filters-card">
    <form class="filters" method="get" action="<?= e(url('stagiaires')) ?>">
        <div class="filter-field filter-grow">
            <label for="filter-search">Recherche</label>
            <div class="input-icon">
                <?= icon('search') ?>
                <input id="filter-search" type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Nom, code ou e-mail…">
            </div>
        </div>

        <div class="filter-field">
            <label for="filter-filiere">Filière</label>
            <select id="filter-filiere" name="filiere" data-filter-filiere>
                <option value="">Toutes les filières</option>
                <?php foreach ($filieres as $filiere): ?>
                    <option value="<?= (int) $filiere['id'] ?>"<?= (int) $filters['filiere'] === (int) $filiere['id'] ? ' selected' : '' ?>><?= e($filiere['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="filter-group">Groupe</label>
            <select id="filter-group" name="group" data-filter-group>
                <option value="">Tous les groupes</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int) $group['id'] ?>" data-filiere="<?= (int) $group['filiere_id'] ?>"<?= (int) $filters['group'] === (int) $group['id'] ? ' selected' : '' ?>><?= e($group['code']) ?> — <?= e($group['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="filter-status">Statut</label>
            <select id="filter-status" name="status">
                <option value="">Tous les statuts</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>"<?= $filters['status'] === $status ? ' selected' : '' ?>><?= e($statusLabels[$status] ?? $status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="filter-sort">Trier par</label>
            <select id="filter-sort" name="sort">
                <option value="name"<?= $filters['sort'] === 'name' ? ' selected' : '' ?>>Nom (A → Z)</option>
                <option value="name_desc"<?= $filters['sort'] === 'name_desc' ? ' selected' : '' ?>>Nom (Z → A)</option>
                <option value="note"<?= $filters['sort'] === 'note' ? ' selected' : '' ?>>Meilleure note</option>
                <option value="note_asc"<?= $filters['sort'] === 'note_asc' ? ' selected' : '' ?>>Note la plus basse</option>
                <option value="recent"<?= $filters['sort'] === 'recent' ? ' selected' : '' ?>>Ajout récent</option>
                <option value="code"<?= $filters['sort'] === 'code' ? ' selected' : '' ?>>Code</option>
            </select>
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Filtrer</button>
            <?php if ($hasFilters): ?>
                <a class="btn btn-ghost" href="<?= e(url('stagiaires')) ?>">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card card-flush">
    <?php if ($result['rows'] === []): ?>
        <div class="empty-state empty-state-lg">
            <?= icon('users') ?>
            <h3>Aucun stagiaire trouvé</h3>
            <p>Modifiez vos filtres ou ajoutez votre premier stagiaire.</p>
            <div class="empty-actions">
                <?php if ($hasFilters): ?><a class="btn btn-ghost" href="<?= e(url('stagiaires')) ?>">Effacer les filtres</a><?php endif; ?>
                <a class="btn btn-primary" href="<?= e(url('stagiaires/create')) ?>"><?= icon('plus') ?> Ajouter un stagiaire</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Stagiaire</th>
                        <th scope="col">Groupe</th>
                        <th scope="col">Note</th>
                        <th scope="col">Absences</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $stagiaire): ?>
                        <?php
                        $note = (float) $stagiaire['note'];
                        $noteWidth = max(0, min(100, ($note / 20) * 100));
                        $open = (int) $stagiaire['absence_open'];
                        ?>
                        <tr>
                            <td>
                                <div class="person">
                                    <span class="avatar"><?= e(initials($stagiaire['first_name'], $stagiaire['last_name'])) ?></span>
                                    <div class="person-copy">
                                        <a class="person-name" href="<?= e(url('stagiaires/' . $stagiaire['id'])) ?>"><?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?></a>
                                        <span class="person-meta"><?= e($stagiaire['code']) ?> · <?= $stagiaire['gender'] === 'F' ? 'Femme' : 'Homme' ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="table-strong"><?= e($stagiaire['group_code']) ?></span>
                                <span class="table-sub"><?= e($stagiaire['filiere_label']) ?></span>
                            </td>
                            <td>
                                <div class="note-cell">
                                    <span class="note note-<?= e(note_tone($note)) ?>"><?= e(note_fr($note)) ?></span>
                                    <span class="mini-track"><span class="mini-fill mini-<?= e(note_tone($note)) ?>" style="width: <?= e((string) round($noteWidth, 1)) ?>%"></span></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($open > 0): ?>
                                    <span class="badge badge-warning"><?= $open ?> à justifier</span>
                                <?php elseif ((int) $stagiaire['absence_total'] > 0): ?>
                                    <span class="badge badge-success"><?= (int) $stagiaire['absence_total'] ?> justifiée<?= (int) $stagiaire['absence_total'] > 1 ? 's' : '' ?></span>
                                <?php else: ?>
                                    <span class="badge badge-muted">Aucune</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($statusBadges[$stagiaire['status']] ?? 'muted') ?>"><?= e($statusLabels[$stagiaire['status']] ?? $stagiaire['status']) ?></span>
                            </td>
                            <td class="td-actions">
                                <a class="icon-btn icon-btn-sm" href="<?= e(url('stagiaires/' . $stagiaire['id'])) ?>" title="Voir la fiche" aria-label="Voir la fiche de <?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?>"><?= icon('eye') ?></a>
                                <a class="icon-btn icon-btn-sm" href="<?= e(url('stagiaires/' . $stagiaire['id'] . '/edit')) ?>" title="Modifier" aria-label="Modifier <?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?>"><?= icon('edit') ?></a>
                                <form action="<?= e(url('stagiaires/' . $stagiaire['id'] . '/delete')) ?>" method="post" data-confirm="Supprimer <?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?> ? Son historique d’absences sera également supprimé.">
                                    <?= csrf_field() ?>
                                    <button class="icon-btn icon-btn-sm icon-btn-danger" type="submit" title="Supprimer" aria-label="Supprimer <?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?>"><?= icon('trash') ?></button>
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
