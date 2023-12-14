<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id' => $this->id_usuario,
          'nombre' => $this->nombre,
          'apellidos' =>  $this->apellidos,
          'fecha_nacimiento' => $this->fecha_nacimiento,
          'correo' => $this->correo_electronico,
          'telefono' => $this->numero_telefono,
          'ajustes' => $this->ajustes,
          'rol' => $this->rol,
          'status' => $this->status
        ];
    }
}
