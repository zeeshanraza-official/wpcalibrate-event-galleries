<?php
/**
 * Frontend Presentation Coordinator.
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
 * Class Frontend
 *
 * Coordinates frontend single event views, archive rendering, and conditional asset loading.
 */
class Frontend {

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
     * @param Gallery_Repository $repository Repository.
     * @param Gallery_Renderer   $renderer   Renderer.
     * @param Settings           $settings   Settings.
     * @param Assets             $assets     Assets.
     */
    public function __construct(Gallery_Repository $repository, Gallery_Renderer $renderer, Settings $settings, Assets $assets) {
        $this->repository = $repository;
        $this->renderer   = $renderer;
        $this->settings   = $settings;
        $this->assets     = $assets;
    }

    /**
     * Initialize frontend hooks.
     *
     * @return void
     */
    public function init(): void {
        add_filter('the_content', [$this, 'filter_single_event_content'], 20);
        add_filter('template_include', [$this, 'filter_archive_template'], 20);
    }

    /**
     * Filter single event post content to automatically append the gallery and meta bar.
     *
     * Respects the active theme structure while preventing duplicate rendering if
     * the gallery shortcode or block is already inserted in post_content.
     *
     * @param string $content Existing post content.
     * @return string Modified content.
     */
    public function filter_single_event_content(string $content): string {
        if (!is_singular(Post_Type::POST_TYPE) || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $post_id = get_the_ID();
        if (!$post_id) {
            return $content;
        }

        // Check if shortcode or block is already present to prevent duplicate rendering
        $has_shortcode = has_shortcode($content, 'wpcalibrate_event_gallery');
        $has_block     = has_block('wpcalibrate/event-gallery', $post_id);

        if ($has_shortcode || $has_block) {
            return $content;
        }

        // Render gallery with event details header enabled for single page
        $gallery_html = $this->renderer->render_gallery($post_id, [
            'show_header' => true,
        ]);

        return $content . "\n\n" . $gallery_html;
    }

    /**
     * Filter archive template.
     *
     * If active theme does not have a dedicated archive-wpcal_event_gallery.php template,
     * injects our clean archive renderer into the loop.
     *
     * @param string $template Matched template file path.
     * @return string Filtered template file path.
     */
    public function filter_archive_template(string $template): string {
        if (is_post_type_archive(Post_Type::POST_TYPE)) {
            // Check if theme has its own template
            $theme_template = locate_template(['archive-wpcal_event_gallery.php']);
            if (!empty($theme_template)) {
                return $theme_template;
            }

            // Hook the loop output in themes using index/archive fallback
            add_filter('the_content', [$this, 'render_archive_in_content'], 20);
        }

        return $template;
    }

    /**
     * Fallback loop content replacement for theme archive views without a custom archive template.
     *
     * @param string $content Content.
     * @return string Archive grid content.
     */
    public function render_archive_in_content(string $content): string {
        if (is_post_type_archive(Post_Type::POST_TYPE) && in_the_loop()) {
            return $this->renderer->render_archive();
        }

        return $content;
    }
}
