<?php
$pageTitle = 'Session expirée';
?>
<section class="error-page">
    <p class="error-code">419</p>
    <h2>Votre session a expiré</h2>
    <p class="muted">Le formulaire a été ouvert trop longtemps ou la session a été réinitialisée. Rechargez la page puis réessayez.</p>
    <a class="btn btn-primary" href="<?= e(url()) ?>"><?= icon('arrow-left') ?> Revenir à l’application</a>
</section>
