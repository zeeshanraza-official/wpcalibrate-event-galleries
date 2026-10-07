<div align="center">
  <img src="branding/icon-dark.png" alt="WPCalibrate Event Galleries" width="128" height="128">
  <h1>WPCalibrate Event Galleries</h1>
  <p><strong>Professional, high-performance event photo gallery management for WordPress.</strong></p>

  <p>
    <a href="https://wordpress.org"><img src="https://img.shields.io/badge/WordPress-6.6%20to%207.1.3-blue.svg" alt="WordPress Version"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4.svg" alt="PHP Version"></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg" alt="License"></a>
    <a href="https://github.com/zeeshanraza-official/wpcalibrate-event-galleries/releases/latest"><img src="https://img.shields.io/badge/Release-v1.0.0-orange.svg" alt="Latest Release"></a>
  </p>
</div>

---

## Table of Contents
- [About](#about)
- [Key Features](#key-features)
- [Architecture & Performance](#architecture--performance)
- [Documentation & User Guide](#documentation--user-guide)
  - [1. Creating an Event Gallery](#1-creating-an-event-gallery)
  - [2. Displaying Galleries](#2-displaying-galleries)
  - [3. Shortcode Reference](#3-shortcode-reference)
  - [4. Gutenberg Block](#4-gutenberg-block)
  - [5. Event Gallery Archive](#5-event-gallery-archive)
  - [6. Admin Settings & Customization](#6-admin-settings--customization)
  - [7. In-Dashboard Updates via GitHub](#7-in-dashboard-updates-via-github)
- [REST API Reference](#rest-api-reference)
- [Developer Hooks & Filters](#developer-hooks--filters)
- [Installation](#installation)
- [Changelog](#changelog)
- [Support & Contact](#support--contact)
- [License](#license)

---

## About

**WPCalibrate Event Galleries** is a focused, production-grade WordPress photo-gallery plugin engineered for photographers, event organizers, schools, sports clubs, corporate teams, and community venues.

Unlike bloated all-in-one event suites that force complex ticketing, RSVP, calendars, and booking workflows onto your site, WPCalibrate Event Galleries focuses purely on **visual excellence and media presentation**. It gives administrators and editors an intuitive interface to curate event photos, customize column layouts, and present responsive photo grids with an accessible fullscreen lightbox and paginated/load-more batching.

Built following modern WordPress coding standards, strict PHP 8.2+ typing, defensive sanitization, and enterprise security practices.

---

## Key Features

- **📸 Media Library Integration**: Multi-select images directly from the WordPress Media Library with visual drag-and-drop reordering and individual photo removal.
- **📱 Responsive Layouts**: Clean CSS Grid and Masonry layouts with granular column controls (Desktop 1–8, Tablet 1–6, Mobile 1–4) and configurable image gap spacing.
- **✨ Accessible Dialog Lightbox**: Pure vanilla JavaScript modal dialog (`<dialog>` / ARIA modal) with keyboard navigation (`Left`/`Right` arrows, `Escape`), focus trapping, touch swipe gestures, photo counter, and image caption support.
- **⚡ Batch Loading & Performance**: Configurable initial load counts with interactive "Load More" pagination powered by a secured, cached REST API endpoint.
- **🔒 Password Protection**: Full native WordPress password protection support (`post_password_required()`). Thumbnails, metadata, and REST API payloads are completely shielded until the password is authenticated.
- **💾 One-Click Photo Downloads**: Global setting and per-gallery override enabling visitors to download full-resolution images with proper download attributes.
- **🧱 Block Editor & Shortcode**: Dedicated server-rendered Gutenberg block (`wpcalibrate/event-gallery`) and versatile shortcode (`[wpcalibrate_event_gallery id="..."]`) sharing a single robust render pipeline.
- **📂 Public Archive & Single Events**: Automatic event gallery archive at `/event-galleries/` with sorting and pagination, plus non-intrusive `the_content` integration for single events.
- **🛡️ Custom Role & Capability Mapping**: Granular capabilities (`edit_event_galleries`, `publish_event_galleries`, `delete_event_galleries`, etc.) assigned safely to Administrators and Editors with `map_meta_cap` author validation.
- **🌐 Internationalization & RTL Ready**: Complete translation readiness with POT catalog (`languages/wpcalibrate-event-galleries.pot`) and dedicated RTL stylesheets.
- **🔄 In-Dashboard Updates via GitHub**: Native WordPress updater hooks check GitHub releases so administrators receive automatic one-click updates directly inside the WordPress admin panel.

---

## Architecture & Performance

```
Admin Dashboard                       Frontend Visitor
┌───────────────────────────────┐     ┌────────────────────────────────┐
│  WPCalibrate Event Galleries  │     │  Single Event / Archive / Block│
│  - Media Multi-select modal   │     │  - Initial Batch Render (HTML) │
│  - Drag-and-drop Reordering   │     │  - Responsive CSS Grid/Masonry │
│  - Event Meta & Date Picker   │     │  - Accessible Dialog Lightbox  │
└───────────────┬───────────────┘     └───────────────┬────────────────┘
                │                                     │
                ▼                                     ▼
┌───────────────────────────────┐     ┌────────────────────────────────┐
│ WordPress Database            │◄────┤ REST API Endpoint              │
│ - Post Type: wpcal_event_gallery    │ /wpcalibrate-event-galleries/v1│
│ - Meta: _wpcal_eg_* keys      │     │ /gallery/{id}/images           │
│ - Options: wpcal_eg_settings  │     │ (Nonced & Cached Batch Loading)│
└───────────────────────────────┘     └────────────────────────────────┘
```

- **Zero Heavy JS Frameworks**: The frontend lightbox and load-more controller run on lightweight, vanilla JavaScript (no jQuery dependency on frontend).
- **Conditional Script Loading**: Scripts and stylesheets are only enqueued on event gallery posts, archive pages, or pages containing the block/shortcode.
- **Defensive Data Handling**: Every input is sanitized (`sanitize_text_field`, `absint`, `wp_kses_post`), and all template outputs are escaped (`esc_html`, `esc_attr`, `esc_url`).

---

## Documentation & User Guide

### 1. Creating an Event Gallery

1. In your WordPress admin menu, navigate to **Event Galleries > Add New Gallery**.
2. Enter the **Event Title** and description in the main editor.
3. In the **Event Details & Photos** metabox:
   - **Event Date**: Choose the date of the event.
   - **Event Location**: Enter the venue or city.
   - **Photos**: Click **Add / Manage Photos** to open the Media Library modal. Select multiple photos and click **Add to Gallery**.
   - **Reorder**: Drag and drop thumbnails to arrange your desired display order.
4. Set gallery layout options in the sidebar:
   - **Layout Style**: Grid or Masonry.
   - **Columns**: Default or custom override (1–8 columns).
   - **Images Per Batch**: Initial load count (e.g. 12, 24).
   - **Enable Downloads**: Enable or disable download buttons for visitors.
5. Set a **Featured Image** (used as cover card in archives) and click **Publish**.

### 2. Displaying Galleries

WPCalibrate Event Galleries supports three ways to display your galleries:
1. **Direct Post View**: Visit the single post permalink (e.g., `https://yoursite.com/event-galleries/summer-gala-2026/`).
2. **Shortcode**: Insert `[wpcalibrate_event_gallery id="123"]` on any Page, Post, or widget.
3. **Gutenberg Block**: Add the **Event Gallery** block in the Block Editor.

### 3. Shortcode Reference

```html
<!-- Display gallery with ID 42 using saved settings -->
[wpcalibrate_event_gallery id="42"]

<!-- Override layout and columns -->
[wpcalibrate_event_gallery id="42" layout="masonry" columns_desktop="4" columns_tablet="2" columns_mobile="1"]

<!-- Custom batch size with download button enabled -->
[wpcalibrate_event_gallery id="42" batch_size="12" show_download="yes"]

<!-- Hide metadata header on single embed -->
[wpcalibrate_event_gallery id="42" show_meta="no"]
```

#### Shortcode Attributes

| Attribute | Type | Default | Description |
|---|---|---|---|
| `id` | integer | `0` | Post ID of the Event Gallery (required). |
| `layout` | string | `grid` | Layout mode: `grid` or `masonry`. |
| `columns_desktop` | integer | `4` | Number of columns on desktop viewports (1–8). |
| `columns_tablet` | integer | `2` | Number of columns on tablet viewports (1–6). |
| `columns_mobile` | integer | `1` | Number of columns on mobile viewports (1–4). |
| `image_gap` | integer | `16` | Gap between image cards in pixels. |
| `batch_size` | integer | `24` | Number of photos loaded per batch (`0` for all). |
| `show_meta` | string | `yes` | Display event date and location header (`yes`/`no`). |
| `show_download` | string | `no` | Display image download button (`yes`/`no`). |

### 4. Gutenberg Block

1. Edit any page or post in the Block Editor.
2. Click the **+** inserter and search for **Event Gallery** (under the *Media* category).
3. In the block inspector on the right sidebar:
   - Select the desired gallery from the dropdown list.
   - Override layout, columns, gaps, batch size, and metadata display.
4. The block renders a live server-side preview inside the editor.

### 5. Event Gallery Archive

The plugin automatically provides a dedicated public archive at:
```
https://example.com/event-galleries/
```
You can customize the archive slug, sort order (Event Date Descending, Event Date Ascending, Published Date, or Title), and cards per page under **WPCalibrate > Event Galleries > Archive** settings.

### 6. Admin Settings & Customization

Under **WPCalibrate > Event Galleries** (or the shared **WPCalibrate** menu):
- **General**: Overview, quick links, and system status.
- **Gallery Defaults**: Global defaults for layout style, desktop/tablet/mobile column counts, gap spacing, batch size, and download permissions.
- **Archive**: Slug customization, default ordering, and cards per page.
- **Data & Tools**: Diagnostics report and toggle for clean uninstall data deletion.
- **Support**: Official contact channels and documentation links.

### 7. In-Dashboard Updates via GitHub

This plugin includes native in-dashboard update detection powered by GitHub Releases.
- When an updated release tag is published on GitHub, WordPress automatically flags an update under **Dashboard > Updates** and **Plugins**.
- Click **Update Now** to perform a zero-downtime, automated update directly from your WordPress admin panel.
- Compatible with WordPress core upgrader and [Git Updater](https://github.com/afragen/git-updater).

---

## REST API Reference

The plugin registers a secured REST endpoint for dynamic image batching:

### `GET /wp-json/wpcalibrate-event-galleries/v1/gallery/{id}/images`

#### Parameters:
- `id` (integer, required): Event Gallery post ID.
- `offset` (integer, optional): Starting photo offset (default: `0`).
- `limit` (integer, optional): Number of photos to retrieve (default: `24`).
- `_wpnonce` (string, required): WordPress REST API nonce.

#### Response:
```json
{
  "success": true,
  "html": "<div class=\"wpcal-eg-item\">...</div>",
  "remaining": 18,
  "count": 12,
  "offset": 24
}
```
*Note: If the gallery post is password-protected and the viewer has not submitted a valid password cookie, the endpoint returns an HTTP 403 Forbidden error.*

---

## Developer Hooks & Filters

```php
// Filter single event gallery output before rendering
add_filter('wpcal_eg_gallery_html', function(string $html, int $gallery_id, array $config): string {
    return $html;
}, 10, 3);

// Filter REST API image payload
add_filter('wpcal_eg_rest_gallery_images', function(array $response_data, int $gallery_id): array {
    return $response_data;
}, 10, 2);

// Modify lightbox markup attributes
add_filter('wpcal_eg_lightbox_attrs', function(array $attrs): array {
    $attrs['data-custom'] = 'true';
    return $attrs;
});
```

---

## Installation

### Manual Upload
1. Download `wpcalibrate-event-galleries-1.0.0.zip` from the [Latest Release](https://github.com/zeeshanraza-official/wpcalibrate-event-galleries/releases/latest).
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Git Clone Installation
```bash
cd wp-content/plugins/
git clone https://github.com/zeeshanraza-official/wpcalibrate-event-galleries.git
```

---

## Changelog

### Version 1.0.0 (2026-10-07)
- **Initial Production Release**.
- **CPT Registration**: Registered `wpcal_event_gallery` post type with title, editor, thumbnail, and excerpt supports.
- **Event Information & Meta**: Event Date, Event Location, and ordered photo ID list storage with defensive sanitization.
- **Media Library Integration**: Native `wp.media` modal multi-selection with thumbnail previews and drag-and-drop sortable ordering.
- **Responsive Display Engine**: CSS Grid and Masonry layouts with configurable desktop, tablet, and mobile column controls.
- **Accessible Dialog Lightbox**: Pure vanilla JS modal lightbox with focus trapping, keyboard navigation, touch swipe support, and photo counter.
- **Batch Loading & REST API**: Server-side batch rendering with secure `/gallery/{id}/images` REST endpoint.
- **Password Protection**: Native `post_password_required()` shielding for single views, shortcodes, blocks, and REST requests.
- **Public Archive**: Dedicated `/event-galleries/` archive page with customizable sorting and pagination.
- **Gutenberg Block & Shortcode**: Block Editor integration (`wpcalibrate/event-gallery`) and shortcode (`[wpcalibrate_event_gallery]`).
- **In-Dashboard GitHub Updates**: Native update detection for automated updates directly from WordPress admin dashboard.
- **Data Retention & Uninstall**: Uninstaller respects data retention setting, safely preserving or purging data on deletion.
- **Automated Verification**: Comprehensive test suite with 21 unit and integration tests passing at 100%.

---

## Support & Contact

- **Author**: [WPCalibrate](https://wpcalibrate.com)
- **Email**: [support@wpcalibrate.com](mailto:support@wpcalibrate.com)
- **Marketplace**: [https://marketplace.wpcalibrate.com/](https://marketplace.wpcalibrate.com/)
- **Phone / WhatsApp**: [+447474795976](https://wa.me/447474795976)

---

## License

This plugin is licensed under the [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE).
