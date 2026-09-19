@extends('layouts.verifier')

@section('title', 'Inspection Queue & Appointments')

@section('content')
    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Active Inspection Queue</h1>
            <p class="text-xs sm:text-sm text-gray-500">Scheduled devices awaiting physical examination and grading at your hub.</p>
        </div>
        <a href="{{ route('verifier.history.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>View Past Inspections</span>
        </a>
    </div>

    {{-- Filter & Search Toolbar --}}
    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('verifier.appointments.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
                <div class="flex-1 min-w-[220px]">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by product title, SKU, or seller..."
                            class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="w-44">
                    <select name="status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">All Active Statuses</option>
                        <option value="scheduled" {{ $currentStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="inspecting" {{ $currentStatus === 'inspecting' ? 'selected' : '' }}>In Progress</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                    Filter
                </button>

                @if ($search || $currentStatus)
                    <a href="{{ route('verifier.appointments.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                        Reset
                    </a>
                @endif
            </form>

            <div class="inline-flex items-center p-1 bg-gray-100 rounded-xl shrink-0">
                <button type="button" id="btn-view-table" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white text-gray-800 shadow-2xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    <span>List View</span>
                </button>
                <button type="button" id="btn-view-calendar" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-900 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>Calendar View</span>
                    @if ($calendarAppointments->count() > 0)
                        <span class="px-1.5 py-0.2 bg-amber-500 text-white rounded-full text-[10px] font-bold">{{ $calendarAppointments->count() }}</span>
                    @endif
                </button>
            </div>
        </div>
    </x-card>

    {{-- List View --}}
    <div id="view-table-container">
        <x-card>
            <x-table :headers="['Product Details', 'Seller', 'Inspection Hub', 'Scheduled For', 'Status', 'Action']" id="appointments-table">
                @forelse ($appointments as $appointment)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-gray-900">
                            <div class="flex items-center gap-3">
                                <img src="{{ $appointment->product->primaryImageUrl() }}" alt="{{ $appointment->product->title }}" class="w-11 h-11 object-cover rounded-lg border border-gray-100 flex-shrink-0">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $appointment->product->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $appointment->product->category?->name ?? 'Electronics' }}
                                        @if ($appointment->product->sku)
                                            · SKU: <span class="font-mono text-gray-600">{{ $appointment->product->sku }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-600">
                            <p class="font-semibold text-gray-900">{{ $appointment->seller->name }}</p>
                            <p class="text-gray-400">{{ $appointment->seller->email }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-600">
                            <p class="font-medium text-gray-800">{{ $appointment->location?->name ?? 'Default Hub' }}</p>
                            <p class="text-[11px] text-gray-400">{{ $appointment->location?->city ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-700">
                            <p class="font-semibold">{{ $appointment->scheduled_at?->format('M j, Y') }}</p>
                            <p class="text-gray-400">{{ $appointment->scheduled_at?->format('g:i A') }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <x-badge :color="$appointment->status->badgeColor()">{{ $appointment->status->label() }}</x-badge>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <form method="POST" action="{{ route('chat.start', $appointment->product) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Message {{ $appointment->seller->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                </form>
                                <a href="{{ route('verifier.appointments.inspect', $appointment) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                                    <span>{{ $appointment->status->value === 'inspecting' ? 'Resume' : 'Inspect' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            No appointments currently match your filter.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                <x-pagination :paginator="$appointments" />
            </div>
        </x-card>
    </div>

    {{-- Calendar View Container --}}
    <div id="view-calendar-container" class="hidden">
        <x-card>
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
        </x-card>
    </div>

    {{-- Verifier Appointment Detail Modal --}}
    <div id="appointment-modal" class="fixed inset-0 z-50 hidden bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-gray-100 transform transition-all">
            <div class="relative bg-gradient-to-r from-gray-900 to-gray-800 p-5 text-white">
                <button type="button" id="modal-close-btn" class="absolute top-4 right-4 text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-500/20 border border-amber-400/30 rounded-xl">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Hub Inspection Slot</h3>
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
                            <span id="m-product-price" class="font-semibold text-amber-600"></span>
                        </div>
                    </div>
                </div>

                {{-- Status & Time --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-xl">
                        <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1">Status</p>
                        <span id="m-status-badge" class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg"></span>
                    </div>

                    <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                        <p class="text-[11px] font-bold text-blue-800 uppercase tracking-wider mb-1">30-Min Slot</p>
                        <p id="m-time-slot" class="text-xs font-bold text-blue-900"></p>
                    </div>
                </div>

                {{-- Seller Information --}}
                <div class="p-3.5 border border-gray-200 rounded-xl bg-white space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Device Owner / Seller</p>
                    <p id="m-seller-name" class="text-xs font-semibold text-gray-900"></p>
                    <p id="m-seller-email" class="text-xs text-gray-500"></p>
                </div>

                {{-- Hub Location --}}
                <div class="p-3.5 border border-gray-200 rounded-xl bg-white space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Inspection Center</p>
                    <p id="m-location-name" class="text-xs font-semibold text-gray-800"></p>
                    <p id="m-location-address" class="text-xs text-gray-500"></p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <button type="button" id="modal-cancel-btn" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer">
                    Close
                </button>

                <a id="m-inspect-btn" href="#" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                    <span>Start Physical Inspection</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appointments = @json($calendarAppointments);

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

                for (let x = firstDayIndex; x > 0; x--) {
                    const dayNum = prevLastDate - x + 1;
                    const cell = document.createElement('div');
                    cell.className = 'p-2 bg-gray-50/50 text-gray-300 min-h-[95px] text-xs font-medium';
                    cell.innerHTML = `<span>${dayNum}</span>`;
                    daysGridEl.appendChild(cell);
                }

                for (let day = 1; day <= lastDate; day++) {
                    const monthStr = String(month + 1).padStart(2, '0');
                    const dayStr = String(day).padStart(2, '0');
                    const formattedDate = `${year}-${monthStr}-${dayStr}`;

                    const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === day;

                    const cell = document.createElement('div');
                    cell.className = `p-2 min-h-[95px] text-xs font-medium relative transition-colors ${isToday ? 'bg-amber-50/40 font-bold' : 'bg-white hover:bg-gray-50'}`;

                    let dayHeaderHTML = `<div class="flex items-center justify-between mb-1">
                        <span class="${isToday ? 'w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-2xs' : 'text-gray-700'}">${day}</span>
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

                const totalCells = firstDayIndex + lastDate;
                const nextDays = (7 - (totalCells % 7)) % 7;
                for (let j = 1; j <= nextDays; j++) {
                    const cell = document.createElement('div');
                    cell.className = 'p-2 bg-gray-50/50 text-gray-300 min-h-[95px] text-xs font-medium';
                    cell.innerHTML = `<span>${j}</span>`;
                    daysGridEl.appendChild(cell);
                }

                document.querySelectorAll('.btn-appt-card').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const apptId = parseInt(btn.dataset.id);
                        openAppointmentModal(apptId);
                    });
                });
            }

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
                document.getElementById('m-seller-name').textContent = appt.seller_name;
                document.getElementById('m-seller-email').textContent = appt.seller_email;
                document.getElementById('m-location-name').textContent = appt.location_name;
                document.getElementById('m-location-address').textContent = appt.location_address || 'Default Scrutiny Hub Location';

                const inspectBtn = document.getElementById('m-inspect-btn');
                if (inspectBtn && appt.inspect_url) {
                    inspectBtn.href = appt.inspect_url;
                }

                modal.classList.remove('hidden');
            }

            const hideModal = () => modal.classList.add('hidden');
            closeBtn?.addEventListener('click', hideModal);
            cancelBtn?.addEventListener('click', hideModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) hideModal();
            });
        });
    </script>
@endsection
