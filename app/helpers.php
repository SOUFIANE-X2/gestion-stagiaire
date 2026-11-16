<?php

declare(strict_types=1);

use App\Core\Csrf;

/**
 * Escape a value for safe HTML output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Read a value from the application configuration using dot notation.
 */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'] ?? [];

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function base_path(): string
{
    return $GLOBALS['base_path'] ?? '';
}

/**
 * Build an application URL that works in a sub-directory and with the PHP dev server.
 */
function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $path = '/' . ltrim($path, '/');

    return base_path() . ($path === '/' ? '/' : $path);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path = ''): never
{
    header('Location: ' . url($path));
    exit;
}

function current_path(): string
{
    return $GLOBALS['current_path'] ?? '/';
}

function is_active(string $prefix): bool
{
    $path = current_path();

    if ($prefix === '/') {
        return $path === '/';
    }

    return $path === $prefix || str_starts_with($path, $prefix . '/');
}

function csrf_field(): string
{
    return Csrf::field();
}

/**
 * Read a trimmed request value from POST, falling back to GET.
 */
function request(string $key, mixed $default = ''): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;

    return is_string($value) ? trim($value) : $value;
}

function old(array $data, string $key, mixed $default = ''): string
{
    return e((string) ($data[$key] ?? $default));
}

function date_fr(?string $date, bool $withYear = true): string
{
    if ($date === null || $date === '') {
        return '—';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '—';
    }

    static $months = [
        1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.', 5 => 'mai', 6 => 'juin',
        7 => 'juil.', 8 => 'août', 9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
    ];

    $label = (int) date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)];

    return $withYear ? $label . ' ' . date('Y', $timestamp) : $label;
}

function month_fr(int $month, bool $short = false): string
{
    $long = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
        7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    $shortNames = [
        1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juil', 8 => 'Août', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
    ];

    return $short ? ($shortNames[$month] ?? '') : ($long[$month] ?? '');
}

function note_fr(mixed $note): string
{
    $formatted = number_format((float) $note, 2, ',', '');

    return rtrim(rtrim($formatted, '0'), ',');
}

function note_tone(mixed $note): string
{
    $value = (float) $note;

    if ($value >= 14) {
        return 'good';
    }

    if ($value >= (float) config('academic.pass_mark', 10)) {
        return 'warn';
    }

    return 'bad';
}

function slot_label(int|string $slot): string
{
    $slots = config('academic.slots', []);

    return $slots[(int) $slot] ?? 'Créneau ' . $slot;
}

function initials(string $firstName, string $lastName): string
{
    return mb_strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
}

function percent(int|float $part, int|float $total): string
{
    if ($total <= 0) {
        return '0 %';
    }

    return number_format(($part / $total) * 100, 0, ',', ' ') . ' %';
}

/**
 * Append the current query string with optional overrides.
 */
function query_string(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);

    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        }
    }

    return $params === [] ? '' : '?' . http_build_query($params);
}

function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true"><use href="#i-' . e($name) . '"></use></svg>';
}

function csrf_token(): string
{
    return Csrf::token();
}
