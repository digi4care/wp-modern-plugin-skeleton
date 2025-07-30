<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WP\Skeleton\Domain\SampleService;

class SampleServiceTest extends TestCase
{
    public function testGreet(): void
    {
        $service = new SampleService();
        $this->assertSame('Hello, Codex!', $service->greet('Codex'));
    }
}
