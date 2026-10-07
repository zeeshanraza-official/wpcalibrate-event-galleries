<?php
/**
 * Shortcode Handler.
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
 * Class Shortcode
 *
 * Handles registration and rendering of [wpcalibrate_event_gallery].
 */
class Shortcode {

    /**
     * Shortcode tag.
     *
     * @var string
     */
    public const SHORTCODE_TAG = 'wpcalibrate_event_gallery';

    /**
     * Gallery renderer.
     *
     * @var Gallery_Renderer
     */
    private Gallery_Renderer $renderer;

    /**
     * Constructor.
     *
     * @param Gallery_Renderer $renderer Renderer service.
     */
    public function __construct(Gallery_Renderer $renderer) {
        $this->renderer = $renderer;
    }

    /**
     * Initialize shortcode hook.
     *
     * @return void
     */
    public function init(): void {
        add_shortcode(self::SHORTCODE_TAG, [$this, 'render_shortcode']);
    }

    /**
     * Render the shortcode.
     *
     * @param array<string, mixed>|string $atts Shortcode attributes.
     * @return string Rendered HTML output.
     */
    public function render_shortcode(array|string $atts): string {
        $raw_atts = is_array($atts) ? $atts : [];

        $defaults = [
            'id'              => 0,
            'layout'          => '',
            'columns_desktop' => 0,
            'columns_tablet'  => 0,
            'columns_mobile'  => 0,
            'show_header'     => 'no',
        ];

        $parsed = shortcode_atts($defaults, $raw_atts, self::SHORTCODE_TAG);

        $gallery_id = (int) $parsed['id'];
        if ($gallery_id <= 0) {
            if (current_user_can('edit_posts')) {
                return '<div class="wpcal-eg-notice wpcal-eg-notice-warning"><p>' .
                    esc_html__('WPCalibrate Event Gallery: Please specify a valid gallery ID attribute, e.g. [wpcalibrate_event_gallery id="123"].', 'wpcalibrate-event-galleries') .
                    '</p></div>';
            }
            return '';
        }

        // Build overrides with strict allowlisting
        $overrides = [];

        if (!empty($parsed['layout'])) {
            $layout = sanitize_key((string) $parsed['layout']);
            if (in_array($layout, ['grid', 'masonry'], true)) {
                $overrides['layout'] = $layout;
            }
        }

        if (!empty($parsed['columns_desktop'])) {
            $col_d = (int) $parsed['columns_desktop'];
            if ($col_d >= 1 && $col_d <= 8) {
                $overrides['columns_desktop'] = $col_d;
            }
        }

        if (!empty($parsed['columns_tablet'])) {
            $col_t = (int) $parsed['columns_tablet'];
            if ($col_t >= 1 && $col_t <= 6) {
                $overrides['columns_tablet'] = $col_t;
            }
        }

        if (!empty($parsed['columns_mobile'])) {
            $col_m = (int) $parsed['columns_mobile'];
            if ($col_m >= 1 && $col_m <= 4) {
                $overrides['columns_mobile'] = $col_m;
            }
        }

        $overrides['show_header'] = ($parsed['show_header'] === 'yes');

        return $this->renderer->render_gallery($gallery_id, $overrides);
    }
}
