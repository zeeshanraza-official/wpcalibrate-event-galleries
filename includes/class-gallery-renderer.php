<?php
/**
 * Unified Gallery Frontend Renderer.
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
 * Class Gallery_Renderer
 *
 * Shared presentation service for single event pages, shortcodes, Gutenberg blocks, and REST responses.
 */
class Gallery_Renderer {

    /**
     * Gallery repository.
     *
     * @var Gallery_Repository
     */
    private Gallery_Repository $repository;

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
     * @param Settings           $settings   Settings.
     * @param Assets             $assets     Assets.
     */
    public function __construct(Gallery_Repository $repository, Settings $settings, Assets $assets) {
        $this->repository = $repository;
        $this->settings   = $settings;
        $this->assets     = $assets;
    }

    /**
     * Render an Event Gallery.
     *
     * @param int                  $post_id   Event Gallery Post ID.
     * @param array<string, mixed> $overrides Shortcode / block display overrides.
     * @return string Rendered HTML.
     */
    public function render_gallery(int $post_id, array $overrides = []): string {
        $gallery = $this->repository->get_gallery($post_id);
        if (!$gallery) {
            if (current_user_can('edit_posts')) {
                return '<div class="wpcal-eg-notice wpcal-eg-notice-warning"><p>' .
                    sprintf(
                        /* translators: %d: Event Gallery Post ID */
                        esc_html__('Event Gallery #%d not found or has been deleted.', 'wpcalibrate-event-galleries'),
                        $post_id
                    ) .
                    '</p></div>';
            }
            return '';
        }

        // Verify status permission
        $status = (string) $gallery['status'];
        if ($status !== 'publish' && !current_user_can('edit_post', $post_id)) {
            return '';
        }

        // Check password protection
        $post = get_post($post_id);
        if ($post instanceof \WP_Post && post_password_required($post)) {
            return $this->render_password_form($post);
        }

        // Enqueue frontend scripts and styles
        $this->assets->enqueue_frontend();

        // Resolve display parameters (overrides > post meta > global defaults)
        $layout = !empty($overrides['layout']) ? sanitize_key((string) $overrides['layout']) : (string) $gallery['layout'];
        if (!in_array($layout, ['grid', 'masonry'], true)) {
            $layout = 'grid';
        }

        $col_d = !empty($overrides['columns_desktop']) ? (int) $overrides['columns_desktop'] : (int) $gallery['columns_desktop'];
        $columns_desktop = max(1, min(8, $col_d));

        $col_t = !empty($overrides['columns_tablet']) ? (int) $overrides['columns_tablet'] : (int) $gallery['columns_tablet'];
        $columns_tablet = max(1, min(6, $col_t));

        $col_m = !empty($overrides['columns_mobile']) ? (int) $overrides['columns_mobile'] : (int) $gallery['columns_mobile'];
        $columns_mobile = max(1, min(4, $col_m));

        $gap = (int) $this->settings->get('default_image_gap', 16);
        $gap = max(0, min(60, $gap));

        $images_per_page = (int) $gallery['images_per_page'];
        $download_active = (bool) $gallery['download_enabled'];

        // Fetch initial batch
        $batch_data   = $this->repository->get_gallery_images($post_id, 1, $images_per_page);
        $images       = $batch_data['items'];
        $total_images = $batch_data['total'];
        $has_more     = $batch_data['has_more'];

        // Determine if we should show the event details header
        $show_header = isset($overrides['show_header']) ? (bool) $overrides['show_header'] : false;

        $style_vars = sprintf(
            '--wpcal-cols-d: %d; --wpcal-cols-t: %d; --wpcal-cols-m: %d; --wpcal-gap: %dpx;',
            $columns_desktop,
            $columns_tablet,
            $columns_mobile,
            $gap
        );

        $wrapper_classes = [
            'wpcal-eg-container',
            'wpcal-eg-layout-' . $layout,
        ];

        /**
         * Filter gallery container CSS classes.
         *
         * @param string[] $wrapper_classes List of CSS classes.
         * @param array    $gallery         Gallery data array.
         */
        $filtered_classes = apply_filters('wpcal_eg_container_classes', $wrapper_classes, $gallery);
        $class_attr       = esc_attr(implode(' ', $filtered_classes));

        ob_start();
        ?>
        <div id="wpcal-eg-<?php echo esc_attr((string) $post_id); ?>" class="<?php echo $class_attr; ?>" style="<?php echo esc_attr($style_vars); ?>" data-gallery-id="<?php echo esc_attr((string) $post_id); ?>">
            <?php if ($show_header) : ?>
                <?php echo $this->render_event_meta_bar($gallery); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>

            <?php if (empty($images)) : ?>
                <div class="wpcal-eg-empty">
                    <p><?php esc_html_e('No photos have been added to this event gallery yet.', 'wpcalibrate-event-galleries'); ?></p>
                </div>
            <?php else : ?>
                <div class="wpcal-eg-grid" role="region" aria-label="<?php echo esc_attr(sprintf(__('Photo gallery: %s', 'wpcalibrate-event-galleries'), $gallery['title'])); ?>">
                    <?php echo $this->render_image_batch($images, $layout, $download_active, 0); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <?php if ($has_more) : ?>
                    <div class="wpcal-eg-load-more-wrap">
                        <button type="button"
                                class="wpcal-eg-btn wpcal-eg-btn-load-more"
                                data-gallery-id="<?php echo esc_attr((string) $post_id); ?>"
                                data-next-page="2"
                                data-per-page="<?php echo esc_attr((string) $images_per_page); ?>"
                                data-total="<?php echo esc_attr((string) $total_images); ?>"
                                aria-label="<?php esc_attr_e('Load more event photos', 'wpcalibrate-event-galleries'); ?>">
                            <span class="wpcal-eg-btn-text"><?php esc_html_e('Load More Photos', 'wpcalibrate-event-galleries'); ?></span>
                            <span class="wpcal-eg-btn-spinner" aria-hidden="true"></span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php echo $this->render_lightbox_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render a batch of gallery images.
     *
     * @param array<int, array<string, mixed>> $images          Array of formatted image data.
     * @param string                           $layout          'grid' or 'masonry'.
     * @param bool                             $download_active Whether downloads are enabled.
     * @param int                              $start_index     Index offset for lightbox tracking.
     * @return string Rendered HTML.
     */
    public function render_image_batch(array $images, string $layout, bool $download_active, int $start_index = 0): string {
        if (empty($images)) {
            return '';
        }

        ob_start();
        $index = $start_index;

        foreach ($images as $img) :
            $index++;
            $full_url     = esc_url((string) $img['full_url']);
            $thumb_html   = $img['html'];
            $caption      = !empty($img['caption']) ? (string) $img['caption'] : '';
            $title        = !empty($img['title']) ? (string) $img['title'] : '';
            $alt          = !empty($img['alt']) ? (string) $img['alt'] : '';
            $download_url = $download_active ? esc_url((string) $img['download_url']) : '';
            $item_id      = (int) $img['id'];

            $caption_attr = esc_attr($caption);
            $title_attr   = esc_attr($title);
            $alt_attr     = esc_attr($alt);
            ?>
            <figure class="wpcal-eg-item" data-index="<?php echo esc_attr((string) $index); ?>" data-attachment-id="<?php echo esc_attr((string) $item_id); ?>">
                <a href="<?php echo $full_url; ?>"
                   class="wpcal-eg-thumbnail-link"
                   data-full="<?php echo $full_url; ?>"
                   data-index="<?php echo esc_attr((string) $index); ?>"
                   data-title="<?php echo $title_attr; ?>"
                   data-caption="<?php echo $caption_attr; ?>"
                   data-download="<?php echo $download_url; ?>"
                   aria-haspopup="dialog"
                   aria-label="<?php echo esc_attr(!empty($caption) ? $caption : (!empty($title) ? $title : sprintf(__('Enlarge photo %d', 'wpcalibrate-event-galleries'), $index))); ?>">
                    <div class="wpcal-eg-img-wrapper">
                        <?php echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <div class="wpcal-eg-overlay" aria-hidden="true">
                            <span class="wpcal-eg-zoom-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                            </span>
                        </div>
                    </div>
                </a>
                <?php if (!empty($caption)) : ?>
                    <figcaption class="wpcal-eg-caption"><?php echo esc_html($caption); ?></figcaption>
                <?php endif; ?>
            </figure>
            <?php
        endforeach;

        return (string) ob_get_clean();
    }

    /**
     * Render the metadata bar (Date, Location, Photo Count).
     *
     * @param array<string, mixed> $gallery Gallery data array.
     * @return string Rendered HTML.
     */
    public function render_event_meta_bar(array $gallery): string {
        $event_date  = (string) $gallery['event_date'];
        $location    = (string) $gallery['location'];
        $photo_count = (int) $gallery['photo_count'];

        ob_start();
        ?>
        <div class="wpcal-eg-meta-bar">
            <?php if (!empty($event_date)) : ?>
                <div class="wpcal-eg-meta-item wpcal-eg-meta-date">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <time datetime="<?php echo esc_attr($event_date); ?>">
                        <?php echo esc_html(wp_date((string) get_option('date_format', 'F j, Y'), strtotime($event_date)) ?: $event_date); ?>
                    </time>
                </div>
            <?php endif; ?>

            <?php if (!empty($location)) : ?>
                <div class="wpcal-eg-meta-item wpcal-eg-meta-location">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><?php echo esc_html($location); ?></span>
                </div>
            <?php endif; ?>

            <div class="wpcal-eg-meta-item wpcal-eg-meta-count">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <span>
                    <?php
                    /* translators: %d: number of photos */
                    echo esc_html(sprintf(_n('%d Photo', '%d Photos', $photo_count, 'wpcalibrate-event-galleries'), $photo_count));
                    ?>
                </span>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render the accessible lightbox modal markup.
     *
     * @return string Lightbox HTML.
     */
    public function render_lightbox_markup(): string {
        ob_start();
        ?>
        <div class="wpcal-eg-lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Photo Lightbox', 'wpcalibrate-event-galleries'); ?>" style="display: none;" tabindex="-1">
            <div class="wpcal-eg-lightbox-backdrop" aria-hidden="true"></div>
            <div class="wpcal-eg-lightbox-content" role="document">
                <div class="wpcal-eg-lightbox-header">
                    <div class="wpcal-eg-lightbox-counter" aria-live="polite">
                        <span class="wpcal-eg-current-idx">1</span> / <span class="wpcal-eg-total-idx">1</span>
                    </div>
                    <div class="wpcal-eg-lightbox-actions">
                        <a href="#" class="wpcal-eg-lightbox-download wpcal-eg-lightbox-btn" download aria-label="<?php esc_attr_e('Download high-resolution photo', 'wpcalibrate-event-galleries'); ?>" style="display: none;" target="_blank" rel="noopener noreferrer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        </a>
                        <button type="button" class="wpcal-eg-lightbox-close wpcal-eg-lightbox-btn" aria-label="<?php esc_attr_e('Close lightbox (Escape)', 'wpcalibrate-event-galleries'); ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                </div>

                <div class="wpcal-eg-lightbox-stage">
                    <button type="button" class="wpcal-eg-lightbox-prev wpcal-eg-lightbox-nav-btn" aria-label="<?php esc_attr_e('Previous photo (Left arrow)', 'wpcalibrate-event-galleries'); ?>">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>

                    <div class="wpcal-eg-lightbox-media-wrap">
                        <div class="wpcal-eg-lightbox-spinner" aria-label="<?php esc_attr_e('Loading photo...', 'wpcalibrate-event-galleries'); ?>"></div>
                        <img src="" alt="" class="wpcal-eg-lightbox-img" />
                    </div>

                    <button type="button" class="wpcal-eg-lightbox-next wpcal-eg-lightbox-nav-btn" aria-label="<?php esc_attr_e('Next photo (Right arrow)', 'wpcalibrate-event-galleries'); ?>">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>

                <div class="wpcal-eg-lightbox-footer">
                    <div class="wpcal-eg-lightbox-caption"></div>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render the password protection barrier form.
     *
     * @param \WP_Post $post Post object.
     * @return string HTML output.
     */
    private function render_password_form(\WP_Post $post): string {
        ob_start();
        ?>
        <div class="wpcal-eg-protected-wrap">
            <div class="wpcal-eg-protected-card">
                <div class="wpcal-eg-protected-icon" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h3><?php esc_html_e('This Event Gallery is Password Protected', 'wpcalibrate-event-galleries'); ?></h3>
                <p><?php esc_html_e('Please enter the password below to view this event photo gallery.', 'wpcalibrate-event-galleries'); ?></p>
                <?php echo get_the_password_form($post); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render an individual card in the event galleries archive.
     *
     * @param \WP_Post             $post     Post object.
     * @param array<string, mixed> $settings Plugin settings.
     * @return string Card HTML.
     */
    public function render_archive_card(\WP_Post $post, array $settings): string {
        $post_id     = $post->ID;
        $title       = get_the_title($post);
        $permalink   = get_permalink($post);
        $is_locked   = post_password_required($post);
        $photo_count = $this->repository->get_gallery_image_count($post_id);
        $event_date  = (string) get_post_meta($post_id, Meta::EVENT_DATE, true);
        $location    = (string) get_post_meta($post_id, Meta::LOCATION, true);

        // Resolve cover image safely without leaking photos if password required
        $cover_html = '';
        if (!$is_locked && has_post_thumbnail($post_id)) {
            $cover_html = get_the_post_thumbnail($post_id, 'medium_large', [
                'class'   => 'wpcal-eg-card-img',
                'loading' => 'lazy',
                'alt'     => esc_attr($title),
            ]);
        } elseif (!$is_locked && $photo_count > 0) {
            // Fallback to first gallery image if no featured cover set
            $raw_ids = get_post_meta($post_id, Meta::IMAGE_IDS, true);
            if (is_array($raw_ids) && !empty($raw_ids[0])) {
                $cover_html = wp_get_attachment_image((int) $raw_ids[0], 'medium_large', false, [
                    'class'   => 'wpcal-eg-card-img',
                    'loading' => 'lazy',
                    'alt'     => esc_attr($title),
                ]);
            }
        }

        ob_start();
        ?>
        <article class="wpcal-eg-card <?php echo $is_locked ? 'wpcal-eg-card-protected' : ''; ?>">
            <a href="<?php echo esc_url($permalink); ?>" class="wpcal-eg-card-link" aria-label="<?php echo esc_attr($title); ?>">
                <div class="wpcal-eg-card-media">
                    <?php if (!empty($cover_html)) : ?>
                        <?php echo $cover_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else : ?>
                        <div class="wpcal-eg-card-placeholder" aria-hidden="true">
                            <?php if ($is_locked) : ?>
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <?php else : ?>
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($settings['archive_show_photo_count'] === 'yes' && !$is_locked && $photo_count > 0) : ?>
                        <span class="wpcal-eg-card-badge">
                            <?php echo esc_html(sprintf(_n('%d photo', '%d photos', $photo_count, 'wpcalibrate-event-galleries'), $photo_count)); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($is_locked) : ?>
                        <span class="wpcal-eg-card-badge wpcal-eg-badge-locked">
                            <?php esc_html_e('Protected', 'wpcalibrate-event-galleries'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="wpcal-eg-card-body">
                    <h3 class="wpcal-eg-card-title"><?php echo esc_html($title); ?></h3>

                    <div class="wpcal-eg-card-meta">
                        <?php if ($settings['archive_show_date'] === 'yes' && !empty($event_date)) : ?>
                            <span class="wpcal-eg-card-date">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <time datetime="<?php echo esc_attr($event_date); ?>">
                                    <?php echo esc_html(wp_date((string) get_option('date_format', 'F j, Y'), strtotime($event_date)) ?: $event_date); ?>
                                </time>
                            </span>
                        <?php endif; ?>

                        <?php if ($settings['archive_show_location'] === 'yes' && !empty($location)) : ?>
                            <span class="wpcal-eg-card-location">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span><?php echo esc_html($location); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Render the complete archive output with pagination.
     *
     * @return string Archive HTML.
     */
    public function render_archive(): string {
        $this->assets->enqueue_frontend();

        $query    = $this->repository->get_archive_query();
        $settings = $this->settings->get_all();

        ob_start();
        ?>
        <div class="wpcal-eg-archive-wrap">
            <header class="wpcal-eg-archive-header">
                <h1 class="wpcal-eg-archive-title"><?php esc_html_e('Event Galleries', 'wpcalibrate-event-galleries'); ?></h1>
            </header>

            <?php if ($query->have_posts()) : ?>
                <div class="wpcal-eg-archive-grid">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php
                        $post = get_post();
                        if ($post instanceof \WP_Post) {
                            echo $this->render_archive_card($post, $settings); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

                <?php
                $pagination_links = paginate_links([
                    'total'     => (int) $query->max_num_pages,
                    'current'   => max(1, (int) get_query_var('paged', 1)),
                    'prev_text' => __('&laquo; Previous', 'wpcalibrate-event-galleries'),
                    'next_text' => __('Next &raquo;', 'wpcalibrate-event-galleries'),
                    'type'      => 'plain',
                ]);

                if (!empty($pagination_links)) :
                    ?>
                    <nav class="wpcal-eg-pagination" aria-label="<?php esc_attr_e('Event Galleries Pagination', 'wpcalibrate-event-galleries'); ?>">
                        <?php echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </nav>
                <?php endif; ?>
            <?php else : ?>
                <div class="wpcal-eg-empty">
                    <p><?php esc_html_e('No event galleries found.', 'wpcalibrate-event-galleries'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
