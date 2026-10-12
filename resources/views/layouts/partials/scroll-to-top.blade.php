{{-- Floating Scroll to Top Button (Matching Image 1 / edutrust.qa design) --}}
<button type="button" id="scroll-to-top-btn" aria-label="Scroll to top" title="Scroll to top"
    class="fixed bottom-6 right-5 sm:right-6 z-40 w-11 h-11 rounded-full bg-[#0c0f1d] hover:bg-black text-white shadow-xl ring-1 ring-white/10 flex items-center justify-center transition-all duration-300 opacity-0 pointer-events-none translate-y-3 hover:scale-110 active:scale-95 cursor-pointer">
    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scrollBtn = document.getElementById('scroll-to-top-btn');
        if (!scrollBtn) return;

        const toggleScrollBtn = () => {
            if (window.scrollY > 250) {
                scrollBtn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-3');
                scrollBtn.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
            } else {
                scrollBtn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-3');
                scrollBtn.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
            }
        };

        window.addEventListener('scroll', toggleScrollBtn, { passive: true });
        toggleScrollBtn();

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
