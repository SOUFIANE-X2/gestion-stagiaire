<?php
/** @var string $content */
$pageTitle = $pageTitle ?? 'Rapport';
$appName = (string) config('app.name', 'INARAIN');
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> · <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('inarain.theme');
                document.documentElement.dataset.theme = theme === 'dark' ? 'dark' : 'light';
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
</head>
<body class="print-body">
<?php require __DIR__ . '/partials/icons.php'; ?>
<div class="print-toolbar">
    <a class="btn btn-ghost" href="<?= e(url('reports')) ?>"><?= icon('arrow-left') ?> Retour aux rapports</a>
    <button class="btn btn-primary" type="button" data-print><?= icon('printer') ?> Imprimer / PDF</button>
</div>

<main class="print-sheet" id="main">
    <?= $content ?>
</main>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
