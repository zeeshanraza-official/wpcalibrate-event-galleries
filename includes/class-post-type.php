<?php
/**
 * Custom Post Type Registration.
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
 * Class Post_Type
 *
 * Registers and configures the wpcal_event_gallery custom post type.
 */
class Post_Type {

    /**
     * Post type identifier.
     *
     * @var string
     */
    public const POST_TYPE = 'wpcal_event_gallery';

    /**
     * Archive rewrite slug.
     *
     * @var string
     */
    public const REWRITE_SLUG = 'event-galleries';

    /**
     * Register post type action.
     *
     * @return void
     */
    public function init(): void {
        add_action('init', [$this, 'register_post_type'], 5);
    }

    /**
     * Register the dedicated Event Gallery custom post type.
     *
     * @return void
     */
    public function register_post_type(): void {
        $labels = [
            'name'                  => _x('Event Galleries', 'Post Type General Name', 'wpcalibrate-event-galleries'),
            'singular_name'         => _x('Event Gallery', 'Post Type Singular Name', 'wpcalibrate-event-galleries'),
            'menu_name'             => __('Event Galleries', 'wpcalibrate-event-galleries'),
            'name_admin_bar'        => __('Event Gallery', 'wpcalibrate-event-galleries'),
            'archives'              => __('Event Gallery Archives', 'wpcalibrate-event-galleries'),
            'attributes'            => __('Event Gallery Attributes', 'wpcalibrate-event-galleries'),
            'parent_item_colon'     => __('Parent Event Gallery:', 'wpcalibrate-event-galleries'),
            'all_items'             => __('All Event Galleries', 'wpcalibrate-event-galleries'),
            'add_new_item'          => __('Add New Event Gallery', 'wpcalibrate-event-galleries'),
            'add_new'               => __('Add New', 'wpcalibrate-event-galleries'),
            'new_item'              => __('New Event Gallery', 'wpcalibrate-event-galleries'),
            'edit_item'             => __('Edit Event Gallery', 'wpcalibrate-event-galleries'),
            'update_item'           => __('Update Event Gallery', 'wpcalibrate-event-galleries'),
            'view_item'             => __('View Event Gallery', 'wpcalibrate-event-galleries'),
            'view_items'            => __('View Event Galleries', 'wpcalibrate-event-galleries'),
            'search_items'          => __('Search Event Galleries', 'wpcalibrate-event-galleries'),
            'not_found'             => __('No event galleries found', 'wpcalibrate-event-galleries'),
            'not_found_in_trash'    => __('No event galleries found in Trash', 'wpcalibrate-event-galleries'),
            'featured_image'        => __('Event Cover Image', 'wpcalibrate-event-galleries'),
            'set_featured_image'    => __('Set event cover image', 'wpcalibrate-event-galleries'),
            'remove_featured_image' => __('Remove event cover image', 'wpcalibrate-event-galleries'),
            'use_featured_image'    => __('Use as event cover image', 'wpcalibrate-event-galleries'),
            'insert_into_item'      => __('Insert into event gallery', 'wpcalibrate-event-galleries'),
            'uploaded_to_this_item' => __('Uploaded to this event gallery', 'wpcalibrate-event-galleries'),
            'items_list'            => __('Event galleries list', 'wpcalibrate-event-galleries'),
            'items_list_navigation' => __('Event galleries list navigation', 'wpcalibrate-event-galleries'),
            'filter_items_list'     => __('Filter event galleries list', 'wpcalibrate-event-galleries'),
        ];

        $capabilities = [
            'edit_post'              => 'edit_event_gallery',
            'read_post'              => 'read_event_gallery',
            'delete_post'            => 'delete_event_gallery',
            'edit_posts'             => 'edit_event_galleries',
            'edit_others_posts'      => 'edit_others_event_galleries',
            'publish_posts'          => 'publish_event_galleries',
            'read_private_posts'     => 'read_private_event_galleries',
            'delete_posts'           => 'delete_event_galleries',
            'delete_private_posts'   => 'delete_private_event_galleries',
            'delete_published_posts' => 'delete_published_event_galleries',
            'delete_others_posts'    => 'delete_others_event_galleries',
            'edit_private_posts'     => 'edit_others_event_galleries',
            'edit_published_posts'   => 'edit_published_event_galleries',
        ];

        $args = [
            'label'               => __('Event Gallery', 'wpcalibrate-event-galleries'),
            'description'         => __('Event-specific photo galleries', 'wpcalibrate-event-galleries'),
            'labels'              => $labels,
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => false, // Handled manually by Admin_Menu for seamless WPCalibrate integration
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => self::REWRITE_SLUG,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => ['event_gallery', 'event_galleries'],
            'capabilities'        => $capabilities,
            'map_meta_cap'        => false, // Managed through our custom map_meta_cap filter
            'show_in_rest'        => true,
            'rewrite'             => [
                'slug'       => self::REWRITE_SLUG,
                'with_front' => false,
                'feeds'      => false,
                'pages'      => true,
            ],
        ];

        register_post_type(self::POST_TYPE, $args);
    }
}
