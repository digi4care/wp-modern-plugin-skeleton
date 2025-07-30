<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress;

class SettingsRepository
{
    public function get(string $key, mixed $default = null): mixed
    {
        return get_option($key, $default);
    }

    public function update(string $key, mixed $value): bool
    {
        return update_option($key, $value);
    }
}
