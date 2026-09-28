<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->profile?->avatar ? MediaUrl::resolve($this->profile->avatar) : null,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
