<?php
$pageTitle = 'Méthode non autorisée';
?>
<section class="error-page">
    <p class="error-code">405</p>
    <h2>Méthode non autorisée</h2>
    <p class="muted">Cette action n’accepte pas la méthode utilisée par votre navigateur.</p>
    <a class="btn btn-primary" href="<?= e(url()) ?>"><?= icon('arrow-left') ?> Retour au tableau de bord</a>
</section>
