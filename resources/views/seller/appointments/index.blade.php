@extends('layouts.saler')

@section('title', 'Verification Appointments')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @if ($eligibleProducts->isNotEmpty() && $locations->isNotEmpty())
        <x-card title="Book Physical Device Scrutiny Appointment (30-Min Slot)">
            <form method="POST" action="{{ route('saler.appointments.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
                @csrf
                <x-select label="Product" name="product_id" :options="$eligibleProducts->pluck('title', 'id')" />
                <x-select label="Verification Center" name="location_id" :options="$locations->pluck('name', 'id')" />
                <x-input label="Inspection Date" name="appointment_date" type="date" value="{{ date('Y-m-d') }}" />
                <x-select label="30-Min Scrutiny Time Slot" name="appointment_time" :options="$timeSlots" />
                <x-button type="submit" class="sm:col-span-4">Book 30-Min Physical Device Scrutiny</x-button>
            </form>
        </x-card>
    @elseif ($locations->isEmpty())
        <x-alert type="info">No verification locations are configured yet — check back soon.</x-alert>
    @endif

    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 mb-5">
            <div>
                <h3 class="text-base font-bold text-gray-900">Your Scrutiny Appointments & Schedule</h3>
                <p class="text-xs text-gray-500 mt-0.5">Switch between list view and interactive calendar to manage physical scrutiny slots.</p>
            </div>

            <div class="inline-flex items-center p-1 bg-gray-100 rounded-xl">
                <button type="button" id="btn-view-table" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white text-gray-800 shadow-2xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    <span>Table View</span>
                </button>
                <button type="button" id="btn-view-calendar" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-900 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>Calendar View</span>
                    @if ($calendarAppointments->count() > 0)
                        <span class="px-1.5 py-0.2 bg-brand-500 text-white rounded-full text-[10px] font-bold">{{ $calendarAppointments->count() }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Table View Container --}}
        <div id="view-table-container">
            <x-table :headers="['Product', 'Verification Status', '30-Min Scheduled Slot', 'Inspection Center', 'Action']" id="appointments-table">
                @forelse ($products as $product)
                    @php($req = $product->latestVerificationRequest)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->title }}" class="w-9 h-9 object-cover rounded-md border border-gray-100 flex-shrink-0">
                                <div>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900 line-clamp-1">{{ $product->title }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $product->category?->name ?? 'General' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$product->verification_status->badgeColor()">{{ $product->verification_status->label() }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            @if ($req?->scheduled_at)
                                <span class="inline-flex items-center gap-1.5 font-semibold text-gray-800 bg-amber-50 text-amber-900 px-2.5 py-1 rounded-lg border border-amber-200/60">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $req->scheduled_at->format('M j, Y \a\t g:i A') }} (30 min)
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            <p class="font-medium text-gray-800">{{ $req?->location?->name ?? '—' }}</p>
                            @if ($req?->location)
                                <p class="text-[10px] text-gray-400">{{ $req->location->city }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs whitespace-nowrap">
                            @if ($req?->scheduled_at)
                                <button type="button" class="btn-open-appointment-detail text-brand-600 font-semibold hover:underline cursor-pointer" data-id="{{ $req->id }}">
                                    View Info
                                </button>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No scrutiny appointments yet.</td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                <x-pagination :paginator="$products" />
            </div>
        </div>

        {{-- Calendar View Container --}}
        <div id="view-calendar-container" class="hidden">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4 bg-gray-50/80 p-3 rounded-xl border border-gray-100">
                <div class="flex items-center gap-2">
                    <button type="button" id="cal-prev-month" class="p-1.5 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 rounded-lg shadow-2xs transition-colors cursor-pointer" title="Previous Month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <h4 id="cal-month-year" class="text-base font-bold text-gray-900 px-2 min-w-[140px] text-center"></h4>
                    <button type="button" id="cal-next-month" class="p-1.5 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 rounded-lg shadow-2xs transition-colors cursor-pointer" title="Next Month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                    <button type="button" id="cal-today-btn" class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-100 text-xs font-semibold text-gray-700 rounded-lg shadow-2xs transition-colors cursor-pointer ml-1">
                        Today
                    </button>
                </div>

                {{-- Legend --}}
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Scheduled</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> In Progress</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Verified</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Rejected</span>
                </div>
            </div>

            {{-- Grid --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-2xs">
                <div class="grid grid-cols-7 bg-gray-100/70 border-b border-gray-200 text-center text-xs font-bold text-gray-600 uppercase py-2.5">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>

                <div id="cal-days-grid" class="grid grid-cols-7 divide-x divide-y divide-gray-200 min-h-[420px]"></div>
            </div>
        </div>
    </x-card>

    {{-- Appointment Detail Modal --}}
    <div id="appointment-modal" class="fixed inset-0 z-50 hidden bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 transform transition-all">
            <div class="relative bg-gradient-to-r from-gray-900 to-gray-800 p-5 text-white">
                <button type="button" id="modal-close-btn" class="absolute top-4 right-4 text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-brand-500/20 border border-brand-400/30 rounded-xl">
                        <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Device Scrutiny Appointment</h3>
                        <p class="text-xs text-gray-300" id="m-formatted-schedule">—</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                {{-- Product Card --}}
                <div class="flex items-center gap-4 p-3.5 bg-gray-50 border border-gray-100 rounded-xl">
                    <img id="m-product-image" src="" alt="" class="w-16 h-16 object-cover rounded-lg border border-gray-200 bg-white shrink-0">
                    <div class="min-w-0 flex-1">
                        <span id="m-product-category" class="inline-block px-2 py-0.5 text-[10px] font-semibold bg-gray-200 text-gray-700 rounded-md mb-1"></span>
                        <h4 id="m-product-title" class="text-sm font-bold text-gray-900 truncate"></h4>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                            <span>SKU: <strong id="m-product-sku" class="font-mono text-gray-700"></strong></span>
                            <span>•</span>
                            <span id="m-product-price" class="font-semibold text-brand-600"></span>
                        </div>
                    </div>
                </div>

                {{-- Status & Location --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-xl">
                        <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1">Scrutiny Status</p>
                        <span id="m-status-badge" class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg"></span>
                    </div>

                    <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                        <p class="text-[11px] font-bold text-blue-800 uppercase tracking-wider mb-1">Time Slot</p>
                        <p id="m-time-slot" class="text-xs font-bold text-blue-900"></p>
                    </div>
                </div>

                {{-- Inspection Hub --}}
                <div class="p-3.5 border border-gray-200 rounded-xl bg-white space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-900">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Inspection Center Hub</span>
                    </div>
                    <p id="m-location-name" class="text-xs font-semibold text-gray-800 pl-6"></p>
                    <p id="m-location-address" class="text-xs text-gray-500 pl-6"></p>
                </div>

                {{-- Assigned Officer --}}
                <div class="p-3.5 border border-gray-200 rounded-xl bg-white space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Assigned Hub Inspector</p>
                    <p id="m-verifier-name" class="text-xs font-semibold text-gray-900"></p>
                    <p id="m-verifier-email" class="text-xs text-gray-500"></p>
                </div>

                {{-- Notes / Instructions --}}
                <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-xl">
                    <p class="text-xs font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Important Instructions for Seller
                    </p>
                    <p id="m-notes" class="text-xs text-amber-800 leading-relaxed"></p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" id="modal-cancel-btn" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appointments = @json($calendarAppointments);

            // View Switcher logic
            const btnTable = document.getElementById('btn-view-table');
            const btnCalendar = document.getElementById('btn-view-calendar');
            const tableContainer = document.getElementById('view-table-container');
            const calendarContainer = document.getElementById('view-calendar-container');

            btnTable?.addEventListener('click', () => {
                tableContainer.classList.remove('hidden');
                calendarContainer.classList.add('hidden');
                btnTable.classList.add('bg-white', 'text-gray-800', 'shadow-2xs');
                btnTable.classList.remove('text-gray-600');
                btnCalendar.classList.remove('bg-white', 'text-gray-800', 'shadow-2xs');
                btnCalendar.classList.add('text-gray-600');
            });

            btnCalendar?.addEventListener('click', () => {
                calendarContainer.classList.remove('hidden');
                tableContainer.classList.add('hidden');
                btnCalendar.classList.add('bg-white', 'text-gray-800', 'shadow-2xs');
                btnCalendar.classList.remove('text-gray-600');
                btnTable.classList.remove('bg-white', 'text-gray-800', 'shadow-2xs');
                btnTable.classList.add('text-gray-600');
                renderCalendar();
            });

            // Calendar Navigation & Rendering
            let currentDate = new Date();

            const monthYearEl = document.getElementById('cal-month-year');
            const daysGridEl = document.getElementById('cal-days-grid');
            const prevBtn = document.getElementById('cal-prev-month');
            const nextBtn = document.getElementById('cal-next-month');
            const todayBtn = document.getElementById('cal-today-btn');

            prevBtn?.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextBtn?.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            todayBtn?.addEventListener('click', () => {
                currentDate = new Date();
                renderCalendar();
            });

            function renderCalendar() {
                if (!daysGridEl || !monthYearEl) return;

                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                monthYearEl.textContent = `${monthNames[month]} ${year}`;

                daysGridEl.innerHTML = '';

                const firstDayIndex = new Date(year, month, 1).getDay();
                const lastDate = new Date(year, month + 1, 0).getDate();
                const prevLastDate = new Date(year, month, 0).getDate();

                const today = new Date();

                // Previous month padding days
                for (let x = firstDayIndex; x > 0; x--) {
                    const dayNum = prevLastDate - x + 1;
                    const cell = document.createElement('div');
                    cell.className = 'p-2 bg-gray-50/50 text-gray-300 min-h-[95px] text-xs font-medium';
                    cell.innerHTML = `<span>${dayNum}</span>`;
                    daysGridEl.appendChild(cell);
                }

                // Current month days
                for (let day = 1; day <= lastDate; day++) {
                    const monthStr = String(month + 1).padStart(2, '0');
                    const dayStr = String(day).padStart(2, '0');
                    const formattedDate = `${year}-${monthStr}-${dayStr}`;

                    const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === day;

                    const cell = document.createElement('div');
                    cell.className = `p-2 min-h-[95px] text-xs font-medium relative transition-colors ${isToday ? 'bg-amber-50/40 font-bold' : 'bg-white hover:bg-gray-50'}`;

                    let dayHeaderHTML = `<div class="flex items-center justify-between mb-1">
                        <span class="${isToday ? 'w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs shadow-2xs' : 'text-gray-700'}">${day}</span>
                    </div>`;

                    const dayAppts = appointments.filter(a => a.date === formattedDate);

                    let apptsHTML = '';
                    if (dayAppts.length > 0) {
                        dayAppts.forEach(appt => {
                            let badgeClass = 'bg-amber-50 text-amber-900 border-amber-200';
                            if (appt.status === 'inspecting') badgeClass = 'bg-blue-50 text-blue-900 border-blue-200';
                            if (appt.status === 'verified') badgeClass = 'bg-emerald-50 text-emerald-900 border-emerald-200';
                            if (appt.status === 'rejected') badgeClass = 'bg-rose-50 text-rose-900 border-rose-200';

                            apptsHTML += `
                                <button type="button" class="btn-appt-card w-full text-left p-1.5 mb-1.5 rounded-lg border text-[11px] font-semibold leading-tight shadow-2xs transition-all hover:scale-[1.02] cursor-pointer ${badgeClass}" data-id="${appt.id}">
                                    <div class="truncate font-bold text-gray-900">${appt.title}</div>
                                    <div class="flex items-center justify-between text-[10px] opacity-85 mt-0.5">
                                        <span>${appt.time}</span>
                                        <span class="capitalize">${appt.status}</span>
                                    </div>
                                </button>
                            `;
                        });
                    }

                    cell.innerHTML = dayHeaderHTML + `<div class="space-y-1">${apptsHTML}</div>`;
                    daysGridEl.appendChild(cell);
                }

                // Next month padding days to complete 7-column grid
                const totalCells = firstDayIndex + lastDate;
                const nextDays = (7 - (totalCells % 7)) % 7;
                for (let j = 1; j <= nextDays; j++) {
                    const cell = document.createElement('div');
                    cell.className = 'p-2 bg-gray-50/50 text-gray-300 min-h-[95px] text-xs font-medium';
                    cell.innerHTML = `<span>${j}</span>`;
                    daysGridEl.appendChild(cell);
                }

                // Bind click events on appointment cards
                document.querySelectorAll('.btn-appt-card').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const apptId = parseInt(btn.dataset.id);
                        openAppointmentModal(apptId);
                    });
                });
            }

            // Modal Logic
            const modal = document.getElementById('appointment-modal');
            const closeBtn = document.getElementById('modal-close-btn');
            const cancelBtn = document.getElementById('modal-cancel-btn');

            function openAppointmentModal(id) {
                const appt = appointments.find(a => a.id === id);
                if (!appt) return;

                document.getElementById('m-formatted-schedule').textContent = appt.formatted_schedule;
                document.getElementById('m-product-title').textContent = appt.title;
                document.getElementById('m-product-image').src = appt.image;
                document.getElementById('m-product-sku').textContent = appt.sku;
                document.getElementById('m-product-price').textContent = appt.price;
                document.getElementById('m-product-category').textContent = appt.category;

                const statusBadge = document.getElementById('m-status-badge');
                statusBadge.textContent = appt.status_label;
                statusBadge.className = `inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg ${appt.status_color}`;

                document.getElementById('m-time-slot').textContent = appt.time + ' (30-Min Slot)';
                document.getElementById('m-location-name').textContent = appt.location_name;
                document.getElementById('m-location-address').textContent = appt.location_address || 'Default Scrutiny Hub Location';
                document.getElementById('m-verifier-name').textContent = appt.verifier_name;
                document.getElementById('m-verifier-email').textContent = appt.verifier_email;
                document.getElementById('m-notes').textContent = appt.notes;

                modal.classList.remove('hidden');
            }

            const hideModal = () => modal.classList.add('hidden');
            closeBtn?.addEventListener('click', hideModal);
            cancelBtn?.addEventListener('click', hideModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) hideModal();
            });

            // Bind click from Table View "View Info" buttons
            document.querySelectorAll('.btn-open-appointment-detail').forEach(btn => {
                btn.addEventListener('click', () => {
                    const apptId = parseInt(btn.dataset.id);
                    openAppointmentModal(apptId);
                });
            });
        });
    </script>
@endsection
