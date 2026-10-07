<?php
/**
 * Admin Module Coordinator.
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
 * Class Admin
 *
 * Coordinates admin menu, event metaboxes, and list-table columns.
 */
class Admin {

    /**
     * Menu handler.
     *
     * @var Menu
     */
    public Menu $menu;

    /**
     * Event metaboxes handler.
     *
     * @var Event_Fields
     */
    public Event_Fields $event_fields;

    /**
     * List table columns handler.
     *
     * @var List_Table_Columns
     */
    public List_Table_Columns $list_table_columns;

    /**
     * Constructor.
     *
     * @param Settings           $settings   Settings.
     * @param Gallery_Repository $repository Repository.
     * @param Assets             $assets     Assets.
     */
    public function __construct(Settings $settings, Gallery_Repository $repository, Assets $assets) {
        $this->menu               = new Menu($settings, $assets);
        $this->event_fields       = new Event_Fields($settings);
        $this->list_table_columns = new List_Table_Columns($repository);
    }

    /**
     * Initialize all admin hooks and services.
     *
     * @return void
     */
    public function init(): void {
        $this->menu->init();
        $this->event_fields->init();
        $this->list_table_columns->init();
    }
}
