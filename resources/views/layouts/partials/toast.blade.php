{{-- Top-right toast notifications + AJAX handler for the newsletter form --}}
<div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-3 w-[calc(100%-2rem)] max-w-sm pointer-events-none" aria-live="polite"></div>

<script>
    (function () {
        const styles = {
            success: { bar: 'bg-emerald-500', icon: '✓' },
            info: { bar: 'bg-sky-500', icon: 'i' },
            error: { bar: 'bg-red-500', icon: '!' },
        };

        const duration = 4500;

        window.showToast = function (message, type) {
            const container = document.getElementById('toast-container');
            const style = styles[type] || styles.success;
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto relative flex items-center gap-3 bg-white border border-gray-100 shadow-lg rounded-lg pr-4 overflow-hidden transition-all duration-300 translate-x-6 opacity-0';

            const bar = document.createElement('span');
            bar.className = 'self-stretch w-1.5 ' + style.bar;
            const icon = document.createElement('span');
            icon.className = 'w-6 h-6 shrink-0 rounded-full text-white text-xs font-bold flex items-center justify-center ' + style.bar;
            icon.textContent = style.icon;
            const text = document.createElement('p');
            text.className = 'py-3 text-sm text-gray-800 flex-1';
            text.textContent = message;
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'text-gray-400 hover:text-gray-700 text-lg leading-none';
            close.setAttribute('aria-label', 'Dismiss');
            close.textContent = '×';

            const countdown = document.createElement('span');
            countdown.className = 'absolute bottom-0 left-0 h-1 w-full origin-left ease-linear opacity-70 ' + style.bar;
            countdown.style.transition = 'transform ' + duration + 'ms linear';

            toast.append(bar, icon, text, close, countdown);
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-6', 'opacity-0');
                requestAnimationFrame(() => { countdown.style.transform = 'scaleX(0)'; });
            });

            const dismiss = () => {
                toast.classList.add('translate-x-6', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            };
            close.addEventListener('click', dismiss);
            setTimeout(dismiss, duration);
        };

        document.querySelectorAll('[data-newsletter-form]').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                button.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: new FormData(form),
                    });
                    const data = await response.json();

                    if (response.ok) {
                        window.showToast(data.message, data.type);
                        form.reset();
                    } else if (response.status === 422) {
                        window.showToast(Object.values(data.errors || {})[0][0], 'error');
                    } else if (response.status === 429) {
                        window.showToast('Too many attempts. Please try again in a minute.', 'error');
                    } else {
                        window.showToast('Something went wrong. Please try again.', 'error');
                    }
                } catch (error) {
                    window.showToast('Network error. Please try again.', 'error');
                } finally {
                    button.disabled = false;
                }
            });
        });
    })();
</script>
