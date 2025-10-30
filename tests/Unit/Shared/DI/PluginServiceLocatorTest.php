<?php
// File: tests/Unit/Shared/DI/PluginServiceLocatorTest.php

declare(strict_types=1);

namespace WP\Skeleton\Tests\Unit\Shared\DI;

use DI\Container;
use WP\Skeleton\Shared\DI\PluginServiceLocator;
use WP\Skeleton\Application\GreetingApplication;
use WP\Skeleton\Domain\SampleService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WP\Skeleton\Shared\DI\PluginServiceLocator
 */
class PluginServiceLocatorTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(Container::class);
        PluginServiceLocator::reset();
    }

    protected function tearDown(): void
    {
        PluginServiceLocator::reset();
        parent::tearDown();
    }

    public function testSetAndGetContainer(): void
    {
        PluginServiceLocator::setContainer($this->container);
        $this->assertSame($this->container, PluginServiceLocator::getContainer());
    }

    public function testGetServiceReturnsCachedInstance(): void
    {
        $mockService = $this->createMock(GreetingApplication::class);

        $this->container->expects($this->once())
            ->method('get')
            ->with(GreetingApplication::class)
            ->willReturn($mockService);

        PluginServiceLocator::setContainer($this->container);

        // First call - should call container
        $service1 = PluginServiceLocator::get(GreetingApplication::class);

        // Second call - should return cached instance (O(1))
        $service2 = PluginServiceLocator::get(GreetingApplication::class);

        $this->assertSame($mockService, $service1);
        $this->assertSame($service1, $service2);
    }

    public function testGetThrowsExceptionWhenContainerNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Service container not initialized');

        PluginServiceLocator::get(GreetingApplication::class);
    }

    public function testHasReturnsFalseWhenContainerNotSet(): void
    {
        $this->assertFalse(PluginServiceLocator::has(GreetingApplication::class));
    }

    public function testHasDelegatesToContainer(): void
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with(GreetingApplication::class)
            ->willReturn(true);

        PluginServiceLocator::setContainer($this->container);

        $this->assertTrue(PluginServiceLocator::has(GreetingApplication::class));
    }

    public function testClearCache(): void
    {
        $mockService = $this->createMock(GreetingApplication::class);

        $this->container->expects($this->exactly(2))
            ->method('get')
            ->with(GreetingApplication::class)
            ->willReturn($mockService);

        PluginServiceLocator::setContainer($this->container);

        // First call
        $service1 = PluginServiceLocator::get(GreetingApplication::class);

        // Clear cache
        PluginServiceLocator::clearCache();

        // Second call after cache clear - should call container again
        $service2 = PluginServiceLocator::get(GreetingApplication::class);

        $this->assertSame($mockService, $service1);
        $this->assertSame($mockService, $service2);
    }
}
