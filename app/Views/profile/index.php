<?php
use App\Core\Csrf;

$roleLabels = ['admin' => 'Administrateur', 'manager' => 'Gestionnaire'];
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Compte</p>
        <h2>Mon compte</h2>
        <p class="muted">Gérez vos informations personnelles et votre mot de passe.</p>
    </div>
</section>

<div class="grid grid-2">
    <section class="card">
        <div class="card-head">
            <div>
                <h3>Informations personnelles</h3>
                <p class="muted">Ces informations apparaissent dans l’interface.</p>
            </div>
        </div>

        <div class="profile-hero profile-hero-tight">
            <span class="avatar avatar-lg"><?= e(initials((string) $admin['first_name'], (string) $admin['last_name'])) ?></span>
            <div>
                <h3><?= e($admin['first_name'] . ' ' . $admin['last_name']) ?></h3>
                <p class="muted"><?= e($roleLabels[$admin['role']] ?? $admin['role']) ?> · <?= e($admin['matricule']) ?></p>
            </div>
        </div>

        <form action="<?= e(url('profile')) ?>" method="post" class="form-stack">
            <?= Csrf::field() ?>
            <div class="form-grid">
                <div class="field">
                    <label for="first_name">Prénom</label>
                    <input id="first_name" name="first_name" type="text" value="<?= e($admin['first_name']) ?>" maxlength="60" required>
                </div>
                <div class="field">
                    <label for="last_name">Nom</label>
                    <input id="last_name" name="last_name" type="text" value="<?= e($admin['last_name']) ?>" maxlength="60" required>
                </div>
            </div>
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="<?= e($admin['email']) ?>" maxlength="160" required>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="matricule-readonly">Matricule</label>
                    <input id="matricule-readonly" type="text" value="<?= e($admin['matricule']) ?>" readonly>
                    <p class="field-hint">Le matricule ne peut pas être modifié.</p>
                </div>
                <div class="field">
                    <label for="role-readonly">Rôle</label>
                    <input id="role-readonly" type="text" value="<?= e($roleLabels[$admin['role']] ?? $admin['role']) ?>" readonly>
                </div>
            </div>
            <div class="form-actions form-actions-inline">
                <button class="btn btn-primary" type="submit"><?= icon('check') ?> Enregistrer</button>
            </div>
        </form>
    </section>

    <div class="stack">
        <section class="card">
            <div class="card-head">
                <div>
                    <h3>Mot de passe</h3>
                    <p class="muted">Choisissez un mot de passe d’au moins 8 caractères.</p>
                </div>
            </div>

            <form action="<?= e(url('profile/password')) ?>" method="post" class="form-stack">
                <?= Csrf::field() ?>
                <div class="field">
                    <label for="current_password">Mot de passe actuel</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input id="new_password" name="password" type="password" autocomplete="new-password" minlength="8" required data-password-input>
                    </div>
                    <div class="field">
                        <label for="new_password_confirm">Confirmation</label>
                        <input id="new_password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <div class="form-actions form-actions-inline">
                    <button class="btn btn-primary" type="submit"><?= icon('shield') ?> Modifier le mot de passe</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <h3>Activité du compte</h3>
                </div>
            </div>
            <dl class="detail-list detail-list-tight">
                <div>
                    <dt><?= icon('clock') ?> Dernière connexion</dt>
                    <dd><?= $admin['last_login_at'] ? e(date_fr($admin['last_login_at']) . ' · ' . date('H:i', strtotime($admin['last_login_at']))) : 'Première connexion' ?></dd>
                </div>
                <div>
                    <dt><?= icon('calendar') ?> Compte créé le</dt>
                    <dd><?= e(date_fr($admin['created_at'])) ?></dd>
                </div>
                <div>
                    <dt><?= icon('shield') ?> Sécurité</dt>
                    <dd>Session protégée · jeton CSRF · mot de passe haché</dd>
                </div>
            </dl>
        </section>
    </div>
</div>
