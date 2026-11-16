<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Flash;
use App\Core\View;
use App\Models\Absence;
use App\Models\Filiere;
use App\Models\Stagiaire;

final class AbsencesController
{
    public function index(): void
    {
        $filters = [
            'search' => mb_substr(trim((string) request('search')), 0, 80),
            'filiere' => max(0, (int) request('filiere')),
            'state' => in_array(request('state'), ['open', 'justified'], true) ? (string) request('state') : '',
            'month' => preg_match('/^\d{4}-\d{2}$/', (string) request('month')) === 1 ? (string) request('month') : '',
            'page' => max(1, (int) request('page', 1)),
        ];

        $result = Absence::paginate($filters + ['per_page' => 12]);

        View::render('absences/index', [
            'pageTitle' => 'Absences',
            'result' => $result,
            'filters' => $filters,
            'stats' => Absence::stats(),
            'filieres' => Filiere::all(),
            'stagiaires' => Stagiaire::all(['status' => 'active']),
        ]);
    }

    public function store(): void
    {
        $stagiaireId = (int) request('stagiaire_id');
        $date = trim((string) request('date'));
        $slot = (int) request('slot');
        $reason = mb_substr(trim((string) request('reason')), 0, 255);
        $errors = [];

        $stagiaire = Stagiaire::find($stagiaireId);
        if (!$stagiaire) {
            $errors[] = 'Sélectionnez un stagiaire valide.';
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            $errors[] = 'Saisissez une date valide.';
        } elseif ($parsed > new \DateTimeImmutable('today')) {
            $errors[] = 'La date de l’absence ne peut pas être dans le futur.';
        }

        if (!array_key_exists($slot, config('academic.slots', []))) {
            $errors[] = 'Sélectionnez un créneau valide.';
        }

        if ($errors === [] && Absence::exists($stagiaireId, $date, $slot)) {
            $errors[] = 'Une absence existe déjà pour ce stagiaire à cette date et ce créneau.';
        }

        if ($errors !== []) {
            Flash::set('error', implode(' ', $errors));
            redirect('absences');
        }

        Absence::createWithPenalty($stagiaireId, $date, $slot, $reason);
        $penalty = note_fr(config('academic.absence_penalty', 0.25));

        Flash::set('success', 'Absence enregistrée. La note du stagiaire a été réduite de ' . $penalty . ' point.');
        redirect('absences');
    }

    public function justify(string $id): void
    {
        $absence = Absence::find((int) $id);

        if (!$absence) {
            Flash::set('error', 'Absence introuvable.');
            redirect('absences');
        }

        $justification = mb_substr(trim((string) request('justification')), 0, 255);
        $updated = Absence::justify((int) $absence['id'], $justification);

        if (!$updated) {
            Flash::set('error', 'Cette absence est déjà justifiée.');
            redirect('absences');
        }

        $reward = note_fr(config('academic.absence_penalty', 0.25));
        Flash::set('success', 'Absence justifiée. La note de ' . $absence['first_name'] . ' a été augmentée de ' . $reward . ' point.');
        redirect('absences');
    }

    public function destroy(string $id): void
    {
        $absence = Absence::find((int) $id);

        if (!$absence) {
            Flash::set('error', 'Absence introuvable.');
            redirect('absences');
        }

        Absence::deleteWithRestore((int) $absence['id']);

        Flash::set('success', 'L’absence a été supprimée et la note recalculée.');
        redirect('absences');
    }
}
