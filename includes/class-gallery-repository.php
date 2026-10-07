<?php
/**
 * Gallery Repository & Data Access.
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
 * Class Gallery_Repository
 *
 * Handles database retrieval and formatting for Event Galleries and their attachments.
 */
class Gallery_Repository {

    /**
     * Settings service.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Constructor.
     *
     * @param Settings $settings Settings service.
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * Retrieve complete structured Event Gallery model.
     *
     * @param int $post_id Post ID.
     * @return array<string, mixed>|null
     */
    public function get_gallery(int $post_id): ?array {
        $post = get_post($post_id);
        if (!$post instanceof \WP_Post || $post->post_type !== Post_Type::POST_TYPE) {
            return null;
        }

        $raw_image_ids = get_post_meta($post_id, Meta::IMAGE_IDS, true);
        $image_ids     = is_array($raw_image_ids) ? array_map('intval', $raw_image_ids) : [];

        // Resolve cover image
        $cover_id  = (int) get_post_thumbnail_id($post_id);
        $cover_url = '';
        if ($cover_id > 0) {
            $cover_src = wp_get_attachment_image_src($cover_id, 'large');
            if ($cover_src) {
                $cover_url = $cover_src[0];
            }
        }

        // Layout overrides with fallback to global settings
        $stored_layout = (string) get_post_meta($post_id, Meta::LAYOUT, true);
        $layout        = ($stored_layout !== '' && $stored_layout !== 'default')
            ? $stored_layout
            : (string) $this->settings->get('default_layout', 'grid');

        // Column overrides
        $col_d = (int) get_post_meta($post_id, Meta::COLUMNS_DESKTOP, true);
        $columns_desktop = ($col_d > 0) ? $col_d : (int) $this->settings->get('default_columns_desktop', 3);

        $col_t = (int) get_post_meta($post_id, Meta::COLUMNS_TABLET, true);
        $columns_tablet = ($col_t > 0) ? $col_t : (int) $this->settings->get('default_columns_tablet', 2);

        $col_m = (int) get_post_meta($post_id, Meta::COLUMNS_MOBILE, true);
        $columns_mobile = ($col_m > 0) ? $col_m : (int) $this->settings->get('default_columns_mobile', 1);

        // Batch size override
        $stored_batch    = (int) get_post_meta($post_id, Meta::IMAGES_PER_PAGE, true);
        $images_per_page = ($stored_batch > 0) ? $stored_batch : (int) $this->settings->get('default_images_per_batch', 24);

        // Download override
        $stored_dl        = (string) get_post_meta($post_id, Meta::DOWNLOAD_ENABLED, true);
        $download_enabled = ($stored_dl !== '' && $stored_dl !== 'default')
            ? ($stored_dl === 'yes')
            : ($this->settings->get('default_download_enabled', 'no') === 'yes');

        return [
            'id'                => $post_id,
            'title'             => get_the_title($post_id),
            'content'           => $post->post_content,
            'excerpt'           => $post->post_excerpt,
            'status'            => $post->post_status,
            'password_required' => post_password_required($post),
            'menu_order'        => (int) $post->menu_order,
            'event_date'        => (string) get_post_meta($post_id, Meta::EVENT_DATE, true),
            'location'          => (string) get_post_meta($post_id, Meta::LOCATION, true),
            'cover_id'          => $cover_id,
            'cover_url'         => $cover_url,
            'image_ids'         => $image_ids,
            'photo_count'       => count($image_ids),
            'layout'            => $layout,
            'columns_desktop'   => $columns_desktop,
            'columns_tablet'    => $columns_tablet,
            'columns_mobile'    => $columns_mobile,
            'images_per_page'   => $images_per_page,
            'download_enabled'  => $download_enabled,
        ];
    }

    /**
     * Efficiently derive photo count from stored post meta without querying attachments.
     *
     * @param int $post_id Post ID.
     * @return int Photo count.
     */
    public function get_gallery_image_count(int $post_id): int {
        $raw = get_post_meta($post_id, Meta::IMAGE_IDS, true);
        if (is_array($raw)) {
            return count($raw);
        }
        return 0;
    }

    /**
     * Retrieve paginated list of image attachment data for a gallery.
     *
     * @param int $post_id  Post ID.
     * @param int $page     Page number (1-based).
     * @param int $per_page Images per page (0 uses gallery's batch size).
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int, has_more: bool}
     */
    public function get_gallery_images(int $post_id, int $page = 1, int $per_page = 0): array {
        $gallery = $this->get_gallery($post_id);
        if (!$gallery) {
            return [
                'items'       => [],
                'total'       => 0,
                'page'        => 1,
                'per_page'    => 24,
                'total_pages' => 0,
                'has_more'    => false,
            ];
        }

        $all_ids = $gallery['image_ids'];
        $total   = count($all_ids);

        if ($per_page <= 0) {
            $per_page = (int) $gallery['images_per_page'];
        }
        $per_page = max(1, $per_page);
        $page     = max(1, $page);

        $total_pages = (int) ceil($total / $per_page);
        $offset      = ($page - 1) * $per_page;
        $batch_ids   = array_slice($all_ids, $offset, $per_page);

        $items = [];
        foreach ($batch_ids as $attachment_id) {
            $image_data = $this->format_attachment_data($attachment_id, (bool) $gallery['download_enabled']);
            if ($image_data !== null) {
                $items[] = $image_data;
            }
        }

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => $total_pages,
            'has_more'    => $page < $total_pages,
        ];
    }

    /**
     * Format a single attachment into structured gallery item data.
     *
     * @param int  $attachment_id   Attachment ID.
     * @param bool $download_active Whether downloads are enabled for this gallery.
     * @return array<string, mixed>|null Null if attachment is invalid or deleted.
     */
    public function format_attachment_data(int $attachment_id, bool $download_active): ?array {
        $attachment = get_post($attachment_id);
        if (!$attachment instanceof \WP_Post || $attachment->post_type !== 'attachment') {
            return null;
        }

        if (!wp_attachment_is_image($attachment_id)) {
            return null;
        }

        // Full resolution image for lightbox
        $full_src = wp_get_attachment_image_src($attachment_id, 'full');
        if (!$full_src) {
            return null;
        }

        // Thumbnail / medium-large for grid
        $thumb_src = wp_get_attachment_image_src($attachment_id, 'large');
        if (!$thumb_src) {
            $thumb_src = $full_src;
        }

        $alt_text = (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $caption  = $attachment->post_excerpt;
        $title    = $attachment->post_title;

        // Generate responsive image tag HTML with native lazy loading
        $img_html = wp_get_attachment_image(
            $attachment_id,
            'large',
            false,
            [
                'class'   => 'wpcal-eg-img',
                'loading' => 'lazy',
                'alt'     => esc_attr($alt_text),
            ]
        );

        return [
            'id'           => $attachment_id,
            'full_url'     => $full_src[0],
            'full_width'   => $full_src[1],
            'full_height'  => $full_src[2],
            'thumb_url'    => $thumb_src[0],
            'thumb_width'  => $thumb_src[1],
            'thumb_height' => $thumb_src[2],
            'title'        => $title,
            'caption'      => $caption,
            'alt'          => $alt_text,
            'html'         => $img_html,
            'download_url' => $download_active ? wp_get_attachment_url($attachment_id) : '',
        ];
    }

    /**
     * Build WP_Query for Event Galleries archive.
     *
     * @param array<string, mixed> $extra_args Additional query arguments.
     * @return \WP_Query
     */
    public function get_archive_query(array $extra_args = []): \WP_Query {
        $sort_order = (string) $this->settings->get('archive_sort_order', 'date_desc');
        $per_page   = (int) $this->settings->get('archive_events_per_page', 12);
        $paged      = max(1, (int) get_query_var('paged', 1));

        $query_args = [
            'post_type'      => Post_Type::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'no_found_rows'  => false,
        ];

        // Apply sort order
        switch ($sort_order) {
            case 'date_asc':
                $query_args['meta_key'] = Meta::EVENT_DATE;
                $query_args['orderby']  = 'meta_value';
                $query_args['order']    = 'ASC';
                break;

            case 'title_asc':
                $query_args['orderby'] = 'title';
                $query_args['order']   = 'ASC';
                break;

            case 'title_desc':
                $query_args['orderby'] = 'title';
                $query_args['order']   = 'DESC';
                break;

            case 'menu_order':
                $query_args['orderby'] = 'menu_order title';
                $query_args['order']   = 'ASC';
                break;

            case 'date_desc':
            default:
                $query_args['meta_key'] = Meta::EVENT_DATE;
                $query_args['orderby']  = 'meta_value date';
                $query_args['order']    = 'DESC';
                break;
        }

        $merged = wp_parse_args($extra_args, $query_args);

        /**
         * Filter archive query arguments.
         *
         * @param array $merged WP_Query arguments.
         */
        $filtered = apply_filters('wpcal_eg_archive_query_args', $merged);

        return new \WP_Query($filtered);
    }
}
