<?php
$statusLabels = ['active' => 'Actif', 'graduated' => 'Diplômé', 'archived' => 'Archivé'];
$statusBadges = ['active' => 'success', 'graduated' => 'info', 'archived' => 'muted'];
$note = (float) $stagiaire['note'];
$noteWidth = max(0, min(100, ($note / 20) * 100));
$justified = (int) $stagiaire['absence_total'] - (int) $stagiaire['absence_open'];
$isAdmin = ($admin['role'] ?? '') === 'admin';
?>
<section class="page-head">
    <div>
        <a class="back-link" href="<?= e(url('stagiaires')) ?>"><?= icon('arrow-left') ?> Tous les stagiaires</a>
        <p class="eyebrow">Fiche stagiaire · <?= e($stagiaire['code']) ?></p>
        <h2><?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?></h2>
        <p class="muted"><?= e($stagiaire['group_code']) ?> · <?= e($stagiaire['filiere_label']) ?> · <?= e($stagiaire['academic_year']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('absences') . '?search=' . urlencode($stagiaire['code'])) ?>"><?= icon('calendar-x') ?> Gérer les absences</a>
        <a class="btn btn-primary" href="<?= e(url('stagiaires/' . $stagiaire['id'] . '/edit')) ?>"><?= icon('edit') ?> Modifier</a>
        <?php if ($isAdmin): ?>
        <form action="<?= e(url('stagiaires/' . $stagiaire['id'] . '/delete')) ?>" method="post" data-confirm="Supprimer <?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?> ? Son historique d’absences sera également supprimé.">
            <?= csrf_field() ?>
            <button class="btn btn-danger-ghost" type="submit"><?= icon('trash') ?> Supprimer</button>
        </form>
        <?php endif; ?>
    </div>
</section>
<div class="profile-grid">
    <section class="card profile-card">
        <div class="profile-hero">
            <span class="avatar avatar-xl"><?= e(initials($stagiaire['first_name'], $stagiaire['last_name'])) ?></span>
            <div>
                <h3><?= e($stagiaire['first_name'] . ' ' . $stagiaire['last_name']) ?></h3>
                <p class="muted"><?= e($stagiaire['code']) ?> · <?= $stagiaire['gender'] === 'F' ? 'Femme' : 'Homme' ?></p>
                <span class="badge badge-<?= e($statusBadges[$stagiaire['status']] ?? 'muted') ?>"><?= e($statusLabels[$stagiaire['status']] ?? $stagiaire['status']) ?></span>
            </div>
        </div>

        <dl class="detail-list">
            <div>
                <dt><?= icon('mail') ?> E-mail</dt>
                <dd><?= $stagiaire['email'] ? '<a href="mailto:' . e($stagiaire['email']) . '">' . e($stagiaire['email']) . '</a>' : '<span class="muted">Non renseigné</span>' ?></dd>
            </div>
            <div>
                <dt><?= icon('phone') ?> Téléphone</dt>
                <dd><?= $stagiaire['phone'] ? '<a href="tel:' . e($stagiaire['phone']) . '">' . e($stagiaire['phone']) . '</a>' : '<span class="muted">Non renseigné</span>' ?></dd>
            </div>
            <div>
                <dt><?= icon('layers') ?> Filière</dt>
                <dd><?= e($stagiaire['filiere_label']) ?> <span class="muted">(<?= e($stagiaire['filiere_code']) ?>)</span></dd>
            </div>
            <div>
                <dt><?= icon('users') ?> Groupe</dt>
                <dd><?= e($stagiaire['group_label']) ?> <span class="muted">· <?= e($stagiaire['academic_year']) ?></span></dd>
            </div>
            <div>
                <dt><?= icon('clock') ?> Ajouté le</dt>
                <dd><?= e(date_fr($stagiaire['created_at'])) ?></dd>
            </div>
        </dl>
    </section>

    <div class="profile-side">
        <section class="card note-card">
            <div class="card-head">
                <div>
                    <h3>Note actuelle</h3>
                    <p class="muted">Impact des absences inclus.</p>
                </div>
                <span class="badge badge-<?= e(note_tone($note) === 'good' ? 'success' : (note_tone($note) === 'warn' ? 'info' : 'danger')) ?>"><?= e(note_tone($note) === 'good' ? 'Très bien' : (note_tone($note) === 'warn' ? 'Moyen' : 'Insuffisant')) ?></span>
            </div>
            <p class="note-hero"><?= e(note_fr($note)) ?><span>/20</span></p>
            <div class="progress progress-lg" role="img" aria-label="<?= e(note_fr($note)) ?> sur 20">
                <span class="progress-<?= e(note_tone($note)) ?>" style="width: <?= e((string) round($noteWidth, 1)) ?>%"></span>
            </div>
            <p class="field-hint">Chaque absence non justifiée retire <?= e(note_fr(config('academic.absence_penalty', 0.25))) ?> point ; une justification le restitue.</p>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <h3>Assiduité</h3>
                    <p class="muted">Historique complet.</p>
                </div>
            </div>
            <div class="mini-stats">
                <div>
                    <span class="mini-stat-value"><?= (int) $stagiaire['absence_total'] ?></span>
                    <span class="mini-stat-label">Absences</span>
                </div>
                <div>
                    <span class="mini-stat-value text-warning"><?= (int) $stagiaire['absence_open'] ?></span>
                    <span class="mini-stat-label">À justifier</span>
                </div>
                <div>
                    <span class="mini-stat-value text-success"><?= $justified ?></span>
                    <span class="mini-stat-label">Justifiées</span>
                </div>
            </div>
            <p class="field-hint"><?= e(percent($justified, (int) $stagiaire['absence_total'])) ?> des absences sont justifiées.</p>
        </section>
    </div>
</div>

<section class="card card-flush">
    <div class="card-head card-head-padded">
        <div>
            <h3>Historique des absences</h3>
            <p class="muted"><?= count($absences) ?> absence<?= count($absences) > 1 ? 's' : '' ?> enregistrée<?= count($absences) > 1 ? 's' : '' ?>.</p>
        </div>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('absences') . '?search=' . urlencode($stagiaire['code'])) ?>"><?= icon('calendar-x') ?> Justifier une absence</a>
    </div>

    <?php if ($absences === []): ?>
        <div class="empty-state">
            <?= icon('check-circle') ?>
            <h3>Aucune absence</h3>
            <p>Ce stagiaire est assidu. Continuez comme ça.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Créneau</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Motif</th>
                        <th scope="col">Justification</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($absences as $absence): ?>
                        <tr>
                            <td class="table-strong"><?= e(date_fr($absence['date'])) ?></td>
                            <td><?= e(slot_label((int) $absence['slot'])) ?></td>
                            <td>
                                <span class="badge <?= (int) $absence['justified'] === 1 ? 'badge-success' : 'badge-warning' ?>"><?= (int) $absence['justified'] === 1 ? 'Justifiée' : 'À justifier' ?></span>
                            </td>
                            <td><?= $absence['reason'] ? e($absence['reason']) : '<span class="muted">—</span>' ?></td>
                            <td><?= $absence['justification'] ? e($absence['justification']) : '<span class="muted">—</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
