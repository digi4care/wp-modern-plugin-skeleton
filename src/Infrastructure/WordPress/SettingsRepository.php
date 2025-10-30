<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress;

use InvalidArgumentException;

/**
 * Handles WordPress options with type safety and validation
 * 
 * This class provides a type-safe wrapper around WordPress options API,
 * ensuring consistent behavior and proper error handling.
 * 
 * @see https://developer.wordpress.org/plugins/settings/settings-api/
 * @since 1.0.0
 */
final class SettingsRepository
{
    /**
     * Retrieves an option value from the WordPress database
     * 
     * @template T
     * @param non-empty-string $key Option name. Must not be empty.
     * @param T $default Default value to return if the option doesn't exist.
     * 
     * @return T|mixed The option value if it exists, otherwise the default value.
     * 
     * @throws InvalidArgumentException If the provided key is empty.
     * 
     * @example
     * // Get an option with a default value
     * $value = $repository->get('my_option', 'default_value');
     * 
     * // Get an option with type hinting
     * /** @var array{enabled: bool, count: int} $config *\/
     * $config = $repository->get('plugin_config', ['enabled' => false, 'count' => 0]);
     * 
     * @see get_option()
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Option key cannot be empty');
        }
        
        $value = get_option($key, self::UNSET_VALUE);
        
        if ($value === self::UNSET_VALUE) {
            return $default;
        }
        
        return $value;
    }

    /**
     * Updates an option value in the WordPress database
     * 
     * @param non-empty-string $key The option name. Must not be empty.
     * @param mixed $value The new option value.
     * @param string $autoload Whether to autoload the option ('yes' or 'no')
     * 
     * @return bool 
     *   - true if the value was updated successfully
     *   - false if the update failed or the value was the same
     * 
     * @throws InvalidArgumentException If the provided key is empty or autoload is invalid.
     * 
     * @example
     * // Update a simple option
     * $success = $repository->update('my_option', 'new value');
     * 
     * // Update an array option
     * $repository->update('complex_config', [
     *     'enabled' => true,
     *     'count' => 42
     * ]);
     * 
     * @see update_option()
     */
    public function update(string $key, mixed $value, string $autoload = 'yes'): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Option key cannot be empty');
        }
        
        if (!in_array($autoload, ['yes', 'no'], true)) {
            throw new InvalidArgumentException('Autoload must be either "yes" or "no"');
        }
        
        return update_option($key, $value, $autoload === 'yes');
    }

    /**
     * Deletes an option from the WordPress database
     * 
     * @param non-empty-string $key The option name. Must not be empty.
     * @return bool True if the option was deleted, false otherwise.
     * 
     * @throws InvalidArgumentException If the provided key is empty.
     * 
     * @see delete_option()
     */
    public function delete(string $key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Option key cannot be empty');
        }
        
        return delete_option($key);
    }

    /**
     * Checks if an option exists
     * 
     * @param non-empty-string $key The option name. Must not be empty.
     * @return bool True if the option exists, false otherwise.
     * 
     * @throws InvalidArgumentException If the provided key is empty.
     */
    public function has(string $key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Option key cannot be empty');
        }
        
        return get_option($key, self::UNSET_VALUE) !== self::UNSET_VALUE;
    }

    /**
     * Get multiple options at once
     * 
     * @param array<non-empty-string> $keys Array of option keys
     * @return array<string, mixed> Associative array of key => value pairs
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            if (!empty($key)) {
                $results[$key] = $this->get($key);
            }
        }
        
        return $results;
    }

    /**
     * Update multiple options at once
     * 
     * @param array<non-empty-string, mixed> $options Associative array of key => value pairs
     * @param string $autoload Whether to autoload the options ('yes' or 'no')
     * @return array<string, bool> Associative array of key => success status
     */
    public function updateMultiple(array $options, string $autoload = 'yes'): array
    {
        $results = [];
        
        foreach ($options as $key => $value) {
            if (!empty($key)) {
                $results[$key] = $this->update($key, $value, $autoload);
            }
        }
        
        return $results;
    }

    /**
     * Special marker for unset values
     */
    private const UNSET_VALUE = '__UNSET_VALUE__';
}