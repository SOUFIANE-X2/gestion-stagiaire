<?php
use App\Core\Csrf;

$old = $old ?? [];
$errors = $errors ?? [];
?>
<div class="auth-card">
    <p class="eyebrow">Nouveau compte</p>
    <h2>Créer un accès</h2>
    <p class="muted">Le compte créé pourra gérer les stagiaires, les absences et les rapports.</p>

    <form action="<?= e(url('register')) ?>" method="post" class="form-stack">
        <?= Csrf::field() ?>

        <div class="form-grid">
            <div class="field">
                <label for="first_name">Prénom</label>
                <input id="first_name" name="first_name" type="text" value="<?= old($old, 'first_name') ?>" autocomplete="given-name" required>
                <?php if (isset($errors['first_name'])): ?><p class="field-error"><?= e($errors['first_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="last_name">Nom</label>
                <input id="last_name" name="last_name" type="text" value="<?= old($old, 'last_name') ?>" autocomplete="family-name" required>
                <?php if (isset($errors['last_name'])): ?><p class="field-error"><?= e($errors['last_name']) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="field">
            <label for="matricule">Matricule</label>
            <input id="matricule" name="matricule" type="text" value="<?= old($old, 'matricule') ?>" placeholder="ADM-002" autocomplete="username" required>
            <?php if (isset($errors['matricule'])): ?><p class="field-error"><?= e($errors['matricule']) ?></p><?php endif; ?>
        </div>

        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="<?= old($old, 'email') ?>" placeholder="vous@inarain.ma" autocomplete="email" required>
            <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required data-password-input>
                <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
                <p class="field-hint">8 caractères minimum.</p>
            </div>

            <div class="field">
                <label for="password_confirm">Confirmation</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" required>
                <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
            </div>
        </div>

        <button class="btn btn-primary btn-block btn-lg" type="submit">Créer mon compte</button>
    </form>

    <p class="auth-switch">Vous avez déjà un compte ? <a href="<?= e(url('login')) ?>">Se connecter</a></p>
</div>
