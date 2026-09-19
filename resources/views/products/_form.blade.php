@php
    $selectedCategoryId = old('category_id', $product->category_id);
    $existingValues = $product->exists ? $product->attributeValues->keyBy('attribute_id') : collect();
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <x-card title="Basic Info">
            <div class="space-y-5">
                <x-input label="Product title" name="title" type="text" :value="old('title', $product->title)" />

                <div class="grid sm:grid-cols-2 gap-5">
                    <x-select
                        label="Category" name="category_id" placeholder="Select a category"
                        :options="$categories->pluck('name', 'id')" :selected="$selectedCategoryId"
                    />
                    <x-select
                        label="Brand (optional)" name="brand_id" placeholder="No brand"
                        :options="$brands->pluck('name', 'id')" :selected="old('brand_id', $product->brand_id)"
                    />
                </div>

                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1.5">Short description</label>
                    <textarea id="short_description" name="short_description" class="w-full">{!! old('short_description', $product->short_description) !!}</textarea>
                    @error('short_description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Full description</label>
                    <textarea id="description" name="description" class="w-full">{!! old('description', $product->description) !!}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <x-card title="Product Images &amp; Media">
            <div id="product-media-manager" class="space-y-4">
                {{-- Hidden State Inputs --}}
                <input type="hidden" name="primary_image_id" id="primary_image_id" value="{{ $product->exists && $product->primaryImage() ? $product->primaryImage()->id : '' }}">
                <input type="hidden" name="primary_image_index" id="primary_image_index" value="">
                <div id="delete-images-container"></div>

                {{-- Dropzone Header & Upload Input --}}
                <div class="border-2 border-dashed border-gray-300 hover:border-brand-500 rounded-xl p-6 text-center bg-gray-50/50 hover:bg-brand-50/20 transition-all cursor-pointer relative group" id="product-image-dropzone">
                    <input type="file" name="images[]" id="product_images_file_input" multiple accept="image/*" class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
                        <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-gray-800"><span class="text-brand-600 underline">Click to upload</span> or drag and drop photos</span>
                            <p class="text-xs text-gray-400 mt-0.5">PNG, JPG, WEBP up to 4MB each (Max 8 images)</p>
                        </div>
                    </div>
                </div>

                {{-- Interactive Cards Grid --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Product Gallery &amp; Main Thumbnail</span>
                        <span class="text-xs text-gray-400">Click ⭐ to set primary thumbnail</span>
                    </div>

                    <div id="product-images-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        {{-- Render Existing Saved Images (if editing) --}}
                        @if ($product->exists && $product->images->isNotEmpty())
                            @foreach ($product->images as $image)
                                <div class="js-image-card existing-image-card relative rounded-xl overflow-hidden border-2 {{ $image->is_primary ? 'border-brand-500 ring-2 ring-brand-500/20 bg-brand-50/20' : 'border-gray-200 bg-gray-50' }} group h-36 flex flex-col justify-between p-1.5 shadow-2xs transition-all" data-image-id="{{ $image->id }}">
                                    <div class="relative w-full h-full rounded-lg overflow-hidden bg-white">
                                        <img src="{{ $image->url() }}" alt="Product Image" class="w-full h-full object-cover">
                                        
                                        {{-- Top-Left Primary Badge --}}
                                        <div class="js-primary-badge absolute top-1.5 left-1.5 z-10 {{ $image->is_primary ? '' : 'hidden' }}">
                                            <span class="bg-amber-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                                ⭐ Primary
                                            </span>
                                        </div>

                                        {{-- Top-Right Always-Visible Red Delete Button --}}
                                        <button type="button" class="absolute top-1.5 right-1.5 z-20 w-7 h-7 bg-white hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-full flex items-center justify-center shadow-md transition-all active:scale-90 cursor-pointer" title="Delete Image" onclick="deleteExistingImage({{ $image->id }}, this)">
                                            <svg class="w-3.5 h-3.5 text-red-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>

                                        {{-- Action Overlay --}}
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 p-2 z-10">
                                            <button type="button" class="js-set-primary-btn px-2.5 py-1 text-[11px] font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-xs transition-transform transform active:scale-95 cursor-pointer" onclick="setPrimaryExisting({{ $image->id }}, this)">
                                                ⭐ Primary
                                            </button>
                                            <button type="button" class="px-2.5 py-1 text-[11px] font-bold text-red-600 bg-white hover:bg-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-lg shadow-xs transition-colors inline-flex items-center gap-1 cursor-pointer" title="Delete Image" onclick="deleteExistingImage({{ $image->id }}, this)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <p id="no-images-notice" class="text-xs text-gray-400 text-center py-6 border border-dashed border-gray-200 rounded-lg {{ ($product->exists && $product->images->isNotEmpty()) ? 'hidden' : '' }}">
                        No photos added yet. Upload photos above to preview cards.
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Attributes">
            <p class="text-sm text-gray-500 mb-4" id="attributes-empty" @if ($selectedCategoryId) style="display:none" @endif>
                Choose a category to see its attributes.
            </p>

            @foreach ($categories as $category)
                @php
                    $isCurrentCategory = (int) $selectedCategoryId === $category->id;
                    $groupedCategoryAttributes = $category->attributes->groupBy(fn ($a) => $a->group?->name ?? 'General Specifications');
                @endphp
                <div
                    data-category-attributes="{{ $category->id }}"
                    class="space-y-6"
                    @if (!$isCurrentCategory) style="display:none" @endif
                >
                    @forelse ($groupedCategoryAttributes as $groupName => $attributesInGroup)
                        <div class="space-y-3">
                            <div class="border-b border-gray-100 pb-1.5 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $groupName }}</h4>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4 items-start">
                                @foreach ($attributesInGroup as $attribute)
                                    <div>
                                        @php
                                            $attrLabel = $attribute->name . ($attribute->pivot?->is_required ? ' *' : '');
                                            $savedAttr = $existingValues->get($attribute->id);
                                            $savedVal = $savedAttr?->displayValue();
                                            $defaultTextVal = ($savedVal !== null && $savedVal !== '') ? $savedVal : ($attribute->unit ?? '');
                                        @endphp

                                        @if ($attribute->type->usesValueList())
                                            @php
                                                $selectedOptionId = $savedAttr?->attribute_value_id;
                                                if ($selectedOptionId === null && filled($attribute->unit)) {
                                                    $selectedOptionId = $attribute->values->firstWhere('value', $attribute->unit)?->id;
                                                }
                                            @endphp
                                            <x-select
                                                :label="$attrLabel"
                                                :name="'attributes['.$attribute->id.']'"
                                                :placeholder="'Select '.$attribute->name"
                                                :options="$attribute->values->pluck('value', 'id')"
                                                :selected="old('attributes.'.$attribute->id, $selectedOptionId)"
                                                :disabled="!$isCurrentCategory"
                                            />
                                        @else
                                            <x-input
                                                :label="$attrLabel"
                                                :name="'attributes['.$attribute->id.']'"
                                                type="text"
                                                :placeholder="$attribute->placeholder ?? $attribute->unit ?? ''"
                                                :value="old('attributes.'.$attribute->id, $defaultTextVal)"
                                                :disabled="!$isCurrentCategory"
                                            />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No attributes configured for this category.</p>
                    @endforelse
                </div>
            @endforeach
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Pricing &amp; Condition">
            <div class="space-y-5">
                <x-select
                    label="Condition" name="condition"
                    :options="collect(\App\Enums\ProductCondition::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])"
                    :selected="old('condition', $product->condition?->value ?? 'new')"
                />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="Price" name="price" type="number" step="0.01" :value="old('price', $product->price)" />
                    <x-input label="Compare-at price" name="compare_price" type="number" step="0.01" :value="old('compare_price', $product->compare_price)" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="SKU (optional)" name="sku" type="text" :value="old('sku', $product->sku)" />
                    <x-input label="Quantity" name="quantity" type="number" :value="old('quantity', $product->quantity ?? 1)" />
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_negotiable" value="1" @checked(old('is_negotiable', $product->is_negotiable)) class="w-4 h-4 rounded accent-brand-500">
                    Price is negotiable
                </label>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400">
                        Grade is assigned by an Openbox verifier during physical inspection and can't be set here.
                        This listing is currently <strong>{{ $product->grade?->label() ?? 'Ungraded' }}</strong>.
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Shipping">
            <div class="space-y-5">
                <x-input label="Weight (kg, optional)" name="weight" type="number" step="0.01" :value="old('weight', $product->weight)" />

                <x-select
                    label="Shipping" name="shipping_type"
                    :options="collect(\App\Enums\ShippingType::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])"
                    :selected="old('shipping_type', $product->shipping_type?->value ?? 'free')"
                />

                <div id="shipping_flat_rate_wrap" @if (old('shipping_type', $product->shipping_type?->value ?? 'free') !== 'flat_rate') style="display:none" @endif>
                    <x-input label="Flat rate amount" name="shipping_flat_rate" type="number" step="0.01" :value="old('shipping_flat_rate', $product->shipping_flat_rate)" />
                </div>
            </div>
        </x-card>

        <x-card title="SEO">
            <div class="space-y-5">
                <x-input label="Meta title" name="meta_title" type="text" :value="old('meta_title', $product->meta_title)" />
                <x-input label="Meta description" name="meta_description" type="text" :value="old('meta_description', $product->meta_description)" />
            </div>
        </x-card>

        <x-button type="submit" class="w-full justify-center">{{ $product->exists ? 'Save Changes' : 'Save as Draft' }}</x-button>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        overflow: hidden !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        background: #ffffff !important;
    }
    .note-editor.note-frame .note-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 8px !important;
    }
    .note-editor.note-frame .note-statusbar {
        background: #f8fafc !important;
        border-top: 1px solid #f1f5f9 !important;
    }
    .note-btn {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.375rem !important;
        color: #334155 !important;
        padding: 4px 8px !important;
        font-size: 12px !important;
    }
    .note-btn:hover, .note-btn:focus, .note-btn.active {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }
    .note-editable {
        background: #ffffff !important;
        font-family: inherit !important;
        color: #1e293b !important;
        font-size: 14px !important;
        line-height: 1.6 !important;
    }
    .note-placeholder {
        font-size: 14px !important;
        color: #94a3b8 !important;
    }
    .note-dropdown-menu {
        border-radius: 0.375rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        border: 1px solid #e2e8f0 !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<script>
    document.getElementById('category_id')?.addEventListener('change', function () {
        const selectedId = this.value;
        document.querySelectorAll('[data-category-attributes]').forEach((el) => {
            const isMatch = el.dataset.categoryAttributes === selectedId;
            el.style.display = isMatch ? '' : 'none';
            el.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !isMatch;
            });
        });
        document.getElementById('attributes-empty').style.display = selectedId ? 'none' : '';
    });

    document.getElementById('shipping_type')?.addEventListener('change', function () {
        document.getElementById('shipping_flat_rate_wrap').style.display = this.value === 'flat_rate' ? '' : 'none';
    });

    $(document).ready(function() {
        if (typeof $.fn.summernote !== 'undefined') {
            $('#short_description').summernote({
                placeholder: 'Add key highlights or bullet points...',
                tabsize: 2,
                height: 140,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'table']],
                    ['view', ['codeview']]
                ]
            });

            $('#description').summernote({
                placeholder: 'Add detailed product specifications, overview, and information...',
                tabsize: 2,
                height: 280,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
    });

    // Product Images Media Manager Script
    (function() {
        function initProductMediaManager() {
            const dropzone = document.getElementById('product-image-dropzone');
            const fileInput = document.getElementById('product_images_file_input');
            const grid = document.getElementById('product-images-grid');
            const notice = document.getElementById('no-images-notice');
            const primaryIdInput = document.getElementById('primary_image_id');
            const primaryIndexInput = document.getElementById('primary_image_index');
            const deleteContainer = document.getElementById('delete-images-container');

            if (!dropzone || !fileInput || !grid) return;

            let selectedFiles = []; // Holds array of newly selected File objects

            dropzone.addEventListener('click', (e) => {
                if (e.target !== fileInput) {
                    fileInput.click();
                }
            });

            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-brand-500', 'bg-brand-50/40');
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-brand-500', 'bg-brand-50/40');
                });
            });

            dropzone.addEventListener('drop', (e) => {
                if (e.dataTransfer.files && e.dataTransfer.files.length) {
                    handleNewFiles(Array.from(e.dataTransfer.files));
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length) {
                    handleNewFiles(Array.from(e.target.files));
                }
            });

            function handleNewFiles(newFiles) {
                const validImages = newFiles.filter(f => f.type && f.type.startsWith('image/'));
                if (!validImages.length) return;

                selectedFiles = [...selectedFiles, ...validImages].slice(0, 8);
                syncFileInput();
                renderNewFileCards();
            }

            function syncFileInput() {
                try {
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                } catch(e) {
                    console.warn('DataTransfer not fully supported:', e);
                }
            }

            function renderNewFileCards() {
                // Remove previous new image cards
                grid.querySelectorAll('.new-image-card').forEach(card => card.remove());

                selectedFiles.forEach((file, index) => {
                    const card = document.createElement('div');
                    card.className = 'js-image-card new-image-card relative rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50 group h-36 flex flex-col justify-between p-1.5 shadow-2xs transition-all';
                    card.dataset.newIndex = index;

                    const objectUrl = URL.createObjectURL(file);

                    card.innerHTML = `
                        <div class="relative w-full h-full rounded-lg overflow-hidden bg-white">
                            <img src="${objectUrl}" alt="New Photo" class="w-full h-full object-cover">
                            
                            <div class="js-primary-badge absolute top-1.5 left-1.5 z-10 hidden">
                                <span class="bg-amber-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                    ⭐ Primary
                                </span>
                            </div>

                            <button type="button" class="js-delete-top-btn absolute top-1.5 right-1.5 z-20 w-7 h-7 bg-white hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-full flex items-center justify-center shadow-md transition-all active:scale-90 cursor-pointer" title="Delete Image">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>

                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 p-2 z-10">
                                <button type="button" class="js-set-primary-new-btn px-2.5 py-1 text-[11px] font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-xs transition-transform transform active:scale-95 cursor-pointer">
                                    ⭐ Primary
                                </button>
                                <button type="button" class="js-delete-new-btn px-2.5 py-1 text-[11px] font-bold text-red-600 bg-white hover:bg-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-lg shadow-xs transition-colors inline-flex items-center gap-1 cursor-pointer" title="Delete Image">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    `;

                    // Primary button listener
                    card.querySelector('.js-set-primary-new-btn').addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        setPrimaryNew(index);
                    });

                    // Delete button listeners (both top button & overlay button if present)
                    card.querySelectorAll('.js-delete-top-btn, .js-delete-new-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            deleteNewImage(index);
                        });
                    });

                    grid.appendChild(card);
                });

                // If no primary is set yet, make the first item primary by default
                const hasExistingPrimary = grid.querySelector('.existing-image-card.border-brand-500');
                if (!hasExistingPrimary && primaryIndexInput.value === '' && selectedFiles.length > 0) {
                    setPrimaryNew(0);
                }

                updateNoticeVisibility();
            }

            window.setPrimaryExisting = function(imageId, btn) {
                primaryIdInput.value = imageId;
                primaryIndexInput.value = '';

                grid.querySelectorAll('.js-image-card').forEach(c => {
                    c.classList.remove('border-brand-500', 'ring-2', 'ring-brand-500/20', 'bg-brand-50/20');
                    c.classList.add('border-gray-200', 'bg-gray-50');
                    const badge = c.querySelector('.js-primary-badge');
                    if (badge) badge.classList.add('hidden');
                });

                const card = btn.closest('.js-image-card');
                if (card) {
                    card.classList.remove('border-gray-200', 'bg-gray-50');
                    card.classList.add('border-brand-500', 'ring-2', 'ring-brand-500/20', 'bg-brand-50/20');
                    const badge = card.querySelector('.js-primary-badge');
                    if (badge) badge.classList.remove('hidden');
                }
            };

            function setPrimaryNew(index) {
                primaryIndexInput.value = index;
                primaryIdInput.value = '';

                grid.querySelectorAll('.js-image-card').forEach(c => {
                    c.classList.remove('border-brand-500', 'ring-2', 'ring-brand-500/20', 'bg-brand-50/20');
                    c.classList.add('border-gray-200', 'bg-gray-50');
                    const badge = c.querySelector('.js-primary-badge');
                    if (badge) badge.classList.add('hidden');
                });

                const card = grid.querySelector(`.new-image-card[data-new-index="${index}"]`);
                if (card) {
                    card.classList.remove('border-gray-200', 'bg-gray-50');
                    card.classList.add('border-brand-500', 'ring-2', 'ring-brand-500/20', 'bg-brand-50/20');
                    const badge = card.querySelector('.js-primary-badge');
                    if (badge) badge.classList.remove('hidden');
                }
            }

            window.deleteExistingImage = function(imageId, btn) {
                const card = btn.closest('.js-image-card');
                if (!card) return;

                // Add hidden input to form
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'delete_images[]';
                hidden.value = imageId;
                deleteContainer.appendChild(hidden);

                const isPrimary = primaryIdInput.value == imageId || card.classList.contains('border-brand-500');

                card.remove();

                if (isPrimary) {
                    primaryIdInput.value = '';
                    const firstRemaining = grid.querySelector('.js-image-card');
                    if (firstRemaining) {
                        if (firstRemaining.classList.contains('existing-image-card')) {
                            setPrimaryExisting(firstRemaining.dataset.imageId, firstRemaining.querySelector('.js-set-primary-btn'));
                        } else if (firstRemaining.dataset.newIndex !== undefined) {
                            setPrimaryNew(parseInt(firstRemaining.dataset.newIndex));
                        }
                    }
                }

                updateNoticeVisibility();
            };

            function deleteNewImage(index) {
                selectedFiles.splice(index, 1);
                syncFileInput();

                if (primaryIndexInput.value == index) {
                    primaryIndexInput.value = '';
                } else if (primaryIndexInput.value > index) {
                    primaryIndexInput.value = primaryIndexInput.value - 1;
                }

                renderNewFileCards();
            }

            function updateNoticeVisibility() {
                const count = grid.querySelectorAll('.js-image-card').length;
                if (notice) {
                    notice.classList.toggle('hidden', count > 0);
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProductMediaManager);
        } else {
            initProductMediaManager();
        }
    })();
</script>
