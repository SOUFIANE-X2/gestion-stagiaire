<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\Admin;

final class ProfileController
{
    public function index(): void
    {
        $admin = Admin::find((int) Auth::user()['id']);

        if (!$admin) {
            Auth::logout();
            redirect('login');
        }

        View::render('profile/index', [
            'pageTitle' => 'Mon compte',
            'admin' => $admin,
        ]);
    }

    public function update(): void
    {
        $admin = Admin::find((int) Auth::user()['id']);

        if (!$admin) {
            Auth::logout();
            redirect('login');
        }

        $data = [
            'first_name' => trim((string) request('first_name')),
            'last_name' => trim((string) request('last_name')),
            'email' => trim((string) request('email')),
        ];
        $errors = [];

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 60) {
            $errors[] = 'Le prénom doit contenir entre 2 et 60 caractères.';
        }

        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 60) {
            $errors[] = 'Le nom doit contenir entre 2 et 60 caractères.';
        }

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Saisissez une adresse e-mail valide.';
        } elseif (Admin::emailExists($data['email'], (int) $admin['id'])) {
            $errors[] = 'Cette adresse est déjà utilisée par un autre compte.';
        }

        if ($errors !== []) {
            Flash::set('error', implode(' ', $errors));
            redirect('profile');
        }

        Admin::updateProfile((int) $admin['id'], $data);

        $_SESSION['admin']['first_name'] = $data['first_name'];
        $_SESSION['admin']['last_name'] = $data['last_name'];
        $_SESSION['admin']['email'] = $data['email'];

        Flash::set('success', 'Votre profil a été mis à jour.');
        redirect('profile');
    }

    public function changePassword(): void
    {
        $admin = Admin::find((int) Auth::user()['id']);

        if (!$admin) {
            Auth::logout();
            redirect('login');
        }

        $current = (string) request('current_password');
        $password = (string) request('password');
        $confirm = (string) request('password_confirm');

        if (!password_verify($current, $admin['password_hash'])) {
            Flash::set('error', 'Le mot de passe actuel est incorrect.');
            redirect('profile');
        }

        if (mb_strlen($password) < 8) {
            Flash::set('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            redirect('profile');
        }

        if ($password !== $confirm) {
            Flash::set('error', 'Les deux mots de passe ne correspondent pas.');
            redirect('profile');
        }

        if (password_verify($password, $admin['password_hash'])) {
            Flash::set('error', 'Choisissez un mot de passe différent de l’actuel.');
            redirect('profile');
        }

        Admin::updatePassword((int) $admin['id'], password_hash($password, PASSWORD_DEFAULT));
        session_regenerate_id(true);

        Flash::set('success', 'Votre mot de passe a été modifié.');
        redirect('profile');
    }
}
