<?php

declare(strict_types=1);

namespace WP\Skeleton\Domain;

use InvalidArgumentException;

/**
 * Memoization trait for caching expensive operations
 *
 * @since 1.0.0
 */
trait MemoizableTrait
{
    /**
     * @var array<string, mixed>
     */
    private static array $memoized = [];

    /**
     * Memoize a callback result
     *
     * @template T
     * @param string $key Unique cache key
     * @param callable(): T $callback Function to cache
     * @return T
     */
    protected function memoize(string $key, callable $callback): mixed
    {
        if (!array_key_exists($key, self::$memoized)) {
            self::$memoized[$key] = $callback();
        }
        return self::$memoized[$key];
    }

    /**
     * Clear memoized cache
     *
     * @param string|null $key Specific key to clear, or null to clear all
     * @return void
     */
    protected function clearMemoized(string $key = null): void
    {
        if ($key === null) {
            self::$memoized = [];
        } else {
            unset(self::$memoized[$key]);
        }
    }

    /**
     * Get memoized cache size (for debugging)
     *
     * @return int
     */
    protected function getMemoizedSize(): int
    {
        return count(self::$memoized);
    }
}

/**
 * Sample service demonstrating domain logic implementation
 *
 * This service provides example business logic that follows domain-driven design principles
 * and demonstrates proper type safety and documentation practices.
 *
 * @since 1.0.0
 */
final class SampleService
{
    use MemoizableTrait;

    /**
     * Generates a personalized greeting message
     *
     * This method takes a name and returns a personalized greeting. The name is automatically
     * trimmed of whitespace, and an exception is thrown if the resulting string is empty.
     *
     * @param non-empty-string $name The name to include in the greeting. Will be trimmed of whitespace.
     *
     * @return non-empty-string A greeting message in the format "Hello, [name]!"
     *
     * @throws InvalidArgumentException If the provided name is empty or contains only whitespace.
     *
     * @example
     * // Basic usage
     * $service = new SampleService();
     * echo $service->greet('John');  // Outputs: "Hello, John!"
     *
     * // With extra whitespace
     * echo $service->greet('  Jane  ');  // Outputs: "Hello, Jane!"
     *
     * // Error case
     * $service->greet('');  // Throws InvalidArgumentException
     */
    public function greet(string $name): string
    {
        // Use memoization for repeated calls with same name
        return $this->memoize("greet_{$name}", function() use ($name) {
            $trimmedName = trim($name);

            if ($trimmedName === '') {
                throw new InvalidArgumentException(
                    'Name cannot be empty or contain only whitespace. Received: ' .
                    var_export($name, true)
                );
            }

            return "Hello, {$trimmedName}!";
        });
    }

    /**
     * Batch greet multiple names with memoization
     *
     * @param array<string> $names Array of names to greet
     * @return array<string> Array of greetings
     */
    public function greetMultiple(array $names): array
    {
        $greetings = [];

        foreach ($names as $name) {
            try {
                $greetings[] = $this->greet($name);
            } catch (InvalidArgumentException $e) {
                // Skip invalid names
                continue;
            }
        }

        return $greetings;
    }

    /**
     * Clear all cached greetings (useful for testing)
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->clearMemoized();
    }
}
