/**
 * WPCalibrate Event Galleries - Admin Media Library Integration & Reordering
 *
 * @package WPCalibrate\EventGalleries
 */

/* global wp, wpcalEgAdmin, jQuery */
(function ($) {
    'use strict';

    $(function () {
        var $grid        = $('#wpcal-eg-thumbnail-grid');
        var $hiddenInput = $('#wpcal-eg-image-ids');
        var $btnSelect   = $('#wpcal-eg-btn-select-photos');
        var $btnClear    = $('#wpcal-eg-btn-clear-photos');
        var $emptyState  = $('#wpcal-eg-empty-state');
        var $counter     = $('.wpcal-eg-photo-counter');
        var mediaFrame;

        // Initialize drag-and-drop sortable
        if ($grid.length) {
            $grid.sortable({
                items: '.wpcal-eg-thumb-item',
                cursor: 'grabbing',
                containment: 'parent',
                tolerance: 'pointer',
                forcePlaceholderSize: true,
                placeholder: 'wpcal-eg-thumb-item ui-sortable-placeholder',
                update: function () {
                    syncImageIds();
                }
            });
        }

        // Click to add photos via WordPress Media Library
        $btnSelect.on('click', function (e) {
            e.preventDefault();

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: (wpcalEgAdmin && wpcalEgAdmin.selectImagesTitle) ? wpcalEgAdmin.selectImagesTitle : 'Select Event Gallery Photos',
                button: {
                    text: (wpcalEgAdmin && wpcalEgAdmin.selectImagesBtn) ? wpcalEgAdmin.selectImagesBtn : 'Add to Event Gallery'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });

            mediaFrame.on('select', function () {
                var selection = mediaFrame.state().get('selection');
                var currentIds = getCurrentIds();

                selection.each(function (attachment) {
                    var attData = attachment.toJSON();
                    var id = parseInt(attData.id, 10);

                    // Prevent duplicate entries of the same attachment
                    if (currentIds.indexOf(id) !== -1) {
                        return;
                    }

                    var thumbUrl = (attData.sizes && attData.sizes.thumbnail) ? attData.sizes.thumbnail.url : attData.url;
                    var title = attData.title || '';

                    var $item = $('<li class="wpcal-eg-thumb-item" data-id="' + id + '" title="' + escapeHtml(title) + '">' +
                        '<div class="wpcal-eg-thumb-card">' +
                        '<img src="' + escapeHtml(thumbUrl) + '" alt="' + escapeHtml(title) + '" class="wpcal-eg-thumb-img" />' +
                        '<button type="button" class="wpcal-eg-thumb-remove" aria-label="' + (wpcalEgAdmin.removePhotoLabel || 'Remove photo') + '">' +
                        '<span class="dashicons dashicons-no-alt"></span>' +
                        '</button>' +
                        '<span class="wpcal-eg-thumb-handle">' +
                        '<span class="dashicons dashicons-move"></span>' +
                        '</span>' +
                        '</div>' +
                        '</li>');

                    $grid.append($item);
                });

                syncImageIds();
            });

            mediaFrame.open();
        });

        // Remove individual photo
        $grid.on('click', '.wpcal-eg-thumb-remove', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.wpcal-eg-thumb-item');
            $item.fadeOut(200, function () {
                $(this).remove();
                syncImageIds();
            });
        });

        // Remove all photos
        $btnClear.on('click', function (e) {
            e.preventDefault();
            var confirmMsg = (wpcalEgAdmin && wpcalEgAdmin.confirmRemoveAll) ? wpcalEgAdmin.confirmRemoveAll : 'Are you sure you want to remove all photos from this gallery?';
            if (window.confirm(confirmMsg)) {
                $grid.empty();
                syncImageIds();
            }
        });

        // Copy shortcode to clipboard on list table
        $(document).on('click', '.wpcal-eg-copyable-code', function () {
            var text = $(this).text();
            var $el = $(this);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    var original = $el.text();
                    $el.text('Copied!');
                    setTimeout(function () {
                        $el.text(original);
                    }, 1500);
                });
            }
        });

        /**
         * Get array of IDs currently in the DOM grid.
         */
        function getCurrentIds() {
            var ids = [];
            $grid.find('.wpcal-eg-thumb-item').each(function () {
                var id = parseInt($(this).attr('data-id'), 10);
                if (id > 0) {
                    ids.push(id);
                }
            });
            return ids;
        }

        /**
         * Sync ordered IDs into the hidden input and update UI counts.
         */
        function syncImageIds() {
            var ids = getCurrentIds();
            $hiddenInput.val(ids.join(','));

            var count = ids.length;
            $counter.text(count + (count === 1 ? ' photo selected' : ' photos selected'));

            if (count > 0) {
                $emptyState.hide();
                $btnClear.show();
            } else {
                $emptyState.show();
                $btnClear.hide();
            }
        }

        /**
         * Escape HTML string helper.
         */
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    });
})(jQuery);
