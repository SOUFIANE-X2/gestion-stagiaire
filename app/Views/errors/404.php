<?php
$pageTitle = 'Page introuvable';
?>
<section class="error-page">
    <p class="error-code">404</p>
    <h2>Cette page n’existe pas</h2>
    <p class="muted">Le lien est peut-être obsolète, ou la ressource a été supprimée.</p>
    <a class="btn btn-primary" href="<?= e(url()) ?>"><?= icon('arrow-left') ?> Retour au tableau de bord</a>
</section>
