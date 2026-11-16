<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Absence;
use App\Models\Filiere;
use App\Models\Stagiaire;

final class ReportsController
{
    public function index(): void
    {
        View::render('reports/index', [
            'pageTitle' => 'Rapports',
            'filieres' => Filiere::allWithStats(),
            'stats' => Absence::stats(),
            'slotStats' => Absence::bySlot(),
            'totalStagiaires' => Stagiaire::countAll(),
            'averageNote' => Stagiaire::averageNote(),
        ]);
    }

    public function filiere(string $id): void
    {
        $filiere = Filiere::find((int) $id);

        if (!$filiere) {
            http_response_code(404);
            View::render('errors/404');

            return;
        }

        $stagiaires = Stagiaire::all(['filiere' => (int) $filiere['id']]);
        $groups = Filiere::groupsForFiliere((int) $filiere['id']);
        $totalAbsences = array_sum(array_column($stagiaires, 'absence_total'));
        $openAbsences = array_sum(array_column($stagiaires, 'absence_open'));
        $average = $stagiaires === []
            ? 0.0
            : array_sum(array_map(static fn (array $row): float => (float) $row['note'], $stagiaires)) / count($stagiaires);

        View::render('reports/filiere', [
            'pageTitle' => 'Rapport — ' . $filiere['label'],
            'filiere' => $filiere,
            'stagiaires' => $stagiaires,
            'groups' => $groups,
            'totalAbsences' => $totalAbsences,
            'openAbsences' => $openAbsences,
            'average' => $average,
        ], 'print');
    }

    public function exportStagiaires(): void
    {
        $rows = Stagiaire::all();

        $this->csv('stagiaires-' . date('Y-m-d') . '.csv', [
            'Code', 'Nom', 'Prénom', 'Genre', 'E-mail', 'Téléphone', 'Groupe', 'Année',
            'Filière', 'Note', 'Statut', 'Absences', 'Absences non justifiées',
        ], array_map(static fn (array $row): array => [
            $row['code'],
            $row['last_name'],
            $row['first_name'],
            $row['gender'],
            $row['email'],
            $row['phone'],
            $row['group_code'],
            $row['academic_year'],
            $row['filiere_label'],
            number_format((float) $row['note'], 2, '.', ''),
            $row['status'],
            $row['absence_total'],
            $row['absence_open'],
        ], $rows));
    }

    public function exportAbsences(): void
    {
        $rows = Absence::all();

        $this->csv('absences-' . date('Y-m-d') . '.csv', [
            'Date', 'Créneau', 'Code stagiaire', 'Nom', 'Prénom', 'Groupe', 'Filière',
            'Justifiée', 'Motif', 'Justification',
        ], array_map(static fn (array $row): array => [
            $row['date'],
            slot_label((int) $row['slot']),
            $row['stagiaire_code'],
            $row['last_name'],
            $row['first_name'],
            $row['group_code'],
            $row['filiere_label'],
            (int) $row['justified'] === 1 ? 'Oui' : 'Non',
            $row['reason'],
            $row['justification'],
        ], $rows));
    }

    private function csv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            return;
        }

        // UTF-8 BOM so Excel opens accented characters correctly.
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }
}
