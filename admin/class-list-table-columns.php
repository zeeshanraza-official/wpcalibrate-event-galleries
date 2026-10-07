<?php
/**
 * Event Gallery Admin List Table Custom Columns.
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
 * Class List_Table_Columns
 *
 * Adds and renders custom columns in edit.php for wpcal_event_gallery.
 */
class List_Table_Columns {

    /**
     * Gallery repository.
     *
     * @var Gallery_Repository
     */
    private Gallery_Repository $repository;

    /**
     * Constructor.
     *
     * @param Gallery_Repository $repository Repository.
     */
    public function __construct(Gallery_Repository $repository) {
        $this->repository = $repository;
    }

    /**
     * Initialize list table hooks.
     *
     * @return void
     */
    public function init(): void {
        $post_type = Post_Type::POST_TYPE;
        add_filter("manage_{$post_type}_posts_columns", [$this, 'customize_columns']);
        add_action("manage_{$post_type}_posts_custom_column", [$this, 'render_column_content'], 10, 2);
        add_filter("manage_edit-{$post_type}_sortable_columns", [$this, 'register_sortable_columns']);
        add_action('pre_get_posts', [$this, 'handle_column_sorting']);
    }

    /**
     * Define custom column order and headers.
     *
     * @param array<string, string> $columns Existing columns.
     * @return array<string, string> Modified columns.
     */
    public function customize_columns(array $columns): array {
        $new_columns = [];

        foreach ($columns as $key => $title) {
            if ($key === 'title') {
                $new_columns['wpcal_cover'] = __('Cover', 'wpcalibrate-event-galleries');
                $new_columns['title']       = $title;
                $new_columns['wpcal_date']  = __('Event Date', 'wpcalibrate-event-galleries');
                $new_columns['wpcal_loc']   = __('Location', 'wpcalibrate-event-galleries');
                $new_columns['wpcal_count'] = __('Photos', 'wpcalibrate-event-galleries');
                $new_columns['wpcal_code']  = __('Shortcode', 'wpcalibrate-event-galleries');
            } else {
                $new_columns[$key] = $title;
            }
        }

        return $new_columns;
    }

    /**
     * Render cell content for custom columns.
     *
     * @param string $column_name Column identifier.
     * @param int    $post_id     Post ID.
     * @return void
     */
    public function render_column_content(string $column_name, int $post_id): void {
        switch ($column_name) {
            case 'wpcal_cover':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [50, 50], [
                        'class' => 'wpcal-eg-admin-list-thumb',
                        'alt'   => get_the_title($post_id),
                    ]);
                } else {
                    $raw_ids = get_post_meta($post_id, Meta::IMAGE_IDS, true);
                    if (is_array($raw_ids) && !empty($raw_ids[0])) {
                        echo wp_get_attachment_image((int) $raw_ids[0], [50, 50], false, [
                            'class' => 'wpcal-eg-admin-list-thumb',
                            'alt'   => get_the_title($post_id),
                        ]);
                    } else {
                        echo '<span class="wpcal-eg-list-no-cover dashicons dashicons-format-image" aria-hidden="true"></span>';
                    }
                }
                break;

            case 'wpcal_date':
                $date = (string) get_post_meta($post_id, Meta::EVENT_DATE, true);
                if (!empty($date)) {
                    $formatted = wp_date((string) get_option('date_format', 'F j, Y'), strtotime($date));
                    echo esc_html($formatted ?: $date);
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;

            case 'wpcal_loc':
                $location = (string) get_post_meta($post_id, Meta::LOCATION, true);
                if (!empty($location)) {
                    echo esc_html($location);
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;

            case 'wpcal_count':
                $count = $this->repository->get_gallery_image_count($post_id);
                printf(
                    '<span class="wpcal-eg-count-badge">%s</span>',
                    esc_html(sprintf(_n('%d photo', '%d photos', $count, 'wpcalibrate-event-galleries'), $count))
                );
                break;

            case 'wpcal_code':
                $code = sprintf('[wpcalibrate_event_gallery id="%d"]', $post_id);
                printf(
                    '<code class="wpcal-eg-copyable-code" title="%s">%s</code>',
                    esc_attr__('Click to copy shortcode', 'wpcalibrate-event-galleries'),
                    esc_html($code)
                );
                break;
        }
    }

    /**
     * Declare sortable columns.
     *
     * @param array<string, string> $columns Sortable columns.
     * @return array<string, string> Modified sortable columns.
     */
    public function register_sortable_columns(array $columns): array {
        $columns['wpcal_date'] = 'wpcal_event_date';
        return $columns;
    }

    /**
     * Safely apply orderby query modification for sortable columns.
     *
     * @param \WP_Query $query Main query.
     * @return void
     */
    public function handle_column_sorting(\WP_Query $query): void {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('post_type') !== Post_Type::POST_TYPE) {
            return;
        }

        $orderby = $query->get('orderby');

        if ($orderby === 'wpcal_event_date') {
            $query->set('meta_key', Meta::EVENT_DATE);
            $query->set('orderby', 'meta_value');
        }
    }
}
