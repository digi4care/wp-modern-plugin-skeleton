<?php

declare(strict_types=1);

namespace WP\Skeleton\Domain;

use InvalidArgumentException;

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
        $trimmedName = trim($name);
        
        if ($trimmedName === '') {
            throw new InvalidArgumentException(
                'Name cannot be empty or contain only whitespace. Received: ' . 
                var_export($name, true)
            );
        }
        
        return "Hello, {$trimmedName}!";
    }
}