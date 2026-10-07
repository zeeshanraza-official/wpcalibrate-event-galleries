<?php
/**
 * Event Gallery Metadata Definitions & Sanitization.
 *
 * @package WPCalibrate\EventGalleries
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Meta
 *
 * Centralizes post-meta keys, registration, and sanitization for Event Galleries.
 */
class Meta {

    // Collision-resistant metadata keys.
    public const EVENT_DATE       = '_wpcal_eg_event_date';
    public const LOCATION         = '_wpcal_eg_location';
    public const IMAGE_IDS         = '_wpcal_eg_image_ids';
    public const LAYOUT            = '_wpcal_eg_layout';
    public const COLUMNS_DESKTOP   = '_wpcal_eg_columns_desktop';
    public const COLUMNS_TABLET    = '_wpcal_eg_columns_tablet';
    public const COLUMNS_MOBILE    = '_wpcal_eg_columns_mobile';
    public const IMAGES_PER_PAGE   = '_wpcal_eg_images_per_page';
    public const DOWNLOAD_ENABLED  = '_wpcal_eg_download_enabled';

    /**
     * Register meta fields with WordPress post meta API.
     *
     * @return void
     */
    public function register_meta(): void {
        $post_type = Post_Type::POST_TYPE;

        register_post_meta($post_type, self::EVENT_DATE, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => [self::class, 'sanitize_event_date'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::LOCATION, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::IMAGE_IDS, [
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                ],
            ],
            'single'            => true,
            'type'              => 'array',
            'sanitize_callback' => [self::class, 'sanitize_image_ids'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::LAYOUT, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => [self::class, 'sanitize_layout'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::COLUMNS_DESKTOP, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'integer',
            'sanitize_callback' => [self::class, 'sanitize_columns_desktop'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::COLUMNS_TABLET, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'integer',
            'sanitize_callback' => [self::class, 'sanitize_columns_tablet'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::COLUMNS_MOBILE, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'integer',
            'sanitize_callback' => [self::class, 'sanitize_columns_mobile'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::IMAGES_PER_PAGE, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'integer',
            'sanitize_callback' => [self::class, 'sanitize_images_per_page'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);

        register_post_meta($post_type, self::DOWNLOAD_ENABLED, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => [self::class, 'sanitize_download_enabled'],
            'auth_callback'     => [self::class, 'auth_edit_gallery'],
        ]);
    }

    /**
     * Authorization callback checking whether current user can edit this gallery.
     *
     * @param bool   $allowed   Whether user is allowed.
     * @param string $meta_key  Meta key.
     * @param int    $post_id   Post ID.
     * @return bool
     */
    public static function auth_edit_gallery(bool $allowed, string $meta_key, int $post_id): bool {
        return current_user_can('edit_post', $post_id);
    }

    /**
     * Sanitize event date (expects YYYY-MM-DD or empty).
     *
     * @param mixed $value Input date string.
     * @return string
     */
    public static function sanitize_event_date(mixed $value): string {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $trimmed = trim($value);
        // Match standard ISO date format YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $trimmed, $matches)) {
            $year  = (int) $matches[1];
            $month = (int) $matches[2];
            $day   = (int) $matches[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return '';
    }

    /**
     * Sanitize array of image attachment IDs.
     * Ensures all IDs are positive integers, verifies they are valid attachments, and preserves order.
     *
     * @param mixed $value Raw input (array or comma-separated string).
     * @return array<int> Cleaned array of valid attachment IDs.
     */
    public static function sanitize_image_ids(mixed $value): array {
        if (is_string($value)) {
            // Handle comma-separated list or JSON
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = explode(',', $value);
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $sanitized_ids = [];
        $seen          = [];

        foreach ($value as $id) {
            $attachment_id = (int) $id;
            if ($attachment_id > 0 && !isset($seen[$attachment_id])) {
                // Ensure attachment actually exists and is an attachment post
                $post = get_post($attachment_id);
                if ($post instanceof \WP_Post && $post->post_type === 'attachment') {
                    // Confirm it is an image MIME type
                    if (wp_attachment_is_image($attachment_id)) {
                        $sanitized_ids[]      = $attachment_id;
                        $seen[$attachment_id] = true;
                    }
                }
            }
        }

        return $sanitized_ids;
    }

    /**
     * Sanitize gallery layout override ('default', 'grid', 'masonry').
     *
     * @param mixed $value Input value.
     * @return string
     */
    public static function sanitize_layout(mixed $value): string {
        $key = sanitize_key((string) $value);
        return in_array($key, ['default', 'grid', 'masonry'], true) ? $key : 'default';
    }

    /**
     * Sanitize desktop columns override (0 for inherit, or 1 to 8).
     *
     * @param mixed $value Input value.
     * @return int
     */
    public static function sanitize_columns_desktop(mixed $value): int {
        $val = (int) $value;
        if ($val === 0) {
            return 0; // Inherit
        }
        return max(1, min(8, $val));
    }

    /**
     * Sanitize tablet columns override (0 for inherit, or 1 to 6).
     *
     * @param mixed $value Input value.
     * @return int
     */
    public static function sanitize_columns_tablet(mixed $value): int {
        $val = (int) $value;
        if ($val === 0) {
            return 0; // Inherit
        }
        return max(1, min(6, $val));
    }

    /**
     * Sanitize mobile columns override (0 for inherit, or 1 to 4).
     *
     * @param mixed $value Input value.
     * @return int
     */
    public static function sanitize_columns_mobile(mixed $value): int {
        $val = (int) $value;
        if ($val === 0) {
            return 0; // Inherit
        }
        return max(1, min(4, $val));
    }

    /**
     * Sanitize images per page override (0 for inherit, or 1 to 100).
     *
     * @param mixed $value Input value.
     * @return int
     */
    public static function sanitize_images_per_page(mixed $value): int {
        $val = (int) $value;
        if ($val === 0) {
            return 0; // Inherit
        }
        return max(1, min(100, $val));
    }

    /**
     * Sanitize download enabled override ('default', 'yes', 'no').
     *
     * @param mixed $value Input value.
     * @return string
     */
    public static function sanitize_download_enabled(mixed $value): string {
        $key = sanitize_key((string) $value);
        return in_array($key, ['default', 'yes', 'no'], true) ? $key : 'default';
    }
}
