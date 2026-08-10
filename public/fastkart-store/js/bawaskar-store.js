(function () {
    var config = window.storefrontUiConfig || {};
    var csrfToken = config.csrfToken || '';
    var cartState = config.cart || {};
    var wishlistConfig = config.wishlist || {};
    var wishlistIds = new Set(Array.isArray(wishlistConfig.ids) ? wishlistConfig.ids.map(function (id) { return parseInt(id, 10); }) : []);

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatPrice(amount) {
        var value = Number(amount || 0);
        return 'Rs. ' + value.toFixed(2);
    }

    function formatQty(quantity) {
        var value = Number(quantity || 0).toFixed(3);
        return value.replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
    }

    function ensureBadge(target, className, hiddenClass) {
        var badge = target.querySelector('.' + className);
        if (!badge) {
            badge = document.createElement('span');
            badge.className = hiddenClass ? className + ' ' + hiddenClass : className;
            badge.innerHTML = '<span class="visually-hidden"></span>';
            target.appendChild(badge);
        }
        return badge;
    }

    function updateWishlistCount(count) {
        document.querySelectorAll('[data-store-wishlist-link], a.header-icon.swap-icon').forEach(function (target) {
            if (wishlistConfig.url && target.getAttribute('href') === 'javascript:void(0)') {
                target.setAttribute('href', wishlistConfig.url);
            }
            var badge = ensureBadge(target, 'store-wishlist-count', 'position-absolute top-0 start-100 translate-middle badge');
            badge.firstChild && badge.firstChild.nodeType === Node.TEXT_NODE ? badge.firstChild.nodeValue = String(count) : badge.insertBefore(document.createTextNode(String(count)), badge.firstChild);
            badge.classList.toggle('d-none', count <= 0);
        });
    }

    function setWishlistButtonState(button, inWishlist) {
        button.classList.toggle('active', inWishlist);
        button.classList.toggle('is-active', inWishlist);
        button.setAttribute('data-in-wishlist', inWishlist ? '1' : '0');
        button.setAttribute('aria-pressed', inWishlist ? 'true' : 'false');
    }

    function syncWishlistButtons(productId, inWishlist) {
        document.querySelectorAll('[data-store-wishlist-toggle][data-product-id="' + productId + '"]').forEach(function (button) {
            setWishlistButtonState(button, inWishlist);
        });
    }

    function toggleWishlistEmptyState() {
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
        toggleWishlistEmptyState();
    }

    function renderWishlistPreview(items) {
        var dropdown = document.querySelector('a.header-icon.swap-icon + .onhover-div .cart-list');
        if (!dropdown) {
            return;
        }
        if (!Array.isArray(items) || items.length === 0) {
            dropdown.innerHTML = '<li><div class="drop-cart"><div class="drop-contain"><h5>Your wishlist is empty.</h5><h6>Save products to review them later.</h6></div></div></li>';
            return;
        }
        dropdown.innerHTML = items.map(function (item) {
            return '<li><div class="drop-cart"><a href="' + escapeHtml(item.product_url) + '" class="drop-image"><img src="' + escapeHtml(item.image_url) + '" class="blur-up lazyload" alt="' + escapeHtml(item.name) + '"></a><div class="drop-contain"><a href="' + escapeHtml(item.product_url) + '"><h5>' + escapeHtml(item.name) + '</h5></a><h6>' + formatPrice(item.price) + '</h6></div></div></li>';
        }).join('');
    }

    function updateCartCount(count) {
        var normalized = formatQty(count || 0);
        document.querySelectorAll('[data-store-cart-count-target], .header-badge .badge, a.header-icon.bag-icon .badge-number').forEach(function (target) {
            target.textContent = normalized;
            var hidden = document.createElement('span');
            hidden.className = 'visually-hidden';
            hidden.textContent = 'cart items';
            target.appendChild(hidden);
        });
    }

    function renderCartPreview(items) {
        document.querySelectorAll('[data-store-cart-list], .onhover-dropdown.header-badge .cart-list').forEach(function (list) {
            if (!Array.isArray(items) || items.length === 0) {
                list.innerHTML = '<li class="product-box-contain"><div class="drop-cart"><div class="drop-contain"><h5>Your cart is empty.</h5><h6>Add products to continue shopping.</h6></div></div></li>';
                return;
            }
            list.innerHTML = items.map(function (item) {
                return '<li class="product-box-contain"><div class="drop-cart"><a href="' + escapeHtml(item.product_url) + '" class="drop-image"><img src="' + escapeHtml(item.image_url) + '" class="blur-up lazyload" alt="' + escapeHtml(item.name) + '"></a><div class="drop-contain"><a href="' + escapeHtml(item.product_url) + '"><h5>' + escapeHtml(item.name) + '</h5></a><h6><span>' + escapeHtml(formatQty(item.quantity)) + ' x</span> ' + escapeHtml(formatPrice(item.unit_price)) + '</h6></div></div></li>';
            }).join('');
        });
    }

    function updateCartTotal(grandTotal) {
        document.querySelectorAll('[data-store-cart-total], .onhover-dropdown.header-badge .price-box h4.theme-color.fw-bold').forEach(function (target) {
            target.textContent = formatPrice(grandTotal || 0);
        });
    }

    function applyCartState() {
        updateCartCount(cartState.count || 0);
        renderCartPreview(cartState.items || []);
        updateCartTotal(cartState.grandTotal || 0);
    }

    function applyInitialState() {
        updateWishlistCount(Number(wishlistConfig.count || 0));
        document.querySelectorAll('[data-store-wishlist-toggle]').forEach(function (button) {
            var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
            setWishlistButtonState(button, wishlistIds.has(productId));
        });
        renderWishlistPreview(wishlistConfig.items || []);
        toggleWishlistEmptyState();
        applyCartState();
    }

    function handleWishlistToggle(event) {
        var button = event.target.closest('[data-store-wishlist-toggle]');
        if (!button || !wishlistConfig.toggleUrl || !csrfToken) {
            return;
        }

        event.preventDefault();

        var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
        if (!productId || button.classList.contains('is-loading')) {
            return;
        }

        button.classList.add('is-loading');

        fetch(wishlistConfig.toggleUrl, {
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
                wishlistConfig.count = Number(payload.count || 0);
                syncWishlistButtons(productId, !!payload.in_wishlist);
                updateWishlistCount(wishlistConfig.count);

                if (payload.in_wishlist) {
                    wishlistIds.add(productId);
                } else {
                    wishlistIds.delete(productId);
                    removeWishlistCard(productId);
                }
            })
            .catch(function () {
                window.location.href = wishlistConfig.url || button.getAttribute('href') || window.location.href;
            })
            .finally(function () {
                button.classList.remove('is-loading');
            });
    }

    function updateCartFromPayload(payload) {
        cartState.count = Number(payload.count || 0);
        cartState.subtotal = Number(payload.subtotal || 0);
        cartState.gstTotal = Number(payload.gst_total || 0);
        cartState.grandTotal = Number(payload.grand_total || 0);
        cartState.items = Array.isArray(payload.items) ? payload.items : [];
        applyCartState();
    }

    function submitCartForm(form) {
        var formData = new FormData(form);
        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    return response.json().then(function (payload) {
                        window.location.href = payload.login_url || config.loginUrl || window.location.href;
                        return null;
                    });
                }
                if (!response.ok) {
                    throw new Error('Cart request failed');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload) {
                    return;
                }
                updateCartFromPayload(payload);
            })
            .catch(function () {
                window.location.reload();
            })
            .finally(function () {
                form.classList.remove('is-loading');
            });
    }

    function handleCartSubmit(event) {
        var form = event.target.closest('form[data-store-cart-add]');
        if (!form) {
            return;
        }

        event.preventDefault();
        if (form.classList.contains('is-loading')) {
            return;
        }
        form.classList.add('is-loading');
        submitCartForm(form);
    }

    function handleDeadCartButton(event) {
        var button = event.target.closest('.addcart-button, .add-cart-button');
        if (!button || button.closest('form[data-store-cart-add]') || button.disabled) {
            return;
        }

        var modalAddButton = button.closest('.modal-button');
        if (modalAddButton) {
            event.preventDefault();
            window.location.href = config.userAuthenticated ? (config.cartUrl || window.location.href) : (config.loginUrl || window.location.href);
        }
    }

    document.addEventListener('click', handleWishlistToggle);
    document.addEventListener('click', handleDeadCartButton);
    document.addEventListener('submit', handleCartSubmit);
    document.addEventListener('DOMContentLoaded', applyInitialState);

    if (document.readyState !== 'loading') {
        applyInitialState();
    }
})();