/**
 * WPCalibrate Event Galleries - Gutenberg Block Editor Script
 *
 * @package WPCalibrate\EventGalleries
 */

/* global wp, wpcalEgBlockData */
(function () {
    'use strict';

    if (!wp || !wp.blocks || !wp.element) {
        return;
    }

    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;
    var components = wp.components || {};
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var RangeControl = components.RangeControl;
    var ServerSideRender = wp.serverSideRender;

    var blockData = window.wpcalEgBlockData || {
        galleries: [{ value: '0', label: '— Select —' }],
        i18n: {
            title: 'WPCalibrate Event Gallery',
            selectLabel: 'Choose Event Gallery',
            selectDesc: 'Select which event gallery to render.',
            settings: 'Gallery Settings',
            layout: 'Layout Override',
            default: 'Default',
            grid: 'Grid',
            masonry: 'Masonry',
            noSelected: 'Please select an Event Gallery in the block settings.'
        }
    };

    registerBlockType('wpcalibrate/event-gallery', {
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var galleryId = attributes.galleryId || '0';

            var inspector = el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { title: blockData.i18n.settings, initialOpen: true },
                    el(SelectControl, {
                        label: blockData.i18n.selectLabel,
                        help: blockData.i18n.selectDesc,
                        value: galleryId,
                        options: blockData.galleries,
                        onChange: function (val) {
                            setAttributes({ galleryId: val });
                        }
                    }),
                    el(SelectControl, {
                        label: blockData.i18n.layout,
                        value: attributes.layout || '',
                        options: [
                            { value: '', label: blockData.i18n.default },
                            { value: 'grid', label: blockData.i18n.grid },
                            { value: 'masonry', label: blockData.i18n.masonry }
                        ],
                        onChange: function (val) {
                            setAttributes({ layout: val });
                        }
                    }),
                    el(RangeControl, {
                        label: 'Desktop Columns',
                        value: attributes.columnsDesktop || 0,
                        min: 0,
                        max: 8,
                        help: '0 inherits global default',
                        onChange: function (val) {
                            setAttributes({ columnsDesktop: val });
                        }
                    })
                )
            );

            if (!galleryId || galleryId === '0') {
                return el(
                    Fragment,
                    {},
                    inspector,
                    el(
                        'div',
                        {
                            className: 'wpcal-eg-block-placeholder',
                            style: {
                                padding: '28px',
                                background: '#f0f0f1',
                                border: '1px dashed #c3c4c7',
                                borderRadius: '4px',
                                textAlign: 'center'
                            }
                        },
                        el('p', { style: { margin: 0, fontWeight: 600, color: '#1d2327' } }, blockData.i18n.title),
                        el('p', { style: { margin: '8px 0 0', color: '#50575e', fontSize: '13px' } }, blockData.i18n.noSelected)
                    )
                );
            }

            // Render live server-side preview if available
            if (ServerSideRender) {
                return el(
                    Fragment,
                    {},
                    inspector,
                    el(ServerSideRender, {
                        block: 'wpcalibrate/event-gallery',
                        attributes: attributes
                    })
                );
            }

            return el(
                Fragment,
                {},
                inspector,
                el('div', { className: 'wpcal-eg-block-preview' }, 'Event Gallery #' + galleryId)
            );
        },
        save: function () {
            return null; // Dynamic server-rendered block
        }
    });
})();
