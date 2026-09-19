@if (Route::has('chat.start-admin'))
    <div id="support-chat-widget" class="fixed bottom-6 right-6 z-50">
        {{-- Launcher Bubble --}}
        <button
            type="button"
            onclick="toggleSupportChatWidget()"
            id="support-chat-launcher"
            class="w-14 h-14 rounded-full bg-brand-500 hover:bg-brand-600 text-white shadow-xl flex items-center justify-center transition-colors cursor-pointer"
            aria-label="Chat with support"
            aria-expanded="false"
        >
            <svg id="support-chat-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg id="support-chat-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Composer Panel --}}
        <div id="support-chat-panel" class="hidden absolute bottom-[4.5rem] right-0 w-80 bg-white border border-gray-100 rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gray-950 px-4 py-3.5 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-brand-500 flex items-center justify-center text-white shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m-5.656 5.656l-3.536 3.536m12.728 0l-3.536-3.536M9.172 9.172L5.636 5.636M12 12a3 3 0 100-6 3 3 0 000 6zm0 0a3 3 0 110 6 3 3 0 010-6z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-white leading-tight">Chat with Support</p>
                    <p class="text-[11px] text-gray-400">We usually reply within a few hours</p>
                </div>
            </div>

            <form method="POST" action="{{ route('chat.start-admin') }}" class="p-4">
                @csrf
                <label for="support-chat-message" class="sr-only">Your message</label>
                <textarea
                    id="support-chat-message"
                    name="body"
                    rows="3"
                    maxlength="2000"
                    placeholder="Describe what you need help with…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-400 resize-none"
                ></textarea>

                <button
                    type="submit"
                    class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-bold bg-brand-500 hover:bg-brand-600 text-white shadow-2xs transition-colors cursor-pointer"
                >
                    <span>Start Conversation</span>
                </button>

                <p class="text-[11px] text-gray-400 mt-2 text-center">
                    Or leave it blank to jump straight into the chat.
                </p>
            </form>
        </div>
    </div>

    <script>
        function toggleSupportChatWidget() {
            const panel = document.getElementById('support-chat-panel');
            const iconOpen = document.getElementById('support-chat-icon-open');
            const iconClose = document.getElementById('support-chat-icon-close');
            const launcher = document.getElementById('support-chat-launcher');
            if (!panel) return;

            const isHidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden', isHidden);
            iconClose.classList.toggle('hidden', !isHidden);
            launcher.setAttribute('aria-expanded', isHidden ? 'true' : 'false');

            if (isHidden) {
                document.getElementById('support-chat-message')?.focus();
            }
        }

        document.addEventListener('click', function (event) {
            const widget = document.getElementById('support-chat-widget');
            const panel = document.getElementById('support-chat-panel');
            if (widget && panel && !widget.contains(event.target) && !panel.classList.contains('hidden')) {
                toggleSupportChatWidget();
            }
        });
    </script>
@endif
