(function () {
    var config = window.storeWishlistConfig || {};
    var wishlistIds = new Set(Array.isArray(config.ids) ? config.ids.map(function (id) { return parseInt(id, 10); }) : []);
    var toggleUrl = config.toggleUrl || '';
    var wishlistUrl = config.wishlistUrl || '';
    var csrfToken = config.csrfToken || '';

    function badgeTargets() {
        return document.querySelectorAll('.header-wishlist, a.header-icon.swap-icon');
    }

    function ensureBadge(target) {
        var badge = target.querySelector('.store-wishlist-count');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'position-absolute top-0 start-100 translate-middle badge store-wishlist-count';
            badge.innerHTML = '<span class="visually-hidden">wishlist items</span>';
            target.appendChild(badge);
        }
        return badge;
    }

    function updateCount(count) {
        badgeTargets().forEach(function (target) {
            if (target.tagName === 'A' && wishlistUrl && target.getAttribute('href') === 'javascript:void(0)') {
                target.setAttribute('href', wishlistUrl);
            }
            var badge = ensureBadge(target);
            badge.firstChild && badge.firstChild.nodeType === Node.TEXT_NODE ? badge.firstChild.nodeValue = String(count) : badge.insertBefore(document.createTextNode(String(count)), badge.firstChild);
            badge.classList.toggle('d-none', count <= 0);
        });
    }

    function setButtonState(button, inWishlist) {
        button.classList.toggle('active', inWishlist);
        button.classList.toggle('is-active', inWishlist);
        button.setAttribute('data-in-wishlist', inWishlist ? '1' : '0');
        button.setAttribute('aria-pressed', inWishlist ? 'true' : 'false');
    }

    function syncButtons(productId, inWishlist) {
        document.querySelectorAll('[data-store-wishlist-toggle][data-product-id="' + productId + '"]').forEach(function (button) {
            setButtonState(button, inWishlist);
        });
    }

    function toggleEmptyState() {
        var grid = document.querySelector('[data-store-wishlist-grid]');
        var empty = document.querySelector('[data-store-wishlist-empty]');
        if (!grid || !empty) {
            return;
        }

        var cards = grid.querySelectorAll('[data-store-wishlist-card]');
        var hasCards = cards.length > 0;
        grid.style.display = hasCards ? '' : 'none';
        empty.classList.toggle('d-none', hasCards);
    }

    function removeWishlistCard(productId) {
        document.querySelectorAll('[data-store-wishlist-card][data-product-id="' + productId + '"]').forEach(function (card) {
            card.remove();
        });
        toggleEmptyState();
    }

    function applyInitialState() {
        updateCount(Number(config.count || 0));
        document.querySelectorAll('[data-store-wishlist-toggle]').forEach(function (button) {
            var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
            setButtonState(button, wishlistIds.has(productId));
        });
        toggleEmptyState();
    }

    function handleToggleClick(event) {
        var button = event.target.closest('[data-store-wishlist-toggle]');
        if (!button || !toggleUrl || !csrfToken) {
            return;
        }

        event.preventDefault();

        var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
        if (!productId || button.classList.contains('is-loading')) {
            return;
        }

        button.classList.add('is-loading');

        fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_id: productId })
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Wishlist request failed');
                }
                return response.json();
            })
            .then(function (payload) {
                var ids = Array.isArray(payload.ids) ? payload.ids.map(function (id) { return parseInt(id, 10); }) : [];
                wishlistIds = new Set(ids);
                syncButtons(productId, !!payload.in_wishlist);
                updateCount(Number(payload.count || 0));

                if (payload.in_wishlist) {
                    wishlistIds.add(productId);
                } else {
                    wishlistIds.delete(productId);
                    removeWishlistCard(productId);
                }
            })
            .catch(function () {
                window.location.href = wishlistUrl || button.getAttribute('href') || window.location.href;
            })
            .finally(function () {
                button.classList.remove('is-loading');
            });
    }

    document.addEventListener('click', handleToggleClick);
    document.addEventListener('DOMContentLoaded', applyInitialState);

    if (document.readyState !== 'loading') {
        applyInitialState();
    }
})();
