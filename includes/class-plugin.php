<?php
/**
 * Main Plugin Coordinator.
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
 * Class Plugin
 *
 * Central coordinator for services, lifecycle, and component initialization.
 */
final class Plugin {

    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Settings service.
     *
     * @var Settings
     */
    public Settings $settings;

    /**
     * Capabilities manager.
     *
     * @var Capabilities
     */
    public Capabilities $capabilities;

    /**
     * Post Type manager.
     *
     * @var Post_Type
     */
    public Post_Type $post_type;

    /**
     * Meta definitions.
     *
     * @var Meta
     */
    public Meta $meta;

    /**
     * Gallery repository.
     *
     * @var Gallery_Repository
     */
    public Gallery_Repository $repository;

    /**
     * Gallery renderer.
     *
     * @var Gallery_Renderer
     */
    public Gallery_Renderer $renderer;

    /**
     * Assets service.
     *
     * @var Assets
     */
    public Assets $assets;

    /**
     * REST API Controller.
     *
     * @var REST_Controller
     */
    public REST_Controller $rest_controller;

    /**
     * Upgrader service.
     *
     * @var Upgrader
     */
    public Upgrader $upgrader;

    /**
     * Admin coordinator.
     *
     * @var Admin
     */
    public Admin $admin;

    /**
     * Frontend coordinator.
     *
     * @var Frontend
     */
    public Frontend $frontend;

    /**
     * Shortcode handler.
     *
     * @var Shortcode
     */
    public Shortcode $shortcode;

    /**
     * Block handler.
     *
     * @var Block
     */
    public Block $block;

    /**
     * Get singleton instance.
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor. Initializes dependencies via constructor injection.
     */
    private function __construct() {
        $this->settings        = new Settings();
        $this->capabilities    = new Capabilities();
        $this->post_type       = new Post_Type();
        $this->meta            = new Meta();
        $this->repository      = new Gallery_Repository($this->settings);
        $this->assets          = new Assets($this->settings);
        $this->renderer        = new Gallery_Renderer($this->repository, $this->settings, $this->assets);
        $this->rest_controller = new REST_Controller($this->repository, $this->renderer);
        $this->upgrader        = new Upgrader($this->settings);
        $this->admin           = new Admin($this->settings, $this->repository, $this->assets);
        $this->frontend        = new Frontend($this->repository, $this->renderer, $this->settings, $this->assets);
        $this->shortcode       = new Shortcode($this->renderer);
        $this->block           = new Block($this->repository, $this->renderer);
    }

    /**
     * Initialize plugin components and hooks.
     *
     * @return void
     */
    public function init(): void {
        // Core data model and lifecycle
        $this->capabilities->init();
        $this->post_type->init();
        add_action('init', [$this->meta, 'register_meta'], 10);
        $this->upgrader->init();
        $this->assets->init();

        // REST API routes
        add_action('rest_api_init', [$this->rest_controller, 'register_routes']);

        // Admin-only components
        if (is_admin()) {
            $this->admin->init();
            $this->add_plugin_action_links();
            $updater = new GitHub_Updater();
            $updater->init();
        }

        // Frontend, shortcode, and block components
        $this->frontend->init();
        $this->shortcode->init();
        $this->block->init();
    }

    /**
     * Add settings link to WordPress Plugins list table.
     *
     * @return void
     */
    private function add_plugin_action_links(): void {
        add_filter('plugin_action_links_' . WPCALIBRATE_EVENT_GALLERIES_BASENAME, static function (array $links): array {
            $settings_url = admin_url('admin.php?page=wpcalibrate-event-galleries-settings');
            $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('Settings', 'wpcalibrate-event-galleries') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        });
    }
}
