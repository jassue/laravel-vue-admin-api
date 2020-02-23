<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'permissions'   => $this->permissions->pluck('id'),
            'is_actionable' => $this->id !== 1 ? true : false,
            'created_at'    => Carbon::createFromTimeString($this->created_at)->format('Y-m-d H:i')
        ];
    }
}
