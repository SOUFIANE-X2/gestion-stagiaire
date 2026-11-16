<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $layout ??= Auth::check() ? 'layout' : 'auth';

        $config = self::$config;
        $flashes = Flash::take();
        $currentAdmin = Auth::user();

        extract($data, EXTR_SKIP);

        // Views may provide a complete admin record; otherwise fall back to the session user.
        $admin = $data['admin'] ?? $currentAdmin;

        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/' . $layout . '.php';
    }
}
