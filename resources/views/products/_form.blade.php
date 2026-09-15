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

        <x-card title="Media">
            <x-file-upload name="images" hint="Up to 8 images, JPG/PNG, 4MB each" multiple />

            @if ($product->exists && $product->images->isNotEmpty())
                <div class="grid grid-cols-4 gap-3 mt-4">
                    @foreach ($product->images as $image)
                        <div class="relative rounded-md overflow-hidden h-20 bg-gray-50">
                            <img src="{{ $image->url() }}" class="w-full h-full object-cover">
                            @if ($image->is_primary)
                                <span class="absolute top-1 left-1 bg-brand-500 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
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
</script>
