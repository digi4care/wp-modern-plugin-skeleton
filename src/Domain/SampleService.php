<?php

declare(strict_types=1);

namespace WP\Skeleton\Domain;

class SampleService
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}
