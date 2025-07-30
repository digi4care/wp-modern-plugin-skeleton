<?php

declare(strict_types=1);

namespace WP\Skeleton\Application;

use WP\Skeleton\Domain\SampleService;

class GreetingApplication
{
    public function __construct(private SampleService $service)
    {
    }

    public function greet(string $name): string
    {
        return $this->service->greet($name);
    }
}
