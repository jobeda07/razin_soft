<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'dob' => Carbon::parse($this->join_date)->format('d-m-Y'),
            'image' => $this->image ? config('app.url') . "/" .$this->image : '',
            'create_date_time' => $this->created_at->format('j F Y  g.i A'),
        ];
    }
}
