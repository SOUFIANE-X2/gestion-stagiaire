<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\Filiere;

final class FilieresController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function index(): void
    {
        View::render('filieres/index', [
            'pageTitle' => 'Filières & groupes',
            'filieres' => Filiere::allWithStats(),
            'groups' => Filiere::groups(),
        ]);
    }

    public function storeFiliere(): void
    {
        $code = mb_strtoupper(trim((string) request('code')));
        $label = trim((string) request('label'));

        if (preg_match('/^[A-Z0-9\-]{2,20}$/', $code) !== 1) {
            Flash::set('error', 'Le code de la filière doit contenir 2 à 20 lettres, chiffres ou tirets.');
            redirect('filieres');
        }

        if (mb_strlen($label) < 3 || mb_strlen($label) > 120) {
            Flash::set('error', 'Le libellé de la filière doit contenir entre 3 et 120 caractères.');
            redirect('filieres');
        }

        if (Filiere::codeExists($code)) {
            Flash::set('error', 'Ce code de filière existe déjà.');
            redirect('filieres');
        }

        Filiere::create($code, $label);
        Flash::set('success', 'La filière « ' . $label . ' » a été créée.');
        redirect('filieres');
    }

    public function destroyFiliere(string $id): void
    {
        $filiere = Filiere::find((int) $id);

        if (!$filiere) {
            Flash::set('error', 'Filière introuvable.');
            redirect('filieres');
        }

        if (!Filiere::delete((int) $filiere['id'])) {
            Flash::set('error', 'Impossible de supprimer une filière qui contient encore des stagiaires.');
            redirect('filieres');
        }

        Flash::set('success', 'La filière a été supprimée.');
        redirect('filieres');
    }

    public function storeGroup(): void
    {
        $code = mb_strtoupper(trim((string) request('code')));
        $label = trim((string) request('label'));
        $filiereId = (int) request('filiere_id');
        $academicYear = trim((string) request('academic_year', '2025-2026'));

        if (preg_match('/^[A-Z0-9\-]{2,20}$/', $code) !== 1) {
            Flash::set('error', 'Le code du groupe doit contenir 2 à 20 lettres, chiffres ou tirets.');
            redirect('filieres');
        }

        if (mb_strlen($label) < 2 || mb_strlen($label) > 120) {
            Flash::set('error', 'Le libellé du groupe doit contenir entre 2 et 120 caractères.');
            redirect('filieres');
        }

        if (!Filiere::find($filiereId)) {
            Flash::set('error', 'Sélectionnez une filière valide.');
            redirect('filieres');
        }

        if (preg_match('/^\d{4}-\d{4}$/', $academicYear) !== 1) {
            Flash::set('error', 'L’année académique doit suivre le format 2025-2026.');
            redirect('filieres');
        }

        if (Filiere::groupCodeExists($code)) {
            Flash::set('error', 'Ce code de groupe existe déjà.');
            redirect('filieres');
        }

        Filiere::createGroup($code, $label, $filiereId, $academicYear);
        Flash::set('success', 'Le groupe « ' . $label . ' » a été créé.');
        redirect('filieres');
    }

    public function destroyGroup(string $id): void
    {
        $group = Filiere::findGroup((int) $id);

        if (!$group) {
            Flash::set('error', 'Groupe introuvable.');
            redirect('filieres');
        }

        if (!Filiere::deleteGroup((int) $group['id'])) {
            Flash::set('error', 'Impossible de supprimer un groupe qui contient encore des stagiaires.');
            redirect('filieres');
        }

        Flash::set('success', 'Le groupe a été supprimé.');
        redirect('filieres');
    }
}
