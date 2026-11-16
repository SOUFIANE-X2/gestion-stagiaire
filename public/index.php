<?php

declare(strict_types=1);

/**
 * Front controller. All requests are routed through this file.
 */

// Let PHP's built-in server deliver static files (CSS, JS, images) directly.
if (PHP_SAPI === 'cli-server') {
    $staticPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $staticFile = __DIR__ . rawurldecode($staticPath);

    if ($staticPath !== '/' && is_file($staticFile)) {
        return false;
    }
}

$root = dirname(__DIR__);

$config = require $root . '/config/config.php';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = '';

// Under Apache the app may live in a sub-directory; the built-in server uses
// the router script and does not expose a stable sub-directory prefix.
if (str_ends_with($scriptName, '/index.php')) {
    // dirname() returns a backslash on Windows, so normalise before trimming.
    $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($scriptDir === '.' || $scriptDir === '/') {
        $scriptDir = '';
    }
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);

if ($scriptDir !== '' && str_starts_with($requestPath, $scriptDir)) {
    $requestPath = substr($requestPath, strlen($scriptDir));
}

$requestPath = '/' . trim($requestPath, '/');

if ($requestPath === '/index.php') {
    $requestPath = '/';
}

$GLOBALS['base_path'] = $scriptDir;
$GLOBALS['current_path'] = $requestPath;
$GLOBALS['config'] = $config;

require $root . '/app/bootstrap.php';

$router = new App\Core\Router();

$guestRoutes = ['/login', '/register'];
$isGuestRoute = in_array($requestPath, $guestRoutes, true);

if (!App\Core\Auth::check() && !$isGuestRoute) {
    App\Core\Flash::set('error', 'Connectez-vous pour accéder à votre espace.');
    redirect('login');
}

if (App\Core\Auth::check() && $isGuestRoute) {
    redirect('');
}

$router->get('/', [App\Controllers\DashboardController::class, 'index']);

$router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->get('/register', [App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [App\Controllers\AuthController::class, 'register']);
$router->post('/logout', [App\Controllers\AuthController::class, 'logout']);

$router->get('/stagiaires', [App\Controllers\StagiairesController::class, 'index']);
$router->get('/stagiaires/create', [App\Controllers\StagiairesController::class, 'create']);
$router->post('/stagiaires', [App\Controllers\StagiairesController::class, 'store']);
$router->get('/stagiaires/{id}', [App\Controllers\StagiairesController::class, 'show']);
$router->get('/stagiaires/{id}/edit', [App\Controllers\StagiairesController::class, 'edit']);
$router->post('/stagiaires/{id}', [App\Controllers\StagiairesController::class, 'update']);
$router->post('/stagiaires/{id}/delete', [App\Controllers\StagiairesController::class, 'destroy']);

$router->get('/absences', [App\Controllers\AbsencesController::class, 'index']);
$router->post('/absences', [App\Controllers\AbsencesController::class, 'store']);
$router->post('/absences/{id}/justify', [App\Controllers\AbsencesController::class, 'justify']);
$router->post('/absences/{id}/delete', [App\Controllers\AbsencesController::class, 'destroy']);

$router->get('/filieres', [App\Controllers\FilieresController::class, 'index']);
$router->post('/filieres', [App\Controllers\FilieresController::class, 'storeFiliere']);
$router->post('/filieres/{id}/delete', [App\Controllers\FilieresController::class, 'destroyFiliere']);
$router->post('/groupes', [App\Controllers\FilieresController::class, 'storeGroup']);
$router->post('/groupes/{id}/delete', [App\Controllers\FilieresController::class, 'destroyGroup']);

$router->get('/reports', [App\Controllers\ReportsController::class, 'index']);
$router->get('/reports/filieres/{id}', [App\Controllers\ReportsController::class, 'filiere']);
$router->get('/reports/stagiaires.csv', [App\Controllers\ReportsController::class, 'exportStagiaires']);
$router->get('/reports/absences.csv', [App\Controllers\ReportsController::class, 'exportAbsences']);

$router->get('/profile', [App\Controllers\ProfileController::class, 'index']);
$router->post('/profile', [App\Controllers\ProfileController::class, 'update']);
$router->post('/profile/password', [App\Controllers\ProfileController::class, 'changePassword']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
