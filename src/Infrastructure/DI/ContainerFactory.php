<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\DI;

use DI\Container;
use DI\ContainerBuilder;
use WP\Skeleton\Shared\Plugin\PluginContext;
use WP\Skeleton\Shared\Exception\PluginException;

class ContainerFactory
{
    /**
     * Create a new DI container
     *
     * @param PluginContext $pluginContext
     * @return Container
     * @throws PluginException
     */
	public function create(PluginContext $pluginContext): Container
	{
		try {
			$containerBuilder = new ContainerBuilder();

			// Enable compilation for production
			if (defined('WP_DEBUG') && WP_DEBUG === false) {
				$containerBuilder->enableCompilation($pluginContext->getCacheDir());
				$containerBuilder->writeProxiesToFile(true, $pluginContext->getCacheDir() . '/proxies');
			}

			// Add base definitions
			$containerBuilder->addDefinitions([
				PluginContext::class => $pluginContext,
			]);

			// Load services configuration
			$servicesPath = $pluginContext->getConfigDir() . '/services.php';
			if (file_exists($servicesPath)) {
				$containerBuilder->addDefinitions($servicesPath);
			}

			// Load DI configuration if it exists
			$diConfigPath = $pluginContext->getConfigDir() . '/di.php';
			if (file_exists($diConfigPath)) {
				$containerBuilder->addDefinitions($diConfigPath);
			}

			return $containerBuilder->build();
		} catch (\Exception $e) {
			throw new PluginException(
				'Failed to create DI container: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}
	}
}
