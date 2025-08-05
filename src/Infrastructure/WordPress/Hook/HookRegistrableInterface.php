<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Hook;

interface HookRegistrableInterface
{
    public function register(): void;
}
