<?php
/** @var string $content */
$pageTitle = $pageTitle ?? 'Connexion';
$appName = (string) config('app.name', 'INARAIN');
$flashes = $flashes ?? [];
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#0f172a">
    <title><?= e($pageTitle) ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('inarain.theme');
                if (theme !== 'light' && theme !== 'dark') {
                    theme = 'light';
                }
                document.documentElement.dataset.theme = theme;
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
</head>
<body class="auth-body">
<?php require __DIR__ . '/partials/icons.php'; ?>
<a class="skip-link" href="#main">Aller au contenu principal</a>

<main class="auth-shell" id="main">
    <section class="auth-brand" aria-label="Présentation">
        <div class="auth-brand-top">
            <span class="brand-mark brand-mark-lg" aria-hidden="true">IN</span>
            <div>
                <strong><?= e($appName) ?></strong>
                <small><?= e(config('app.tagline', 'Gestion des stagiaires')) ?></small>
            </div>
        </div>

        <div class="auth-pitch">
            <p class="eyebrow">Espace administration</p>
            <h1>Suivez vos stagiaires, leurs notes et leurs absences.</h1>
            <p>Une plateforme claire pour piloter les groupes, enregistrer les absences et générer les rapports de fin de module.</p>
        </div>

        <ul class="auth-points">
            <li><span><?= icon('users') ?></span> Gestion complète des stagiaires et des groupes</li>
            <li><span><?= icon('calendar-x') ?></span> Absences, justification et impact automatique sur la note</li>
            <li><span><?= icon('file') ?></span> Rapports imprimables et exports CSV</li>
        </ul>

        <p class="auth-footnote"><?= icon('shield') ?> Accès protégé par session, jeton CSRF et mots de passe hachés.</p>
    </section>

    <section class="auth-panel">
        <div class="auth-panel-inner">
            <?php if ($flashes !== []): ?>
                <div class="flash-stack" role="status" aria-live="polite">
                    <?php foreach ($flashes as $flash): ?>
                        <div class="alert alert-<?= e($flash['type']) ?>" data-dismissible>
                            <?= icon($flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'alert' : 'info')) ?>
                            <p><?= e($flash['message']) ?></p>
                            <button class="alert-close" type="button" data-dismiss aria-label="Fermer"><?= icon('x') ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
        <p class="auth-copyright"><?= e($appName) ?> · PHP 8 &amp; MySQL · <?= date('Y') ?></p>
    </section>
</main>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
