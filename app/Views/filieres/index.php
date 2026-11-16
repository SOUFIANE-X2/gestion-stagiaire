<?php
use App\Core\Csrf;
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Structure pédagogique</p>
        <h2>Filières &amp; groupes</h2>
        <p class="muted">Organisez les filières et les groupes utilisés par les stagiaires.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-ghost" type="button" data-open-dialog="filiere-dialog"><?= icon('plus') ?> Nouvelle filière</button>
        <button class="btn btn-primary" type="button" data-open-dialog="group-dialog"><?= icon('plus') ?> Nouveau groupe</button>
    </div>
</section>

<?php if ($filieres === []): ?>
    <section class="card empty-state empty-state-lg">
        <?= icon('layers') ?>
        <h3>Aucune filière</h3>
        <p>Créez une filière puis un groupe pour commencer à inscrire des stagiaires.</p>
        <button class="btn btn-primary" type="button" data-open-dialog="filiere-dialog"><?= icon('plus') ?> Créer une filière</button>
    </section>
<?php else: ?>
    <div class="filiere-grid">
        <?php foreach ($filieres as $filiere): ?>
            <article class="card filiere-card">
                <div class="filiere-head">
                    <span class="code-chip"><?= e($filiere['code']) ?></span>
                    <?php if ((int) $filiere['stagiaire_count'] === 0): ?>
                        <form action="<?= e(url('filieres/' . $filiere['id'] . '/delete')) ?>" method="post" data-confirm="Supprimer la filière <?= e($filiere['label']) ?> ? Ses groupes vides seront aussi supprimés.">
                            <?= csrf_field() ?>
                            <button class="icon-btn icon-btn-sm icon-btn-danger" type="submit" title="Supprimer la filière" aria-label="Supprimer la filière <?= e($filiere['label']) ?>"><?= icon('trash') ?></button>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-muted" title="Cette filière contient encore des stagiaires"><?= icon('shield') ?> Protégée</span>
                    <?php endif; ?>
                </div>
                <h3><?= e($filiere['label']) ?></h3>
                <div class="filiere-stats">
                    <span><strong><?= (int) $filiere['group_count'] ?></strong> groupe<?= (int) $filiere['group_count'] > 1 ? 's' : '' ?></span>
                    <span><strong><?= (int) $filiere['stagiaire_count'] ?></strong> stagiaire<?= (int) $filiere['stagiaire_count'] > 1 ? 's' : '' ?></span>
                    <span><strong><?= e(note_fr($filiere['average_note'])) ?></strong>/20</span>
                </div>
                <div class="progress progress-sm"><span style="width: <?= e((string) round(((float) $filiere['average_note'] / 20) * 100, 1)) ?>%"></span></div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="card card-flush">
    <div class="card-head card-head-padded">
        <div>
            <h3>Groupes</h3>
            <p class="muted"><?= count($groups) ?> groupe<?= count($groups) > 1 ? 's' : '' ?> au total.</p>
        </div>
    </div>

    <?php if ($groups === []): ?>
        <div class="empty-state">
            <?= icon('users') ?>
            <h3>Aucun groupe</h3>
            <p>Créez un groupe rattaché à une filière.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Groupe</th>
                        <th scope="col">Filière</th>
                        <th scope="col">Année</th>
                        <th scope="col">Stagiaires</th>
                        <th scope="col">Moyenne</th>
                        <th scope="col" class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td>
                                <span class="table-strong"><?= e($group['code']) ?></span>
                                <span class="table-sub"><?= e($group['label']) ?></span>
                            </td>
                            <td><?= e($group['filiere_label']) ?></td>
                            <td><?= e($group['academic_year']) ?></td>
                            <td><?= (int) $group['stagiaire_count'] ?></td>
                            <td><span class="note note-<?= e(note_tone($group['average_note'])) ?>"><?= e(note_fr($group['average_note'])) ?></span></td>
                            <td class="td-actions">
                                <?php if ((int) $group['stagiaire_count'] === 0): ?>
                                    <form action="<?= e(url('groupes/' . $group['id'] . '/delete')) ?>" method="post" data-confirm="Supprimer le groupe <?= e($group['code']) ?> ?">
                                        <?= csrf_field() ?>
                                        <button class="icon-btn icon-btn-sm icon-btn-danger" type="submit" title="Supprimer le groupe" aria-label="Supprimer le groupe <?= e($group['code']) ?>"><?= icon('trash') ?></button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<dialog class="dialog" id="filiere-dialog" aria-labelledby="filiere-dialog-title">
    <form class="dialog-form" action="<?= e(url('filieres')) ?>" method="post">
        <?= csrf_field() ?>
        <header class="dialog-head">
            <div>
                <p class="eyebrow">Structure</p>
                <h3 id="filiere-dialog-title">Nouvelle filière</h3>
            </div>
            <button class="icon-btn" type="button" data-close-dialog aria-label="Fermer"><?= icon('x') ?></button>
        </header>
        <div class="dialog-body form-stack">
            <div class="form-grid">
                <div class="field">
                    <label for="filiere-code">Code <span class="required">*</span></label>
                    <input id="filiere-code" type="text" name="code" maxlength="20" placeholder="DD" required>
                    <p class="field-hint">2 à 20 lettres, chiffres ou tirets.</p>
                </div>
                <div class="field">
                    <label for="filiere-label">Libellé <span class="required">*</span></label>
                    <input id="filiere-label" type="text" name="label" maxlength="120" placeholder="Développement Digital" required>
                </div>
            </div>
        </div>
        <footer class="dialog-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Annuler</button>
            <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Créer la filière</button>
        </footer>
    </form>
</dialog>

<dialog class="dialog" id="group-dialog" aria-labelledby="group-dialog-title">
    <form class="dialog-form" action="<?= e(url('groupes')) ?>" method="post">
        <?= csrf_field() ?>
        <header class="dialog-head">
            <div>
                <p class="eyebrow">Structure</p>
                <h3 id="group-dialog-title">Nouveau groupe</h3>
            </div>
            <button class="icon-btn" type="button" data-close-dialog aria-label="Fermer"><?= icon('x') ?></button>
        </header>
        <div class="dialog-body form-stack">
            <div class="form-grid">
                <div class="field">
                    <label for="group-code">Code <span class="required">*</span></label>
                    <input id="group-code" type="text" name="code" maxlength="20" placeholder="DD-103" required>
                </div>
                <div class="field">
                    <label for="group-label">Libellé <span class="required">*</span></label>
                    <input id="group-label" type="text" name="label" maxlength="120" placeholder="Développement Digital — G3" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="group-filiere">Filière <span class="required">*</span></label>
                    <select id="group-filiere" name="filiere_id" required>
                        <option value="">Sélectionner…</option>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?= (int) $filiere['id'] ?>"><?= e($filiere['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="group-year">Année académique <span class="required">*</span></label>
                    <input id="group-year" type="text" name="academic_year" value="<?= e(date('Y') . '-' . ((int) date('Y') + 1)) ?>" pattern="\d{4}-\d{4}" required>
                </div>
            </div>
        </div>
        <footer class="dialog-foot">
            <button class="btn btn-ghost" type="button" data-close-dialog>Annuler</button>
            <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Créer le groupe</button>
        </footer>
    </form>
</dialog>
