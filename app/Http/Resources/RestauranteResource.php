<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestauranteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id_restaurante,
            "restaurante" => $this->nombre_restaurante,
            "descripcion" => $this->descripcion_restaurante,
            "representante" => $this->usuario,
            "logo" => $this->logo,
            "status" => $this->status
        ];
    }
}
