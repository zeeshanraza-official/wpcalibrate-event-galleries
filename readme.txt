=== WPCalibrate Event Galleries ===
Contributors: wpcalibrate
Donate link: https://wpcalibrate.com/
Tags: event gallery, photo gallery, photography, event photos, lightbox
Requires at least: 6.6
Tested up to: 7.1.3
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create event-specific photo galleries, manage event details, and display galleries with responsive grid and masonry layouts, lightbox, and batch loading.

== Description ==

WPCalibrate Event Galleries is a dedicated photo-gallery solution designed specifically for events, functions, ceremonies, parties, and conferences. It empowers WordPress administrators and editors to organize photos by event, attach event dates and venues, and display them in clean, responsive layouts.

Unlike bloated multipurpose plugins or complex booking systems, WPCalibrate Event Galleries focuses exclusively on what matters most: presenting your event photography beautifully and efficiently.

### Key Features

* **Event-Specific Photo Organization**: Group photos under dedicated Event Galleries with Event Name, Date, Venue/Location, and Cover Image.
* **Native Media Library Workflow**: Select multiple photos directly from the native WordPress Media Library modal without duplicating files on disk.
* **Drag-and-Drop Reordering**: Order your photos visually with instant thumbnail reordering.
* **Responsive Grid & Masonry Layouts**: Choose between uniform aspect-ratio Grid or height-preserving Masonry layouts.
* **Configurable Responsive Columns**: Customize desktop (1–8), tablet (1–6), and mobile (1–4) column counts globally or per-gallery.
* **Accessible Image Lightbox**: Built-in accessible lightbox featuring keyboard navigation (Left/Right arrows, Escape), image counter, captions, touch gestures, and focus trapping.
* **Performance Batching (Load More)**: Render an initial batch of thumbnails server-side and dynamically load subsequent batches via WordPress REST API.
* **Password Protection**: Full native WordPress password protection integration. Password-protected galleries safely conceal thumbnails and REST batches until visitors authenticate.
* **Configurable Visitor Downloads**: Enable or disable direct high-resolution photo download actions globally or per-gallery.
* **Gutenberg Block & Shortcode**: Embed any gallery using the dynamic "WPCalibrate Event Gallery" Gutenberg block or simple shortcode `[wpcalibrate_event_gallery id="123"]`. Compatible with Elementor, Divi, Bricks, and all major page builders.
* **Dedicated Event Archive**: Clean, paginated archive at `/event-galleries/` with configurable sorting by event date, title, or custom order.
* **Role Permissions**: Administrators and Editors can manage event galleries using a custom capability model.
* **Shared WPCalibrate Ecosystem**: Seamlessly integrates into the unified WPCalibrate admin dashboard without creating duplicate top-level menus.
* **Clean Data Retention**: Data is safely preserved on uninstall by default, with an optional setting to completely purge data if desired.

== Installation ==

1. Upload the `wpcalibrate-event-galleries` folder to the `/wp-content/plugins/` directory, or install the ZIP file via **Plugins > Add New > Upload Plugin** in your WordPress dashboard.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Access **WPCalibrate > Event Galleries** to create your first event gallery.
4. Add photos using the native Media Library button, drag to reorder, set the event date, and publish!

== Frequently Asked Questions ==

= Does removing a photo from a gallery delete it from the Media Library? =
No. Photos are referenced solely by attachment ID. Removing a photo from an Event Gallery removes it from that gallery only; the file remains in your WordPress Media Library.

= Can I password-protect an Event Gallery? =
Yes. Simply set the post visibility to Password Protected in the WordPress editor. Unauthenticated visitors and the REST API will not receive any photo data or thumbnails until the correct post password has been submitted.

= How do I embed a gallery in a post or page? =
You can use the Gutenberg block "WPCalibrate Event Gallery" or insert the shortcode `[wpcalibrate_event_gallery id="123"]` (replacing 123 with your gallery's ID).

= Is this compatible with page builders like Elementor or Divi? =
Yes. Any page builder that supports WordPress shortcodes can render WPCalibrate Event Galleries using the `[wpcalibrate_event_gallery id="123"]` shortcode.

= Where do I configure global settings? =
Go to **WPCalibrate > Gallery Settings** in your WordPress dashboard.

== Screenshots ==

1. Event Galleries list table with cover images, dates, photo counts, and shortcodes.
2. Editing an Event Gallery with multi-select media library workflow and drag-and-drop reordering.
3. Responsive frontend Grid layout with event metadata bar and Load More button.
4. Built-in accessible lightbox modal with keyboard navigation, counter, and captions.
5. Plugin settings page beneath the shared WPCalibrate dashboard.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Event Gallery custom post type with custom capability mapping for Administrators and Editors.
* Multi-select Media Library integration and drag-and-drop thumbnail reordering.
* Grid and Masonry layouts with customizable desktop, tablet, and mobile column counts.
* Built-in accessible lightbox with keyboard navigation, focus trap, and touch swipe.
* REST API batch loading with Load More button.
* Native WordPress password protection support.
* Optional visitor photo download links.
* Dynamic Gutenberg block and `[wpcalibrate_event_gallery]` shortcode.
* Public event archive with configurable sorting and pagination.
* Shared WPCalibrate admin menu integration.
