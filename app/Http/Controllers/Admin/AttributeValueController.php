<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeValueRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\RedirectResponse;

class AttributeValueController extends Controller
{
    public function store(StoreAttributeValueRequest $request, Attribute $attribute): RedirectResponse
    {
        $attribute->values()->create($request->validated());

        return back()->with('status', 'Value added.');
    }

    public function destroy(Attribute $attribute, AttributeValue $value): RedirectResponse
    {
        $value->delete();

        return back()->with('status', 'Value removed.');
    }
}
