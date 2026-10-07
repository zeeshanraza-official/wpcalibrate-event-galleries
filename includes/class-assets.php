<?php
/**
 * Asset Enqueuing & Registration Service.
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
 * Class Assets
 *
 * Manages conditional registration and enqueuing of CSS and JavaScript assets.
 */
class Assets {

    /**
     * Settings service.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Track whether frontend assets have been enqueued.
     *
     * @var bool
     */
    private bool $frontend_enqueued = false;

    /**
     * Constructor.
     *
     * @param Settings $settings Settings service.
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * Initialize asset hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);
    }

    /**
     * Resolve branding icon URL.
     *
     * @param string $variant 'white' for dark backgrounds or 'dark' for light backgrounds.
     * @return string Icon URL or empty string if not found.
     */
    public static function get_branding_icon_url(string $variant = 'dark'): string {
        $filename = ($variant === 'white') ? 'icon-white.png' : 'icon-dark.png';
        $path     = WPCALIBRATE_EVENT_GALLERIES_PATH . 'branding/' . $filename;

        if (file_exists($path)) {
            return WPCALIBRATE_EVENT_GALLERIES_URL . 'branding/' . $filename;
        }

        return '';
    }

    /**
     * Enqueue admin assets conditionally only on relevant screens.
     *
     * @param string $hook_suffix Current admin screen hook.
     * @return void
     */
    public function enqueue_admin_assets(string $hook_suffix): void {
        global $post_type, $pagenow;

        $is_gallery_screen = ($post_type === Post_Type::POST_TYPE) ||
            ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === Post_Type::POST_TYPE) ||
            ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === Post_Type::POST_TYPE) ||
            ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type((int) $_GET['post']) === Post_Type::POST_TYPE);

        $is_settings_screen = (isset($_GET['page']) && $_GET['page'] === 'wpcalibrate-event-galleries-settings');

        if (!$is_gallery_screen && !$is_settings_screen) {
            return;
        }

        $version = WPCALIBRATE_EVENT_GALLERIES_VERSION;

        // Admin CSS
        $css_file = is_rtl() ? 'admin-rtl.css' : 'admin.css';
        wp_enqueue_style(
            'wpcal-eg-admin',
            WPCALIBRATE_EVENT_GALLERIES_URL . 'assets/css/' . $css_file,
            [],
            $version
        );

        // On event gallery edit screens, enqueue WordPress media modal and drag-and-drop sortable script
        if ($is_gallery_screen) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script(
                'wpcal-eg-admin-gallery',
                WPCALIBRATE_EVENT_GALLERIES_URL . 'assets/js/admin-gallery.js',
                ['jquery', 'jquery-ui-sortable'],
                $version,
                true
            );

            wp_localize_script('wpcal-eg-admin-gallery', 'wpcalEgAdmin', [
                'selectImagesTitle' => __('Select Event Gallery Photos', 'wpcalibrate-event-galleries'),
                'selectImagesBtn'   => __('Add to Event Gallery', 'wpcalibrate-event-galleries'),
                'removePhotoLabel'  => __('Remove photo from gallery', 'wpcalibrate-event-galleries'),
                'confirmRemoveAll'  => __('Are you sure you want to remove all photos from this gallery?', 'wpcalibrate-event-galleries'),
            ]);
        }
    }

    /**
     * Pre-register frontend assets so they can be enqueued when galleries render.
     *
     * @return void
     */
    public function register_frontend_assets(): void {
        $version = WPCALIBRATE_EVENT_GALLERIES_VERSION;

        // Register frontend CSS
        $css_file = is_rtl() ? 'gallery-rtl.css' : 'gallery.css';
        wp_register_style(
            'wpcal-eg-frontend',
            WPCALIBRATE_EVENT_GALLERIES_URL . 'assets/css/' . $css_file,
            [],
            $version
        );

        // Register frontend JS (Vanilla JS in footer)
        wp_register_script(
            'wpcal-eg-frontend',
            WPCALIBRATE_EVENT_GALLERIES_URL . 'assets/js/gallery.js',
            [],
            $version,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ]
        );

        // Localized parameters for REST requests & Lightbox accessibility
        wp_localize_script('wpcal-eg-frontend', 'wpcalEgData', [
            'restUrl' => esc_url_raw(rest_url('wpcalibrate-event-galleries/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
            'i18n'    => [
                'loading'     => __('Loading photos...', 'wpcalibrate-event-galleries'),
                'loadMore'    => __('Load More Photos', 'wpcalibrate-event-galleries'),
                'noMore'      => __('All photos loaded', 'wpcalibrate-event-galleries'),
                'counter'     => /* translators: 1: current index, 2: total images */ __('%1$s / %2$s', 'wpcalibrate-event-galleries'),
                'errorLoad'   => __('Failed to load photos. Please try again.', 'wpcalibrate-event-galleries'),
                'download'    => __('Download photo', 'wpcalibrate-event-galleries'),
                'close'       => __('Close lightbox', 'wpcalibrate-event-galleries'),
                'prev'        => __('Previous photo', 'wpcalibrate-event-galleries'),
                'next'        => __('Next photo', 'wpcalibrate-event-galleries'),
            ],
        ]);
    }

    /**
     * Enqueue frontend assets on demand when a gallery is rendered.
     *
     * @return void
     */
    public function enqueue_frontend(): void {
        if ($this->frontend_enqueued) {
            return;
        }

        wp_enqueue_style('wpcal-eg-frontend');
        wp_enqueue_script('wpcal-eg-frontend');

        $this->frontend_enqueued = true;
    }
}
