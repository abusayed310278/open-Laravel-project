<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_slug',
        'title',
        'route_name',
        'url_path',
        'icon',
        'icon_svg',
        'group_name',
        'permission_slug',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeForRole($query, string $roleSlug)
    {
        return $query->where('role_slug', $roleSlug);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
