<script>
    window.storeWishlistConfig = {
        count: {{ (int) ($storeWishlistCount ?? 0) }},
        ids: @json(array_values(array_map('intval', $storeWishlistProductIds ?? []))),
        toggleUrl: @json(route('store.wishlist.toggle')),
        wishlistUrl: @json(route('store.page', ['page' => 'wishlist'])),
        csrfToken: @json(csrf_token())
    };
</script>
<script src="{{ asset('fastkart-store/js/bawaskar-store.js') }}"></script>
