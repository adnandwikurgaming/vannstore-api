<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Info publik penjual; email & data internal tidak ikut. @mixin \App\Models\User */
class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_name' => $this->store_name,
        ];
    }
}
