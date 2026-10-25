<button type="button" class="wishlist-toggle {{ !empty($wishlistDetailed) ? 'wishlist-toggle-detail' : 'wishlist-toggle-card' }}"
    data-wishlist-toggle data-product-id="{{ $wishlistProduct->id }}" data-product-title="{{ $wishlistProduct->title }}"
    data-product-price="{{ $wishlistProduct->price }}" data-product-url="{{ route('frontend.products.show', $wishlistProduct->id) }}"
    data-product-image="{{ $wishlistImage }}" aria-pressed="false" aria-label="افزودن {{ $wishlistProduct->title }} به علاقه‌مندی‌ها" title="افزودن به علاقه‌مندی‌ها">
    <i class="zmdi zmdi-favorite-outline" aria-hidden="true"></i>
    @if (!empty($wishlistDetailed))
        <span class="visually-hidden" data-wishlist-label>افزودن به علاقه‌مندی‌ها</span>
    @endif
</button>
