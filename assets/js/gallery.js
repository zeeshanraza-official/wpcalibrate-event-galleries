/**
 * WPCalibrate Event Galleries - Frontend Lightbox & Load More
 *
 * @package WPCalibrate\EventGalleries
 */

/* global wpcalEgData */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initGalleries();
    });

    function initGalleries() {
        var galleries = document.querySelectorAll('.wpcal-eg-container');
        galleries.forEach(function (container) {
            setupGallery(container);
        });
    }

    function setupGallery(container) {
        var galleryId    = container.getAttribute('data-gallery-id');
        var grid         = container.querySelector('.wpcal-eg-grid');
        var loadMoreBtn  = container.querySelector('.wpcal-eg-btn-load-more');
        var lightboxEl   = container.querySelector('.wpcal-eg-lightbox');

        if (!lightboxEl) {
            return;
        }

        // Lightbox elements
        var backdrop     = lightboxEl.querySelector('.wpcal-eg-lightbox-backdrop');
        var closeBtn     = lightboxEl.querySelector('.wpcal-eg-lightbox-close');
        var prevBtn      = lightboxEl.querySelector('.wpcal-eg-lightbox-prev');
        var nextBtn      = lightboxEl.querySelector('.wpcal-eg-lightbox-next');
        var imgEl        = lightboxEl.querySelector('.wpcal-eg-lightbox-img');
        var captionEl    = lightboxEl.querySelector('.wpcal-eg-lightbox-caption');
        var counterCur   = lightboxEl.querySelector('.wpcal-eg-current-idx');
        var counterTot   = lightboxEl.querySelector('.wpcal-eg-total-idx');
        var downloadBtn  = lightboxEl.querySelector('.wpcal-eg-lightbox-download');

        var currentIndex = 0;
        var lastFocusedElement = null;
        var touchStartX = 0;
        var touchEndX   = 0;

        /**
         * Collect list of gallery items currently in the grid.
         */
        function getItems() {
            var links = container.querySelectorAll('.wpcal-eg-thumbnail-link');
            var items = [];
            links.forEach(function (link) {
                items.push({
                    link: link,
                    fullUrl: link.getAttribute('data-full') || link.getAttribute('href'),
                    title: link.getAttribute('data-title') || '',
                    caption: link.getAttribute('data-caption') || '',
                    downloadUrl: link.getAttribute('data-download') || ''
                });
            });
            return items;
        }

        // Open lightbox on thumbnail click
        container.addEventListener('click', function (e) {
            var link = e.target.closest('.wpcal-eg-thumbnail-link');
            if (!link) {
                return;
            }

            e.preventDefault();
            lastFocusedElement = link;

            var items = getItems();
            var index = 0;
            for (var i = 0; i < items.length; i++) {
                if (items[i].link === link) {
                    index = i;
                    break;
                }
            }

            openLightbox(index);
        });

        function openLightbox(index) {
            var items = getItems();
            if (index < 0 || index >= items.length) {
                return;
            }

            currentIndex = index;
            lightboxEl.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            renderActiveImage();

            // Move focus to close button for accessibility
            if (closeBtn) {
                closeBtn.focus();
            }

            document.addEventListener('keydown', handleKeyNav);
        }

        function closeLightbox() {
            lightboxEl.style.display = 'none';
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleKeyNav);

            // Return focus to originating element
            if (lastFocusedElement) {
                lastFocusedElement.focus();
                lastFocusedElement = null;
            }
        }

        function showPrev() {
            var items = getItems();
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = items.length - 1; // loop
            }
            renderActiveImage();
        }

        function showNext() {
            var items = getItems();
            if (currentIndex < items.length - 1) {
                currentIndex++;
            } else {
                currentIndex = 0; // loop
            }
            renderActiveImage();
        }

        function renderActiveImage() {
            var items = getItems();
            var item = items[currentIndex];
            if (!item) {
                return;
            }

            lightboxEl.classList.add('wpcal-img-loading');
            imgEl.classList.remove('wpcal-loaded');

            var tempImg = new Image();
            tempImg.onload = function () {
                imgEl.src = item.fullUrl;
                imgEl.alt = item.caption || item.title || '';
                imgEl.classList.add('wpcal-loaded');
                lightboxEl.classList.remove('wpcal-img-loading');
            };
            tempImg.onerror = function () {
                lightboxEl.classList.remove('wpcal-img-loading');
            };
            tempImg.src = item.fullUrl;

            // Update counter
            if (counterCur) {
                counterCur.textContent = String(currentIndex + 1);
            }
            if (counterTot) {
                counterTot.textContent = String(items.length);
            }

            // Update caption
            if (captionEl) {
                captionEl.textContent = item.caption || item.title || '';
            }

            // Update download button
            if (downloadBtn) {
                if (item.downloadUrl) {
                    downloadBtn.href = item.downloadUrl;
                    downloadBtn.style.display = 'inline-flex';
                } else {
                    downloadBtn.style.display = 'none';
                }
            }
        }

        function handleKeyNav(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeLightbox();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                showPrev();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                showNext();
            } else if (e.key === 'Tab') {
                // Focus trap within modal
                trapFocus(e);
            }
        }

        function trapFocus(e) {
            var focusables = lightboxEl.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            var first = focusables[0];
            var last  = focusables[focusables.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }

        // Close and navigation button events
        if (closeBtn) {
            closeBtn.addEventListener('click', closeLightbox);
        }
        if (backdrop) {
            backdrop.addEventListener('click', closeLightbox);
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', showPrev);
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', showNext);
        }

        // Mobile touch swipe gestures
        lightboxEl.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        lightboxEl.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            var threshold = 40;
            if (touchEndX < touchStartX - threshold) {
                showNext(); // Swipe left -> Next
            } else if (touchEndX > touchStartX + threshold) {
                showPrev(); // Swipe right -> Prev
            }
        }

        // ==========================================================================
        // Load More Handler (REST API)
        // ==========================================================================
        if (loadMoreBtn && grid) {
            loadMoreBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (loadMoreBtn.disabled) {
                    return;
                }

                var nextPage = parseInt(loadMoreBtn.getAttribute('data-next-page'), 10) || 2;
                var perPage  = parseInt(loadMoreBtn.getAttribute('data-per-page'), 10) || 24;

                loadMoreBtn.disabled = true;
                loadMoreBtn.classList.add('wpcal-loading');

                var restBase = (window.wpcalEgData && window.wpcalEgData.restUrl)
                    ? window.wpcalEgData.restUrl
                    : '/wp-json/wpcalibrate-event-galleries/v1/';
                var endpoint = restBase + 'gallery/' + galleryId + '/images?page=' + nextPage + '&per_page=' + perPage;

                var headers = {
                    'Accept': 'application/json'
                };
                if (window.wpcalEgData && window.wpcalEgData.nonce) {
                    headers['X-WP-Nonce'] = window.wpcalEgData.nonce;
                }

                fetch(endpoint, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('Network error ' + res.status);
                    }
                    return res.json();
                })
                .then(function (data) {
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.classList.remove('wpcal-loading');

                    if (data && data.html) {
                        // Append new images safely
                        var tempWrap = document.createElement('div');
                        tempWrap.innerHTML = data.html;
                        while (tempWrap.firstChild) {
                            grid.appendChild(tempWrap.firstChild);
                        }

                        // Update lightbox total counter
                        var items = getItems();
                        if (counterTot) {
                            counterTot.textContent = String(items.length);
                        }

                        if (data.has_more) {
                            loadMoreBtn.setAttribute('data-next-page', String(nextPage + 1));
                        } else {
                            loadMoreBtn.style.display = 'none';
                        }
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }
                })
                .catch(function (err) {
                    console.error('WPCalibrate Load More error:', err);
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.classList.remove('wpcal-loading');
                });
            });
        }
    }
})();
