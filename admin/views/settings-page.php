<?php
/**
 * Settings Page View.
 *
 * @package WPCalibrate\EventGalleries
 *
 * @var array<string, mixed> $settings
 * @var array<string, string> $tabs
 * @var string $active_tab
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

$notice        = isset($_GET['wpcalibrate_notice']) ? sanitize_key((string) $_GET['wpcalibrate_notice']) : '';
$branding_logo = Assets::get_branding_icon_url('dark');
?>
<div class="wrap wpcalibrate-event-galleries-admin-wrap">
    <header class="wpcalibrate-header">
        <div class="wpcalibrate-header-brand">
            <?php if (!empty($branding_logo)) : ?>
                <img src="<?php echo esc_url($branding_logo); ?>" alt="WPCalibrate" class="wpcalibrate-logo" width="36" height="36" />
            <?php endif; ?>
            <div class="wpcalibrate-title-group">
                <h1 class="wpcalibrate-title"><?php esc_html_e('WPCalibrate Event Galleries', 'wpcalibrate-event-galleries'); ?></h1>
                <span class="wpcalibrate-version-badge">v<?php echo esc_html(WPCALIBRATE_EVENT_GALLERIES_VERSION); ?></span>
            </div>
        </div>
        <div class="wpcalibrate-header-actions">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . Post_Type::POST_TYPE)); ?>" class="button">
                <span class="dashicons dashicons-format-gallery" style="vertical-align: middle; margin-top: -2px;"></span>
                <?php esc_html_e('All Event Galleries', 'wpcalibrate-event-galleries'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=' . Post_Type::POST_TYPE)); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle; margin-top: -2px;"></span>
                <?php esc_html_e('Add New Gallery', 'wpcalibrate-event-galleries'); ?>
            </a>
        </div>
    </header>

    <?php if ($notice === 'saved') : ?>
        <div class="notice notice-success is-dismissible" style="margin-top: 16px;">
            <p><strong><?php esc_html_e('Settings saved successfully.', 'wpcalibrate-event-galleries'); ?></strong></p>
        </div>
    <?php endif; ?>

    <nav class="nav-tab-wrapper wpcalibrate-nav-tabs" aria-label="<?php esc_attr_e('Settings Tabs', 'wpcalibrate-event-galleries'); ?>">
        <?php foreach ($tabs as $tab_key => $tab_label) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=' . Menu::SETTINGS_SLUG . '&tab=' . $tab_key)); ?>"
               class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="wpcalibrate-tab-content">
        <?php if ($active_tab === 'support') : ?>
            <?php
            $tab_file = WPCALIBRATE_EVENT_GALLERIES_PATH . 'admin/views/partials/tab-support.php';
            if (file_exists($tab_file)) {
                include $tab_file;
            }
            ?>
        <?php else : ?>
            <form method="post" action="" class="wpcalibrate-settings-form" novalidate>
                <?php wp_nonce_field('wpcal_eg_settings_save', 'wpcal_eg_nonce'); ?>
                <input type="hidden" name="wpcal_eg_save_settings" value="1" />
                <input type="hidden" name="wpcal_eg_active_tab" value="<?php echo esc_attr($active_tab); ?>" />

                <?php
                $partial = WPCALIBRATE_EVENT_GALLERIES_PATH . 'admin/views/partials/tab-' . $active_tab . '.php';
                if (file_exists($partial)) {
                    include $partial;
                } else {
                    esc_html_e('Tab content not found.', 'wpcalibrate-event-galleries');
                }
                ?>

                <div class="wpcalibrate-submit-bar">
                    <?php submit_button(__('Save Changes', 'wpcalibrate-event-galleries'), 'primary', 'submit', false); ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
