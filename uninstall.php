<?php
/**
 * Plugin Uninstaller.
 *
 * Runs automatically when WPCalibrate Event Galleries is deleted via the WordPress admin.
 *
 * @package WPCalibrate\EventGalleries
 */

declare(strict_types=1);

// Prevent direct file access.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check data retention preference.
$settings = get_option('wpcal_eg_settings', []);
$should_delete = is_array($settings) && isset($settings['delete_data_on_uninstall']) && $settings['delete_data_on_uninstall'] === 'yes';

// If retention is ON (deletion is OFF), preserve user content and settings safely.
if (!$should_delete) {
    return;
}

// 1. Remove all Event Gallery posts and their associated metadata.
// Photos in the WordPress Media Library are NEVER deleted.
$gallery_posts = get_posts([
    'post_type'      => 'wpcal_event_gallery',
    'post_status'    => 'any',
    'numberposts'    => -1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
]);

if (!empty($gallery_posts)) {
    foreach ($gallery_posts as $post_id) {
        // Force delete post and all its postmeta
        wp_delete_post((int) $post_id, true);
    }
}

// 2. Remove plugin-owned options.
delete_option('wpcal_eg_settings');
delete_option('wpcalibrate_event_galleries_db_version');
delete_option('wpcalibrate_event_galleries_version');

// 3. Remove custom capabilities from Administrator and Editor roles.
$roles_to_clean = ['administrator', 'editor'];
$primitive_caps = [
    'edit_event_galleries',
    'edit_others_event_galleries',
    'publish_event_galleries',
    'read_private_event_galleries',
    'delete_event_galleries',
    'delete_others_event_galleries',
    'delete_published_event_galleries',
    'delete_private_event_galleries',
    'edit_published_event_galleries',
];

foreach ($roles_to_clean as $role_name) {
    $role = get_role($role_name);
    if ($role instanceof \WP_Role) {
        foreach ($primitive_caps as $cap) {
            $role->remove_cap($cap);
        }
    }
}

// 4. Flush rewrite rules cleanly.
flush_rewrite_rules(false);
