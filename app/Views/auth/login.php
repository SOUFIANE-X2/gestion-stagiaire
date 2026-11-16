<?php
use App\Core\Csrf;
?>
<div class="auth-card">
    <p class="eyebrow">Connexion</p>
    <h2>Ravi de vous revoir</h2>
    <p class="muted">Entrez vos identifiants pour accéder au tableau de bord.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert">
            <?= icon('alert') ?>
            <p><?= e($error) ?></p>
        </div>
    <?php endif; ?>

    <form action="<?= e(url('login')) ?>" method="post" class="form-stack">
        <?= Csrf::field() ?>

        <div class="field">
            <label for="matricule">Matricule</label>
            <div class="input-icon">
                <?= icon('user') ?>
                <input id="matricule" name="matricule" type="text" value="<?= e((string) ($matricule ?? '')) ?>" autocomplete="username" required autofocus>
            </div>
        </div>

        <div class="field">
            <label for="password">Mot de passe</label>
            <div class="input-icon">
                <?= icon('shield') ?>
                <input id="password" name="password" type="password" autocomplete="current-password" required data-password-input>
                <button class="input-action" type="button" data-toggle-password aria-label="Afficher le mot de passe" aria-controls="password">
                    <?= icon('eye') ?>
                </button>
            </div>
        </div>

        <button class="btn btn-primary btn-block btn-lg" type="submit">Se connecter</button>
    </form>

    <p class="auth-switch">Pas encore de compte ? <a href="<?= e(url('register')) ?>">Créer un compte</a></p>
    <p class="auth-hint"><?= icon('info') ?> Les identifiants de démonstration sont fournis dans le fichier README du projet.</p>
</div>
