{{-- Site-wide compare tray: the list lives in localStorage so it follows the visitor across pages. --}}
<div id="site-compare-dock" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 w-[95%] max-w-2xl bg-gray-900 text-white rounded-2xl shadow-2xl ring-1 ring-black/20 p-3 sm:p-4 transition-all duration-300 translate-y-36 opacity-0 pointer-events-none flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <span class="shrink-0 text-xs sm:text-sm font-bold tracking-tight text-white">Compare (<span id="site-compare-count">0</span>/{{ \App\Http\Controllers\CompareController::MAX_PRODUCTS }})</span>
        <div id="site-compare-thumbs" class="flex items-center gap-2 overflow-x-auto py-1"></div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('compare') }}" id="site-compare-open" class="bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs sm:text-sm px-4 py-2 sm:py-2.5 rounded-xl transition-all active:scale-95">Compare Now</a>
        <button type="button" id="site-compare-clear" class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-white/10 text-xs font-semibold cursor-pointer">Clear</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // The home page still ships its own tray; don't double-bind there.
        if (document.getElementById('floating-compare-dock')) return;

        const KEY = 'openbox_compare_items';
        const MAX = {{ \App\Http\Controllers\CompareController::MAX_PRODUCTS }};
        const compareUrl = @json(route('compare'));
        const dock = document.getElementById('site-compare-dock');
        const thumbs = document.getElementById('site-compare-thumbs');
        const onComparePage = !!document.getElementById('compare-page');

        let list = [];
        try { list = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { list = []; }

        const urlFor = () => list.length ? compareUrl + '?ids=' + list.map(item => item.id).join(',') : compareUrl;

        const render = () => {
            document.getElementById('site-compare-count').textContent = list.length;
            document.getElementById('site-compare-open').href = urlFor();
            document.querySelectorAll('[data-site-compare-link]').forEach(a => { a.href = urlFor(); });

            const show = list.length > 0 && !onComparePage;
            dock.classList.toggle('translate-y-36', !show);
            dock.classList.toggle('opacity-0', !show);
            dock.classList.toggle('opacity-100', show);
            dock.classList.toggle('pointer-events-none', !show);

            thumbs.innerHTML = '';
            list.forEach((item, index) => {
                const wrap = document.createElement('div');
                wrap.className = 'relative shrink-0';
                const box = document.createElement('div');
                box.className = 'w-10 h-10 rounded-xl bg-white p-1 overflow-hidden';
                const img = document.createElement('img');
                img.src = item.image || '';
                img.alt = item.title || '';
                img.className = 'w-full h-full object-contain';
                box.appendChild(img);
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.textContent = '✕';
                remove.title = 'Remove';
                remove.className = 'absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center cursor-pointer';
                remove.addEventListener('click', () => { list.splice(index, 1); save(); });
                wrap.append(box, remove);
                thumbs.appendChild(wrap);
            });

            const ids = list.map(item => String(item.id));
            document.querySelectorAll('.js-compare-btn').forEach(btn => {
                const active = ids.includes(String(btn.dataset.compareId));
                btn.classList.toggle('bg-brand-50', active);
                btn.classList.toggle('text-brand-600', active);
                btn.classList.toggle('ring-2', active);
                btn.classList.toggle('ring-brand-500', active);
                btn.classList.toggle('text-gray-600', !active);
            });
        };

        const save = () => {
            try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {}
            render();
        };

        document.querySelectorAll('.js-compare-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const id = String(btn.dataset.compareId);
                const existing = list.findIndex(item => String(item.id) === id);
                if (existing > -1) {
                    list.splice(existing, 1);
                } else {
                    if (list.length >= MAX) {
                        alert('You can compare a maximum of ' + MAX + ' products at a time.');
                        return;
                    }
                    list.push({
                        id,
                        title: btn.dataset.compareTitle || '',
                        price: btn.dataset.comparePrice || '',
                        rawPrice: parseFloat(btn.dataset.compareRawPrice || '0'),
                        image: btn.dataset.compareImage || '',
                        brand: btn.dataset.compareBrand || '',
                        category: btn.dataset.compareCategory || '',
                        condition: btn.dataset.compareCondition || 'Refurbished',
                        rating: btn.dataset.compareRating || '4.9',
                        seller: btn.dataset.compareSeller || 'Openbox',
                        href: btn.dataset.compareHref || '#',
                    });
                }
                save();
            });
        });

        document.getElementById('site-compare-clear').addEventListener('click', () => { list = []; save(); });
        render();
    });
</script>
