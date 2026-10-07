<?php
/**
 * REST API Controller for Event Galleries.
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
 * Class REST_Controller
 *
 * Exposes secure REST API routes for dynamic batch loading of gallery images.
 */
class REST_Controller extends \WP_REST_Controller {

    /**
     * REST API Route Namespace.
     *
     * @var string
     */
    protected $namespace = 'wpcalibrate-event-galleries/v1';

    /**
     * REST API Base.
     *
     * @var string
     */
    protected $rest_base = 'gallery';

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
     * Register REST API routes on rest_api_init.
     *
     * @return void
     */
    public function register_routes(): void {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)/images',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_gallery_images'],
                    'permission_callback' => [$this, 'check_read_permission'],
                    'args'                => [
                        'id'       => [
                            'description' => __('Unique identifier for the Event Gallery.', 'wpcalibrate-event-galleries'),
                            'type'        => 'integer',
                            'required'    => true,
                            'minimum'     => 1,
                        ],
                        'page'     => [
                            'description' => __('Current page of the collection.', 'wpcalibrate-event-galleries'),
                            'type'        => 'integer',
                            'default'     => 1,
                            'minimum'     => 1,
                        ],
                        'per_page' => [
                            'description' => __('Maximum number of images to return.', 'wpcalibrate-event-galleries'),
                            'type'        => 'integer',
                            'default'     => 24,
                            'minimum'     => 1,
                            'maximum'     => 100,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Verify whether the requester has permission to read the event gallery images.
     *
     * @param \WP_REST_Request $request REST request.
     * @return bool|\WP_Error True if authorized, WP_Error otherwise.
     */
    public function check_read_permission(\WP_REST_Request $request): bool|\WP_Error {
        $post_id = (int) $request->get_param('id');
        $post    = get_post($post_id);

        if (!$post instanceof \WP_Post || $post->post_type !== Post_Type::POST_TYPE) {
            return new \WP_Error(
                'rest_gallery_not_found',
                __('Event gallery not found.', 'wpcalibrate-event-galleries'),
                ['status' => 404]
            );
        }

        // Unpublished gallery permissions
        if ($post->post_status !== 'publish') {
            if (!current_user_can('read_post', $post_id)) {
                return new \WP_Error(
                    'rest_gallery_forbidden',
                    __('You do not have permission to view this event gallery.', 'wpcalibrate-event-galleries'),
                    ['status' => 403]
                );
            }
        }

        // Post Password protection check
        if (post_password_required($post)) {
            return new \WP_Error(
                'rest_gallery_password_required',
                __('This event gallery is password-protected. Password authorization is required.', 'wpcalibrate-event-galleries'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Retrieve a batch of gallery images.
     *
     * @param \WP_REST_Request $request REST request.
     * @return \WP_REST_Response|\WP_Error Response object.
     */
    public function get_gallery_images(\WP_REST_Request $request): \WP_REST_Response|\WP_Error {
        $post_id  = (int) $request->get_param('id');
        $page     = (int) $request->get_param('page');
        $per_page = (int) $request->get_param('per_page');

        $gallery = $this->repository->get_gallery($post_id);
        if (!$gallery) {
            return new \WP_Error(
                'rest_gallery_not_found',
                __('Event gallery not found.', 'wpcalibrate-event-galleries'),
                ['status' => 404]
            );
        }

        $batch_data = $this->repository->get_gallery_images($post_id, $page, $per_page);
        $images     = $batch_data['items'];
        $layout     = (string) $gallery['layout'];
        $downloads  = (bool) $gallery['download_enabled'];

        // Calculate offset index for lightbox tracking
        $start_index = ($page - 1) * $per_page;
        $html        = $this->renderer->render_image_batch($images, $layout, $downloads, $start_index);

        $response_data = [
            'success'      => true,
            'gallery_id'   => $post_id,
            'page'         => $batch_data['page'],
            'per_page'     => $batch_data['per_page'],
            'total'        => $batch_data['total'],
            'total_pages'  => $batch_data['total_pages'],
            'has_more'     => $batch_data['has_more'],
            'html'         => $html,
            'images'       => $images,
        ];

        return new \WP_REST_Response($response_data, 200);
    }
}
