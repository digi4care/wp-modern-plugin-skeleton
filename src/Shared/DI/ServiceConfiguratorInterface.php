<?php

/**
 * Service Configurator Interface
 *
 * Defines the contract for classes that configure specific services.
 * This interface follows the Interface Segregation Principle.
 *
 * @package WP\Skeleton\Shared\DI
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Shared\DI;

use DI\ContainerBuilder;

/**
 * Contract for classes that configure specific services
 */
interface ServiceConfiguratorInterface
{
    /**
     * Configure services for the container
     */
    public function configureServices(ContainerBuilder $builder): void;
}