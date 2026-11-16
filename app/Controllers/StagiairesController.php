<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\Absence;
use App\Models\Filiere;
use App\Models\Stagiaire;

final class StagiairesController
{
    private const STATUSES = ['active', 'graduated', 'archived'];
    private const SORTS = ['name', 'name_desc', 'note', 'note_asc', 'recent', 'code'];

    public function index(): void
    {
        $filters = $this->filters();
        $result = Stagiaire::paginate($filters + ['per_page' => 10]);

        View::render('stagiaires/index', [
            'pageTitle' => 'Stagiaires',
            'result' => $result,
            'filters' => $filters,
            'filieres' => Filiere::all(),
            'groups' => Filiere::groupOptions(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(string $id): void
    {
        $stagiaire = Stagiaire::find((int) $id);

        if (!$stagiaire) {
            $this->notFound();
            return;
        }

        $absences = Absence::forStagiaire((int) $stagiaire['id']);

        View::render('stagiaires/show', [
            'pageTitle' => $stagiaire['first_name'] . ' ' . $stagiaire['last_name'],
            'stagiaire' => $stagiaire,
            'absences' => $absences,
        ]);
    }

    public function create(): void
    {
        $groups = Filiere::groupOptions();

        View::render('stagiaires/form', [
            'pageTitle' => 'Nouveau stagiaire',
            'mode' => 'create',
            'stagiaire' => [
                'code' => Stagiaire::nextCode(),
                'first_name' => '',
                'last_name' => '',
                'gender' => 'F',
                'email' => '',
                'phone' => '',
                'group_id' => $groups[0]['id'] ?? '',
                'note' => '20.00',
                'status' => 'active',
            ],
            'groups' => $groups,
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate();

        if ($errors !== []) {
            http_response_code(422);
            View::render('stagiaires/form', [
                'pageTitle' => 'Nouveau stagiaire',
                'mode' => 'create',
                'stagiaire' => $data + ['code' => Stagiaire::nextCode()],
                'groups' => Filiere::groupOptions(),
                'errors' => $errors,
            ]);

            return;
        }

        $data['code'] = Stagiaire::nextCode();
        $id = Stagiaire::create($data);

        Flash::set('success', 'Le stagiaire ' . $data['first_name'] . ' ' . $data['last_name'] . ' a été ajouté.');
        redirect('stagiaires/' . $id);
    }

    public function edit(string $id): void
    {
        $stagiaire = Stagiaire::find((int) $id);

        if (!$stagiaire) {
            $this->notFound();
            return;
        }

        View::render('stagiaires/form', [
            'pageTitle' => 'Modifier ' . $stagiaire['first_name'] . ' ' . $stagiaire['last_name'],
            'mode' => 'edit',
            'stagiaire' => $stagiaire,
            'groups' => Filiere::groupOptions(),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $stagiaire = Stagiaire::find((int) $id);

        if (!$stagiaire) {
            $this->notFound();
            return;
        }

        [$data, $errors] = $this->validate((int) $stagiaire['id']);

        if ($errors !== []) {
            http_response_code(422);
            View::render('stagiaires/form', [
                'pageTitle' => 'Modifier ' . $stagiaire['first_name'] . ' ' . $stagiaire['last_name'],
                'mode' => 'edit',
                'stagiaire' => $data + ['id' => $stagiaire['id'], 'code' => $stagiaire['code']],
                'groups' => Filiere::groupOptions(),
                'errors' => $errors,
            ]);

            return;
        }

        Stagiaire::update((int) $stagiaire['id'], $data);

        Flash::set('success', 'Les informations ont été mises à jour.');
        redirect('stagiaires/' . $stagiaire['id']);
    }

    public function destroy(string $id): void
    {
        Auth::requireAdmin();

        $stagiaire = Stagiaire::find((int) $id);

        if (!$stagiaire) {
            $this->notFound();
            return;
        }

        Stagiaire::delete((int) $stagiaire['id']);
        Flash::set('success', 'Le stagiaire et son historique d’absences ont été supprimés.');
        redirect('stagiaires');
    }

    private function filters(): array
    {
        $status = (string) request('status');
        $sort = (string) request('sort', 'name');

        return [
            'search' => mb_substr(trim((string) request('search')), 0, 80),
            'filiere' => max(0, (int) request('filiere')),
            'group' => max(0, (int) request('group')),
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'name',
            'page' => max(1, (int) request('page', 1)),
        ];
    }

    /**
     * @return array{0: array, 1: array<string, string>}
     */
    private function validate(?int $id = null): array
    {
        $data = [
            'first_name' => trim((string) request('first_name')),
            'last_name' => trim((string) request('last_name')),
            'gender' => (string) request('gender', 'F'),
            'email' => trim((string) request('email')),
            'phone' => trim((string) request('phone')),
            'group_id' => (int) request('group_id'),
            'note' => trim((string) request('note')),
            'status' => (string) request('status', 'active'),
        ];
        $errors = [];

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 60) {
            $errors['first_name'] = 'Le prénom doit contenir entre 2 et 60 caractères.';
        }

        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 60) {
            $errors['last_name'] = 'Le nom doit contenir entre 2 et 60 caractères.';
        }

        if (!in_array($data['gender'], ['F', 'M'], true)) {
            $errors['gender'] = 'Sélectionnez un genre valide.';
        }

        if ($data['email'] !== '') {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Saisissez une adresse e-mail valide.';
            } elseif (Stagiaire::emailExists($data['email'], $id)) {
                $errors['email'] = 'Cette adresse est déjà utilisée par un autre stagiaire.';
            }
        }

        if ($data['phone'] !== '' && preg_match('/^[0-9+\s().\-]{6,30}$/', $data['phone']) !== 1) {
            $errors['phone'] = 'Saisissez un numéro de téléphone valide.';
        }

        if (!Filiere::findGroup($data['group_id'])) {
            $errors['group_id'] = 'Sélectionnez un groupe valide.';
        }

        if (!is_numeric($data['note']) || (float) $data['note'] < 0 || (float) $data['note'] > 20) {
            $errors['note'] = 'La note doit être comprise entre 0 et 20.';
        } else {
            $data['note'] = number_format((float) $data['note'], 2, '.', '');
        }

        if (!in_array($data['status'], self::STATUSES, true)) {
            $errors['status'] = 'Sélectionnez un statut valide.';
        }

        return [$data, $errors];
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('errors/404');
    }
}
