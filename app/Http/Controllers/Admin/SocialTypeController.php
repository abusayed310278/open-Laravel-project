<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SocialTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|StreamedResponse
    {
        $query = SocialType::query()->ordered();

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('icon', 'like', "%{$search}%");
            });
        }

        // CSV Export support
        if ($request->query('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $socialTypes = $query->paginate(20)->withQueryString();

        return view('admin.social-types.index', compact('socialTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
            'icon_svg' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['order'] = $validated['order'] ?? (SocialType::max('order') + 1);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (SocialType::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$originalSlug}-{$count}";
            $count++;
        }

        SocialType::create($validated);

        return redirect()->route('admin.social-types.index')
            ->with('status', "Social type '{$validated['name']}' created successfully.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SocialType $socialType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
            'icon_svg' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['order'] = $validated['order'] ?? $socialType->order;
        $validated['is_active'] = $request->boolean('is_active', true);

        $socialType->update($validated);

        return redirect()->route('admin.social-types.index')
            ->with('status', "Social type '{$socialType->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SocialType $socialType): RedirectResponse
    {
        $name = $socialType->name;
        $socialType->delete();

        return redirect()->route('admin.social-types.index')
            ->with('status', "Social type '{$name}' deleted successfully.");
    }

    /**
     * Export social types to CSV.
     */
    private function exportCsv($socialTypes): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="social_types_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($socialTypes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Type', 'Icon', 'Order', 'Status', 'Created At']);

            foreach ($socialTypes as $type) {
                fputcsv($file, [
                    $type->id,
                    $type->name,
                    $type->icon,
                    $type->order,
                    $type->is_active ? 'Active' : 'Inactive',
                    $type->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
