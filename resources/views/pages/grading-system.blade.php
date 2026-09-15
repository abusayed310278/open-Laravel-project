@extends('layouts.app')

@section('title', 'Grading System')

@section('content')
    <div class="bg-gray-50 py-14 text-center">
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <p class="text-brand-500 font-semibold text-sm mb-3 uppercase tracking-wide">Shop with confidence</p>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">How Openbox Grading Works</h1>
            <p class="text-gray-500 leading-relaxed">
                Every used or refurbished item sold on Openbox is physically inspected by an Openbox verifier before
                its grade badge unlocks. Here's exactly what each grade means.
            </p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-14">
        <div class="grid md:grid-cols-3 gap-5 mb-14">
            <div class="bg-white border border-gray-100 rounded-md p-6">
                <x-grade-badge grade="A" class="mb-4" />
                <h2 class="font-semibold text-gray-900 mb-2">Like New</h2>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">No visible scratches or defects. Fully tested and certified by an Openbox verifier.</p>
                <p class="text-xs text-gray-400">Battery health above <strong class="text-gray-600">90%</strong></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-md p-6">
                <x-grade-badge grade="B" class="mb-4" />
                <h2 class="font-semibold text-gray-900 mb-2">Good</h2>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">Minor cosmetic marks with no impact on functionality. Fully functional.</p>
                <p class="text-xs text-gray-400">Battery health above <strong class="text-gray-600">80%</strong></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-md p-6">
                <x-grade-badge grade="C" class="mb-4" />
                <h2 class="font-semibold text-gray-900 mb-2">Fair</h2>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">Visible wear and scratches. Fully functional with cosmetic imperfections.</p>
                <p class="text-xs text-gray-400">Battery health above <strong class="text-gray-600">70%</strong></p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-10">
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-3">Condition vs. Grade</h2>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    <strong class="text-gray-700">Condition</strong> — new, used, or refurbished — is declared by the
                    seller when they list an item.
                </p>
                <p class="text-sm text-gray-500 leading-relaxed">
                    <strong class="text-gray-700">Grade</strong> — A, B, C, or ungraded — is assigned only by an
                    Openbox verifier during physical inspection. Grades apply to used and refurbished items; new
                    items are always Grade A. Once assigned, a grade is locked and the seller can't change it.
                </p>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 mb-3">What gets checked</h2>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-start gap-2"><span class="text-brand-500 mt-0.5">✓</span> Screen, body, and cosmetic condition</li>
                    <li class="flex items-start gap-2"><span class="text-brand-500 mt-0.5">✓</span> Battery health and charging</li>
                    <li class="flex items-start gap-2"><span class="text-brand-500 mt-0.5">✓</span> All ports, buttons, and sensors</li>
                    <li class="flex items-start gap-2"><span class="text-brand-500 mt-0.5">✓</span> Serial number / IMEI verification</li>
                    <li class="flex items-start gap-2"><span class="text-brand-500 mt-0.5">✓</span> Full functional test before listing goes live</li>
                </ul>
            </div>
        </div>

        <div class="mt-14 bg-gray-900 rounded-md p-10 text-center">
            <h2 class="text-xl font-bold text-white mb-2">Selling something used?</h2>
            <p class="text-gray-400 text-sm mb-6">Get it graded and unlock buyer trust.</p>
            <x-button as="a" :href="Route::has('register') ? route('register') : '#'">Start Selling</x-button>
        </div>
    </div>
@endsection
