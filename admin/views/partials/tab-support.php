<?php
/**
 * Tab Partial: Support & Contact.
 *
 * @package WPCalibrate\EventGalleries
 */

declare(strict_types=1);

namespace WPCalibrate\EventGalleries;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wpcalibrate-support-grid">
    <div class="wpcalibrate-section-card">
        <h2><?php esc_html_e('Contact & Official Support', 'wpcalibrate-event-galleries'); ?></h2>
        <p class="wpcalibrate-section-desc">
            <?php esc_html_e('Need assistance with WPCalibrate Event Galleries, custom integration, or bug reports? Reach out to our engineering and support team directly.', 'wpcalibrate-event-galleries'); ?>
        </p>

        <ul class="wpcalibrate-contact-list">
            <li>
                <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e('Email Support', 'wpcalibrate-event-galleries'); ?></strong><br />
                    <a href="mailto:support@wpcalibrate.com">support@wpcalibrate.com</a>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e('Official Website', 'wpcalibrate-event-galleries'); ?></strong><br />
                    <a href="<?php echo esc_url('https://wpcalibrate.com'); ?>" target="_blank" rel="noopener noreferrer">https://wpcalibrate.com</a>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-cart" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e('WPCalibrate Marketplace', 'wpcalibrate-event-galleries'); ?></strong><br />
                    <a href="<?php echo esc_url('https://marketplace.wpcalibrate.com/'); ?>" target="_blank" rel="noopener noreferrer">https://marketplace.wpcalibrate.com/</a>
                </div>
            </li>
            <li>
                <span class="dashicons dashicons-phone" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e('Telephone & WhatsApp', 'wpcalibrate-event-galleries'); ?></strong><br />
                    <a href="tel:+447474795976">+447474795976</a> &bull;
                    <a href="<?php echo esc_url('https://wa.me/447474795976'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Chat on WhatsApp', 'wpcalibrate-event-galleries'); ?></a>
                </div>
            </li>
        </ul>
    </div>

    <div class="wpcalibrate-section-card">
        <h2><?php esc_html_e('Documentation & FAQ', 'wpcalibrate-event-galleries'); ?></h2>
        <p class="wpcalibrate-section-desc">
            <?php esc_html_e('Helpful reminders and frequently asked questions for administrators and content editors.', 'wpcalibrate-event-galleries'); ?>
        </p>

        <div class="wpcalibrate-faq-item">
            <h4><?php esc_html_e('How do I display a gallery on any page?', 'wpcalibrate-event-galleries'); ?></h4>
            <p><?php esc_html_e('Use the Gutenberg block "WPCalibrate Event Gallery" or the shortcode [wpcalibrate_event_gallery id="XX"] where XX is your event gallery ID.', 'wpcalibrate-event-galleries'); ?></p>
        </div>

        <div class="wpcalibrate-faq-item">
            <h4><?php esc_html_e('Does deleting a photo from a gallery delete it from WordPress?', 'wpcalibrate-event-galleries'); ?></h4>
            <p><?php esc_html_e('No. Photos are referenced by attachment ID from your WordPress Media Library. Removing a photo from an event gallery merely detaches it from that specific event.', 'wpcalibrate-event-galleries'); ?></p>
        </div>

        <div class="wpcalibrate-faq-item">
            <h4><?php esc_html_e('Can I password-protect an Event Gallery?', 'wpcalibrate-event-galleries'); ?></h4>
            <p><?php esc_html_e('Yes! Use the native WordPress Password Protection feature under Post Visibility. Visitors must submit the correct password before images, thumbnails, or REST batches can be accessed.', 'wpcalibrate-event-galleries'); ?></p>
        </div>
    </div>
</div>
