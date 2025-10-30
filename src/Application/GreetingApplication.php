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

/**
 * Service responsible for handling greeting-related application logic
 */
final class GreetingApplication
{
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
     * @param non-empty-string $name The name to include in the greeting
     * @return non-empty-string The generated greeting
     * 
     * @throws InvalidArgumentException If the provided name is invalid
     * 
     * @example
     * $app = new GreetingApplication(new SampleService());
     * echo $app->greet('John'); // Outputs: "Hello, John!"
     */
    public function greet(string $name): string
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }
        
        return $this->service->greet($name);
    }

    /**
     * Generate multiple greetings for an array of names
     * 
     * @param array<non-empty-string> $names Array of names to greet
     * @return array<non-empty-string> Array of greetings
     */
    public function greetMultiple(array $names): array
    {
        $greetings = [];
        
        foreach ($names as $name) {
            if (trim($name) !== '') {
                $greetings[] = $this->greet($name);
            }
        }
        
        return $greetings;
    }

    /**
     * Generate a greeting with custom format
     * 
     * @param non-empty-string $name The name to include in the greeting
     * @param string $format The greeting format (e.g., "Hello, {name}!")
     * @return non-empty-string The formatted greeting
     */
    public function greetWithFormat(string $name, string $format = "Hello, {name}!"): string
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }
        
        $cleanName = trim($name);
        $greeting = str_replace('{name}', $cleanName, $format);
        
        if ($greeting === '') {
            throw new InvalidArgumentException('Generated greeting cannot be empty');
        }
        
        return $greeting;
    }

    /**
     * Validate if a name is acceptable for greeting
     * 
     * @param string $name The name to validate
     * @return bool True if the name is valid, false otherwise
     */
    public function isValidName(string $name): bool
    {
        $trimmed = trim($name);
        
        if ($trimmed === '') {
            return false;
        }
        
        // Basic validation - you can extend this with more rules
        if (strlen($trimmed) > 100) {
            return false;
        }
        
        // Check for potentially harmful characters
        if (preg_match('/[<>{}]/', $trimmed)) {
            return false;
        }
        
        return true;
    }
}