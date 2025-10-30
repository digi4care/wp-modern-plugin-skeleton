<?php
/**
 * Performance Benchmark Script
 *
 * Usage: php bin/benchmark.php
 *
 * @package WP\Skeleton\Bin
 * @since 1.0.0
 */

declare(strict_types=1);

// Only run from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Bootstrap WordPress
require_once __DIR__ . '/../../wp-load.php';

// Bootstrap plugin
require_once __DIR__ . '/../vendor/autoload.php';

use WP\Skeleton\Application\GreetingApplication;
use WP\Skeleton\Infrastructure\WordPress\SettingsRepository;
use WP\Skeleton\Shared\DI\PluginServiceLocator;

/**
 * Performance Benchmark Class
 */
class PerformanceBenchmark
{
    private float $startTime;
    private float $memoryUsage;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->memoryUsage = memory_get_usage(true);
    }

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->memoryUsage = memory_get_usage(true);
    }

    public function lap(string $operation): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $duration = $endTime - $this->startTime;
        $memoryUsed = $endMemory - $this->memoryUsage;

        $this->startTime = $endTime;
        $this->memoryUsage = $endMemory;

        return [
            'operation' => $operation,
            'duration_ms' => round($duration * 1000, 2),
            'memory_used_kb' => round($memoryUsed / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
    }
}

/**
 * Run benchmarks
 */
function run_benchmarks(): void
{
    $benchmark = new PerformanceBenchmark();
    $results = [];

    echo "🚀 WP Skeleton Performance Benchmark\n";
    echo "===================================\n\n";

    // Benchmark 1: SettingsRepository with caching
    $settingsRepo = PluginServiceLocator::get(SettingsRepository::class);

    // Warm up cache
    $settingsRepo->get('test_option', 'default');

    $iterations = 1000;
    $benchmark->start();

    for ($i = 0; $i < $iterations; $i++) {
        $settingsRepo->get('test_option', 'default');
    }

    $results[] = $benchmark->lap("SettingsRepository get() x{$iterations}");

    // Benchmark 2: GreetingApplication single calls
    $greetingApp = PluginServiceLocator::get(GreetingApplication::class);

    $benchmark->start();
    for ($i = 0; $i < $iterations; $i++) {
        $greetingApp->greet("User{$i}");
    }

    $results[] = $benchmark->lap("GreetingApplication greet() x{$iterations}");

    // Benchmark 3: GreetingApplication batch calls
    $names = array_map(fn($i) => "User{$i}", range(1, 100));

    $benchmark->start();
    for ($i = 0; $i < 100; $i++) {
        $greetingApp->greetMultiple($names);
    }

    $results[] = $benchmark->lap("GreetingApplication greetMultiple(100) x100");

    // Benchmark 4: Service Locator access
    $benchmark->start();
    for ($i = 0; $i < $iterations; $i++) {
        PluginServiceLocator::get(GreetingApplication::class);
    }

    $results[] = $benchmark->lap("ServiceLocator get() x{$iterations}");

    // Display results
    echo "📊 Benchmark Results:\n";
    echo "====================\n";

    foreach ($results as $result) {
        printf(
            "%-40s %8.2f ms %8.2f KB (peak: %.2f MB)\n",
            $result['operation'],
            $result['duration_ms'],
            $result['memory_used_kb'],
            $result['peak_memory_mb']
        );
    }

    // Performance statistics
    echo "\n📈 Performance Statistics:\n";
    echo "========================\n";

    $totalTime = array_sum(array_column($results, 'duration_ms'));
    $averageTime = $totalTime / count($results);

    printf("Total execution time: %.2f ms\n", $totalTime);
    printf("Average per operation: %.2f ms\n", $averageTime);
    printf("Peak memory usage: %.2f MB\n", max(array_column($results, 'peak_memory_mb')));

    // Service Locator stats
    if (method_exists(PluginServiceLocator::class, 'getPerformanceStats')) {
        $slStats = PluginServiceLocator::getPerformanceStats();
        echo "\n🔧 Service Locator Stats:\n";
        echo "========================\n";
        print_r($slStats);
    }
}

// Run benchmarks
try {
    run_benchmarks();
} catch (Exception $e) {
    echo "❌ Benchmark failed: " . $e->getMessage() . "\n";
    exit(1);
}
