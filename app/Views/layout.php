<?php
/** @var string $content */
$pageTitle = $pageTitle ?? 'Tableau de bord';
$admin = $admin ?? [];
$flashes = $flashes ?? [];
$appName = (string) config('app.name', 'INARAIN');
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
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.dataset.theme = theme;
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu principal</a>

<?php require __DIR__ . '/partials/icons.php'; ?>

<div class="app-shell">
    <aside class="sidebar" id="sidebar" aria-label="Navigation principale">
        <a class="brand" href="<?= e(url()) ?>">
            <span class="brand-mark" aria-hidden="true">IN</span>
            <span class="brand-copy">
                <strong><?= e($appName) ?></strong>
                <small><?= e(config('app.tagline', 'Gestion des stagiaires')) ?></small>
            </span>
        </a>

        <nav class="nav">
            <p class="nav-label">Pilotage</p>
            <a class="nav-link<?= is_active('/') ? ' is-active' : '' ?>" href="<?= e(url()) ?>"<?= is_active('/') ? ' aria-current="page"' : '' ?>>
                <?= icon('dashboard') ?><span>Tableau de bord</span>
            </a>
            <a class="nav-link<?= is_active('/absences') ? ' is-active' : '' ?>" href="<?= e(url('absences')) ?>"<?= is_active('/absences') ? ' aria-current="page"' : '' ?>>
                <?= icon('calendar-x') ?><span>Absences</span>
            </a>
            <a class="nav-link<?= is_active('/reports') ? ' is-active' : '' ?>" href="<?= e(url('reports')) ?>"<?= is_active('/reports') ? ' aria-current="page"' : '' ?>>
                <?= icon('file') ?><span>Rapports</span>
            </a>

            <p class="nav-label">Gestion</p>
            <a class="nav-link<?= is_active('/stagiaires') ? ' is-active' : '' ?>" href="<?= e(url('stagiaires')) ?>"<?= is_active('/stagiaires') ? ' aria-current="page"' : '' ?>>
                <?= icon('users') ?><span>Stagiaires</span>
            </a>
            <a class="nav-link<?= is_active('/filieres') ? ' is-active' : '' ?>" href="<?= e(url('filieres')) ?>"<?= is_active('/filieres') ? ' aria-current="page"' : '' ?>>
                <?= icon('layers') ?><span>Filières &amp; groupes</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a class="user-card" href="<?= e(url('profile')) ?>">
                <span class="avatar"><?= e(initials((string) ($admin['first_name'] ?? ''), (string) ($admin['last_name'] ?? ''))) ?></span>
                <span class="user-copy">
                    <strong><?= e(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')) ?></strong>
                    <small><?= e(ucfirst((string) ($admin['role'] ?? 'manager'))) ?> · <?= e((string) ($admin['matricule'] ?? '')) ?></small>
                </span>
            </a>
            <form action="<?= e(url('logout')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-block" type="submit"><?= icon('logout') ?><span>Se déconnecter</span></button>
            </form>
        </div>
    </aside>

    <button class="sidebar-backdrop" type="button" data-close-sidebar aria-label="Fermer la navigation" tabindex="-1"></button>

    <div class="workspace">
        <header class="topbar">
            <button class="icon-btn menu-toggle" type="button" data-toggle-sidebar aria-label="Ouvrir la navigation" aria-controls="sidebar" aria-expanded="false">
                <?= icon('menu') ?>
            </button>

            <div class="topbar-title">
                <span class="topbar-eyebrow"><?= e($appName) ?></span>
                <h1><?= e($pageTitle) ?></h1>
            </div>

            <form class="topbar-search" action="<?= e(url('stagiaires')) ?>" method="get" role="search">
                <?= icon('search') ?>
                <label class="sr-only" for="global-search">Rechercher un stagiaire</label>
                <input id="global-search" type="search" name="search" placeholder="Rechercher un stagiaire…" value="<?= is_active('/stagiaires') ? e((string) request('search')) : '' ?>">
            </form>

            <div class="topbar-actions">
                <button class="icon-btn theme-toggle" type="button" data-toggle-theme aria-label="Changer de thème" aria-pressed="false">
                    <span class="theme-icon theme-icon-light"><?= icon('sun') ?></span>
                    <span class="theme-icon theme-icon-dark"><?= icon('moon') ?></span>
                </button>
                <a class="icon-btn" href="<?= e(url('profile')) ?>" aria-label="Mon compte"><?= icon('user') ?></a>
            </div>
        </header>

        <main class="main" id="main">
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
        </main>

        <footer class="app-footer">
            <span><?= e($appName) ?> · <?= e(config('app.tagline', '')) ?></span>
            <span>PHP 8 &amp; MySQL · <?= date('Y') ?></span>
        </footer>
    </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
