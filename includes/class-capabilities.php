<?php
/**
 * Role & Capability Management.
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
 * Class Capabilities
 *
 * Configures custom capability mapping and assigns roles for Event Galleries.
 */
class Capabilities {

    /**
     * Primitive capabilities for Event Galleries.
     *
     * @var string[]
     */
    public const PRIMITIVE_CAPS = [
        'edit_event_galleries',
        'edit_others_event_galleries',
        'publish_event_galleries',
        'read_private_event_galleries',
        'delete_event_galleries',
        'delete_others_event_galleries',
        'delete_published_event_galleries',
        'delete_private_event_galleries',
        'edit_published_event_galleries',
    ];

    /**
     * Settings capability required for plugin-wide configuration.
     *
     * @var string
     */
    public const SETTINGS_CAP = 'manage_options';

    /**
     * Register meta cap filtering.
     *
     * @return void
     */
    public function init(): void {
        add_filter('map_meta_cap', [$this, 'filter_map_meta_cap'], 10, 4);
    }

    /**
     * Add custom capabilities to Administrator and Editor roles.
     *
     * @return void
     */
    public static function add_capabilities(): void {
        // Administrator gets all capabilities
        $admin_role = get_role('administrator');
        if ($admin_role instanceof \WP_Role) {
            foreach (self::PRIMITIVE_CAPS as $cap) {
                $admin_role->add_cap($cap);
            }
        }

        // Editor gets all gallery authoring/publishing capabilities
        $editor_role = get_role('editor');
        if ($editor_role instanceof \WP_Role) {
            foreach (self::PRIMITIVE_CAPS as $cap) {
                $editor_role->add_cap($cap);
            }
        }
    }

    /**
     * Remove custom capabilities from all roles during complete uninstall.
     *
     * @return void
     */
    public static function remove_capabilities(): void {
        $roles_to_clean = ['administrator', 'editor'];

        foreach ($roles_to_clean as $role_name) {
            $role = get_role($role_name);
            if ($role instanceof \WP_Role) {
                foreach (self::PRIMITIVE_CAPS as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * Granular meta capability mapping for Event Galleries.
     *
     * @param string[] $caps    Primitive capabilities required.
     * @param string   $cap     Capability being checked.
     * @param int      $user_id Current user ID.
     * @param array    $args    Additional arguments passed to map_meta_cap.
     * @return string[]
     */
    public function filter_map_meta_cap(array $caps, string $cap, int $user_id, array $args): array {
        // Only map Event Gallery meta capabilities
        if (!in_array($cap, ['edit_event_gallery', 'read_event_gallery', 'delete_event_gallery'], true)) {
            return $caps;
        }

        $post_id = isset($args[0]) ? (int) $args[0] : 0;
        $post    = get_post($post_id);

        if (!$post instanceof \WP_Post || $post->post_type !== Post_Type::POST_TYPE) {
            return $caps;
        }

        $post_author = (int) $post->post_author;
        $caps        = [];

        switch ($cap) {
            case 'edit_event_gallery':
                if ($user_id === $post_author) {
                    $caps[] = 'edit_event_galleries';
                } else {
                    $caps[] = 'edit_others_event_galleries';
                }
                break;

            case 'delete_event_gallery':
                if ($user_id === $post_author) {
                    $caps[] = 'delete_event_galleries';
                } else {
                    $caps[] = 'delete_others_event_galleries';
                }
                break;

            case 'read_event_gallery':
                if ('private' !== $post->post_status) {
                    $caps[] = 'read';
                } elseif ($user_id === $post_author) {
                    $caps[] = 'read';
                } else {
                    $caps[] = 'read_private_event_galleries';
                }
                break;
        }

        return $caps;
    }
}
