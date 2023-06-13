<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference' => 'PRDT-' . $this->resource->id,
            'name' => $this->resource->name,
            'quantityPlaquette' => $this->resource->quantityPlaquette,
            'quantityBoite' => $this->resource->quantityBoite,
            'quantityGellule' => $this->resource->quantityGellule,
            'priceBoite' => $this->resource->priceBoite,
            'pricePlaquette' => $this->resource->pricePlaquette,
            'priceGellule' => $this->resource->priceGellule,
            'numberGellule' => $this->resource->numberGellule,
            'numberPlaquette' => $this->resource->numberPlaquette,
            'datePeremption' => $this->resource->datePeremption,
            'libele' => $this->resource->libele
        ];
    }
}
