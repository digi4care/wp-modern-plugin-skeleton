<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DI\Container;
use WP\Skeleton\Infrastructure\DI\ContainerProvider;
use WP\Skeleton\Shared\Plugin\PluginContext;

class ContainerProviderTest extends TestCase
{
    public function testReturnsSingletonContainer(): void
    {
        ContainerProvider::setPluginContext(new PluginContext(__FILE__, 'test', 'info'));
        $c1 = ContainerProvider::getContainer();
        $c2 = ContainerProvider::getContainer();
        $this->assertInstanceOf(Container::class, $c1);
        $this->assertSame($c1, $c2);
    }
}
