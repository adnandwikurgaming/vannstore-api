<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_available' => $this->is_active && $this->stock > 0,
            'is_active' => $this->is_active,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'seller' => new SellerResource($this->whenLoaded('seller')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
