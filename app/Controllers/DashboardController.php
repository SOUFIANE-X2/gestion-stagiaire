<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Absence;
use App\Models\Filiere;
use App\Models\Stagiaire;

final class DashboardController
{
    public function index(): void
    {
        $absenceStats = Absence::stats();
        $statusCounts = Stagiaire::statusCounts();
        $totalStagiaires = Stagiaire::countAll();
        $averageNote = Stagiaire::averageNote();
        $byFiliere = Stagiaire::byFiliere();
        $trend = Absence::monthlyTrend(6);
        $recent = Absence::recent(6);
        $top = Stagiaire::top(5);
        $filieres = Filiere::allWithStats();

        $maxTrend = max(1, ...array_column($trend, 'total'));

        View::render('dashboard', [
            'absenceStats' => $absenceStats,
            'statusCounts' => $statusCounts,
            'totalStagiaires' => $totalStagiaires,
            'averageNote' => $averageNote,
            'byFiliere' => $byFiliere,
            'trend' => $trend,
            'maxTrend' => $maxTrend,
            'recent' => $recent,
            'top' => $top,
            'filieres' => $filieres,
            'pageTitle' => 'Tableau de bord',
        ]);
    }
}
