<?php

/**
 * Application layer service for greeting functionality
 *
 * This class serves as an entry point for greeting-related use cases,
 * coordinating between the presentation layer and domain services.
 *
 * @package WP\Skeleton\Application
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WP\Skeleton\Application;

use WP\Skeleton\Domain\SampleService;
use InvalidArgumentException;
use WP_Error;

/**
 * Service responsible for handling greeting-related application logic
 */
final class GreetingApplication
{
    private const MAX_NAME_LENGTH = 100;
    private const NAME_PATTERN = '/^[a-zA-Z0-9\s\-\'\.]+$/u';

    /**
     * Cached regex pattern for better performance
     */
    private static ?string $cachedPattern = null;

    /**
     * @param SampleService $service The domain service for greeting operations
     */
    public function __construct(
        private readonly SampleService $service
    ) {
    }

    /**
     * Generate a greeting for the given name
     *
     * @param string $name The name to include in the greeting
     * @return string|WP_Error The generated greeting or WP_Error for user-facing errors
     *
     * @example
     * // Basic usage
     * $app = new GreetingApplication(new SampleService());
     * echo $app->greet('John'); // Outputs: "Hello, John!"
     *
     * // With extra whitespace
     * echo $app->greet('  Jane  ');  // Outputs: "Hello, Jane!"
     *
     * // Error case
     * $result = $app->greet('');  // Returns WP_Error
     */
    public function greet(string $name): string|WP_Error
    {
        $validation = $this->validateName($name);
        if (is_wp_error($validation)) {
            return $validation;
        }

        try {
            return $this->service->greet($name);
        } catch (InvalidArgumentException $e) {
            // Convert domain exceptions to user-friendly WP_Error
            return new WP_Error(
                'greeting_error',
                __('Could not generate greeting. Please try again.', 'wp-skeleton'),
                ['status' => 400]
            );
        }
    }

    /**
     * Generate multiple greetings for an array of names
     *
     * @param array<string> $names Array of names to greet
     * @return array<string> Array of successful greetings
     */
    public function greetMultiple(array $names): array
    {
        // Pre-validate all names first (single pass - O(n))
        $validNames = array_filter($names, function($name) {
            return $this->isValidName($name);
        });

        // Batch processing with domain service optimization
        return $this->service->greetMultiple($validNames);
    }

    /**
     * Generate a greeting with custom format
     *
     * @param string $name The name to include in the greeting
     * @param string $format The greeting format (e.g., "Hello, {name}!")
     * @return string|WP_Error The formatted greeting or WP_Error for user-facing errors
     */
    public function greetWithFormat(string $name, string $format = "Hello, {name}!"): string|WP_Error
    {
        $validation = $this->validateName($name);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $cleanName = trim($name);
        $greeting = str_replace('{name}', $cleanName, $format);

        if ($greeting === '') {
            return new WP_Error(
                'empty_greeting',
                __('The generated greeting cannot be empty.', 'wp-skeleton'),
                ['status' => 400]
            );
        }

        return $greeting;
    }

    /**
     * Validate if a name is acceptable for greeting with strict rules
     *
     * Time Complexity: O(1) - Constant time operation
     * Space Complexity: O(1) - Constant space usage
     *
     * @param string $name The name to validate
     * @return true|WP_Error True if valid, WP_Error with user-friendly message if invalid
     */
    public function validateName(string $name): bool|WP_Error
    {
        $trimmed = trim($name);

        // Check empty (fastest check first)
        if ($trimmed === '') {
            return new WP_Error(
                'empty_name',
                __('Name cannot be empty.', 'wp-skeleton'),
                ['status' => 400]
            );
        }

        // Check length
        if (strlen($trimmed) > self::MAX_NAME_LENGTH) {
            return new WP_Error(
                'name_too_long',
                sprintf(
                    __('Name cannot exceed %d characters.', 'wp-skeleton'),
                    self::MAX_NAME_LENGTH
                ),
                ['status' => 400]
            );
        }

        // Check pattern with cached regex for better performance
        if (self::$cachedPattern === null) {
            self::$cachedPattern = self::NAME_PATTERN;
        }

        if (!preg_match(self::$cachedPattern, $trimmed)) {
            return new WP_Error(
                'invalid_name_characters',
                __('Name can only contain letters, numbers, spaces, hyphens, apostrophes, and dots.', 'wp-skeleton'),
                ['status' => 400]
            );
        }

        return true;
    }

    /**
     * Validate name without WP_Error (for internal use)
     *
     * @internal For domain/internal use only
     */
    public function isValidName(string $name): bool
    {
        $validation = $this->validateName($name);
        return $validation === true;
    }

    /**
     * Batch validate multiple names efficiently
     *
     * @param array<string> $names Array of names to validate
     * @return array<string> Array of valid names
     */
    public function filterValidNames(array $names): array
    {
        return array_filter($names, function($name) {
            return $this->isValidName($name);
        });
    }

    /**
     * Get performance statistics (for debugging)
     *
     * @return array<string, mixed>
     */
    public function getPerformanceStats(): array
    {
        return [
            'max_name_length' => self::MAX_NAME_LENGTH,
            'name_pattern' => self::NAME_PATTERN,
            'cached_pattern' => self::$cachedPattern !== null,
        ];
    }
}
