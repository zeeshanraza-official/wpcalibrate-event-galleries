<?php
/**
 * Admin Menu & Settings Page Integration.
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
 * Class Menu
 *
 * Handles shared WPCalibrate parent menu integration and isolated settings screens.
 */
class Menu {

    /**
     * Shared WPCalibrate parent menu slug.
     *
     * @var string
     */
    public const PARENT_SLUG = 'wpcalibrate';

    /**
     * Settings page slug.
     *
     * @var string
     */
    public const SETTINGS_SLUG = 'wpcalibrate-event-galleries-settings';

    /**
     * Settings service.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Assets service.
     *
     * @var Assets
     */
    private Assets $assets;

    /**
     * Constructor.
     *
     * @param Settings $settings Settings.
     * @param Assets   $assets   Assets.
     */
    public function __construct(Settings $settings, Assets $assets) {
        $this->settings = $settings;
        $this->assets   = $assets;
    }

    /**
     * Initialize menu hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'register_menu_items'], 20);
        add_action('admin_init', [$this, 'handle_save_settings']);
        add_action('admin_head', [$this, 'inject_sidebar_icon_style']);
    }

    /**
     * Inject inline CSS to constrain the WPCalibrate top-level menu icon to standard 20x20px.
     *
     * @return void
     */
    public function inject_sidebar_icon_style(): void {
        ?>
        <style id="wpcalibrate-menu-icon-style">
            #adminmenu .toplevel_page_wpcalibrate div.wp-menu-image img {
                width: 20px !important;
                height: 20px !important;
                max-width: 20px !important;
                max-height: 20px !important;
                padding-top: 7px !important;
                object-fit: contain !important;
            }
        </style>
        <?php
    }

    /**
     * Register WPCalibrate parent menu (if not yet registered) and plugin-specific submenus.
     *
     * @return void
     */
    public function register_menu_items(): void {
        global $admin_page_hooks;

        $parent_exists = isset($admin_page_hooks[self::PARENT_SLUG]);

        // Register top-level WPCalibrate menu if this is the first WPCalibrate plugin active
        if (!$parent_exists) {
            $icon_url = Assets::get_branding_icon_url('white');
            if (empty($icon_url)) {
                $icon_url = 'dashicons-format-gallery';
            }

            add_menu_page(
                'WPCalibrate',
                'WPCalibrate',
                'edit_event_galleries',
                self::PARENT_SLUG,
                [$this, 'render_parent_landing'],
                $icon_url,
                65
            );
        }

        // Submenu: Event Galleries (links directly to CPT list table)
        add_submenu_page(
            self::PARENT_SLUG,
            __('Event Galleries', 'wpcalibrate-event-galleries'),
            __('Event Galleries', 'wpcalibrate-event-galleries'),
            'edit_event_galleries',
            'edit.php?post_type=' . Post_Type::POST_TYPE
        );

        // Submenu: Add New Event Gallery
        add_submenu_page(
            self::PARENT_SLUG,
            __('Add New Gallery', 'wpcalibrate-event-galleries'),
            __('Add New Gallery', 'wpcalibrate-event-galleries'),
            'edit_event_galleries',
            'post-new.php?post_type=' . Post_Type::POST_TYPE
        );

        // Submenu: Isolated Settings Page
        add_submenu_page(
            self::PARENT_SLUG,
            __('Gallery Settings', 'wpcalibrate-event-galleries'),
            __('Gallery Settings', 'wpcalibrate-event-galleries'),
            'manage_options',
            self::SETTINGS_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Parent menu landing callback. Redirects to Event Galleries list table.
     *
     * @return void
     */
    public function render_parent_landing(): void {
        if (!current_user_can('edit_event_galleries')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'wpcalibrate-event-galleries'));
        }

        wp_safe_redirect(admin_url('edit.php?post_type=' . Post_Type::POST_TYPE));
        exit;
    }

    /**
     * Process settings form submission.
     *
     * @return void
     */
    public function handle_save_settings(): void {
        if (!isset($_POST['wpcal_eg_save_settings'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized capability.', 'wpcalibrate-event-galleries'));
        }

        check_admin_referer('wpcal_eg_settings_save', 'wpcal_eg_nonce');

        $raw_input  = isset($_POST['wpcal_eg']) && is_array($_POST['wpcal_eg']) ? wp_unslash($_POST['wpcal_eg']) : [];
        $active_tab = isset($_POST['wpcal_eg_active_tab']) ? sanitize_key((string) $_POST['wpcal_eg_active_tab']) : 'general';

        $this->settings->update_all($raw_input);

        $redirect_url = add_query_arg([
            'page'               => self::SETTINGS_SLUG,
            'tab'                => $active_tab,
            'wpcalibrate_notice' => 'saved',
        ], admin_url('admin.php'));

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Render the settings page template.
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage Event Galleries settings.', 'wpcalibrate-event-galleries'));
        }

        $tabs = [
            'general'          => __('General', 'wpcalibrate-event-galleries'),
            'gallery-defaults' => __('Gallery Defaults', 'wpcalibrate-event-galleries'),
            'archive'          => __('Archive', 'wpcalibrate-event-galleries'),
            'data-tools'       => __('Data & Tools', 'wpcalibrate-event-galleries'),
            'support'          => __('Support', 'wpcalibrate-event-galleries'),
        ];

        $active_tab = isset($_GET['tab']) ? sanitize_key((string) $_GET['tab']) : 'general';
        if (!array_key_exists($active_tab, $tabs)) {
            $active_tab = 'general';
        }

        $settings = $this->settings->get_all();

        include WPCALIBRATE_EVENT_GALLERIES_PATH . 'admin/views/settings-page.php';
    }
}
