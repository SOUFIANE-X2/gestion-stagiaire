<?php
use App\Core\Csrf;

$mode = $mode ?? 'create';
$errors = $errors ?? [];
$stagiaire = $stagiaire ?? [];
$groups = $groups ?? [];
$action = $mode === 'edit' ? url('stagiaires/' . $stagiaire['id']) : url('stagiaires');
$grouped = [];
foreach ($groups as $group) {
    $grouped[$group['filiere_label']][] = $group;
}
$statusLabels = ['active' => 'Actif', 'graduated' => 'Diplômé', 'archived' => 'Archivé'];
$value = static fn (string $key, string $default = ''): string => (string) ($stagiaire[$key] ?? $default);
?>
<section class="page-head">
    <div>
        <a class="back-link" href="<?= e($mode === 'edit' ? url('stagiaires/' . $stagiaire['id']) : url('stagiaires')) ?>"><?= icon('arrow-left') ?> Retour</a>
        <p class="eyebrow"><?= $mode === 'edit' ? 'Modification' : 'Inscription' ?></p>
        <h2><?= $mode === 'edit' ? 'Modifier le stagiaire' : 'Nouveau stagiaire' ?></h2>
        <p class="muted"><?= $mode === 'edit' ? 'Mettez à jour la fiche et la scolarité.' : 'Renseignez l’identité, les coordonnées et le groupe du stagiaire.' ?></p>
    </div>
    <div class="page-actions">
        <span class="code-chip"><?= icon('cap') ?> <?= e($value('code', 'Automatique')) ?></span>
    </div>
</section>

<form action="<?= e($action) ?>" method="post" class="form-page">
    <?= Csrf::field() ?>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Identité</h3>
                <p class="muted">Informations personnelles du stagiaire.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="first_name">Prénom <span class="required">*</span></label>
                <input id="first_name" name="first_name" type="text" value="<?= old($stagiaire, 'first_name') ?>" autocomplete="given-name" required maxlength="60"<?= isset($errors['first_name']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['first_name'])): ?><p class="field-error"><?= e($errors['first_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="last_name">Nom <span class="required">*</span></label>
                <input id="last_name" name="last_name" type="text" value="<?= old($stagiaire, 'last_name') ?>" autocomplete="family-name" required maxlength="60"<?= isset($errors['last_name']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['last_name'])): ?><p class="field-error"><?= e($errors['last_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="gender">Genre <span class="required">*</span></label>
                <select id="gender" name="gender" required<?= isset($errors['gender']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="F"<?= $value('gender', 'F') === 'F' ? ' selected' : '' ?>>Femme</option>
                    <option value="M"<?= $value('gender') === 'M' ? ' selected' : '' ?>>Homme</option>
                </select>
                <?php if (isset($errors['gender'])): ?><p class="field-error"><?= e($errors['gender']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="status">Statut <span class="required">*</span></label>
                <select id="status" name="status" required<?= isset($errors['status']) ? ' aria-invalid="true"' : '' ?>>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>"<?= $value('status', 'active') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['status'])): ?><p class="field-error"><?= e($errors['status']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Coordonnées</h3>
                <p class="muted">Facultatives, mais utiles pour contacter le stagiaire.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="<?= old($stagiaire, 'email') ?>" autocomplete="email" maxlength="160" placeholder="prenom.nom@inarain.ma"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="phone">Téléphone</label>
                <input id="phone" name="phone" type="tel" value="<?= old($stagiaire, 'phone') ?>" autocomplete="tel" maxlength="30" placeholder="+212 6…"<?= isset($errors['phone']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['phone'])): ?><p class="field-error"><?= e($errors['phone']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <div>
                <h3>Scolarité</h3>
                <p class="muted">Le groupe détermine la filière utilisée dans les rapports.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="group_id">Groupe <span class="required">*</span></label>
                <select id="group_id" name="group_id" required<?= isset($errors['group_id']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="">Sélectionner un groupe…</option>
                    <?php foreach ($grouped as $filiereLabel => $filiereGroups): ?>
                        <optgroup label="<?= e($filiereLabel) ?>">
                            <?php foreach ($filiereGroups as $group): ?>
                                <option value="<?= (int) $group['id'] ?>"<?= (int) $value('group_id') === (int) $group['id'] ? ' selected' : '' ?>><?= e($group['code']) ?> — <?= e($group['label']) ?> (<?= e($group['academic_year']) ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['group_id'])): ?><p class="field-error"><?= e($errors['group_id']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="note">Note sur 20 <span class="required">*</span></label>
                <input id="note" name="note" type="number" min="0" max="20" step="0.25" value="<?= old($stagiaire, 'note', '20') ?>" required<?= isset($errors['note']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['note'])): ?><p class="field-error"><?= e($errors['note']) ?></p><?php endif; ?>
                <p class="field-hint">Chaque absence non justifiée retire 0,25 point.</p>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <a class="btn btn-ghost" href="<?= e($mode === 'edit' ? url('stagiaires/' . $stagiaire['id']) : url('stagiaires')) ?>">Annuler</a>
        <button class="btn btn-primary" type="submit"><?= icon($mode === 'edit' ? 'check' : 'plus') ?> <?= $mode === 'edit' ? 'Enregistrer les modifications' : 'Créer le stagiaire' ?></button>
    </div>
</form>
