<?php

declare(strict_types=1);

namespace WP\Skeleton\Blocks;

use WP\Skeleton\Blocks\Blocks;

class BlocksLoader {
    /**
     * Initialize blocks if Gutenberg is active
     */
    public static function init(): void {
        // Only load blocks if Gutenberg is active
        if (self::isGutenbergActive()) {
            $blocks = new Blocks();
            $blocks->init();
        }
    }

    /**
     * Check if Gutenberg is active
     */
    private static function isGutenbergActive(): bool {
        // Check if Gutenberg is active (WordPress 5.0+ or Gutenberg plugin)
        $gutenberg    = function_exists('register_block_type');
        $isGutenberg  = function_exists('is_gutenberg_page') && is_gutenberg_page();
        $isClassicEditor = class_exists('Classic_Editor');
        
        // Return true if Gutenberg is active and not disabled by Classic Editor
        return $gutenberg && !($isClassicEditor && !$isGutenberg);
    }
}

// Hook into WordPress
add_action('init', ['WP\\Skeleton\\Blocks\\BlocksLoader', 'init']);
