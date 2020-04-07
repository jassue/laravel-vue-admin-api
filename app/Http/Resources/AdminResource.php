<?php

namespace App\Http\Resources;

use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Admin\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'username'      => $this->username,
            'status'        => $this->status,
            'status_text'   => StatusEnum::$statusMap[$this->status],
            'roles'         => $this->roles,
            'permissions'   => $this->permissions,
            'is_actionable' => $this->id !== $request->user()->id && $this->id !== Admin::value('id') ? true : false,
            'created_at'    => Carbon::createFromTimeString($this->created_at)->format('Y-m-d H:i')
        ];
    }
}
