<script>
    window.__AUTH_IS_LOGGED_IN__ = {{ auth()->check() ? 'true' : 'false' }};
    window.__AUTH_WISHLIST_IDS__ = @json($authWishlistIds ?? []);

    (() => {
        const WISHLIST_KEY = 'openbox_wishlist_ids';

        function getWishlistIds() {
            if (window.__AUTH_IS_LOGGED_IN__) {
                return Array.isArray(window.__AUTH_WISHLIST_IDS__) ? window.__AUTH_WISHLIST_IDS__.map(String) : [];
            }
            try {
                const ids = JSON.parse(localStorage.getItem(WISHLIST_KEY) || '[]');
                return Array.isArray(ids) ? ids.map(String) : [];
            } catch (e) {
                return [];
            }
        }

        function saveWishlistIds(ids) {
            const normalized = Array.from(new Set(ids.map(String)));
            if (window.__AUTH_IS_LOGGED_IN__) {
                window.__AUTH_WISHLIST_IDS__ = normalized;
            }
            try {
                localStorage.setItem(WISHLIST_KEY, JSON.stringify(normalized));
            } catch (e) {}
        }

        function syncWishlistUI() {
            const ids = new Set(getWishlistIds());
            document.querySelectorAll('.js-wishlist-btn').forEach(btn => {
                const id = String(btn.dataset.wishlistId || '');
                if (!id) return;
                const isLiked = ids.has(id);
                const icon = btn.querySelector('.js-wishlist-icon');
                if (icon) {
                    icon.classList.toggle('fill-current', isLiked);
                    icon.classList.toggle('text-red-500', isLiked);
                }
                btn.classList.toggle('text-red-500', isLiked);
                btn.classList.toggle('bg-red-50', isLiked);
                btn.setAttribute('title', isLiked ? 'Remove from wishlist' : 'Save to wishlist');
            });

            const badge = document.getElementById('header-wishlist-badge');
            if (badge) {
                const count = ids.size;
                badge.textContent = count;
                badge.classList.toggle('hidden', count === 0);
            }

            document.querySelectorAll('.js-wishlist-page-count').forEach(el => {
                el.textContent = `(${ids.size})`;
            });
        }

        function removeWishlistCard(productId) {
            const cards = document.querySelectorAll(`[data-wishlist-item-card="${productId}"]`);
            cards.forEach(card => {
                card.style.transition = 'all 0.25s ease-out';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.remove();
                    const remaining = document.querySelectorAll('[data-wishlist-item-card]').length;
                    document.querySelectorAll('.js-wishlist-page-count').forEach(el => {
                        el.textContent = `(${remaining})`;
                    });
                    if (remaining === 0) {
                        const grid = document.getElementById('wishlist-items-grid');
                        const empty = document.getElementById('wishlist-empty-state');
                        if (grid) grid.classList.add('hidden');
                        if (empty) empty.classList.remove('hidden');
                    }
                }, 250);
            });
        }

        // Initialize state
        if (window.__AUTH_IS_LOGGED_IN__ && Array.isArray(window.__AUTH_WISHLIST_IDS__)) {
            saveWishlistIds(window.__AUTH_WISHLIST_IDS__);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', syncWishlistUI);
        } else {
            syncWishlistUI();
        }
        window.addEventListener('load', syncWishlistUI);

        // Click handler for wishlist heart button or remove button
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-wishlist-btn, [data-wishlist-remove-btn]');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const productId = String(btn.dataset.wishlistId || btn.dataset.wishlistRemoveBtn || '');
            if (!productId || productId === 'null') return;

            const current = getWishlistIds();
            const isCurrentlyLiked = current.includes(productId);
            const isRemoving = btn.hasAttribute('data-wishlist-remove-btn') || isCurrentlyLiked;
            const next = isRemoving
                ? current.filter(id => id !== productId)
                : [...current, productId];

            saveWishlistIds(next);
            syncWishlistUI();

            if (isRemoving) {
                removeWishlistCard(productId);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/wishlist/' + productId + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.status === 401) return null;
                return res.json();
            })
            .then(data => {
                if (data && typeof data.added === 'boolean') {
                    let synced = getWishlistIds();
                    if (data.added && !synced.includes(productId)) {
                        synced.push(productId);
                    } else if (!data.added && synced.includes(productId)) {
                        synced = synced.filter(id => id !== productId);
                        removeWishlistCard(productId);
                    }
                    saveWishlistIds(synced);
                    syncWishlistUI();
                }
            })
            .catch(() => {});
        });
    })();
</script>
