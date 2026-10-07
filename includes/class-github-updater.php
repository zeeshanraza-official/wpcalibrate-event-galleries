<?php
/**
 * GitHub In-Dashboard Plugin Updater.
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
 * Class GitHub_Updater
 *
 * Checks GitHub Releases API for updates and integrates with WordPress dashboard plugin updater.
 */
class GitHub_Updater {

    /**
     * GitHub repository owner/slug.
     *
     * @var string
     */
    private string $repository = 'zeeshanraza-official/wpcalibrate-event-galleries';

    /**
     * Current plugin slug.
     *
     * @var string
     */
    private string $plugin_slug = 'wpcalibrate-event-galleries';

    /**
     * Main plugin file relative to WP_PLUGIN_DIR.
     *
     * @var string
     */
    private string $plugin_basename = 'wpcalibrate-event-galleries/wpcalibrate-event-galleries.php';

    /**
     * Transient cache key.
     *
     * @var string
     */
    private string $transient_key = 'wpcalibrate_event_galleries_github_release';

    /**
     * Initialize updater hooks.
     *
     * @return void
     */
    public function init(): void {
        add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_post_install', [$this, 'post_install_rename'], 10, 3);
    }

    /**
     * Check GitHub for a newer release than current version.
     *
     * @param object|bool $transient Plugins update transient.
     * @return object|bool
     */
    public function check_for_update($transient) {
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if (empty($release) || empty($release['tag_name'])) {
            return $transient;
        }

        $remote_version = ltrim((string) $release['tag_name'], 'v');
        $current_version = WPCALIBRATE_EVENT_GALLERIES_VERSION;

        if (version_compare($remote_version, $current_version, '>')) {
            $download_url = $this->get_package_download_url($release);

            if (!empty($download_url)) {
                $obj = new \stdClass();
                $obj->slug        = $this->plugin_slug;
                $obj->plugin      = $this->plugin_basename;
                $obj->new_version = $remote_version;
                $obj->url         = $release['html_url'] ?? ('https://github.com/' . $this->repository);
                $obj->package     = $download_url;
                $obj->icons       = [
                    'default' => WPCALIBRATE_EVENT_GALLERIES_URL . 'branding/icon-dark.png',
                ];

                $transient->response[$this->plugin_basename] = $obj;
                unset($transient->no_update[$this->plugin_basename]);
            }
        }

        return $transient;
    }

    /**
     * Provide plugin information for the "View version details" modal popup.
     *
     * @param false|object|array $result Result object.
     * @param string             $action Action name.
     * @param object             $args Arguments object.
     * @return false|object
     */
    public function plugin_info($result, string $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $release = $this->get_latest_release();
        if (empty($release)) {
            return $result;
        }

        $remote_version = ltrim((string) ($release['tag_name'] ?? '1.0.0'), 'v');

        $info = new \stdClass();
        $info->name           = 'WPCalibrate Event Galleries';
        $info->slug           = $this->plugin_slug;
        $info->version        = $remote_version;
        $info->author         = '<a href="https://wpcalibrate.com">WPCalibrate</a>';
        $info->homepage       = 'https://github.com/' . $this->repository;
        $info->requires       = WPCALIBRATE_EVENT_GALLERIES_MIN_WP;
        $info->requires_php   = WPCALIBRATE_EVENT_GALLERIES_MIN_PHP;
        $info->download_link  = $this->get_package_download_url($release);
        $info->last_updated   = isset($release['published_at']) ? gmdate('Y-m-d', strtotime((string) $release['published_at'])) : gmdate('Y-m-d');
        $info->sections       = [
            'description' => esc_html__('Event photo gallery management with responsive grid and masonry layouts, accessible lightbox, and load more.', 'wpcalibrate-event-galleries'),
            'changelog'   => !empty($release['body']) ? nl2br(esc_html((string) $release['body'])) : esc_html__('See GitHub releases for details.', 'wpcalibrate-event-galleries'),
        ];

        return $info;
    }

    /**
     * Ensure the unzipped plugin directory name is always wpcalibrate-event-galleries.
     *
     * @param bool  $response Install response.
     * @param array $hook_extra Extra hook parameters.
     * @param array $result Result array.
     * @return array
     */
    public function post_install_rename(bool $response, array $hook_extra, array $result): array {
        global $wp_filesystem;

        if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
            return $result;
        }

        if (!isset($result['destination'])) {
            return $result;
        }

        $proper_destination = WP_PLUGIN_DIR . '/' . $this->plugin_slug;

        if ($result['destination'] !== $proper_destination && is_object($wp_filesystem)) {
            $wp_filesystem->move($result['destination'], $proper_destination);
            $result['destination'] = $proper_destination;
        }

        return $result;
    }

    /**
     * Fetch latest release from GitHub API with transient caching.
     *
     * @return array<string, mixed>
     */
    private function get_latest_release(): array {
        $cached = get_transient($this->transient_key);
        if (is_array($cached)) {
            return $cached;
        }

        $api_url = 'https://api.github.com/repos/' . $this->repository . '/releases/latest';

        $response = wp_remote_get($api_url, [
            'timeout'    => 5,
            'user-agent' => 'WordPress/WPCalibrate-Event-Galleries-' . WPCALIBRATE_EVENT_GALLERIES_VERSION,
            'headers'    => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            set_transient($this->transient_key, [], HOUR_IN_SECONDS);
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) {
            return [];
        }

        set_transient($this->transient_key, $data, 12 * HOUR_IN_SECONDS);
        return $data;
    }

    /**
     * Get download URL for release zip asset or fallback to zipball.
     *
     * @param array<string, mixed> $release GitHub release array.
     * @return string
     */
    private function get_package_download_url(array $release): string {
        if (!empty($release['assets']) && is_array($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (isset($asset['name']) && str_starts_with((string) $asset['name'], 'wpcalibrate-event-galleries') && str_ends_with((string) $asset['name'], '.zip')) {
                    return (string) ($asset['browser_download_url'] ?? '');
                }
            }
        }

        return (string) ($release['zipball_url'] ?? '');
    }
}
