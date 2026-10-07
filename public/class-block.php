<?php
/**
 * Gutenberg Dynamic Block Registration.
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
 * Class Block
 *
 * Handles server-side registration and rendering for wpcalibrate/event-gallery block.
 */
class Block {

    /**
     * Block name.
     *
     * @var string
     */
    public const BLOCK_NAME = 'wpcalibrate/event-gallery';

    /**
     * Gallery repository.
     *
     * @var Gallery_Repository
     */
    private Gallery_Repository $repository;

    /**
     * Gallery renderer.
     *
     * @var Gallery_Renderer
     */
    private Gallery_Renderer $renderer;

    /**
     * Constructor.
     *
     * @param Gallery_Repository $repository Repository.
     * @param Gallery_Renderer   $renderer   Renderer.
     */
    public function __construct(Gallery_Repository $repository, Gallery_Renderer $renderer) {
        $this->repository = $repository;
        $this->renderer   = $renderer;
    }

    /**
     * Initialize block hooks.
     *
     * @return void
     */
    public function init(): void {
        add_action('init', [$this, 'register_block']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
    }

    /**
     * Register the dynamic Gutenberg block.
     *
     * @return void
     */
    public function register_block(): void {
        $block_json = WPCALIBRATE_EVENT_GALLERIES_PATH . 'blocks/event-gallery/block.json';

        if (file_exists($block_json)) {
            register_block_type($block_json, [
                'render_callback' => [$this, 'render_block'],
            ]);
        }
    }

    /**
     * Enqueue block editor JavaScript and pass list of published galleries.
     *
     * @return void
     */
    public function enqueue_block_editor_assets(): void {
        $version = WPCALIBRATE_EVENT_GALLERIES_VERSION;

        wp_enqueue_script(
            'wpcal-eg-block-editor',
            WPCALIBRATE_EVENT_GALLERIES_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render'],
            $version,
            true
        );

        // Fetch published galleries for the inspector dropdown
        $galleries_query = new \WP_Query([
            'post_type'      => Post_Type::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        $gallery_options = [
            [
                'value' => '0',
                'label' => __('— Select an Event Gallery —', 'wpcalibrate-event-galleries'),
            ],
        ];

        if ($galleries_query->have_posts()) {
            foreach ($galleries_query->posts as $g_post) {
                if ($g_post instanceof \WP_Post) {
                    $gallery_options[] = [
                        'value' => (string) $g_post->ID,
                        'label' => esc_html($g_post->post_title ?: sprintf(__('Gallery #%d', 'wpcalibrate-event-galleries'), $g_post->ID)),
                    ];
                }
            }
        }

        wp_localize_script('wpcal-eg-block-editor', 'wpcalEgBlockData', [
            'galleries' => $gallery_options,
            'i18n'      => [
                'title'       => __('WPCalibrate Event Gallery', 'wpcalibrate-event-galleries'),
                'selectLabel' => __('Choose Event Gallery', 'wpcalibrate-event-galleries'),
                'selectDesc'  => __('Select which event gallery to render.', 'wpcalibrate-event-galleries'),
                'settings'    => __('Gallery Settings', 'wpcalibrate-event-galleries'),
                'layout'      => __('Layout Override', 'wpcalibrate-event-galleries'),
                'default'     => __('Default (From Gallery / Global)', 'wpcalibrate-event-galleries'),
                'grid'        => __('Grid', 'wpcalibrate-event-galleries'),
                'masonry'     => __('Masonry', 'wpcalibrate-event-galleries'),
                'noSelected'  => __('Please select an Event Gallery in the block settings to preview.', 'wpcalibrate-event-galleries'),
            ],
        ]);
    }

    /**
     * Render callback for dynamic block.
     *
     * @param array<string, mixed> $attributes Block attributes.
     * @return string Rendered HTML output.
     */
    public function render_block(array $attributes): string {
        $gallery_id = isset($attributes['galleryId']) ? (int) $attributes['galleryId'] : 0;
        if ($gallery_id <= 0) {
            if (is_admin()) {
                return '<div class="wpcal-eg-notice wpcal-eg-notice-info"><p>' .
                    esc_html__('Please select an Event Gallery in the block inspector sidebar.', 'wpcalibrate-event-galleries') .
                    '</p></div>';
            }
            return '';
        }

        $overrides = [];
        if (!empty($attributes['layout']) && in_array($attributes['layout'], ['grid', 'masonry'], true)) {
            $overrides['layout'] = $attributes['layout'];
        }
        if (!empty($attributes['columnsDesktop'])) {
            $overrides['columns_desktop'] = (int) $attributes['columnsDesktop'];
        }
        if (!empty($attributes['columnsTablet'])) {
            $overrides['columns_tablet'] = (int) $attributes['columnsTablet'];
        }
        if (!empty($attributes['columnsMobile'])) {
            $overrides['columns_mobile'] = (int) $attributes['columnsMobile'];
        }

        return $this->renderer->render_gallery($gallery_id, $overrides);
    }
}
