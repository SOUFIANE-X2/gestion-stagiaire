<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\Admin;

final class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', ['matricule' => ''], 'auth');
    }

    public function login(): void
    {
        $matricule = trim((string) request('matricule'));
        $password = (string) request('password');

        if (Auth::tooManyAttempts()) {
            View::render('auth/login', [
                'error' => 'Trop de tentatives. Réessayez dans quelques minutes.',
                'matricule' => $matricule,
            ], 'auth');

            return;
        }

        if ($matricule === '' || $password === '' || !Auth::attempt($matricule, $password)) {
            Auth::recordFailure();
            View::render('auth/login', [
                'error' => 'Matricule ou mot de passe incorrect.',
                'matricule' => $matricule,
            ], 'auth');

            return;
        }

        Auth::clearFailures();
        $admin = Auth::user();
        Flash::set('success', 'Bienvenue, ' . ($admin['first_name'] ?? '') . '. Votre espace est prêt.');
        redirect('');
    }

    public function showRegister(): void
    {
        if (!config('app.allow_registration', true)) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');

            return;
        }

        View::render('auth/register', ['old' => []], 'auth');
    }

    public function register(): void
    {
        if (!config('app.allow_registration', true)) {
            http_response_code(404);
            View::render('errors/404', [], 'auth');

            return;
        }

        $data = [
            'matricule' => trim((string) request('matricule')),
            'first_name' => trim((string) request('first_name')),
            'last_name' => trim((string) request('last_name')),
            'email' => trim((string) request('email')),
        ];
        $password = (string) request('password');
        $confirm = (string) request('password_confirm');
        $errors = [];

        if (preg_match('/^[A-Za-z0-9\-]{3,30}$/', $data['matricule']) !== 1) {
            $errors['matricule'] = 'Utilisez 3 à 30 caractères : lettres, chiffres ou tirets.';
        } elseif (Admin::matriculeExists($data['matricule'])) {
            $errors['matricule'] = 'Ce matricule est déjà utilisé.';
        }

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 60) {
            $errors['first_name'] = 'Le prénom doit contenir entre 2 et 60 caractères.';
        }

        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 60) {
            $errors['last_name'] = 'Le nom doit contenir entre 2 et 60 caractères.';
        }

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Saisissez une adresse e-mail valide.';
        } elseif (Admin::emailExists($data['email'])) {
            $errors['email'] = 'Un compte utilise déjà cette adresse.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $confirm) {
            $errors['password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('auth/register', ['errors' => $errors, 'old' => $data], 'auth');

            return;
        }

        $adminId = Admin::create([
            'matricule' => $data['matricule'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ]);

        Auth::attempt($data['matricule'], $password);
        Admin::touchLogin($adminId);

        Flash::set('success', 'Votre compte administrateur a été créé.');
        redirect('');
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        Flash::set('success', 'Vous êtes déconnecté.');
        redirect('login');
    }
}
